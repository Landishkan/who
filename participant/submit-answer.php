<?php
require_once __DIR__ . '/../config.php';

// Разрешаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: initial.php');
    exit;
}

$test_id = intval($_POST['test_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

if (!$test_id || empty($answers)) {
    die("Ошибка: некорректные данные теста.");
}

// Генерируем хэш устройства
function getDeviceHash() {
    $fingerprint = $_SERVER['REMOTE_ADDR'] . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
    return hash('sha256', $fingerprint);
}

$device_hash = getDeviceHash();

try {
    $pdo = getDB();

    // Начинаем транзакцию с блокировкой
    $pdo->beginTransaction();

    try {
        // Блокируем запись в таблице tests для этого теста
        $stmt = $pdo->prepare("SELECT id, type, is_active FROM tests WHERE id = ? FOR UPDATE");
        $stmt->execute([$test_id]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$test) {
            $pdo->rollBack();
            die("Тест не найден.");
        }

        if (!$test['is_active']) {
            $pdo->rollBack();
            die("Этот тест уже завершен спикером.");
        }

        // Проверяем внутри транзакции, не проходил ли уже этот пользователь данный тест
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM answers WHERE test_id = ? AND device_hash = ?");
        $stmt->execute([$test_id, $device_hash]);
        $existing_answers = $stmt->fetchColumn();
        
        if ($existing_answers > 0) {
            $pdo->rollBack();
            die("Вы уже прошли этот тест. Повторное прохождение невозможно.");
        }

        // Вставляем ответы
        $stmt = $pdo->prepare("INSERT INTO answers (test_id, question_id, answer, device_hash) VALUES (?, ?, ?, ?)");
        
        $saved_count = 0;
        foreach ($answers as $question_id => $answer) {
            if (!in_array($answer, ['agree', 'disagree'])) {
                continue;
            }
            $stmt->execute([$test_id, intval($question_id), $answer, $device_hash]);
            $saved_count++;
        }

        // Если сохранили хотя бы один ответ, увеличиваем счетчик участников
        if ($saved_count > 0) {
            $pdo->prepare("UPDATE tests SET participants_count = participants_count + 1 WHERE id = ?")
                ->execute([$test_id]);
        }

        $pdo->commit();
        
        $test_type = $test['type'];

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    die("Ошибка при сохранении результатов: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .success-container {
            max-width: 700px;
            margin: 50px auto;
            text-align: center;
        }
        
        .success-box {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(40, 167, 69, 0.5);
            border-radius: 15px;
            padding: 50px 30px;
            box-shadow: 0 0 40px rgba(40, 167, 69, 0.2);
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(40, 167, 69, 0.6);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .success-title {
            font-family: 'Orbitron', monospace;
            color: #28a745;
            font-size: 28px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .success-message {
            font-family: 'Rajdhani', sans-serif;
            font-size: 20px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .success-hint {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline"><?= $test_type === 'initial' ? 'Начальный тест' : 'Конечный тест' ?></p>
            </div>
            <nav class="header-nav">
                <a href="../index.html" class="nav-button">
                    <i class="fas fa-home"></i> На главную
                </a>
            </nav>
        </header>
        
        <main class="main-content">
            <div class="success-container animate__animated animate__zoomIn">
                <div class="success-box">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h2 class="success-title">Ответы сохранены!</h2>
                    
                    <?php if ($test_type === 'initial'): ?>
                        <p class="success-message">
                            Отлично! Твои ответы зафиксированы.<br>
                            Теперь внимательно слушай спикера и готовься к финальному тесту!
                        </p>
                        <p class="success-hint">Можешь закрыть эту страницу или просто свернуть браузер.</p>
                    <?php else: ?>
                        <p class="success-message">
                            Спасибо за участие!<br>
                            Твои ответы помогут нам сделать следующие мероприятия еще лучше.
                        </p>
                        <p class="success-hint">Мероприятие завершено. Хорошего дня!</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор</div>
            <div class="footer-links">
                <a href="../index.html" class="footer-link">Главная</a>
            </div>
        </footer>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>