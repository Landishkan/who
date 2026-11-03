<?php
require_once __DIR__ . '/config.php';

echo "<h2>🔍 Диагностика подключения к БД</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;'>";

// 1. Проверяем, что в $_ENV
echo "=== ПЕРЕМЕННЫЕ БД ИЗ .env ===\n";
echo "DB_HOST: " . var_export($_ENV['DB_HOST'] ?? 'НЕ НАЙДЕНО', true) . "\n";
echo "DB_NAME: " . var_export($_ENV['DB_NAME'] ?? 'НЕ НАЙДЕНО', true) . "\n";
echo "DB_USER: " . var_export($_ENV['DB_USER'] ?? 'НЕ НАЙДЕНО', true) . "\n";
echo "DB_PASS: " . var_export($_ENV['DB_PASS'] ?? 'НЕ НАЙДЕНО', true) . "\n\n";

// 2. Проверяем все переменные в $_ENV
echo "=== ВСЕ ПЕРЕМЕННЫЕ В \$_ENV ===\n";
print_r($_ENV);

echo "</pre>";
?>