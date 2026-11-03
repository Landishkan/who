<?php
// Функция загрузки переменных из .env
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("Файл .env не найден по пути: " . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Пропускаем комментарии и пустые строки
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Разбираем KEY=VALUE (ограничиваем до 2 частей, чтобы = в пароле не ломал парсинг)
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Убираем кавычки только если они есть с обеих сторон
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }
            
            // Загружаем во все массивы
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
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
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? ''; // 🔥 Используем DB_PASSWORD, как у тебя в .env
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception("Ошибка подключения к БД: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Функция для защиты от XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Конфигурация спикеров (массив логинов из .env)
$speakers_json = $_ENV['SPEAKERS_JSON'] ?? '{}';
$speakers_list = json_decode($speakers_json, true);
if (!is_array($speakers_list)) {
    $speakers_list = [];
}
?>