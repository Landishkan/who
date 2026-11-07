<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

// Фильтры
$filter_district = $_GET['district'] ?? '';
$filter_mode = $_GET['mode'] ?? '';
$filter_speaker = $_GET['speaker'] ?? '';
$filter_test_type = $_GET['test_type'] ?? '';
$filter_age_group = $_GET['age_group'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$show_hidden = isset($_GET['show_hidden']) && $_GET['show_hidden'] === '1';

try {
    $pdo = getDB();
    
    // Списки для фильтров
    $districts = $pdo->query("SELECT DISTINCT district FROM sessions WHERE is_hidden = 0 ORDER BY district")->fetchAll(PDO::FETCH_COLUMN);
    $speakers = $pdo->query("SELECT DISTINCT speaker_login FROM sessions WHERE is_hidden = 0 AND speaker_login IS NOT NULL ORDER BY speaker_login")->fetchAll(PDO::FETCH_COLUMN);
    $age_groups = $pdo->query("SELECT DISTINCT age_group FROM sessions WHERE is_hidden = 0 AND age_group IS NOT NULL ORDER BY age_group")->fetchAll(PDO::FETCH_COLUMN);

    // Общая статистика (по текущим фильтрам, кроме show_hidden)
    $stats_sql = "SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN mode = 'online' THEN 1 ELSE 0 END) as online_count,
        SUM(CASE WHEN mode = 'offline' THEN 1 ELSE 0 END) as offline_count,
        SUM(CASE WHEN final_status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        AVG(CASE WHEN growth_percentage IS NOT NULL THEN growth_percentage END) as avg_growth
        FROM sessions WHERE 1=1";
    $stats_params = [];
    if (!$show_hidden) { $stats_sql .= " AND is_hidden = 0"; }
    if ($filter_district) { $stats_sql .= " AND district = :district"; $stats_params[':district'] = $filter_district; }
    if ($filter_mode) { $stats_sql .= " AND mode = :mode"; $stats_params[':mode'] = $filter_mode; }
    if ($filter_speaker) { $stats_sql .= " AND speaker_login = :speaker"; $stats_params[':speaker'] = $filter_speaker; }
    if ($filter_test_type) { $stats_sql .= " AND test_type = :test_type"; $stats_params[':test_type'] = $filter_test_type; }
    if ($filter_age_group) { $stats_sql .= " AND age_group = :age_group"; $stats_params[':age_group'] = $filter_age_group; }
    
    $stats = $pdo->prepare($stats_sql);
    $stats->execute($stats_params);
    $stats_data = $stats->fetch(PDO::FETCH_ASSOC);

    // Статистика по проблемным темам
    $theme_sql = "SELECT problem_theme, COUNT(*) as count FROM sessions WHERE is_hidden = 0 AND problem_theme IS NOT NULL GROUP BY problem_theme ORDER BY count DESC";
    $theme_stats = $pdo->query($theme_sql)->fetchAll(PDO::FETCH_ASSOC);

    // Получаем сессии с фильтрами
    $sql = "SELECT * FROM sessions WHERE 1=1";
    $params = [];
    if (!$show_hidden) { $sql .= " AND is_hidden = 0"; }
    if ($filter_district) { $sql .= " AND district = :district"; $params[':district'] = $filter_district; }
    if ($filter_mode) { $sql .= " AND mode = :mode"; $params[':mode'] = $filter_mode; }
    if ($filter_speaker) { $sql .= " AND speaker_login = :speaker"; $params[':speaker'] = $filter_speaker; }
    if ($filter_test_type) { $sql .= " AND test_type = :test_type"; $params[':test_type'] = $filter_test_type; }
    if ($filter_age_group) { $sql .= " AND age_group = :age_group"; $params[':age_group'] = $filter_age_group; }
    if ($filter_date_from) { $sql .= " AND created_at >= :date_from"; $params[':date_from'] = $filter_date_from . ' 00:00:00'; }
    if ($filter_date_to) { $sql .= " AND created_at <= :date_to"; $params[':date_to'] = $filter_date_to . ' 23:59:59'; }
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

$theme_names = [
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
    <title>Админ-панель | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .admin-container { max-width: 1400px; margin: 30px auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 25px; text-align: center; }
        .stat-card h3 { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .number { font-family: 'Orbitron', monospace; font-size: 36px; color: #4bcde7; text-shadow: 0 0 15px rgba(75, 205, 235, 0.5); }
        .stat-card .number.green { color: #28a745; text-shadow: 0 0 15px rgba(40, 167, 69, 0.5); }
        .stat-card .number.orange { color: #ffc107; text-shadow: 0 0 15px rgba(255, 193, 7, 0.5); }
        .filters-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        .filters-card h2 { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; margin-bottom: 20px; }
        .filters-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        .filter-group label { display: block; font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 14px; margin-bottom: 5px; text-transform: uppercase; }
        .filter-group select, .filter-group input { width: 100%; padding: 10px; background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 6px; color: #fff; font-size: 14px; }
        .filter-group select:focus, .filter-group input:focus { outline: none; border-color: #4bcde7; }
        .filter-btn { padding: 10px 20px; background: linear-gradient(135deg, #4bcde7 0%, #254883 100%); border: none; border-radius: 6px; color: #fff; font-family: 'Rajdhani', sans-serif; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .filter-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(75, 205, 235, 0.4); }
        .reset-btn { padding: 10px 20px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 6px; color: #fff; font-family: 'Rajdhani', sans-serif; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s ease; }
        .reset-btn:hover { background: rgba(255, 255, 255, 0.2); }
        .export-btn { padding: 10px 20px; background: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; border-radius: 6px; color: #28a745; font-family: 'Rajdhani', sans-serif; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s ease; }
        .export-btn:hover { background: rgba(40, 167, 69, 0.3); }
        .sessions-list { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 25px; }
        .sessions-list h2 { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; margin-bottom: 20px; }
        .session-item { background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px; margin-bottom: 15px; border-left: 4px solid #4bcde7; transition: all 0.3s ease; }
        .session-item:hover { border-left-color: #28a745; }
        .session-item.hidden { opacity: 0.5; border-left-color: #666; }
        .session-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
        .session-title { font-family: 'Orbitron', monospace; color: #fff; font-size: 18px; }
        .session-meta { display: flex; gap: 15px; flex-wrap: wrap; font-family: 'Roboto', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 14px; margin-bottom: 15px; }
        .session-meta span { display: flex; align-items: center; gap: 5px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; font-family: 'Rajdhani', sans-serif; }
        .badge-online { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .badge-offline { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
        .badge-completed { background: rgba(75, 205, 235, 0.2); color: #4bcde7; border: 1px solid #4bcde7; }
        .badge-pending { background: rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.6); border: 1px solid rgba(255, 255, 255, 0.3); }
        .badge-hidden { background: rgba(102, 102, 102, 0.2); color: #666; border: 1px solid #666; }
        .session-results { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px; }
        .result-item { background: rgba(75, 205, 235, 0.1); padding: 8px 15px; border-radius: 6px; font-family: 'Rajdhani', sans-serif; font-size: 14px; }
        .result-item.problem { background: rgba(220, 53, 69, 0.1); color: #ff6b6b; }
        .result-item.growth { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .session-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .action-btn { padding: 8px 15px; border-radius: 6px; font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.3s ease; border: none; }
        .action-btn.hide { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
        .action-btn.delete { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid #dc3545; }
        .action-btn.unhide { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .theme-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .theme-stat-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 8px; padding: 20px; text-align: center; }
        .theme-stat-card h4 { font-family: 'Rajdhani', sans-serif; color: rgba(255, 255, 255, 0.7); font-size: 14px; margin-bottom: 10px; }
        .theme-stat-card .count { font-family: 'Orbitron', monospace; font-size: 24px; color: #4bcde7; }
        .empty-state { text-align: center; padding: 60px 20px; color: rgba(255, 255, 255, 0.5); }
        .logout-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: rgba(220, 53, 69, 0.2); border: 1px solid #dc3545; border-radius: 6px; color: #ff6b6b; text-decoration: none; font-family: 'Rajdhani', sans-serif; font-weight: 600; transition: all 0.3s ease; z-index: 1000; }
        .logout-btn:hover { background: rgba(220, 53, 69, 0.3); }
        .success-message { background: rgba(40, 167, 69, 0.15); border: 1px solid #28a745; border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #28a745; font-family: 'Rajdhani', sans-serif; text-align: center; }
        .filters-actions { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; margin-top: 15px; }
    </style>
</head>
<body>
    <a href="auth.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Выйти</a>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Админ-панель</p>
            </div>
        </header>
        <main class="main-content admin-container animate__animated animate__fadeIn">
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
                <div class="success-message"><i class="fas fa-check-circle"></i> Сессия успешно удалена/скрыта</div>
            <?php endif; ?>

            <!-- Общая статистика -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Всего сессий</h3>
                    <div class="number"><?= $stats_data['total_sessions'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Онлайн</h3>
                    <div class="number"><?= $stats_data['online_count'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Офлайн</h3>
                    <div class="number orange"><?= $stats_data['offline_count'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Завершено</h3>
                    <div class="number green"><?= $stats_data['completed_count'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Средний прирост</h3>
                    <div class="number green"><?= ($stats_data['avg_growth'] ?? 0) ? round($stats_data['avg_growth']) . '%' : '—' ?></div>
                </div>
            </div>

            <!-- Статистика по темам -->
            <?php if (!empty($theme_stats)): ?>
                <h2 style="font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 20px;">Проблемные темы</h2>
                <div class="theme-stats">
                    <?php foreach ($theme_stats as $theme_stat): ?>
                        <div class="theme-stat-card">
                            <h4><?= $theme_names[$theme_stat['problem_theme']] ?? $theme_stat['problem_theme'] ?></h4>
                            <div class="count"><?= $theme_stat['count'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Фильтры -->
            <div class="filters-card">
                <h2><i class="fas fa-filter"></i> Фильтры</h2>
                <form method="GET" action="index.php">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Район</label>
                            <select name="district">
                                <option value="">Все районы</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?= e($district) ?>" <?= $filter_district === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Спикер</label>
                            <select name="speaker">
                                <option value="">Все спикеры</option>
                                <?php foreach ($speakers as $speaker): ?>
                                    <option value="<?= e($speaker) ?>" <?= $filter_speaker === $speaker ? 'selected' : '' ?>><?= e($speaker) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Тип теста</label>
                            <select name="test_type">
                                <option value="">Все типы</option>
                                <option value="general" <?= $filter_test_type === 'general' ? 'selected' : '' ?>>Общий</option>
                                <option value="fake_hunter" <?= $filter_test_type === 'fake_hunter' ? 'selected' : '' ?>>Охотник за фейками</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Возраст</label>
                            <select name="age_group">
                                <option value="">Все возрасты</option>
                                <?php foreach ($age_groups as $age): ?>
                                    <option value="<?= e($age) ?>" <?= $filter_age_group === $age ? 'selected' : '' ?>><?= e($age) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Режим</label>
                            <select name="mode">
                                <option value="">Все режимы</option>
                                <option value="online" <?= $filter_mode === 'online' ? 'selected' : '' ?>>Онлайн</option>
                                <option value="offline" <?= $filter_mode === 'offline' ? 'selected' : '' ?>>Офлайн</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Дата с</label>
                            <input type="date" name="date_from" value="<?= e($filter_date_from) ?>">
                        </div>
                        <div class="filter-group">
                            <label>Дата по</label>
                            <input type="date" name="date_to" value="<?= e($filter_date_to) ?>">
                        </div>
                        <div class="filter-group" style="display: flex; align-items: flex-end;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="show_hidden" value="1" <?= $show_hidden ? 'checked' : '' ?> style="width: auto;">
                                Показать скрытые
                            </label>
                        </div>
                    </div>
                    <div class="filters-actions">
                        <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Применить</button>
                        <a href="index.php" class="reset-btn"><i class="fas fa-undo"></i> Сбросить</a>
                        <a href="export.php?<?= http_build_query($_GET) ?>" class="export-btn"><i class="fas fa-file-csv"></i> Скачать отчет (CSV)</a>
                    </div>
                </form>
            </div>

            <!-- Список сессий -->
            <div class="sessions-list">
                <h2><i class="fas fa-list"></i> Сессии (<?= count($sessions) ?>)</h2>
                <?php if (empty($sessions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Нет сессий по выбранным фильтрам</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <div class="session-item <?= $session['is_hidden'] ? 'hidden' : '' ?>">
                            <div class="session-header">
                                <div class="session-title">
                                    <?= e($session['institution']) ?>
                                    <?php if ($session['age_group']): ?> • <?= e($session['age_group']) ?><?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <span class="badge badge-<?= $session['mode'] ?>"><?= $session['mode'] === 'online' ? 'Онлайн' : 'Офлайн' ?></span>
                                    <span class="badge" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
                                        <?= $session['test_type'] === 'fake_hunter' ? 'Охотник за фейками' : 'Общий' ?>
                                    </span>
                                    <?php if ($session['is_hidden']): ?><span class="badge badge-hidden">Скрыта</span><?php endif; ?>
                                    <span class="badge badge-<?= $session['final_status'] === 'completed' ? 'completed' : 'pending' ?>">
                                        <?= $session['final_status'] === 'completed' ? 'Завершена' : 'В процессе' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="session-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= e($session['district']) ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('d.m.Y H:i', strtotime($session['created_at'])) ?></span>
                                <span><i class="fas fa-users"></i> <?= e($session['format']) ?></span>
                                <?php if (!empty($session['speaker_login'])): ?>
                                    <span><i class="fas fa-user-astronaut"></i> <?= e($session['speaker_login']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($session['problem_theme'] || $session['growth_percentage'] !== null): ?>
                                <div class="session-results">
                                    <?php if ($session['problem_theme']): ?>
                                        <div class="result-item problem"><i class="fas fa-exclamation-triangle"></i> Проблема: <?= $theme_names[$session['problem_theme']] ?? $session['problem_theme'] ?></div>
                                    <?php endif; ?>
                                    <?php if ($session['growth_percentage'] !== null): ?>
                                        <div class="result-item growth"><i class="fas fa-chart-line"></i> Прирост: +<?= $session['growth_percentage'] ?>%</div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="session-actions">
                                <?php if ($session['is_hidden']): ?>
                                    <a href="delete-session.php?id=<?= $session['id'] ?>&action=unhide" class="action-btn unhide" onclick="return confirm('Показать эту сессию?')">Показать</a>
                                <?php else: ?>
                                    <a href="delete-session.php?id=<?= $session['id'] ?>&action=hide" class="action-btn hide" onclick="return confirm('Скрыть эту сессию?')">Скрыть</a>
                                <?php endif; ?>
                                <a href="../speaker/view-results.php?id=<?= $session['id'] ?>&from=admin" target="_blank" class="action-btn" style="background: rgba(75, 205, 235, 0.2); color: #4bcde7; border: 1px solid #4bcde7;">Результаты</a>
                                <a href="delete-session.php?id=<?= $session['id'] ?>&action=delete" class="action-btn delete" onclick="return confirm('⚠️ УДАЛИТЬ БЕЗВОЗВРАТНО?')">Удалить</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Админ-панель</div>
        </footer>
    </div>
</body>
</html>