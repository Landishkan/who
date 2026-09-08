<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/districts.php';

$success = false;
$session_id = null;
$initial_link = '';
$final_link = '';
$error = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $district = trim($_POST['district'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $age_group = trim($_POST['age_group'] ?? '');
    $speaker_name = trim($_POST['speaker_name'] ?? '');
    $format = trim($_POST['format'] ?? 'Лекция');

    // Базовая валидация
    if (empty($district) || empty($institution)) {
        $error = "Район и название учреждения обязательны для заполнения.";
    } else {
        try {
            $pdo = getDB();
            $pdo->beginTransaction();

            // 1. Создаем сессию (пока без ID тестов)
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

            // 2. Создаем начальный тест
            $stmt = $pdo->prepare("
                INSERT INTO tests (session_id, type, is_active, participants_count)
                VALUES (:session_id, 'initial', 1, 0)
            ");
            $stmt->execute([':session_id' => $session_id]);
            $initial_test_id = $pdo->lastInsertId();

            // 3. Создаем конечный тест
            $stmt = $pdo->prepare("
                INSERT INTO tests (session_id, type, is_active, participants_count)
                VALUES (:session_id, 'final', 1, 0)
            ");
            $stmt->execute([':session_id' => $session_id]);
            $final_test_id = $pdo->lastInsertId();

            // 4. Обновляем сессию, привязывая ID тестов
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

            // Формируем ссылки для участников
            // В реальном домене это будет https://cyberquestor.ru/participant/initial.php
            $base_url = "https://" . $_SERVER['HTTP_HOST'] . "/participant";
            $initial_link = $base_url . "/initial.php";
            $final_link = $base_url . "/final.php";
            
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
    <title>Создать онлайн-тест - КиберКвестор</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 20px; font-size: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; transition: border 0.3s; }
        input:focus, select:focus { outline: none; border-color: #3498db; }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-primary { background: #3498db; color: white; width: 100%; }
        .btn-primary:hover { background: #2980b9; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-secondary:hover { background: #7f8c8d; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        .success-box { background: #d4edda; color: #155724; padding: 20px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .link-box { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-top: 15px; word-break: break-all; font-family: monospace; }
        .qr-hint { font-size: 14px; color: #666; margin-top: 10px; }
        .back-link { display: block; margin-top: 20px; text-align: center; color: #3498db; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if ($success): ?>
                <h1>✅ Тест успешно создан!</h1>
                <div class="success-box">
                    <p><strong>Сессия №<?= $session_id ?></strong> активирована.</p>
                    <p style="margin-top: 10px;">Созданы начальный и конечный тесты для мероприятия.</p>
                </div>

                <h3> Ссылки для участников:</h3>
                
                <div class="link-box">
                    <strong>1. Начальный тест (показать в начале):</strong><br>
                    <?= e($initial_link) ?>
                </div>
                <p class="qr-hint">Сгенерируйте QR-код из этой ссылки и выведите на экран.</p>

                <div class="link-box">
                    <strong>2. Конечный тест (показать в конце):</strong><br>
                    <?= e($final_link) ?>
                </div>
                <p class="qr-hint">Этот QR-код покажете после мероприятия.</p>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="index.php" class="btn btn-secondary">Вернуться в панель</a>
                </div>

            <?php else: ?>
                <h1>➕ Создание онлайн-теста</h1>
                
                <?php if ($error): ?>
                    <div class="error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="district">Район *</label>
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
                        <label for="institution">Название учреждения *</label>
                        <input type="text" name="institution" id="institution" required 
                               placeholder="Например: Школа №5 или ДК Юность"
                               value="<?= e($_POST['institution'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="age_group">Возраст / Класс (необязательно)</label>
                        <input type="text" name="age_group" id="age_group" 
                               placeholder="Например: 9А или 12-14 лет"
                               value="<?= e($_POST['age_group'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="speaker_name">Имя спикера (необязательно)</label>
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

                    <button type="submit" class="btn btn-primary">Создать тест и получить ссылки</button>
                </form>

                <a href="index.php" class="back-link">← Вернуться в панель</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>