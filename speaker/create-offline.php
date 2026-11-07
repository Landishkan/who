<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/districts.php';
require_once __DIR__ . '/check-auth.php';

$success = false;
$session_id = null;
$calculator_link = '';
$error = '';

// Получаем логин текущего спикера из сессии
$current_speaker_login = $_SESSION['speaker_login'] ?? 'speaker';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $district = trim($_POST['district'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $age_group = trim($_POST['age_group'] ?? '');
    $speaker_name = trim($_POST['speaker_name'] ?? '');
    $format = trim($_POST['format'] ?? 'Лекция');
    $test_type = trim($_POST['test_type'] ?? 'general');
    $note = trim($_POST['note'] ?? '');
    
    if (empty($district) || empty($institution)) {
        $error = "Район и название учреждения обязательны для заполнения.";
    } else {
        try {
            $pdo = getDB();
            // Создаем сессию с новыми полями
            $stmt = $pdo->prepare("
                INSERT INTO sessions (district, institution, age_group, speaker_name, format, mode, test_type, note, speaker_login, initial_status, final_status)
VALUES (:district, :institution, :age_group, :speaker_name, :format, 'offline', 'pending', 'pending')            ");
            $stmt->execute([
                ':district' => $district,
                ':institution' => $institution,
                ':age_group' => $age_group,
                ':speaker_name' => $speaker_name,
                ':format' => $format,
                ':test_type' => $test_type,
                ':note' => $note,
                ':speaker_login' => $current_speaker_login
            ]);
            $session_id = $pdo->lastInsertId();
            $calculator_link = "offline-calculator.php?session_id=" . $session_id;
            $success = true;
        } catch (Exception $e) {
            $error = "Ошибка при создании: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать офлайн-тест - КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .speaker-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 15px; padding: 40px; margin: 30px auto; max-width: 800px; box-shadow: 0 0 30px rgba(75, 205, 235, 0.2); }
        .speaker-panel h1 { font-family: 'Orbitron', monospace; color: #4bcde7; text-align: center; margin-bottom: 30px; font-size: 32px; text-transform: uppercase; letter-spacing: 2px; }
        .cyber-form .form-group { margin-bottom: 25px; }
        .cyber-form label { display: block; font-family: 'Rajdhani', sans-serif; font-weight: 600; font-size: 18px; color: #4bcde7; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .cyber-form input, .cyber-form select, .cyber-form textarea { width: 100%; padding: 15px; background: rgba(0, 0, 0, 0.6) !important; border: 2px solid rgba(75, 205, 235, 0.5); border-radius: 8px; color: #fff !important; font-family: 'Roboto', sans-serif; font-size: 16px; transition: all 0.3s ease; appearance: none; -webkit-appearance: none; -moz-appearance: none; box-sizing: border-box; }
        .cyber-form select option { background: #1a1a2e !important; color: #fff !important; padding: 10px; }
        .cyber-form input:focus, .cyber-form select:focus, .cyber-form textarea:focus { outline: none; border-color: #4bcde7; box-shadow: 0 0 15px rgba(75, 205, 235, 0.5); background: rgba(0, 0, 0, 0.8) !important; }
        .cyber-form input::placeholder, .cyber-form textarea::placeholder { color: rgba(255, 255, 255, 0.5); }
        .cyber-form textarea { resize: vertical; min-height: 80px; }
        .radio-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .radio-item { flex: 1; min-width: 200px; }
        .radio-item label { display: flex; align-items: center; gap: 10px; padding: 15px; background: rgba(0, 0, 0, 0.4); border: 2px solid rgba(75, 205, 235, 0.3); border-radius: 8px; cursor: pointer; transition: all 0.3s ease; font-family: 'Rajdhani', sans-serif; font-size: 16px; color: #fff; text-transform: none; }
        .radio-item input[type="radio"] { width: 20px; height: 20px; cursor: pointer; }
        .radio-item label:hover { border-color: #4bcde7; background: rgba(75, 205, 235, 0.1); }
        .radio-item input[type="radio"]:checked + label, .radio-item label:has(input[type="radio"]:checked) { border-color: #4bcde7; background: rgba(75, 205, 235, 0.2); box-shadow: 0 0 15px rgba(75, 205, 235, 0.3); }
        .cyber-button { display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #4bcde7 0%, #254883 100%); color: #fff; border: none; border-radius: 8px; font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; width: 100%; margin-top: 20px; }
        .cyber-button:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(75, 205, 235, 0.5); }
        .error-message { background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 25px; color: #ff6b6b; font-family: 'Rajdhani', sans-serif; font-weight: 600; }
        .success-box { background: rgba(40, 167, 69, 0.2); border: 2px solid #28a745; border-radius: 8px; padding: 25px; margin-bottom: 30px; text-align: center; }
        .success-box h2 { font-family: 'Orbitron', monospace; color: #28a745; margin-bottom: 15px; font-size: 24px; }
        .success-box p { font-family: 'Roboto', sans-serif; color: #fff; margin: 10px 0; }
        .link-container { background: rgba(0, 0, 0, 0.3); border: 2px solid rgba(75, 205, 235, 0.5); border-radius: 8px; padding: 20px; margin: 20px 0; }
        .link-container h3 { font-family: 'Rajdhani', sans-serif; color: #4bcde7; margin-bottom: 10px; font-size: 18px; }
        .url-link { display: block; font-family: 'Courier New', monospace; background: rgba(75, 205, 235, 0.1); padding: 15px; border-radius: 5px; color: #4bcde7; font-size: 14px; text-decoration: none; word-break: break-all; transition: all 0.3s ease; border: 1px dashed rgba(75, 205, 235, 0.3); }
        .url-link:hover { background: rgba(75, 205, 235, 0.2); border-color: #4bcde7; color: #fff; }
        .url-link i { margin-right: 8px; }
        .link-container .hint { font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-top: 10px; font-style: italic; }
        .back-button { display: inline-block; margin-top: 20px; padding: 12px 30px; background: rgba(255, 255, 255, 0.1); border: 2px solid #4bcde7; border-radius: 8px; color: #4bcde7; text-decoration: none; font-family: 'Rajdhani', sans-serif; font-weight: 600; transition: all 0.3s ease; }
        .back-button:hover { background: rgba(75, 205, 235, 0.2); transform: translateY(-2px); }
        .required { color: #ff6b6b; }
        .info-box { background: rgba(75, 205, 235, 0.1); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 8px; padding: 20px; margin-bottom: 25px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 16px; }
        .info-box i { color: #4bcde7; margin-right: 10px; }
        .test-type-info { background: rgba(75, 205, 235, 0.1); border-left: 4px solid #4bcde7; padding: 15px; margin-bottom: 20px; border-radius: 0 8px 8px 0; }
        .test-type-info p { margin: 5px 0; font-family: 'Rajdhani', sans-serif; font-size: 15px; color: rgba(255, 255, 255, 0.9); }
        .test-type-info strong { color: #4bcde7; }
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
                        <h2><i class="fas fa-check-circle"></i> Офлайн-сессия создана!</h2>
                        <p><strong>Сессия №<?= $session_id ?></strong> активирована</p>
                        <p>Тип теста: <strong><?= $test_type === 'fake_hunter' ? 'Охотник за фейками' : 'Общий' ?></strong></p>
                        <p>Калькулятор готов к использованию</p>
                    </div>
                    <div class="link-container">
                        <h3><i class="fas fa-calculator"></i> Офлайн-калькулятор:</h3>
                        <a href="offline-calculator.php?session_id=<?= $session_id ?>" class="url-link" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            https://cyberquestor.ru/speaker/offline-calculator.php?session_id=<?= $session_id ?>
                        </a>
                        <p class="hint">Нажмите на ссылку или кнопку ниже, чтобы открыть калькулятор</p>
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="offline-calculator.php?session_id=<?= $session_id ?>" class="cyber-button" style="display: inline-block; width: auto; padding: 15px 40px; text-decoration: none;">
                                <i class="fas fa-rocket"></i> Открыть калькулятор
                            </a>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <a href="index.php" class="back-button">
                            <i class="fas fa-arrow-left"></i> Вернуться в панель
                        </a>
                    </div>
                <?php else: ?>
                    <h1><i class="fas fa-calculator"></i> Создание офлайн-теста</h1>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <strong>Офлайн-режим</strong> — для случаев, когда у участников нет интернета или устройств.
                        Вы будете зачитывать вопросы залу и визуально оценивать реакцию, выбирая один из пяти вариантов ответа.
                    </div>
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
                            <label for="age_group">Возрастная группа <span class="required">*</span></label>
                            <select name="age_group" id="age_group" required>
                                <option value="">-- Выберите возраст --</option>
                                <option value="Менее 10 лет" <?= (isset($_POST['age_group']) && $_POST['age_group'] === 'Менее 10 лет') ? 'selected' : '' ?>>Менее 10 лет</option>
                                <option value="11-12 лет" <?= (isset($_POST['age_group']) && $_POST['age_group'] === '11-12 лет') ? 'selected' : '' ?>>11-12 лет</option>
                                <option value="13-14 лет" <?= (isset($_POST['age_group']) && $_POST['age_group'] === '13-14 лет') ? 'selected' : '' ?>>13-14 лет</option>
                                <option value="15-16 лет" <?= (isset($_POST['age_group']) && $_POST['age_group'] === '15-16 лет') ? 'selected' : '' ?>>15-16 лет</option>
                                <option value="17-18 лет" <?= (isset($_POST['age_group']) && $_POST['age_group'] === '17-18 лет') ? 'selected' : '' ?>>17-18 лет</option>
                                <option value="18+ лет" <?= (isset($_POST['age_group']) && $_POST['age_group'] === '18+ лет') ? 'selected' : '' ?>>18+ лет</option>
                            </select>
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
                        <div class="form-group">
                            <label>Тип теста <span class="required">*</span></label>
                            <div class="test-type-info">
                                <p><strong>Общий тест:</strong> базовые темы (буллинг, мошенничество, сетевой этикет, фейки)</p>
                                <p><strong>Охотник за фейками:</strong> углубленный тест по распознаванию фейков и мошенничества</p>
                            </div>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="test_type" id="test_general" value="general" <?= (!isset($_POST['test_type']) || $_POST['test_type'] === 'general') ? 'checked' : '' ?>>
                                    <label for="test_general">
                                        <i class="fas fa-shield-alt"></i> Общий тест
                                    </label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="test_type" id="test_fake_hunter" value="fake_hunter" <?= (isset($_POST['test_type']) && $_POST['test_type'] === 'fake_hunter') ? 'checked' : '' ?>>
                                    <label for="test_fake_hunter">
                                        <i class="fas fa-search"></i> Охотник за фейками
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="note">Примечание</label>
                            <textarea name="note" id="note" 
                                      placeholder="Личные заметки о мероприятии (не отображаются в общей статистике)"
                                      rows="3"><?= e($_POST['note'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="cyber-button">
                            <i class="fas fa-magic"></i> Создать и открыть калькулятор
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