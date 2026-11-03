<?php
// Этот файл подключается в начале каждого файла панели спикера
// Он проверяет, авторизован ли пользователь
require_once __DIR__ . '/../config.php';
session_start();

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

// 🔥 Дополнительная проверка: если в сессии нет логина (старая сессия до обновления),
// принудительно выходим, чтобы пользователь перезашел
if (!isset($_SESSION['speaker_login']) || empty($_SESSION['speaker_login'])) {
    session_destroy();
    header('Location: auth.php?relogin=1');
    exit;
}
?>