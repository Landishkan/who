<?php
// Функция загрузки переменных из .env
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Файл .env не найден по пути: " . $path);
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Загружаем .env
loadEnv(__DIR__ . '/.env');

// Функция получения подключения к БД
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $dbname = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
            
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
        } catch (PDOException $e) {
            // В продакшене не показываем детали ошибки
            error_log("Ошибка подключения к БД: " . $e->getMessage());
            die("Ошибка подключения к базе данных");
        }
    }
    
    return $pdo;
}

// Функция для безопасного вывода (защита от XSS)
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}