<?php
require_once __DIR__ . '/../config.php';

$session_id = intval($_GET['session_id'] ?? 0);
$calculated = false;
$session = null;
$questions = [];
$theme_results = [];
$problem_theme = null;
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
    
    // Получаем информацию о сессии
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND mode = 'offline'");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        die("Офлайн-сессия не найдена.");
    }
    
    if ($session['initial_status'] === 'completed') {
        die("Эта сессия уже завершена.");
    }
    
    // Получаем офлайн-вопросы
    $stmt = $pdo->prepare("SELECT id, text, theme, correct_answer FROM questions WHERE test_type = 'offline' ORDER BY theme, id");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Если нажали "Подсчитать"
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'calculate') {
        $verdicts = $_POST['verdicts'] ?? [];
        
        if (empty($verdicts)) {
            $error = "Не получены ответы. Пожалуйста, оцените каждый вопрос.";
        } else {
            $pdo->beginTransaction();
            
            try {
                // Сохраняем ответы в БД
                $stmt = $pdo->prepare("INSERT INTO offline_results (session_id, question_id, verdict) VALUES (?, ?, ?)");
                
                foreach ($verdicts as $question_id => $verdict) {
                    if (!in_array($verdict, ['majority_agree', 'fifty_fifty', 'majority_disagree'])) {
                        continue;
                    }
                    $stmt->execute([$session_id, intval($question_id), $verdict]);
                }
                
                // Считаем результаты по темам
                // majority_agree = 80% согласны, fifty_fifty = 50%, majority_disagree = 20%
                $verdict_percentages = [
                    'majority_agree' => 80,
                    'fifty_fifty' => 50,
                    'majority_disagree' => 20
                ];
                
                $theme_stats = [];
                foreach ($questions as $q) {
                    $theme = $q['theme'];
                    $verdict = $verdicts[$q['id']] ?? null;
                    
                    if (!$verdict || !isset($verdict_percentages[$verdict])) {
                        continue;
                    }
                    
                    if (!isset($theme_stats[$theme])) {
                        $theme_stats[$theme] = ['correct_sum' => 0, 'count' => 0];
                    }
                    
                    $agree_percentage = $verdict_percentages[$verdict];
                    
                    // Если правильный ответ "agree" → процент правильных = процент согласных
                    // Если правильный ответ "disagree" → процент правильных = 100 - процент согласных
                    if ($q['correct_answer'] === 'agree') {
                        $theme_stats[$theme]['correct_sum'] += $agree_percentage;
                    } else {
                        $theme_stats[$theme]['correct_sum'] += (100 - $agree_percentage);
                    }
                    $theme_stats[$theme]['count']++;
                }
                
                // Вычисляем средние проценты по темам
                foreach ($theme_stats as $theme => $stats) {
                    if ($stats['count'] > 0) {
                        $percentage = round($stats['correct_sum'] / $stats['count']);
                        $theme_results[$theme] = [
                            'percentage' => $percentage,
                            'error_percentage' => 100 - $percentage,
                            'count' => $stats['count']
                        ];
                    }
                }
                
                // Определяем проблемную тему
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
                
                // Обновляем сессию
                $stmt = $pdo->prepare("
                    UPDATE sessions 
                    SET initial_status = 'completed', problem_theme = ?
                    WHERE id = ?
                ");
                $stmt->execute([$problem_theme, $session_id]);
                
                $pdo->commit();
                $calculated = true;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Ошибка при подсчете: " . $e->getMessage();
            }
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
    <title>Офлайн-калькулятор | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .calculator-container { max-width: 1000px; margin: 30px auto; }
        .calculator-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 30px; margin-bottom: 20px; }
        .calculator-card h1 { font-family: 'Orbitron', monospace; color: #4bcde7; text-align: center; margin-bottom: 30px; font-size: 28px; }
        .session-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; padding: 20px; background: rgba(0, 0, 0, 0.3); border-radius: 8px; }
        .info-item { text-align: center; }
        .info-label { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.6); font-size: 14px; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; }
        .instruction-box { background: rgba(75, 205, 235, 0.1); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 8px; padding: 20px; margin-bottom: 30px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 16px; }
        .instruction-box h3 { color: #4bcde7; margin-bottom: 10px; font-family: 'Orbitron', monospace; font-size: 16px; }
        .instruction-box ol { margin-left: 20px; }
        .instruction-box li { margin-bottom: 8px; }
        .question-block { background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #4bcde7; }
        .question-theme { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .question-text { font-family: 'Rajdhani', sans-serif; font-size: 20px; font-weight: 600; color: #fff; margin-bottom: 20px; line-height: 1.4; }
        .verdict-buttons { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .verdict-btn { padding: 15px; background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(75, 205, 235, 0.3); border-radius: 8px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-align: center; }
        .verdict-btn:hover { background: rgba(75, 205, 235, 0.2); border-color: #4bcde7; }
        .verdict-btn.selected { background: #4bcde7; color: #000; border-color: #4bcde7; }
        .verdict-btn input { display: none; }
        .calculate-btn { display: block; width: 100%; max-width: 400px; margin: 30px auto; padding: 18px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 8px; color: #fff; font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; }
        .calculate-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(40, 167, 69, 0.5); }
        .calculate-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .results-section { margin-top: 40px; }
        .problem-theme-box { background: rgba(220, 53, 69, 0.15); border: 2px solid #dc3545; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 30px; }
        .problem-theme-box h2 { font-family: 'Orbitron', monospace; color: #ff6b6b; font-size: 24px; margin-bottom: 15px; }
        .problem-theme-box .theme-name { font-family: 'Orbitron', monospace; color: #fff; font-size: 32px; margin: 15px 0; text-shadow: 0 0 20px rgba(255, 107, 107, 0.5); }
        .problem-theme-box .recommendation { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.9); font-size: 18px; margin-top: 15px; }
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
                <p class="tagline">Офлайн-калькулятор</p>
            </div>
            <nav class="header-nav">
                <a href="index.php" class="nav-button">
                    <i class="fas fa-arrow-left"></i> В панель
                </a>
            </nav>
        </header>
        
        <main class="main-content calculator-container animate__animated animate__fadeIn">
            <div class="calculator-card">
                <h1><i class="fas fa-calculator"></i> Офлайн-опрос</h1>
                
                <div class="session-info">
                    <div class="info-item">
                        <div class="info-label">Учреждение</div>
                        <div class="info-value"><?= e($session['institution']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Район</div>
                        <div class="info-value"><?= e($session['district']) ?></div>
                    </div>
                    <?php if ($session['age_group']): ?>
                    <div class="info-item">
                        <div class="info-label">Возраст</div>
                        <div class="info-value"><?= e($session['age_group']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <div class="info-label">Формат</div>
                        <div class="info-value"><?= e($session['format']) ?></div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-box">
                        <i class="fas fa-exclamation-triangle"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$calculated): ?>
                    <div class="instruction-box">
                        <h3><i class="fas fa-info-circle"></i> Как проводить опрос:</h3>
                        <ol>
                            <li>Зачитывайте вопрос вслух аудитории</li>
                            <li>Попросите поднять руку тех, кто <strong>согласен</strong> с утверждением</li>
                            <li>Визуально оцените реакцию зала</li>
                            <li>Выберите один из трёх вариантов ниже</li>
                            <li>Переходите к следующему вопросу</li>
                            <li>После всех вопросов нажмите "Подсчитать результат"</li>
                        </ol>
                    </div>
                    
                    <form method="POST" action="" id="calculatorForm">
                        <?php 
                        $current_theme = null;
                        foreach ($questions as $q): 
                            if ($current_theme !== $q['theme']):
                                $current_theme = $q['theme'];
                        ?>
                            <div style="margin-top: 30px; margin-bottom: 20px;">
                                <div style="font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; text-transform: uppercase; border-bottom: 2px solid rgba(75, 205, 235, 0.3); padding-bottom: 10px;">
                                    <?= $theme_names[$q['theme']] ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="question-block">
                            <div class="question-text"><?= e($q['text']) ?></div>
                            <div class="verdict-buttons">
                                <label class="verdict-btn">
                                    <input type="radio" name="verdicts[<?= $q['id'] ?>]" value="majority_agree" required>
                                    <i class="fas fa-users"></i> Большинство согласны
                                </label>
                                <label class="verdict-btn">
                                    <input type="radio" name="verdicts[<?= $q['id'] ?>]" value="fifty_fifty" required>
                                    <i class="fas fa-balance-scale"></i> 50/50
                                </label>
                                <label class="verdict-btn">
                                    <input type="radio" name="verdicts[<?= $q['id'] ?>]" value="majority_disagree" required>
                                    <i class="fas fa-users-slash"></i> Большинство не согласны
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="calculate-btn" name="action" value="calculate">
                            <i class="fas fa-chart-bar"></i> Подсчитать результат
                        </button>
                    </form>
                    
                <?php else: ?>
                    <div class="results-section">
                        <div class="problem-theme-box">
                            <h2><i class="fas fa-exclamation-triangle"></i> Проблемная тема</h2>
                            <div class="theme-name"><?= $theme_names[$problem_theme] ?? $problem_theme ?></div>
                            <div class="recommendation">
                                Начните мероприятие с этой темы. Здесь у аудитории наибольшие пробелы в знаниях.
                            </div>
                        </div>
                        
                        <h3 style="font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 20px; text-align: center;">Детализация по темам</h3>
                        
                        <div class="theme-results">
                            <?php foreach ($theme_results as $theme => $data): ?>
                                <div class="theme-card <?= $theme === $problem_theme ? 'problem' : '' ?>">
                                    <div class="theme-header">
                                        <div class="theme-name-small"><?= $theme_names[$theme] ?></div>
                                        <div class="theme-percentage"><?= $data['percentage'] ?>%</div>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $data['percentage'] ?>%"></div>
                                    </div>
                                    <div class="theme-stats">
                                        Правильных ответов: примерно <?= $data['percentage'] ?>% 
                                        (ошибок: <?= $data['error_percentage'] ?>%)
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <a href="index.php" class="close-btn">
                            <i class="fas fa-check"></i> Завершить и вернуться в панель
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Офлайн-калькулятор</div>
            <div class="footer-links">
                <a href="index.php" class="footer-link">Панель спикера</a>
            </div>
        </footer>
    </div>
    
    <script>
        // Подсветка выбранных кнопок
        document.querySelectorAll('.verdict-btn input').forEach(input => {
            input.addEventListener('change', function() {
                const parent = this.closest('.verdict-buttons');
                parent.querySelectorAll('.verdict-btn').forEach(btn => btn.classList.remove('selected'));
                this.closest('.verdict-btn').classList.add('selected');
            });
        });
    </script>
    <script src="../js/script.js"></script>
</body>
</html>