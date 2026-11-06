<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

echo "<h2>🔍 Диагностика тестов и вопросов</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;font-family:monospace;'>";

echo "=== 1. Последние тесты в таблице `tests` ===\n";
$tests = $pdo->query("SELECT id, session_id, type, is_active FROM tests ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tests as $t) {
    echo sprintf("ID: %-3s | Session: %-3s | Type: '%-20s' | Active: %s\n", 
        $t['id'], $t['session_id'], $t['type'], $t['is_active']);
}

echo "\n=== 2. Какие типы вопросов есть в таблице `questions` ===\n";
$types = $pdo->query("SELECT DISTINCT test_type FROM questions")->fetchAll(PDO::FETCH_COLUMN);
foreach ($types as $type) {
    $count = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = '$type'")->fetchColumn();
    echo sprintf("Type: '%-20s' | Количество вопросов: %s\n", $type, $count);
}

echo "\n⚠️ Удали этот файл после проверки!</pre>";
?>