<?php
require_once __DIR__ . '/../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Доступ запрещен");
}

$filter_district = $_GET['district'] ?? '';
$filter_mode = $_GET['mode'] ?? '';
$filter_speaker = $_GET['speaker'] ?? '';
$filter_test_type = $_GET['test_type'] ?? '';
$filter_age_group = $_GET['age_group'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

try {
    $pdo = getDB();
    $sql = "SELECT district, institution, age_group, speaker_login, format, mode, test_type, problem_theme, growth_percentage, created_at FROM sessions WHERE is_hidden = 0";
    $params = [];
    
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

// Отправляем заголовки для скачивания CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="cyberquestor_report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// BOM для корректного отображения кириллицы в Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Заголовки столбцов
fputcsv($output, [
    'Дата', 'Район', 'Учреждение', 'Возраст', 'Спикер', 'Формат', 'Режим', 'Тип теста', 'Проблемная тема', 'Прирост (%)'
], ';'); // Используем точку с запятой для корректного открытия в русском Excel

$theme_names = [
    'bullying' => 'Буллинг',
    'fraud' => 'Мошенничество',
    'etiquette' => 'Сетевой этикет',
    'fakes' => 'Фейки'
];

foreach ($sessions as $session) {
    fputcsv($output, [
        date('d.m.Y H:i', strtotime($session['created_at'])),
        $session['district'],
        $session['institution'],
        $session['age_group'] ?? '',
        $session['speaker_login'] ?? '',
        $session['format'],
        $session['mode'] === 'online' ? 'Онлайн' : 'Офлайн',
        $session['test_type'] === 'fake_hunter' ? 'Охотник за фейками' : 'Общий',
        $theme_names[$session['problem_theme']] ?? ($session['problem_theme'] ?? '—'),
        $session['growth_percentage'] !== null ? $session['growth_percentage'] : '—'
    ], ';');
}

fclose($output);
exit;