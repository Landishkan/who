<?php
require_once __DIR__ . '/config.php';

echo "<h2>🔧 Исправление вопросов для формата 'Согласен/Не согласен'</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;font-family:monospace;'>";

try {
    $pdo = getDB();
    
    // Удаляем старые вопросы fake_hunter_final
    $pdo->exec("DELETE FROM questions WHERE test_type = 'fake_hunter_final'");
    echo "✅ Удалены старые вопросы fake_hunter_final\n\n";
    
    // Добавляем новые вопросы в формате утверждений
    $questions = [
        ["Шокирующую новость лучше всего проверять на нескольких авторитетных сайтах.", "fakes", "agree", "fake_hunter_final"],
        ["Термин 'фейк' означает заведомо ложную информацию, выдаваемую за правду.", "fakes", "agree", "fake_hunter_final"],
        ["Главный признак фишингового сайта — подозрительный URL и просьба ввести личные данные.", "fraud", "agree", "fake_hunter_final"],
        ["Социальная инженерия — это манипуляция людьми для получения конфиденциальной информации.", "fraud", "agree", "fake_hunter_final"],
        ["QR-код для 'получения подарка' может вести на вредоносный сайт.", "fraud", "agree", "fake_hunter_final"],
        ["Сообщению в мессенджере от 'банка' с просьбой назвать код из СМС доверять нельзя.", "fraud", "agree", "fake_hunter_final"],
        ["Если друг просит деньги в соцсети, нужно связаться с ним по видеосвязи или телефону.", "fraud", "agree", "fake_hunter_final"],
        ["Пароли безопаснее всего хранить в менеджере паролей.", "fraud", "agree", "fake_hunter_final"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    
    echo "✅ Добавлено 8 новых вопросов в формате утверждений\n\n";
    
    // Проверяем результат
    $count = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'fake_hunter_final'")->fetchColumn();
    echo "Всего вопросов fake_hunter_final: $count\n";
    
    echo "\n⚠️ УДАЛИ ЭТОТ ФАЙЛ С СЕРВЕРА!\n";
    
} catch (Exception $e) {
    echo "\n❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>