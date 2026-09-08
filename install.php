<?php
require_once __DIR__ . '/config.php';

$allow_install = true;

if (!$allow_install) {
    die("Установка запрещена. Удалите этот файл.");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка базы данных - КиберКвестор</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Установка базы данных (MySQL)</h1>
        
        <?php
        try {
            $pdo = getDB();
            echo "<div class='success'>✅ Подключение к базе данных успешно!</div>";
            
            // Отключаем проверку внешних ключей на время создания
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Таблица вопросов
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS questions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    text TEXT NOT NULL,
                    theme VARCHAR(20) NOT NULL,
                    correct_answer VARCHAR(10) NOT NULL,
                    test_type VARCHAR(10) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "<div class='success'>✅ Таблица <code>questions</code> создана</div>";
            
            // Таблица сессий
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    district VARCHAR(100) NOT NULL,
                    institution VARCHAR(200) NOT NULL,
                    age_group VARCHAR(50) DEFAULT NULL,
                    speaker_name VARCHAR(100) DEFAULT NULL,
                    format VARCHAR(50) DEFAULT 'Лекция',
                    mode VARCHAR(10) NOT NULL,
                    initial_test_id INT DEFAULT NULL,
                    final_test_id INT DEFAULT NULL,
                    initial_status VARCHAR(20) DEFAULT 'pending',
                    final_status VARCHAR(20) DEFAULT 'pending',
                    problem_theme VARCHAR(20) DEFAULT NULL,
                    growth_percentage INT DEFAULT NULL,
                    is_hidden TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "<div class='success'>✅ Таблица <code>sessions</code> создана</div>";
            
            // Таблица тестов
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id INT NOT NULL,
                    type VARCHAR(10) NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    participants_count INT DEFAULT 0,
                    closed_at TIMESTAMP NULL DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "<div class='success'>✅ Таблица <code>tests</code> создана</div>";
            
            // Таблица ответов
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS answers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    test_id INT NOT NULL,
                    question_id INT NOT NULL,
                    answer VARCHAR(10) NOT NULL,
                    device_hash VARCHAR(64) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_device_test (test_id, device_hash)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "<div class='success'>✅ Таблица <code>answers</code> создана</div>";
            
            // Таблица офлайн-результатов
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS offline_results (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id INT NOT NULL,
                    question_id INT NOT NULL,
                    verdict VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "<div class='success'>✅ Таблица <code>offline_results</code> создана</div>";
            
            // Включаем проверку внешних ключей
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            // Внешние ключи
            $pdo->exec("
                ALTER TABLE tests 
                ADD CONSTRAINT fk_tests_session 
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
            ");
            echo "<div class='success'>✅ Внешний ключ <code>tests → sessions</code> создан</div>";
            
            $pdo->exec("
                ALTER TABLE answers 
                ADD CONSTRAINT fk_answers_test 
                FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
            ");
            $pdo->exec("
                ALTER TABLE answers 
                ADD CONSTRAINT fk_answers_question 
                FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
            ");
            echo "<div class='success'>✅ Внешние ключи <code>answers</code> созданы</div>";
            
            $pdo->exec("
                ALTER TABLE offline_results 
                ADD CONSTRAINT fk_offline_session 
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
            ");
            $pdo->exec("
                ALTER TABLE offline_results 
                ADD CONSTRAINT fk_offline_question 
                FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
            ");
            echo "<div class='success'>✅ Внешние ключи <code>offline_results</code> созданы</div>";
            
            $pdo->exec("
                ALTER TABLE sessions 
                ADD CONSTRAINT fk_initial_test 
                FOREIGN KEY (initial_test_id) REFERENCES tests(id) ON DELETE SET NULL
            ");
            $pdo->exec("
                ALTER TABLE sessions 
                ADD CONSTRAINT fk_final_test 
                FOREIGN KEY (final_test_id) REFERENCES tests(id) ON DELETE SET NULL
            ");
            echo "<div class='success'>✅ Внешние ключи <code>sessions → tests</code> созданы</div>";
            
            // Индексы
            $pdo->exec("CREATE INDEX idx_tests_session ON tests(session_id)");
            $pdo->exec("CREATE INDEX idx_tests_active ON tests(is_active)");
            $pdo->exec("CREATE INDEX idx_answers_test ON answers(test_id)");
            $pdo->exec("CREATE INDEX idx_sessions_hidden ON sessions(is_hidden)");
            $pdo->exec("CREATE INDEX idx_sessions_created ON sessions(created_at)");
            echo "<div class='success'>✅ Индексы созданы</div>";
            
            echo "<div class='success'><strong>🎉 Установка завершена успешно!</strong></div>";
            
            echo "<div class='warning'>
                <strong>⚠️ ВАЖНО:</strong><br>
                1. Удалите файл <code>install.php</code> с сервера<br>
                2. Или измените <code>\$allow_install = false;</code> в начале файла
            </div>";
            
            // Показываем созданные таблицы
            echo "<h3>📋 Созданные таблицы:</h3><ul>";
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                echo "<li><strong>{$table}</strong></li>";
            }
            echo "</ul>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>❌ Ошибка:</strong><br>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>