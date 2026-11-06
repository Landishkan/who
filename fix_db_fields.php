<?php
require_once __DIR__ . '/config.php';

echo "<h2>🔧 Исправление полей в базе данных</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;font-family:monospace;'>";

try {
    $pdo = getDB();
    
    echo "1. Изменяем поле type в таблице tests...\n";
    $pdo->exec("ALTER TABLE tests MODIFY COLUMN type VARCHAR(30) NOT NULL");
    echo "   ✅ tests.type теперь VARCHAR(30)\n\n";
    
    echo "2. Изменяем поле test_type в таблице questions...\n";
    $pdo->exec("ALTER TABLE questions MODIFY COLUMN test_type VARCHAR(30) NOT NULL");
    echo "   ✅ questions.test_type теперь VARCHAR(30)\n\n";
    
    echo "3. Проверяем результаты...\n";
    
    // Проверяем tests
    $tests = $pdo->query("SELECT id, type FROM tests ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== Последние тесты ===\n";
    foreach ($tests as $t) {
        echo sprintf("ID: %-3s | Type: '%s'\n", $t['id'], $t['type']);
    }
    
    // Проверяем questions
    $qtypes = $pdo->query("SELECT DISTINCT test_type FROM questions")->fetchAll(PDO::FETCH_COLUMN);
    echo "\n=== Типы вопросов ===\n";
    foreach ($qtypes as $qt) {
        $count = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = '$qt'")->fetchColumn();
        echo sprintf("Type: '%-20s' | Кол-во: %s\n", $qt, $count);
    }
    
    echo "\n✅ Исправление завершено!\n";
    echo "\n⚠️ УДАЛИ ЭТОТ ФАЙЛ С СЕРВЕРА!\n";
    
} catch (Exception $e) {
    echo "\n❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>