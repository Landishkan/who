<?php
require_once __DIR__ . '/../config.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

$session_id = intval($_GET['id'] ?? 0);

try {
    $pdo = getDB();
    
    // Получаем информацию о сессии
    $stmt = $pdo->prepare("
        SELECT s.*, 
               t_initial.id as initial_test_id,
               t_final.id as final_test_id
        FROM sessions s
        LEFT JOIN tests t_initial ON s.initial_test_id = t_initial.id
        LEFT JOIN tests t_final ON s.final_test_id = t_final.id
        WHERE s.id = ?
    ");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        die("Сессия не найдена.");
    }
    
    $theme_names = [
        'bullying' => 'Буллинг',
        'fraud' => 'Мошенничество',
        'etiquette' => 'Сетевой этикет',
        'fakes' => 'Фейки'
    ];
    
    $theme_priority = [
        'bullying' => 1,
        'fraud' => 2,
        'etiquette' => 3,
        'fakes' => 3
    ];
    
    // Результаты начального теста
    $initial_results = [];
    if ($session['initial_test_id']) {
        $stmt = $pdo->prepare("
            SELECT a.question_id, a.answer, q.theme, q.correct_answer
            FROM answers a
            JOIN questions q ON a.question_id = q.id
            WHERE a.test_id = ?
        ");
        $stmt->execute([$session['initial_test_id']]);
        $initial_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($initial_answers)) {
            $theme_stats = [];
            foreach ($initial_answers as $answer) {
                $theme = $answer['theme'];
                if (!isset($theme_stats[$theme])) {
                    $theme_stats[$theme] = ['correct' => 0, 'total' => 0];
                }
                $theme_stats[$theme]['total']++;
                if ($answer['answer'] === $answer['correct_answer']) {
                    $theme_stats[$theme]['correct']++;
                }
            }
            
            foreach ($theme_stats as $theme => $stats) {
                $initial_results[$theme] = [
                    'correct' => $stats['correct'],
                    'total' => $stats['total'],
                    'percentage' => round(($stats['correct'] / $stats['total']) * 100),
                    'error_percentage' => round(100 - ($stats['correct'] / $stats['total']) * 100)
                ];
            }
        }
    }
    
    // Результаты конечного теста (если онлайн)
    $final_results = [];
    if ($session['mode'] === 'online' && $session['final_test_id']) {
        $stmt = $pdo->prepare("
            SELECT a.question_id, a.answer, q.theme, q.correct_answer
            FROM answers a
            JOIN questions q ON a.question_id = q.id
            WHERE a.test_id = ?
        ");
        $stmt->execute([$session['final_test_id']]);
        $final_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($final_answers)) {
            $theme_stats = [];
            foreach ($final_answers as $answer) {
                $theme = $answer['theme'];
                if (!isset($theme_stats[$theme])) {
                    $theme_stats[$theme] = ['correct' => 0, 'total' => 0];
                }
                $theme_stats[$theme]['total']++;
                if ($answer['answer'] === $answer['correct_answer']) {
                    $theme_stats[$theme]['correct']++;
                }
            }
            
            foreach ($theme_stats as $theme => $stats) {
                $final_results[$theme] = [
                    'correct' => $stats['correct'],
                    'total' => $stats['total'],
                    'percentage' => round(($stats['correct'] / $stats['total']) * 100),
                    'error_percentage' => round(100 - ($stats['correct'] / $stats['total']) * 100)
                ];
            }
        }
    }
    
    // Результаты офлайн-теста
    $offline_results = [];
    if ($session['mode'] === 'offline') {
        $stmt = $pdo->prepare("
            SELECT o.question_id, o.verdict, q.theme, q.correct_answer
            FROM offline_results o
            JOIN questions q ON o.question_id = q.id
            WHERE o.session_id = ?
        ");
        $stmt->execute([$session_id]);
        $offline_answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($offline_answers)) {
            $verdict_percentages = [
                'majority_agree' => 80,
                'fifty_fifty' => 50,
                'majority_disagree' => 20
            ];
            
            $theme_stats = [];
            foreach ($offline_answers as $answer) {
                $theme = $answer['theme'];
                $verdict = $answer['verdict'];
                
                if (!isset($theme_stats[$theme])) {
                    $theme_stats[$theme] = ['correct_sum' => 0, 'count' => 0];
                }
                
                $agree_percentage = $verdict_percentages[$verdict] ?? 50;
                
                if ($answer['correct_answer'] === 'agree') {
                    $theme_stats[$theme]['correct_sum'] += $agree_percentage;
                } else {
                    $theme_stats[$theme]['correct_sum'] += (100 - $agree_percentage);
                }
                $theme_stats[$theme]['count']++;
            }
            
            foreach ($theme_stats as $theme => $stats) {
                if ($stats['count'] > 0) {
                    $percentage = round($stats['correct_sum'] / $stats['count']);
                    $offline_results[$theme] = [
                        'percentage' => $percentage,
                        'error_percentage' => 100 - $percentage,
                        'count' => $stats['count']
                    ];
                }
            }
        }
    }
    
    // Определяем какие результаты показывать
    $results_to_show = [];
    if ($session['mode'] === 'online' && !empty($final_results)) {
        $results_to_show = $final_results;
    } elseif (!empty($initial_results)) {
        $results_to_show = $initial_results;
    } elseif (!empty($offline_results)) {
        $results_to_show = $offline_results;
    }
    
    // Считаем прирост
    $growth_data = null;
    if (!empty($initial_results) && !empty($final_results)) {
        $growth_data = ['by_theme' => [], 'total_growth' => 0, 'count' => 0];
        
        foreach ($final_results as $theme => $final_data) {
            if (isset($initial_results[$theme])) {
                $initial_percentage = $initial_results[$theme]['percentage'];
                $final_percentage = $final_data['percentage'];
                $growth = $final_percentage - $initial_percentage;
                
                $growth_data['by_theme'][$theme] = [
                    'initial' => $initial_percentage,
                    'final' => $final_percentage,
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
    
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты сессии #<?= $session_id ?> | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .results-container { max-width: 1000px; margin: 30px auto; }
        .results-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 30px; margin-bottom: 20px; }
        .results-card h1 { font-family: 'Orbitron', monospace; color: #4bcde7; text-align: center; margin-bottom: 30px; font-size: 28px; }
        .session-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; padding: 20px; background: rgba(0, 0, 0, 0.3); border-radius: 8px; }
        .info-item { text-align: center; }
        .info-label { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.6); font-size: 14px; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; }
        
        .growth-hero { background: rgba(40, 167, 69, 0.15); border: 2px solid #28a745; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 30px; }
        .growth-hero h2 { font-family: 'Orbitron', monospace; color: #28a745; font-size: 24px; margin-bottom: 20px; }
        .growth-hero .growth-number { font-family: 'Orbitron', monospace; font-size: 64px; color: #28a745; text-shadow: 0 0 30px rgba(40, 167, 69, 0.6); margin: 20px 0; }
        .growth-hero .growth-label { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.8); font-size: 20px; text-transform: uppercase; letter-spacing: 2px; }
        
        .problem-theme-box { background: rgba(220, 53, 69, 0.15); border: 2px solid #dc3545; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 30px; }
        .problem-theme-box h2 { font-family: 'Orbitron', monospace; color: #ff6b6b; font-size: 24px; margin-bottom: 15px; }
        .problem-theme-box .theme-name { font-family: 'Orbitron', monospace; color: #fff; font-size: 32px; margin: 15px 0; }
        
        .theme-results { display: grid; gap: 15px; margin-bottom: 30px; }
        .theme-card { background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px; border-left: 4px solid #4bcde7; }
        .theme-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .theme-name-small { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 16px; }
        .theme-percentage { font-family: 'Orbitron', monospace; font-size: 24px; color: #fff; }
        .progress-bar { height: 8px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; overflow: hidden; margin-bottom: 10px; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #4bcde7 0%, #254883 100%); }
        .theme-stats { font-family: 'Roboto', sans-serif; color: rgba(255, 255, 255, 0.6); font-size: 14px; }
        
        .growth-details { background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 12px; padding: 25px; margin-top: 30px; }
        .growth-details h3 { font-family: 'Orbitron', monospace; color: #28a745; font-size: 20px; margin-bottom: 20px; text-align: center; }
        .growth-by-theme { display: grid; gap: 10px; }
        .growth-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(0, 0, 0, 0.3); border-radius: 6px; }
        .growth-item .theme { font-family: 'Rajdhani', sans-serif; color: #fff; font-size: 16px; }
        .growth-item .values { font-family: 'Roboto', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 14px; }
        .growth-item .growth-value { font-family: 'Orbitron', monospace; font-size: 18px; color: #28a745; }
        .growth-item .growth-value.negative { color: #dc3545; }
        
        .back-btn { display: inline-block; margin-top: 20px; padding: 12px 30px; background: rgba(255, 255, 255, 0.1); border: 2px solid #4bcde7; border-radius: 8px; color: #4bcde7; text-decoration: none; font-family: 'Rajdhani', sans-serif; font-weight: 600; transition: all 0.3s ease; }
        .back-btn:hover { background: rgba(75, 205, 235, 0.2); }
        
        .mode-badge { display: inline-block; padding: 5px 15px; border-radius: 12px; font-family: 'Rajdhani', sans-serif; font-weight: 700; text-transform: uppercase; margin-bottom: 20px; }
        .mode-badge.online { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .mode-badge.offline { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Результаты сессии #<?= $session_id ?></p>
            </div>
            <nav class="header-nav">
                <a href="index.php" class="nav-button">
                    <i class="fas fa-arrow-left"></i> В админ-панель
                </a>
            </nav>
        </header>
        
        <main class="main-content results-container animate__animated animate__fadeIn">
            <div class="results-card">
                <h1><i class="fas fa-chart-bar"></i> Результаты сессии</h1>
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <span class="mode-badge <?= $session['mode'] ?>">
                        <?= $session['mode'] === 'online' ? 'Онлайн' : 'Офлайн' ?>
                    </span>
                </div>
                
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
                    <div class="info-item">
                        <div class="info-label">Дата</div>
                        <div class="info-value"><?= date('d.m.Y H:i', strtotime($session['created_at'])) ?></div>
                    </div>
                </div>
                
                <?php if ($session['problem_theme']): ?>
                    <div class="problem-theme-box">
                        <h2><i class="fas fa-exclamation-triangle"></i> Проблемная тема</h2>
                        <div class="theme-name"><?= $theme_names[$session['problem_theme']] ?? $session['problem_theme'] ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($growth_data && isset($growth_data['average_growth'])): ?>
                    <div class="growth-hero">
                        <h2><i class="fas fa-chart-line"></i> Прирост знаний</h2>
                        <div class="growth-number">+<?= $growth_data['average_growth'] ?>%</div>
                        <div class="growth-label">средний прирост по всем темам</div>
                    </div>
                <?php endif; ?>
                
                <h3 style="font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 20px; text-align: center;">
                    Детализация по темам
                </h3>
                
                <div class="theme-results">
                    <?php foreach ($results_to_show as $theme => $data): ?>
                        <div class="theme-card">
                            <div class="theme-header">
                                <div class="theme-name-small"><?= $theme_names[$theme] ?></div>
                                <div class="theme-percentage"><?= $data['percentage'] ?>%</div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $data['percentage'] ?>%"></div>
                            </div>
                            <div class="theme-stats">
                                Правильных ответов: <?= $data['correct'] ?? '≈' ?> из <?= $data['total'] ?? $data['count'] ?> 
                                (ошибок: <?= $data['error_percentage'] ?>%)
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($growth_data && !empty($growth_data['by_theme'])): ?>
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
                
                <div style="text-align: center;">
                    <a href="index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Вернуться в админ-панель
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>