<?php
require_once __DIR__ . '/config.php';

echo "<h2>🔧 Исправление таблицы tests</h2>";

try {
    $pdo = getDB();
    
    // Изменяем тип поля type с VARCHAR(10) на VARCHAR(30)
    $pdo->exec("ALTER TABLE tests MODIFY COLUMN type VARCHAR(30) NOT NULL");
    
    echo "<div style='background: rgba(40, 167, 69, 0.2); border: 2px solid #28a745; border-radius: 8px; padding: 20px; color: #fff;'>";
    echo "<strong>✅ Поле type изменено на VARCHAR(30)</strong>";
    echo "</div>";
    
    // Проверяем текущие записи
    $tests = $pdo->query("SELECT id, session_id, type FROM tests ORDER BY id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Последние 20 записей в tests:</h3>";
    echo "<table border='1' style='background: #222; color: #0f0; padding: 10px;'>";
    echo "<tr><th>ID</th><th>Session ID</th><th>Type</th></tr>";
    foreach ($tests as $test) {
        echo "<tr>";
        echo "<td>{$test['id']}</td>";
        echo "<td>{$test['session_id']}</td>";
        echo "<td>{$test['type']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107; border-radius: 8px; padding: 20px; color: #fff; margin-top: 20px;'>";
    echo "<strong>⚠️ Теперь удали этот файл с сервера!</strong>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545; border-radius: 8px; padding: 20px; color: #ff6b6b;'>";
    echo "❌ Ошибка: " . e($e->getMessage());
    echo "</div>";
}
?>