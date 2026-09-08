<?php
// Подключаем конфиг
require_once __DIR__ . '/config.php';

// Проверка безопасности - можно удалить после использования
$allow_install = true; // Временно true, потом измени на false или удали файл

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
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .success { 
            color: #28a745; 
            background: #d4edda; 
            padding: 15px; 
            border-radius: 4px;
            margin: 10px 0;
        }
        .error { 
            color: #dc3545; 
            background: #f8d7da; 
            padding: 15px; 
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            color: #0c5460;
            background: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .warning {
            color: #856404;
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Установка базы данных</h1>
        
        <?php
        try {
            $pdo = getDB();
            echo "<div class='success'>✅ Подключение к базе данных успешно!</div>";
            
            // SQL для создания таблиц
            $sql = "
            -- Таблица вопросов
            CREATE TABLE IF NOT EXISTS questions (
                id SERIAL PRIMARY KEY,
                text TEXT NOT NULL,
                theme VARCHAR(20) NOT NULL CHECK (theme IN ('bullying', 'fraud', 'etiquette', 'fakes')),
                correct_answer VARCHAR(10) NOT NULL CHECK (correct_answer IN ('agree', 'disagree')),
                test_type VARCHAR(10) NOT NULL CHECK (test_type IN ('initial', 'final')),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            -- Таблица сессий
            CREATE TABLE IF NOT EXISTS sessions (
                id SERIAL PRIMARY KEY,
                district VARCHAR(100) NOT NULL,
                institution VARCHAR(200) NOT NULL,
                age_group VARCHAR(50),
                speaker_name VARCHAR(100),
                format VARCHAR(50) DEFAULT 'Лекция',
                mode VARCHAR(10) NOT NULL CHECK (mode IN ('online', 'offline')),
                initial_test_id INTEGER,
                final_test_id INTEGER,
                initial_status VARCHAR(20) DEFAULT 'pending' CHECK (initial_status IN ('pending', 'completed')),
                final_status VARCHAR(20) DEFAULT 'pending' CHECK (final_status IN ('pending', 'completed', 'skipped')),
                problem_theme VARCHAR(20),
                growth_percentage INTEGER,
                is_hidden BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            -- Таблица тестов
            CREATE TABLE IF NOT EXISTS tests (
                id SERIAL PRIMARY KEY,
                session_id INTEGER REFERENCES sessions(id) ON DELETE CASCADE,
                type VARCHAR(10) NOT NULL CHECK (type IN ('initial', 'final')),
                is_active BOOLEAN DEFAULT TRUE,
                participants_count INTEGER DEFAULT 0,
                closed_at TIMESTAMP
            );
            
            -- Таблица ответов
            CREATE TABLE IF NOT EXISTS answers (
                id SERIAL PRIMARY KEY,
                test_id INTEGER REFERENCES tests(id) ON DELETE CASCADE,
                question_id INTEGER REFERENCES questions(id),
                answer VARCHAR(10) NOT NULL CHECK (answer IN ('agree', 'disagree')),
                device_hash VARCHAR(64) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(test_id, device_hash)
            );
            
            -- Таблица офлайн-результатов
            CREATE TABLE IF NOT EXISTS offline_results (
                id SERIAL PRIMARY KEY,
                session_id INTEGER REFERENCES sessions(id) ON DELETE CASCADE,
                question_id INTEGER REFERENCES questions(id),
                verdict VARCHAR(20) NOT NULL CHECK (verdict IN ('majority_agree', 'fifty_fifty', 'majority_disagree')),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            ";
            
            // Выполняем SQL
            $pdo->exec($sql);
            echo "<div class='success'>✅ Таблицы успешно созданы!</div>";
            
            // Создаём индексы
            $indexes = "
            CREATE INDEX IF NOT EXISTS idx_tests_session ON tests(session_id);
            CREATE INDEX IF NOT EXISTS idx_tests_active ON tests(is_active);
            CREATE INDEX IF NOT EXISTS idx_answers_test ON answers(test_id);
            CREATE INDEX IF NOT EXISTS idx_answers_device ON answers(test_id, device_hash);
            CREATE INDEX IF NOT EXISTS idx_sessions_hidden ON sessions(is_hidden);
            CREATE INDEX IF NOT EXISTS idx_sessions_created ON sessions(created_at);
            ";
            
            $pdo->exec($indexes);
            echo "<div class='success'>✅ Индексы успешно созданы!</div>";
            
            // Добавляем внешние ключи (если их ещё нет)
            try {
                $pdo->exec("
                    ALTER TABLE sessions 
                    ADD CONSTRAINT IF NOT EXISTS fk_initial_test 
                    FOREIGN KEY (initial_test_id) REFERENCES tests(id) ON DELETE SET NULL
                ");
                $pdo->exec("
                    ALTER TABLE sessions 
                    ADD CONSTRAINT IF NOT EXISTS fk_final_test 
                    FOREIGN KEY (final_test_id) REFERENCES tests(id) ON DELETE SET NULL
                ");
                echo "<div class='success'>✅ Внешние ключи успешно созданы!</div>";
            } catch (PDOException $e) {
                echo "<div class='info'>ℹ️ Внешние ключи уже существуют или не требуются</div>";
            }
            
            echo "<div class='success'><strong>🎉 Установка завершена успешно!</strong></div>";
            
            echo "<div class='warning'>
                <strong>⚠️ ВАЖНО:</strong><br>
                1. Удалите файл <code>install.php</code> с сервера для безопасности<br>
                2. Или измените переменную <code>\$allow_install = false;</code> в начале файла
            </div>";
            
            // Проверяем, какие таблицы есть в БД
            echo "<h3>📋 Созданные таблицы:</h3>";
            $tables = $pdo->query("
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = 'public' 
                ORDER BY table_name
            ")->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<ul>";
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
        
        <div class="info">
            <strong>📌 Следующие шаги:</strong>
            <ol>
                <li>Удалите файл <code>install.php</code></li>
                <li>Перейдите к панели спикера: <a href="/speaker/index.php">speaker/index.php</a></li>
            </ol>
        </div>
    </div>
</body>
</html>