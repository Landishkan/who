<?php
require_once __DIR__ . '/../config.php';

// Получаем список активных тестов
try {
    $pdo = getDB();
    
    // Активные тесты
    $activeTests = $pdo->query("
        SELECT 
            t.id as test_id,
            t.type,
            t.participants_count,
            s.district,
            s.institution,
            s.age_group,
            s.speaker_name,
            s.format,
            s.mode,
            s.created_at
        FROM tests t
        JOIN sessions s ON t.session_id = s.id
        WHERE t.is_active = 1 AND s.is_hidden = 0
        ORDER BY s.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Завершенные сессии
    $completedSessions = $pdo->query("
        SELECT 
            s.id,
            s.district,
            s.institution,
            s.age_group,
            s.speaker_name,
            s.format,
            s.mode,
            s.initial_status,
            s.final_status,
            s.problem_theme,
            s.growth_percentage,
            s.created_at
        FROM sessions s
        WHERE s.is_hidden = 0 AND (s.initial_status = 'completed' OR s.final_status = 'completed')
        ORDER BY s.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Ошибка загрузки данных: " . $e->getMessage());
}

// Перевод тем
$themeNames = [
    'bullying' => 'Буллинг',
    'fraud' => 'Мошенничество',
    'etiquette' => 'Сетевой этикет',
    'fakes' => 'Фейки'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель спикера - КиберКвестор</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            font-size: 28px;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 16px;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        
        .tab.active {
            color: #3498db;
            border-bottom-color: #3498db;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .card-meta {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-online {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-offline {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-initial {
            background: #cce5ff;
            color: #004085;
        }
        
        .badge-final {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .counter {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin: 15px 0;
        }
        
        .problem-theme {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        
        .growth {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .date {
            color: #95a5a6;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🎤 Панель спикера</h1>
            <p class="subtitle">Управление тестами и анализ аудитории</p>
        </header>
        
        <div class="actions">
            <a href="create-online.php" class="btn btn-primary">➕ Создать онлайн-тест</a>
            <a href="create-offline.php" class="btn btn-secondary"> Создать офлайн-тест</a>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="switchTab('active')">Активные тесты</button>
            <button class="tab" onclick="switchTab('completed')">Завершенные</button>
        </div>
        
        <!-- Активные тесты -->
        <div id="active" class="tab-content active">
            <?php if (empty($activeTests)): ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>Нет активных тестов</p>
                        <p style="font-size: 14px; margin-top: 10px;">Создайте новый тест, чтобы начать</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($activeTests as $test): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <?= e($test['institution']) ?>
                                <?php if ($test['age_group']): ?>
                                    • <?= e($test['age_group']) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge badge-<?= $test['mode'] ?>"><?= $test['mode'] === 'online' ? 'Онлайн' : 'Офлайн' ?></span>
                                <span class="badge badge-<?= $test['type'] ?>"><?= $test['type'] === 'initial' ? 'Начальный' : 'Конечный' ?></span>
                            </div>
                        </div>
                        
                        <div class="card-meta">
                            📍 <?= e($test['district']) ?> район | 
                            📅 <?= date('d.m.Y H:i', strtotime($test['created_at'])) ?> |
                            🎯 <?= e($test['format']) ?>
                            <?php if ($test['speaker_name']): ?>
                                | 👤 <?= e($test['speaker_name']) ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="counter">
                            Ответили всего: <?= $test['participants_count'] ?>
                        </div>
                        
                        <a href="calculate.php?test_id=<?= $test['test_id'] ?>" class="btn btn-success">
                            📊 Подсчитать результат
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Завершенные тесты -->
        <div id="completed" class="tab-content">
            <?php if (empty($completedSessions)): ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <p>Нет завершенных тестов</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($completedSessions as $session): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <?= e($session['institution']) ?>
                                <?php if ($session['age_group']): ?>
                                    • <?= e($session['age_group']) ?>
                                <?php endif; ?>
                            </div>
                            <span class="badge badge-<?= $session['mode'] ?>"><?= $session['mode'] === 'online' ? 'Онлайн' : 'Офлайн' ?></span>
                        </div>
                        
                        <div class="card-meta">
                            📍 <?= e($session['district']) ?> район | 
                            📅 <?= date('d.m.Y H:i', strtotime($session['created_at'])) ?> |
                            🎯 <?= e($session['format']) ?>
                            <?php if ($session['speaker_name']): ?>
                                | 👤 <?= e($session['speaker_name']) ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($session['problem_theme']): ?>
                            <div class="problem-theme">
                                🔴 Проблемная тема: <strong><?= $themeNames[$session['problem_theme']] ?? $session['problem_theme'] ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($session['growth_percentage'] !== null): ?>
                            <div class="growth">
                                 Прирост знаний: <strong>+<?= $session['growth_percentage'] ?>%</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Скрываем все вкладки
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Убираем активность со всех кнопок
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Показываем нужную вкладку
            document.getElementById(tabName).classList.add('active');
            
            // Активируем кнопку
            event.target.classList.add('active');
        }
    </script>
</body>
</html>