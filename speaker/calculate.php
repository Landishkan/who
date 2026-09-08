<?php
require_once __DIR__ . '/../config.php';

$test_id = intval($_GET['test_id'] ?? 0);
$calculated = false;
$test = null;
$session = null;
$theme_results = [];
$problem_theme = null;
$growth_data = null;
$error = '';

// Приоритеты тем
$theme_priority = [
    'bullying' => 1,
    'fraud' => 2,
    'etiquette' => 3,
    'fakes' => 3
];

$theme_names = [
    'bullying' => 'Буллинг',
    'fraud' => 'Мошенничество',
    'etiquette' => 'Сетевой этикет',
    'fakes' => 'Фейки'
];

try {
    $pdo = getDB();
    
    // Получаем информацию о тесте
 $stmt = $pdo->prepare("
    SELECT t.*, 
           s.id as session_id, 
           s.district, 
           s.institution, 
           s.age_group, 
           s.speaker_name, 
           s.format, 
           s.mode,
           s.initial_test_id,
           s.final_test_id,
           s.initial_status,
           s.final_status
    FROM tests t
    JOIN sessions s ON t.session_id = s.id
    WHERE t.id = ?
");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$test) {
        die("Тест не найден.");
    }
    
    if (!$test['is_active']) {
        die("Этот тест уже завершен.");
    }
    
    // Если нажали "Подсчитать"
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'calculate') {
        $pdo->beginTransaction();
        
        try {
            // Получаем все ответы для этого теста
            $stmt = $pdo->prepare("
                SELECT a.question_id, a.answer, q.theme, q.correct_answer
                FROM answers a
                JOIN questions q ON a.question_id = q.id
                WHERE a.test_id = ?
            ");
            $stmt->execute([$test_id]);
            $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($answers)) {
                throw new Exception("Нет ответов для подсчета.");
            }
            
            // Считаем результаты по темам
            $theme_stats = [];
            foreach ($answers as $answer) {
                $theme = $answer['theme'];
                if (!isset($theme_stats[$theme])) {
                    $theme_stats[$theme] = ['correct' => 0, 'total' => 0];
                }
                $theme_stats[$theme]['total']++;
                if ($answer['answer'] === $answer['correct_answer']) {
                    $theme_stats[$theme]['correct']++;
                }
            }
            
            // Вычисляем проценты
            foreach ($theme_stats as $theme => $stats) {
                $theme_results[$theme] = [
                    'correct' => $stats['correct'],
                    'total' => $stats['total'],
                    'percentage' => round(($stats['correct'] / $stats['total']) * 100),
                    'error_percentage' => round(100 - ($stats['correct'] / $stats['total']) * 100)
                ];
            }
            
            // Для НАЧАЛЬНОГО теста — определяем проблемную тему
            if ($test['type'] === 'initial') {
                $min_percentage = 101;
                $min_priority = 999;
                
                foreach ($theme_results as $theme => $data) {
                    $percentage = $data['percentage'];
                    $priority = $theme_priority[$theme] ?? 999;
                    
                    if ($percentage < $min_percentage || ($percentage === $min_percentage && $priority < $min_priority)) {
                        $min_percentage = $percentage;
                        $min_priority = $priority;
                        $problem_theme = $theme;
                    }
                }
            }
            
            // Для КОНЕЧНОГО теста — считаем прирост
            if ($test['type'] === 'final') {
                // Получаем результаты начального теста
                $stmt = $pdo->prepare("
                    SELECT a.question_id, a.answer, q.theme, q.correct_answer
                    FROM answers a
                    JOIN questions q ON a.question_id = q.id
                    WHERE a.test_id = ?
                ");
                $stmt->execute([$test['initial_test_id'] ?? 0]);
                $initial_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($initial_answers)) {
                    $initial_stats = [];
                    foreach ($initial_answers as $answer) {
                        $theme = $answer['theme'];
                        if (!isset($initial_stats[$theme])) {
                            $initial_stats[$theme] = ['correct' => 0, 'total' => 0];
                        }
                        $initial_stats[$theme]['total']++;
                        if ($answer['answer'] === $answer['correct_answer']) {
                            $initial_stats[$theme]['correct']++;
                        }
                    }
                    
                    $growth_data = ['by_theme' => [], 'total_growth' => 0, 'count' => 0];
                    
                    foreach ($theme_results as $theme => $final_data) {
                        if (isset($initial_stats[$theme])) {
                            $initial_percentage = round(($initial_stats[$theme]['correct'] / $initial_stats[$theme]['total']) * 100);
                            $growth = $final_data['percentage'] - $initial_percentage;
                            $growth_data['by_theme'][$theme] = [
                                'initial' => $initial_percentage,
                                'final' => $final_data['percentage'],
                                'growth' => $growth
                            ];
                            $growth_data['total_growth'] += $growth;
                            $growth_data['count']++;
                        }
                    }
                    
                    if ($growth_data['count'] > 0) {
                        $growth_data['average_growth'] = round($growth_data['total_growth'] / $growth_data['count']);
                    }
                }
            }
            
            // Обновляем сессию
            $update_fields = [];
            $update_params = [];
            
            if ($test['type'] === 'initial') {
                $update_fields[] = "initial_status = 'completed'";
                $update_fields[] = "problem_theme = :problem_theme";
                $update_params[':problem_theme'] = $problem_theme;
            } else {
                $update_fields[] = "final_status = 'completed'";
                if ($growth_data && isset($growth_data['average_growth'])) {
                    $update_fields[] = "growth_percentage = :growth";
                    $update_params[':growth'] = $growth_data['average_growth'];
                }
            }
            
            $update_params[':session_id'] = $test['session_id'];
            
            $sql = "UPDATE sessions SET " . implode(', ', $update_fields) . " WHERE id = :session_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_params);
            
            // Закрываем тест
            $stmt = $pdo->prepare("UPDATE tests SET is_active = 0, closed_at = NOW() WHERE id = ?");
            $stmt->execute([$test_id]);
            
            $pdo->commit();
            $calculated = true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Ошибка при подсчете: " . $e->getMessage();
        }
    }
    
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подсчет результатов | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .calculate-container { max-width: 1000px; margin: 30px auto; }
        .calculate-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 30px; margin-bottom: 20px; }
        .calculate-card h1 { font-family: 'Orbitron', monospace; color: #4bcde7; text-align: center; margin-bottom: 30px; font-size: 28px; }
        .test-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; padding: 20px; background: rgba(0, 0, 0, 0.3); border-radius: 8px; }
        .info-item { text-align: center; }
        .info-label { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.6); font-size: 14px; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; }
        .participants-count { text-align: center; margin: 30px 0; }
        .participants-count .number { font-family: 'Orbitron', monospace; font-size: 48px; color: #4bcde7; text-shadow: 0 0 20px rgba(75, 205, 235, 0.5); }
        .participants-count .label { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 18px; text-transform: uppercase; letter-spacing: 2px; }
        .calculate-btn { display: block; width: 100%; max-width: 400px; margin: 30px auto; padding: 18px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 8px; color: #fff; font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; }
        .calculate-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(40, 167, 69, 0.5); }
        .results-section { margin-top: 40px; }
        
        /* Проблемная тема (для начального теста) */
        .problem-theme-box { background: rgba(220, 53, 69, 0.15); border: 2px solid #dc3545; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 30px; }
        .problem-theme-box h2 { font-family: 'Orbitron', monospace; color: #ff6b6b; font-size: 24px; margin-bottom: 15px; }
        .problem-theme-box .theme-name { font-family: 'Orbitron', monospace; color: #fff; font-size: 32px; margin: 15px 0; text-shadow: 0 0 20px rgba(255, 107, 107, 0.5); }
        .problem-theme-box .recommendation { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.9); font-size: 18px; margin-top: 15px; }
        
        /* Прирост знаний (для конечного теста) */
        .growth-hero { background: rgba(40, 167, 69, 0.15); border: 2px solid #28a745; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 30px; }
        .growth-hero h2 { font-family: 'Orbitron', monospace; color: #28a745; font-size: 24px; margin-bottom: 20px; }
        .growth-hero .growth-number { font-family: 'Orbitron', monospace; font-size: 64px; color: #28a745; text-shadow: 0 0 30px rgba(40, 167, 69, 0.6); margin: 20px 0; }
        .growth-hero .growth-label { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.8); font-size: 20px; text-transform: uppercase; letter-spacing: 2px; }
        .growth-hero .growth-message { font-family: 'Rajdhani', sans-serif; color: #fff; font-size: 18px; margin-top: 15px; }
        
        .theme-results { display: grid; gap: 15px; margin-bottom: 30px; }
        .theme-card { background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px; border-left: 4px solid #4bcde7; }
        .theme-card.problem { border-left-color: #dc3545; background: rgba(220, 53, 69, 0.1); }
        .theme-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .theme-name-small { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 16px; }
        .theme-card.problem .theme-name-small { color: #ff6b6b; }
        .theme-percentage { font-family: 'Orbitron', monospace; font-size: 24px; color: #fff; }
        .theme-card.problem .theme-percentage { color: #ff6b6b; }
        .progress-bar { height: 8px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; overflow: hidden; margin-bottom: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4bcde7 0%, #254883 100%); transition: width 1s ease; }
        .theme-card.problem .progress-fill { background: linear-gradient(90deg, #dc3545 0%, #ff6b6b 100%); }
        .theme-stats { font-family: 'Roboto', sans-serif; color: rgba(255, 255, 255, 0.6); font-size: 14px; }
        
        .growth-details { background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 12px; padding: 25px; margin-top: 30px; }
        .growth-details h3 { font-family: 'Orbitron', monospace; color: #28a745; font-size: 20px; margin-bottom: 20px; text-align: center; }
        .growth-by-theme { display: grid; gap: 10px; }
        .growth-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(0, 0, 0, 0.3); border-radius: 6px; }
        .growth-item .theme { font-family: 'Rajdhani', sans-serif; color: #fff; font-size: 16px; }
        .growth-item .values { font-family: 'Roboto', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 14px; }
        .growth-item .growth-value { font-family: 'Orbitron', monospace; font-size: 18px; color: #28a745; }
        .growth-item .growth-value.negative { color: #dc3545; }
        
        .close-btn { display: block; width: 100%; max-width: 400px; margin: 30px auto; padding: 18px; background: rgba(255, 255, 255, 0.1); border: 2px solid #4bcde7; border-radius: 8px; color: #4bcde7; font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; text-decoration: none; text-align: center; }
        .close-btn:hover { background: rgba(75, 205, 235, 0.2); transform: translateY(-2px); }
        .error-box { background: rgba(220, 53, 69, 0.15); border: 1px solid #dc3545; border-radius: 8px; padding: 20px; color: #ff6b6b; font-family: 'Rajdhani', sans-serif; font-size: 16px; text-align: center; margin-bottom: 20px; }
        .warning-box { background: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; border-radius: 8px; padding: 20px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 16px; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Подсчет результатов</p>
            </div>
            <nav class="header-nav">
                <a href="index.php" class="nav-button">
                    <i class="fas fa-arrow-left"></i> В панель
                </a>
            </nav>
        </header>
        
        <main class="main-content calculate-container animate__animated animate__fadeIn">
            <div class="calculate-card">
                <h1><i class="fas fa-calculator"></i> <?= $test['type'] === 'initial' ? 'Начальный тест' : 'Конечный тест' ?></h1>
                
                <div class="test-info">
                    <div class="info-item">
                        <div class="info-label">Учреждение</div>
                        <div class="info-value"><?= e($test['institution']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Район</div>
                        <div class="info-value"><?= e($test['district']) ?></div>
                    </div>
                    <?php if ($test['age_group']): ?>
                    <div class="info-item">
                        <div class="info-label">Возраст</div>
                        <div class="info-value"><?= e($test['age_group']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <div class="info-label">Формат</div>
                        <div class="info-value"><?= e($test['format']) ?></div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-box">
                        <i class="fas fa-exclamation-triangle"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$calculated): ?>
                    <div class="participants-count">
                        <div class="number"><?= $test['participants_count'] ?></div>
                        <div class="label">участников ответили</div>
                    </div>
                    
                    <?php if ($test['participants_count'] === 0): ?>
                        <div class="error-box">
                            <i class="fas fa-info-circle"></i> Пока никто не прошел тест. Дождитесь участников.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="calculate">
                            <button type="submit" class="calculate-btn">
                                <i class="fas fa-chart-bar"></i> Подсчитать результат
                            </button>
                        </form>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="results-section">
                        
                        <?php if ($test['type'] === 'initial'): ?>
                            <!-- НАЧАЛЬНЫЙ ТЕСТ: показываем проблемную тему -->
                            <div class="problem-theme-box">
                                <h2><i class="fas fa-exclamation-triangle"></i> Проблемная тема</h2>
                                <div class="theme-name"><?= $theme_names[$problem_theme] ?? $problem_theme ?></div>
                                <div class="recommendation">
                                    Начните мероприятие с этой темы. Здесь у аудитории наибольшие пробелы в знаниях.
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- КОНЕЧНЫЙ ТЕСТ: показываем прирост знаний -->
                            <?php if ($growth_data && isset($growth_data['average_growth'])): ?>
                                <div class="growth-hero">
                                    <h2><i class="fas fa-chart-line"></i> Прирост знаний</h2>
                                    <div class="growth-number">+<?= $growth_data['average_growth'] ?>%</div>
                                    <div class="growth-label">средний прирост по всем темам</div>
                                    <div class="growth-message">
                                        <?php if ($growth_data['average_growth'] > 30): ?>
                                            🎉 Отличный результат! Аудитория значительно улучшила знания.
                                        <?php elseif ($growth_data['average_growth'] > 10): ?>
                                            👍 Хороший результат! Заметный прогресс в знаниях.
                                        <?php elseif ($growth_data['average_growth'] > 0): ?>
                                            📈 Небольшой, но положительный прирост.
                                        <?php else: ?>
                                            📊 Прирост минимальный. Возможно, стоит повторить материал.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="warning-box">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Данные начального теста не найдены. Невозможно рассчитать прирост.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Детализация по темам (для обоих типов) -->
                        <h3 style="font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 20px; text-align: center;">Детализация по темам</h3>
                        
                        <div class="theme-results">
                            <?php foreach ($theme_results as $theme => $data): ?>
                                <div class="theme-card <?= ($test['type'] === 'initial' && $theme === $problem_theme) ? 'problem' : '' ?>">
                                    <div class="theme-header">
                                        <div class="theme-name-small"><?= $theme_names[$theme] ?></div>
                                        <div class="theme-percentage"><?= $data['percentage'] ?>%</div>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $data['percentage'] ?>%"></div>
                                    </div>
                                    <div class="theme-stats">
                                        Правильных ответов: <?= $data['correct'] ?> из <?= $data['total'] ?> 
                                        (ошибок: <?= $data['error_percentage'] ?>%)
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Детальный прирост по темам (только для конечного теста) -->
                        <?php if ($test['type'] === 'final' && $growth_data && !empty($growth_data['by_theme'])): ?>
                            <div class="growth-details">
                                <h3><i class="fas fa-chart-bar"></i> Прирост по каждой теме</h3>
                                
                                <div class="growth-by-theme">
                                    <?php foreach ($growth_data['by_theme'] as $theme => $data): ?>
                                        <div class="growth-item">
                                            <div class="theme"><?= $theme_names[$theme] ?></div>
                                            <div class="values">
                                                <?= $data['initial'] ?>% → <?= $data['final'] ?>%
                                            </div>
                                            <div class="growth-value <?= $data['growth'] < 0 ? 'negative' : '' ?>">
                                                <?= $data['growth'] > 0 ? '+' : '' ?><?= $data['growth'] ?>%
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <a href="index.php" class="close-btn">
                            <i class="fas fa-check"></i> Завершить и вернуться в панель
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Подсчет результатов</div>
            <div class="footer-links">
                <a href="index.php" class="footer-link">Панель спикера</a>
            </div>
        </footer>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>