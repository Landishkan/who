<?php
require_once __DIR__ . '/../config.php';

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
    <title>Панель спикера | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Дополнительные стили специально для панели спикера */
        .speaker-dashboard {
            margin-top: 30px;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .cyber-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #4bcde7 0%, #254883 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Orbitron', monospace;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            flex: 1;
            justify-content: center;
        }

        .cyber-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(75, 205, 235, 0.4);
        }

        .cyber-button.secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #4bcde7;
        }

        .cyber-button.secondary:hover {
            background: rgba(75, 205, 235, 0.2);
        }

        /* Табы */
        .cyber-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid rgba(75, 205, 235, 0.3);
        }

        .cyber-tab {
            padding: 12px 25px;
            background: none;
            border: none;
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            text-transform: uppercase;
        }

        .cyber-tab.active {
            color: #4bcde7;
        }

        .cyber-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #4bcde7;
            box-shadow: 0 0 10px #4bcde7;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        /* Карточки */
        .cyber-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(75, 205, 235, 0.3);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .cyber-card:hover {
            border-color: rgba(75, 205, 235, 0.6);
            box-shadow: 0 0 20px rgba(75, 205, 235, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-title {
            font-family: 'Orbitron', monospace;
            font-size: 20px;
            color: #fff;
            margin: 0;
        }

        .card-meta {
            font-family: 'Roboto', sans-serif;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Бейджи */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-online { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .badge-offline { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
        .badge-initial { background: rgba(75, 205, 235, 0.2); color: #4bcde7; border: 1px solid #4bcde7; }
        .badge-final { background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3); }

        /* Счетчик */
        .participant-counter {
            font-family: 'Orbitron', monospace;
            font-size: 32px;
            color: #4bcde7;
            margin: 20px 0;
            text-shadow: 0 0 10px rgba(75, 205, 235, 0.5);
        }

        .participant-counter span {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
            font-family: 'Rajdhani', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Статусы и результаты */
        .status-box {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-problem {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid #dc3545;
            color: #ff6b6b;
        }

        .status-growth {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid #28a745;
            color: #5cdb5c;
        }

        .calculate-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 12px 25px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: 'Rajdhani', sans-serif;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .calculate-btn:hover {
            background: #218838;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            transform: translateY(-2px);
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: rgba(75, 205, 235, 0.3);
        }

        .empty-state p {
            font-family: 'Rajdhani', sans-serif;
            font-size: 18px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Панель управления спикера</p>
            </div>
            <nav class="header-nav">
                <a href="../index.html" class="nav-button">
                    <i class="fas fa-home"></i> На главную
                </a>
            </nav>
        </header>
        
        <main class="main-content speaker-dashboard animate__animated animate__fadeIn">
            <div class="action-buttons">
                <a href="create-online.php" class="cyber-button">
                    <i class="fas fa-wifi"></i> Создать онлайн-тест
                </a>
                <a href="create-offline.php" class="cyber-button secondary">
                    <i class="fas fa-calculator"></i> Создать офлайн-тест
                </a>
            </div>

            <div class="cyber-tabs">
                <button class="cyber-tab active" onclick="switchTab('active', this)">
                    <i class="fas fa-bolt"></i> Активные
                </button>
                <button class="cyber-tab" onclick="switchTab('completed', this)">
                    <i class="fas fa-chart-bar"></i> Завершенные
                </button>
            </div>

            <!-- Активные тесты -->
            <div id="active" class="tab-content active">
                <?php if (empty($activeTests)): ?>
                    <div class="cyber-card">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Нет активных тестов</p>
                            <p style="font-size: 14px; margin-top: 10px;">Создайте новый тест, чтобы начать мероприятие</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeTests as $test): ?>
                        <div class="cyber-card">
                            <div class="card-header">
                                <h3 class="card-title"><?= e($test['institution']) ?></h3>
                                <div style="display: flex; gap: 10px;">
                                    <span class="badge badge-<?= $test['mode'] ?>">
                                        <i class="fas fa-<?= $test['mode'] === 'online' ? 'wifi' : 'calculator' ?>"></i>
                                        <?= $test['mode'] === 'online' ? 'Онлайн' : 'Офлайн' ?>
                                    </span>
                                    <span class="badge badge-<?= $test['type'] ?>">
                                        <?= $test['type'] === 'initial' ? 'Начальный' : 'Конечный' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= e($test['district']) ?> район</span>
                                <span><i class="fas fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($test['created_at'])) ?></span>
                                <span><i class="fas fa-users"></i> <?= e($test['format']) ?></span>
                                <?php if ($test['age_group']): ?>
                                    <span><i class="fas fa-user-graduate"></i> <?= e($test['age_group']) ?></span>
                                <?php endif; ?>
                                <?php if ($test['speaker_name']): ?>
                                    <span><i class="fas fa-microphone"></i> <?= e($test['speaker_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="participant-counter">
                                <?= $test['participants_count'] ?> <span>ответили</span>
                            </div>
                            
                            <a href="calculate.php?test_id=<?= $test['test_id'] ?>" class="calculate-btn">
                                <i class="fas fa-calculator"></i> Подсчитать результат
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Завершенные тесты -->
            <div id="completed" class="tab-content">
                <?php if (empty($completedSessions)): ?>
                    <div class="cyber-card">
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <p>Нет завершенных тестов</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($completedSessions as $session): ?>
                        <div class="cyber-card">
                            <div class="card-header">
                                <h3 class="card-title"><?= e($session['institution']) ?></h3>
                                <span class="badge badge-<?= $session['mode'] ?>">
                                    <?= $session['mode'] === 'online' ? 'Онлайн' : 'Офлайн' ?>
                                </span>
                            </div>
                            
                            <div class="card-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= e($session['district']) ?> район</span>
                                <span><i class="fas fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($session['created_at'])) ?></span>
                                <span><i class="fas fa-users"></i> <?= e($session['format']) ?></span>
                                <?php if ($session['age_group']): ?>
                                    <span><i class="fas fa-user-graduate"></i> <?= e($session['age_group']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($session['problem_theme']): ?>
                                <div class="status-box status-problem">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Проблемная тема: <strong><?= $themeNames[$session['problem_theme']] ?? $session['problem_theme'] ?></strong>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($session['growth_percentage'] !== null): ?>
                                <div class="status-box status-growth">
                                    <i class="fas fa-chart-line"></i>
                                    Прирост знаний: <strong>+<?= $session['growth_percentage'] ?>%</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Панель спикера</div>
            <div class="footer-links">
                <a href="../index.html" class="footer-link">Главная</a>
                <a href="create-online.php" class="footer-link">Создать тест</a>
            </div>
        </footer>
    </div>
    
    <script>
        function switchTab(tabName, btnElement) {
            // Скрываем все вкладки
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Убираем активность со всех кнопок
            document.querySelectorAll('.cyber-tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Показываем нужную вкладку
            document.getElementById(tabName).classList.add('active');
            
            // Активируем нажатую кнопку
            btnElement.classList.add('active');
        }
    </script>
    <script src="../js/script.js"></script>
</body>
</html>