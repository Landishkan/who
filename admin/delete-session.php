<?php
require_once __DIR__ . '/../config.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth.php');
    exit;
}

$session_id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!$session_id) {
    die("Неверный ID сессии.");
}

try {
    $pdo = getDB();
    
    if ($action === 'hide') {
        // Мягкое удаление (скрыть из списков)
        $stmt = $pdo->prepare("UPDATE sessions SET is_hidden = 1 WHERE id = ?");
        $stmt->execute([$session_id]);
    } elseif ($action === 'delete') {
        // Физическое удаление сессии и всех связанных данных
        $pdo->beginTransaction();
        
        try {
            // Получаем ID тестов для удаления ответов
            $stmt = $pdo->prepare("SELECT initial_test_id, final_test_id FROM sessions WHERE id = ?");
            $stmt->execute([$session_id]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                throw new Exception("Сессия не найдена.");
            }
            
            // Удаляем ответы
            if ($session['initial_test_id']) {
                $pdo->prepare("DELETE FROM answers WHERE test_id = ?")->execute([$session['initial_test_id']]);
            }
            if ($session['final_test_id']) {
                $pdo->prepare("DELETE FROM answers WHERE test_id = ?")->execute([$session['final_test_id']]);
            }
            
            // Удаляем офлайн-результаты
            $pdo->prepare("DELETE FROM offline_results WHERE session_id = ?")->execute([$session_id]);
            
            // Удаляем тесты (каскадно удалятся ответы, если остались)
            $pdo->prepare("DELETE FROM tests WHERE session_id = ?")->execute([$session_id]);
            
            // Удаляем сессию
            $pdo->prepare("DELETE FROM sessions WHERE id = ?")->execute([$session_id]);
            
            $pdo->commit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } else {
        die("Неверное действие.");
    }
    
    header('Location: index.php?deleted=1');
    exit;
    
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}