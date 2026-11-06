<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/check-auth.php';

$session_id = intval($_GET['session_id'] ?? 0);
$error = '';
$success_msg = '';

// Приоритеты тем и названия
$theme_priority = ['bullying' => 1, 'fraud' => 2, 'etiquette' => 3, 'fakes' => 3];
$theme_names = ['bullying' => 'Буллинг', 'fraud' => 'Мошенничество', 'etiquette' => 'Сетевой этикет', 'fakes' => 'Фейки'];

// Процентные значения для кнопок
$verdict_percentages = ['pct_0' => 0, 'pct_25' => 25, 'pct_50' => 50, 'pct_75' => 75, 'pct_95' => 95];

try {
    $pdo = getDB();
    
    // Получаем информацию о сессии
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND mode = 'offline'");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        die("Офлайн-сессия не найдена.");
    }
    
    // Определяем тип теста
    $is_fake_hunter = ($session['test_type'] ?? 'general') === 'fake_hunter';
    $test_prefix = $is_fake_hunter ? 'fake_hunter' : 'general';
    
    // Определяем статусы этапов
    $initial_done = ($session['initial_status'] === 'completed');
    $final_done = ($session['final_status'] === 'completed');
    
    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $verdicts = $_POST['verdicts'] ?? [];
        $phase = $_POST['phase'] ?? 'initial';
        
        if (empty($verdicts)) {
            $error = "Не получены ответы. Пожалуйста, оцените каждый вопрос.";
        } else {
            $pdo->beginTransaction();
            try {
                // Сохраняем ответы в БД с указанием фазы
                $stmt = $pdo->prepare("INSERT INTO offline_results (session_id, question_id, verdict, phase) VALUES (?, ?, ?, ?)");
                foreach ($verdicts as $question_id => $verdict) {
                    if (!array_key_exists($verdict, $verdict_percentages)) continue;
                    $stmt->execute([$session_id, intval($question_id), $verdict, $phase]);
                }
                
                // Считаем результаты для текущей фазы
                $theme_stats = calculateThemeStats($pdo, $session_id, $phase, $test_prefix);
                
                if ($phase === 'initial') {
                    // Для начального этапа определяем проблемную тему (только для general)
                    $problem_theme = null;
                    if (!$is_fake_hunter) {
                        $problem_theme = findProblemTheme($theme_stats);
                    }
                    
                    // Сохраняем результаты начального этапа в JSON (чтобы потом сравнить с финальным)
                    $initial_scores_json = json_encode($theme_stats);
                    
                    $update_fields = ["initial_status = 'completed'", "initial_scores = :initial_scores"];
                    $update_params = [':initial_scores' => $initial_scores_json, ':session_id' => $session_id];
                    
                    if (!$is_fake_hunter && $problem_theme) {
                        $update_fields[] = "problem_theme = :problem_theme";
                        $update_params[':problem_theme'] = $problem_theme;
                    }
                    
                    $sql = "UPDATE sessions SET " . implode(', ', $update_fields) . " WHERE id = :session_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($update_params);
                    
                    $success_msg = "✅ Начальный опрос завершен! Теперь проведите лекцию и переходите к конечному опросу.";
                    
                } elseif ($phase === 'final') {
                    // Для конечного этапа считаем прирост
                    $initial_scores = json_decode($session['initial_scores'] ?? '{}', true);
                    $growth_data = calculateGrowth($theme_stats, $initial_scores);
                    $avg_growth = $growth_data['average_growth'] ?? 0;
                    
                    $update_fields = ["final_status = 'completed'", "growth_percentage = :growth"];
                    $update_params = [':growth' => $avg_growth, ':session_id' => $session_id];
                    
                    $sql = "UPDATE sessions SET " . implode(', ', $update_fields) . " WHERE id = :session_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($update_params);
                    
                    $success_msg = "🎉 Конечный опрос завершен! Прирост знаний: +{$avg_growth}%";
                }
                
                $pdo->commit();
                
                // Перезагружаем данные сессии после обновления
                $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
                $stmt->execute([$session_id]);
                $session = $stmt->fetch(PDO::FETCH_ASSOC);
                $initial_done = ($session['initial_status'] === 'completed');
                $final_done = ($session['final_status'] === 'completed');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Ошибка при подсчете: " . $e->getMessage();
            }
        }
    }
    
    // Загружаем вопросы для отображения
    $initial_questions = [];
    $final_questions = [];
    
    if (!$initial_done) {
        $stmt = $pdo->prepare("SELECT id, text, theme FROM questions WHERE test_type = ? ORDER BY theme, id");
        $stmt->execute([$test_prefix . '_offline_initial']);
        $initial_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if ($initial_done && !$final_done) {
        $stmt = $pdo->prepare("SELECT id, text, theme FROM questions WHERE test_type = ? ORDER BY theme, id");
        $stmt->execute([$test_prefix . '_offline_final']);
        $final_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Загружаем результаты начального этапа для отображения
        $initial_results = calculateThemeStats($pdo, $session_id, 'initial', $test_prefix);
    }
    
    if ($final_done) {
        // Загружаем оба результата для финального отчета
        $initial_results = calculateThemeStats($pdo, $session_id, 'initial', $test_prefix);
        $final_results = calculateThemeStats($pdo, $session_id, 'final', $test_prefix);
        $growth_data = calculateGrowth($final_results, $initial_results);
    }
    
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

function calculateThemeStats($pdo, $session_id, $phase, $test_prefix) {
    global $verdict_percentages;
    
    $stmt = $pdo->prepare("
        SELECT o.question_id, o.verdict, q.theme, q.correct_answer 
        FROM offline_results o 
        JOIN questions q ON o.question_id = q.id 
        WHERE o.session_id = ? AND o.phase = ? AND q.test_type LIKE ?
    ");
    $stmt->execute([$session_id, $phase, $test_prefix . '%']);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $theme_stats = [];
    foreach ($answers as $a) {
        $theme = $a['theme'];
        $verdict = $a['verdict'];
        if (!isset($verdict_percentages[$verdict])) continue;
        
        if (!isset($theme_stats[$theme])) {
            $theme_stats[$theme] = ['correct_sum' => 0, 'count' => 0];
        }
        
        $agree_pct = $verdict_percentages[$verdict];
        if ($a['correct_answer'] === 'agree') {
            $theme_stats[$theme]['correct_sum'] += $agree_pct;
        } else {
            $theme_stats[$theme]['correct_sum'] += (100 - $agree_pct);
        }
        $theme_stats[$theme]['count']++;
    }
    
    $results = [];
    foreach ($theme_stats as $theme => $stats) {
        if ($stats['count'] > 0) {
            $pct = round($stats['correct_sum'] / $stats['count']);
            $results[$theme] = ['percentage' => $pct, 'error_percentage' => 100 - $pct, 'count' => $stats['count']];
        }
    }
    return $results;
}

function findProblemTheme($theme_stats) {
    global $theme_priority;
    $min_pct = 101;
    $min_priority = 999;
    $problem = null;
    foreach ($theme_stats as $theme => $data) {
        $pct = $data['percentage'];
        $priority = $theme_priority[$theme] ?? 999;
        if ($pct < $min_pct || ($pct === $min_pct && $priority < $min_priority)) {
            $min_pct = $pct;
            $min_priority = $priority;
            $problem = $theme;
        }
    }
    return $problem;
}

function calculateGrowth($final_stats, $initial_stats) {
    $growth = ['by_theme' => [], 'total_growth' => 0, 'count' => 0];
    foreach ($final_stats as $theme => $final_data) {
        if (isset($initial_stats[$theme])) {
            $initial_pct = $initial_stats[$theme]['percentage'];
            $final_pct = $final_data['percentage'];
            $diff = $final_pct - $initial_pct;
            $growth['by_theme'][$theme] = ['initial' => $initial_pct, 'final' => $final_pct, 'growth' => $diff];
            $growth['total_growth'] += $diff;
            $growth['count']++;
        }
    }
    if ($growth['count'] > 0) {
        $growth['average_growth'] = round($growth['total_growth'] / $growth['count']);
    } else {
        $growth['average_growth'] = 0;
    }
    return $growth;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Офлайн-калькулятор | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
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
        
        /* Статусы этапов */
        .phase-status { display: flex; gap: 20px; margin-bottom: 30px; justify-content: center; flex-wrap: wrap; }
        .phase-badge { padding: 10px 20px; border-radius: 8px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 16px; text-transform: uppercase; border: 2px solid; }
        .phase-badge.done { background: rgba(40, 167, 69, 0.2); color: #28a745; border-color: #28a745; }
        .phase-badge.pending { background: rgba(255, 193, 7, 0.2); color: #ffc107; border-color: #ffc107; }
        
        .instruction-box { background: rgba(75, 205, 235, 0.1); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 8px; padding: 20px; margin-bottom: 30px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 16px; }
        .instruction-box h3 { color: #4bcde7; margin-bottom: 10px; font-family: 'Orbitron', monospace; font-size: 16px; }
        .instruction-box ol { margin-left: 20px; }
        .instruction-box li { margin-bottom: 8px; }
        
        .phase-section { margin-bottom: 40px; padding: 20px; border: 1px solid rgba(75, 205, 235, 0.2); border-radius: 12px; background: rgba(0, 0, 0, 0.2); }
        .phase-header { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .phase-header i { color: #28a745; }
        
        .question-block { background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #4bcde7; }
        .question-text { font-family: 'Rajdhani', sans-serif; font-size: 20px; font-weight: 600; color: #fff; margin-bottom: 20px; line-height: 1.4; }
        .verdict-buttons { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
        .verdict-btn { padding: 15px; background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(75, 205, 235, 0.3); border-radius: 8px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-align: center; }
        .verdict-btn:hover { background: rgba(75, 205, 235, 0.2); border-color: #4bcde7; }
        .verdict-btn.selected { background: #4bcde7; color: #000; border-color: #4bcde7; }
        .verdict-btn input { display: none; }
        
        .calculate-btn { display: block; width: 100%; max-width: 400px; margin: 30px auto; padding: 18px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 8px; color: #fff; font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; }
        .calculate-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(40, 167, 69, 0.5); }
        
        .results-section { margin-top: 40px; }
        .problem-theme-box { background: rgba(220, 53, 69, 0.15); border: 2px solid #dc3545; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 30px; }
        .problem-theme-box h2 { font-family: 'Orbitron', monospace; color: #ff6b6b; font-size: 24px; margin-bottom: 15px; }
        .problem-theme-box .theme-name { font-family: 'Orbitron', monospace; color: #fff; font-size: 32px; margin: 15px 0; }
        
        .growth-hero { background: rgba(40, 167, 69, 0.15); border: 2px solid #28a745; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 30px; }
        .growth-hero h2 { font-family: 'Orbitron', monospace; color: #28a745; font-size: 24px; margin-bottom: 20px; }
        .growth-hero .growth-number { font-family: 'Orbitron', monospace; font-size: 64px; color: #28a745; text-shadow: 0 0 30px rgba(40, 167, 69, 0.6); margin: 20px 0; }
        
        .theme-results { display: grid; gap: 15px; margin-bottom: 30px; }
        .theme-card { background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px; border-left: 4px solid #4bcde7; }
        .theme-card.problem { border-left-color: #dc3545; background: rgba(220, 53, 69, 0.1); }
        .theme-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .theme-name-small { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 16px; }
        .theme-card.problem .theme-name-small { color: #ff6b6b; }
        .theme-percentage { font-family: 'Orbitron', monospace; font-size: 24px; color: #fff; }
        .progress-bar { height: 8px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; overflow: hidden; margin-bottom: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4bcde7 0%, #254883 100%); }
        .theme-card.problem .progress-fill { background: linear-gradient(90deg, #dc3545 0%, #ff6b6b 100%); }
        
        .growth-details { background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 12px; padding: 25px; margin-top: 30px; }
        .growth-details h3 { font-family: 'Orbitron', monospace; color: #28a745; font-size: 20px; margin-bottom: 20px; text-align: center; }
        .growth-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(0, 0, 0, 0.3); border-radius: 6px; margin-bottom: 10px; }
        .growth-item .theme { font-family: 'Rajdhani', sans-serif; color: #fff; font-size: 16px; }
        .growth-item .growth-value { font-family: 'Orbitron', monospace; font-size: 18px; color: #28a745; }
        .growth-item .growth-value.negative { color: #dc3545; }
        
        .close-btn { display: block; width: 100%; max-width: 400px; margin: 30px auto; padding: 18px; background: rgba(255, 255, 255, 0.1); border: 2px solid #4bcde7; border-radius: 8px; color: #4bcde7; font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; text-transform: uppercase; text-decoration: none; text-align: center; transition: all 0.3s ease; }
        .close-btn:hover { background: rgba(75, 205, 235, 0.2); transform: translateY(-2px); }
        
        .error-box { background: rgba(220, 53, 69, 0.15); border: 1px solid #dc3545; border-radius: 8px; padding: 20px; color: #ff6b6b; text-align: center; margin-bottom: 20px; }
        .success-box { background: rgba(40, 167, 69, 0.15); border: 1px solid #28a745; border-radius: 8px; padding: 20px; color: #28a745; text-align: center; margin-bottom: 20px; font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 600; }
        .warning-box { background: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; border-radius: 8px; padding: 20px; color: #fff; text-align: center; margin-bottom: 20px; }
        .test-type-badge { display: inline-block; padding: 8px 20px; border-radius: 12px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14px; text-transform: uppercase; margin-bottom: 20px; }
        .test-type-badge.general { background: rgba(75, 205, 235, 0.2); color: #4bcde7; border: 1px solid #4bcde7; }
        .test-type-badge.fake_hunter { background: rgba(255, 107, 107, 0.2); color: #ff6b6b; border: 1px solid #ff6b6b; }
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
                <a href="index.php" class="nav-button"><i class="fas fa-arrow-left"></i> В панель</a>
            </nav>
        </header>
        
        <main class="main-content calculator-container animate__animated animate__fadeIn">
            <div class="calculator-card">
                <div style="text-align: center;">
                    <span class="test-type-badge <?= $is_fake_hunter ? 'fake_hunter' : 'general' ?>">
                        <?= $is_fake_hunter ? '🔍 Охотник за фейками' : '🛡️ Общий тест' ?>
                    </span>
                </div>
                <h1><i class="fas fa-calculator"></i> Офлайн-опрос</h1>
                
                <div class="session-info">
                    <div class="info-item"><div class="info-label">Учреждение</div><div class="info-value"><?= e($session['institution']) ?></div></div>
                    <div class="info-item"><div class="info-label">Район</div><div class="info-value"><?= e($session['district']) ?></div></div>
                    <?php if ($session['age_group']): ?><div class="info-item"><div class="info-label">Возраст</div><div class="info-value"><?= e($session['age_group']) ?></div></div><?php endif; ?>
                    <div class="info-item"><div class="info-label">Формат</div><div class="info-value"><?= e($session['format']) ?></div></div>
                </div>
                
                <!-- Статусы этапов -->
                <div class="phase-status">
                    <div class="phase-badge <?= $initial_done ? 'done' : 'pending' ?>">
                        <i class="fas fa-<?= $initial_done ? 'check-circle' : 'clock' ?>"></i>
                        Этап 1: Начальный опрос <?= $initial_done ? '(Завершен)' : '(Ожидает)' ?>
                    </div>
                    <div class="phase-badge <?= $final_done ? 'done' : 'pending' ?>">
                        <i class="fas fa-<?= $final_done ? 'check-circle' : 'clock' ?>"></i>
                        Этап 2: Конечный опрос <?= $final_done ? '(Завершен)' : '(Ожидает)' ?>
                    </div>
                </div>
                
                <?php if ($error): ?><div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= e($error) ?></div><?php endif; ?>
                <?php if ($success_msg): ?><div class="success-box"><?= $success_msg ?></div><?php endif; ?>
                
                <?php if (!$initial_done): ?>
                    <!-- ЭТАП 1: НАЧАЛЬНЫЙ ОПРОС -->
                    <div class="phase-section">
                        <div class="phase-header"><i class="fas fa-clipboard-list"></i> Этап 1: Начальный опрос (до лекции)</div>
                        <div class="instruction-box">
                            <h3><i class="fas fa-info-circle"></i> Как проводить опрос:</h3>
                            <ol>
                                <li>Зачитывайте утверждение вслух аудитории</li>
                                <li>Попросите поднять руку тех, кто <strong>согласен</strong> с утверждением</li>
                                <li>Визуально оцените процент поднявших руки</li>
                                <li>Выберите один из пяти вариантов ниже</li>
                            </ol>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="phase" value="initial">
                            <?php renderQuestions($initial_questions); ?>
                            <button type="submit" class="calculate-btn" name="action" value="calculate_initial">
                                <i class="fas fa-check"></i> Завершить начальный опрос
                            </button>
                        </form>
                    </div>
                    
                <?php elseif ($initial_done && !$final_done): ?>
                    <!-- РЕЗУЛЬТАТЫ НАЧАЛЬНОГО ЭТАПА -->
                    <div class="phase-section" style="opacity: 0.8;">
                        <div class="phase-header"><i class="fas fa-check-circle"></i> Результаты начального опроса</div>
                        <?php if (!$is_fake_hunter && !empty($initial_results)): ?>
                            <?php $problem = findProblemTheme($initial_results); ?>
                            <?php if ($problem): ?>
                                <div class="problem-theme-box">
                                    <h2><i class="fas fa-exclamation-triangle"></i> Проблемная тема</h2>
                                    <div class="theme-name"><?= $theme_names[$problem] ?? $problem ?></div>
                                    <div class="recommendation">Начните лекцию с этой темы.</div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="warning-box"><i class="fas fa-info-circle"></i> Для "Охотника за фейками" проблемная тема не определяется.</div>
                        <?php endif; ?>
                        <div class="theme-results">
                            <?php foreach ($initial_results as $theme => $data): ?>
                                <div class="theme-card <?= (!$is_fake_hunter && $theme === ($problem ?? null)) ? 'problem' : '' ?>">
                                    <div class="theme-header">
                                        <div class="theme-name-small"><?= $theme_names[$theme] ?></div>
                                        <div class="theme-percentage"><?= $data['percentage'] ?>%</div>
                                    </div>
                                    <div class="progress-bar"><div class="progress-fill" style="width: <?= $data['percentage'] ?>%"></div></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- ЭТАП 2: КОНЕЧНЫЙ ОПРОС -->
                    <div class="phase-section">
                        <div class="phase-header"><i class="fas fa-clipboard-check"></i> Этап 2: Конечный опрос (после лекции)</div>
                        <div class="instruction-box">
                            <h3><i class="fas fa-info-circle"></i> Проведите конечный опрос:</h3>
                            <p>Задайте те же вопросы (или аналогичные из этого списка) и оцените реакцию зала.</p>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="phase" value="final">
                            <?php renderQuestions($final_questions); ?>
                            <button type="submit" class="calculate-btn" name="action" value="calculate_final">
                                <i class="fas fa-chart-line"></i> Подсчитать итоговый результат
                            </button>
                        </form>
                    </div>
                    
                <?php elseif ($final_done): ?>
                    <!-- ФИНАЛЬНЫЙ ОТЧЕТ -->
                    <div class="results-section">
                        <?php if (!$is_fake_hunter && !empty($initial_results)): ?>
                            <?php $problem = findProblemTheme($initial_results); ?>
                            <?php if ($problem): ?>
                                <div class="problem-theme-box">
                                    <h2><i class="fas fa-exclamation-triangle"></i> Проблемная тема (до лекции)</h2>
                                    <div class="theme-name"><?= $theme_names[$problem] ?? $problem ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="growth-hero">
                            <h2><i class="fas fa-chart-line"></i> Итоговый прирост знаний</h2>
                            <div class="growth-number">+<?= $growth_data['average_growth'] ?? 0 ?>%</div>
                            <div class="growth-label">средний прирост по всем темам</div>
                        </div>
                        
                        <h3 style="font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 20px; text-align: center;">Детализация прироста по темам</h3>
                        <div class="growth-details">
                            <?php foreach ($growth_data['by_theme'] as $theme => $data): ?>
                                <div class="growth-item">
                                    <div class="theme"><?= $theme_names[$theme] ?></div>
                                    <div class="theme" style="color: rgba(255,255,255,0.6); font-size: 14px;">
                                        <?= $data['initial'] ?>% → <?= $data['final'] ?>%
                                    </div>
                                    <div class="growth-value <?= $data['growth'] < 0 ? 'negative' : '' ?>">
                                        <?= $data['growth'] > 0 ? '+' : '' ?><?= $data['growth'] ?>%
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <a href="index.php" class="close-btn"><i class="fas fa-check"></i> Вернуться в панель</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        document.querySelectorAll('.verdict-btn input').forEach(input => {
            input.addEventListener('change', function() {
                const parent = this.closest('.verdict-buttons');
                parent.querySelectorAll('.verdict-btn').forEach(btn => btn.classList.remove('selected'));
                this.closest('.verdict-btn').classList.add('selected');
            });
        });
    </script>
</body>
</html>

<?php
// Вспомогательная функция для рендеринга вопросов (внутри HTML)
function renderQuestions($questions) {
    global $theme_names;
    $current_theme = null;
    foreach ($questions as $q):
        if ($current_theme !== $q['theme']):
            $current_theme = $q['theme'];
            echo '<div style="margin-top: 30px; margin-bottom: 20px;">';
            echo '<div style="font-family: \'Orbitron\', monospace; color: #4bcde7; font-size: 18px; text-transform: uppercase; border-bottom: 2px solid rgba(75, 205, 235, 0.3); padding-bottom: 10px;">';
            echo $theme_names[$q['theme']] ?? $q['theme'];
            echo '</div></div>';
        endif;
        ?>
        <div class="question-block">
            <div class="question-text"><?= e($q['text']) ?></div>
            <div class="verdict-buttons">
                <label class="verdict-btn"><input type="radio" name="verdicts[<?= $q['id'] ?>]" value="pct_0" required><i class="fas fa-users-slash"></i> 0% подняли</label>
                <label class="verdict-btn"><input type="radio" name="verdicts[<?= $q['id'] ?>]" value="pct_25" required><i class="fas fa-user-friends"></i> 25% подняли</label>
                <label class="verdict-btn"><input type="radio" name="verdicts[<?= $q['id'] ?>]" value="pct_50" required><i class="fas fa-balance-scale"></i> 50% подняли</label>
                <label class="verdict-btn"><input type="radio" name="verdicts[<?= $q['id'] ?>]" value="pct_75" required><i class="fas fa-users"></i> 75% подняли</label>
                <label class="verdict-btn"><input type="radio" name="verdicts[<?= $q['id'] ?>]" value="pct_95" required><i class="fas fa-user-check"></i> 95-100% подняли</label>
            </div>
        </div>
        <?php
    endforeach;
}
?>