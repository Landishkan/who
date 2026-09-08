<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/districts.php';

$success = false;
$session_id = null;
$error = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $district = trim($_POST['district'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $age_group = trim($_POST['age_group'] ?? '');
    $speaker_name = trim($_POST['speaker_name'] ?? '');
    $format = trim($_POST['format'] ?? 'Лекция');

    if (empty($district) || empty($institution)) {
        $error = "Район и название учреждения обязательны для заполнения.";
    } else {
        try {
            $pdo = getDB();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO sessions (district, institution, age_group, speaker_name, format, mode, initial_status, final_status)
                VALUES (:district, :institution, :age_group, :speaker_name, :format, 'online', 'pending', 'pending')
            ");
            $stmt->execute([
                ':district' => $district,
                ':institution' => $institution,
                ':age_group' => $age_group,
                ':speaker_name' => $speaker_name,
                ':format' => $format
            ]);
            $session_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO tests (session_id, type, is_active, participants_count)
                VALUES (:session_id, 'initial', 1, 0)
            ");
            $stmt->execute([':session_id' => $session_id]);
            $initial_test_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO tests (session_id, type, is_active, participants_count)
                VALUES (:session_id, 'final', 1, 0)
            ");
            $stmt->execute([':session_id' => $session_id]);
            $final_test_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                UPDATE sessions 
                SET initial_test_id = :initial_id, final_test_id = :final_id 
                WHERE id = :session_id
            ");
            $stmt->execute([
                ':initial_id' => $initial_test_id,
                ':final_id' => $final_test_id,
                ':session_id' => $session_id
            ]);

            $pdo->commit();
            $success = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Ошибка при создании теста: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать тест - КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .speaker-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(75, 205, 235, 0.3);
            border-radius: 15px;
            padding: 40px;
            margin: 30px auto;
            max-width: 800px;
            box-shadow: 0 0 30px rgba(75, 205, 235, 0.2);
        }
        
        .speaker-panel h1 {
            font-family: 'Orbitron', monospace;
            color: #4bcde7;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .cyber-form .form-group {
            margin-bottom: 25px;
        }
        
        .cyber-form label {
            display: block;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            font-size: 18px;
            color: #4bcde7;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .cyber-form input,
        .cyber-form select {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(75, 205, 235, 0.5);
            border-radius: 8px;
            color: #fff;
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .cyber-form input:focus,
        .cyber-form select:focus {
            outline: none;
            border-color: #4bcde7;
            box-shadow: 0 0 15px rgba(75, 205, 235, 0.5);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .cyber-form input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .cyber-button {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #4bcde7 0%, #254883 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: 'Orbitron', monospace;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 20px;
        }
        .cyber-form select,
.cyber-form input {
    width: 100%;
    padding: 15px;
    background: rgba(0, 0, 0, 0.6) !important;
    border: 2px solid rgba(75, 205, 235, 0.5);
    border-radius: 8px;
    color: #fff !important;
    font-family: 'Roboto', sans-serif;
    font-size: 16px;
    transition: all 0.3s ease;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.cyber-form select option {
    background: #1a1a2e !important;
    color: #fff !important;
    padding: 10px;
}

.cyber-form select:focus,
.cyber-form input:focus {
    outline: none;
    border-color: #4bcde7;
    box-shadow: 0 0 15px rgba(75, 205, 235, 0.5);
    background: rgba(0, 0, 0, 0.8) !important;
}
        .cyber-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(75, 205, 235, 0.5);
        }
        
        .cyber-button:active {
            transform: translateY(-1px);
        }
        
        .error-message {
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            color: #ff6b6b;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
        }
        
        .success-box {
            background: rgba(40, 167, 69, 0.2);
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .success-box h2 {
            font-family: 'Orbitron', monospace;
            color: #28a745;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .success-box p {
            font-family: 'Roboto', sans-serif;
            color: #fff;
            margin: 10px 0;
        }
        
        .link-container {
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(75, 205, 235, 0.5);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .link-container h3 {
            font-family: 'Rajdhani', sans-serif;
            color: #4bcde7;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .link-container .url {
            font-family: 'Courier New', monospace;
            background: rgba(75, 205, 235, 0.1);
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
            color: #4bcde7;
            font-size: 14px;
        }
        
        .link-container .hint {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 10px;
            font-style: italic;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #4bcde7;
            border-radius: 8px;
            color: #4bcde7;
            text-decoration: none;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: rgba(75, 205, 235, 0.2);
            transform: translateY(-2px);
        }
        
        .required {
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Панель спикера</p>
            </div>
            <nav class="header-nav">
                <a href="index.php" class="nav-button">
                    <i class="fas fa-home"></i> В панель
                </a>
            </nav>
        </header>
        
        <main class="main-content">
            <div class="speaker-panel animate__animated animate__fadeIn">
                <?php if ($success): ?>
                    <div class="success-box">
                        <h2><i class="fas fa-check-circle"></i> Тест успешно создан!</h2>
                        <p><strong>Сессия №<?= $session_id ?></strong> активирована</p>
                        <p>Созданы начальный и конечный тесты для мероприятия</p>
                    </div>

                    <div class="link-container">
                        <h3><i class="fas fa-qrcode"></i> Начальный тест (показать в начале):</h3>
                        <div class="url">https://cyberquestor.ru/participant/initial.php</div>
                        <p class="hint">Сгенерируйте QR-код из этой ссылки и выведите на экран</p>
                    </div>

                    <div class="link-container">
                        <h3><i class="fas fa-qrcode"></i> Конечный тест (показать в конце):</h3>
                        <div class="url">https://cyberquestor.ru/participant/final.php</div>
                        <p class="hint">Этот QR-код покажете после мероприятия</p>
                    </div>

                    <div style="text-align: center;">
                        <a href="index.php" class="back-button">
                            <i class="fas fa-arrow-left"></i> Вернуться в панель
                        </a>
                    </div>

                <?php else: ?>
                    <h1><i class="fas fa-plus-circle"></i> Создание онлайн-теста</h1>
                    
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i> <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="cyber-form">
                        <div class="form-group">
                            <label for="district">Район <span class="required">*</span></label>
                            <select name="district" id="district" required>
                                <option value="">-- Выберите район --</option>
                                <?php foreach ($districts as $district): ?>
                                    <option value="<?= e($district) ?>" <?= (isset($_POST['district']) && $_POST['district'] === $district) ? 'selected' : '' ?>>
                                        <?= e($district) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="institution">Название учреждения <span class="required">*</span></label>
                            <input type="text" name="institution" id="institution" required 
                                   placeholder="Например: Школа №5 или ДК Юность"
                                   value="<?= e($_POST['institution'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="age_group">Возраст / Класс</label>
                            <input type="text" name="age_group" id="age_group" 
                                   placeholder="Например: 9А или 12-14 лет"
                                   value="<?= e($_POST['age_group'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="speaker_name">Имя спикера</label>
                            <input type="text" name="speaker_name" id="speaker_name" 
                                   placeholder="Как к вам обращаться в статистике"
                                   value="<?= e($_POST['speaker_name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="format">Формат мероприятия</label>
                            <select name="format" id="format">
                                <option value="Лекция" <?= (isset($_POST['format']) && $_POST['format'] === 'Лекция') ? 'selected' : '' ?>>Лекция</option>
                                <option value="Квиз" <?= (isset($_POST['format']) && $_POST['format'] === 'Квиз') ? 'selected' : '' ?>>Квиз</option>
                                <option value="Игра" <?= (isset($_POST['format']) && $_POST['format'] === 'Игра') ? 'selected' : '' ?>>Игра</option>
                                <option value="Другое" <?= (isset($_POST['format']) && $_POST['format'] === 'Другое') ? 'selected' : '' ?>>Другое</option>
                            </select>
                        </div>

                        <button type="submit" class="cyber-button">
                            <i class="fas fa-magic"></i> Создать тест
                        </button>
                    </form>

                    <div style="text-align: center;">
                        <a href="index.php" class="back-button">
                            <i class="fas fa-arrow-left"></i> Вернуться в панель
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Панель спикера</div>
            <div class="footer-links">
                <a href="index.php" class="footer-link">Панель спикера</a>
                <a href="../index.html" class="footer-link">Главная</a>
            </div>
        </footer>
    </div>
    
    <script src="../js/script.js"></script>
</body>
</html>