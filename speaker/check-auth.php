<?php
// Этот файл подключается в начале каждого файла панели спикера
// Он проверяет, авторизован ли пользователь

require_once __DIR__ . '/../config.php';

session_start();

// Получаем данные из .env
$speaker_login = $_ENV['SPEAKER_LOGIN'] ?? 'speaker';
$speaker_password = $_ENV['SPEAKER_PASSWORD'] ?? 'default';

// Если нажали "Выйти"
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: auth.php');
    exit;
}

// Проверяем авторизацию
if (!isset($_SESSION['speaker_logged_in']) || $_SESSION['speaker_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}