<?php
require_once __DIR__ . '/config.php';

echo "<h2>🔍 Диагностика .env и БД</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;font-size:14px;'>";

// 1. Проверяем, что в $_ENV
echo "=== ПЕРЕМЕННЫЕ ИЗ .env ===\n";
echo "SPEAKER_LOGIN: " . var_export($_ENV['SPEAKER_LOGIN'] ?? 'НЕ НАЙДЕНО', true) . "\n";
echo "SPEAKER_PASSWORD: " . var_export($_ENV['SPEAKER_PASSWORD'] ?? 'НЕ НАЙДЕНО', true) . "\n";
echo "SPEAKERS_JSON: " . var_export($_ENV['SPEAKERS_JSON'] ?? 'НЕ НАЙДЕНО', true) . "\n\n";

// 2. Проверяем подключение к БД
echo "=== ПОДКЛЮЧЕНИЕ К БД ===\n";
try {
    $pdo = getDB();
    echo "✅ БД подключена успешно\n\n";
    
    // 3. Проверяем таблицу sessions
    echo "=== ТАБЛИЦА sessions ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Колонки: " . implode(', ', $columns) . "\n\n";
    
    // 4. Проверяем, есть ли данные
    $count = $pdo->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
    echo "Записей в sessions: $count\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка БД: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>