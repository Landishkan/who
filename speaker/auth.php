<?php
require_once __DIR__ . '/../config.php';

session_start();

// Если уже авторизован — редирект на главную
if (isset($_SESSION['speaker_logged_in']) && $_SESSION['speaker_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$speaker_login = $_ENV['SPEAKER_LOGIN'] ?? 'speaker';
$speaker_password = $_ENV['SPEAKER_PASSWORD'] ?? 'default';

$error = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($login === $speaker_login && $password === $speaker_password) {
        $_SESSION['speaker_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в панель спикера | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .auth-container { max-width: 500px; margin: 100px auto; }
        .auth-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 40px; }
        .auth-card h1 { font-family: 'Orbitron', monospace; color: #4bcde7; text-align: center; margin-bottom: 30px; font-size: 24px; }
        .auth-form .form-group { margin-bottom: 25px; }
        .auth-form label { display: block; font-family: 'Rajdhani', sans-serif; font-weight: 600; font-size: 16px; color: #4bcde7; margin-bottom: 10px; text-transform: uppercase; }
        .auth-form input { width: 100%; padding: 15px; background: rgba(0, 0, 0, 0.6); border: 2px solid rgba(75, 205, 235, 0.5); border-radius: 8px; color: #fff; font-size: 16px; }
        .auth-form input:focus { outline: none; border-color: #4bcde7; box-shadow: 0 0 15px rgba(75, 205, 235, 0.5); }
        .auth-btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #4bcde7 0%, #254883 100%); border: none; border-radius: 8px; color: #fff; font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; text-transform: uppercase; cursor: pointer; transition: all 0.3s ease; }
        .auth-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(75, 205, 235, 0.5); }
        .error-message { background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545; border-radius: 8px; padding: 15px; margin-bottom: 25px; color: #ff6b6b; text-align: center; }
        .info-box { background: rgba(75, 205, 235, 0.1); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Панель спикера</p>
            </div>
        </header>
        
        <main class="main-content">
            <div class="auth-container animate__animated animate__fadeIn">
                <div class="auth-card">
                    <h1><i class="fas fa-lock"></i> Вход для спикера</h1>
                    
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i> 
                        Доступ только для авторизованных спикеров проекта
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i> <?= e($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="auth-form">
                        <div class="form-group">
                            <label for="login">Логин</label>
                            <input type="text" name="login" id="login" required autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <input type="password" name="password" id="password" required>
                        </div>
                        
                        <button type="submit" class="auth-btn">
                            <i class="fas fa-sign-in-alt"></i> Войти
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>