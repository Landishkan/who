<?php
require_once __DIR__ . '/config.php';

echo "<h2>➕ Добавление офлайн-вопросов</h2>";

try {
    $pdo = getDB();
    
    // Проверяем, есть ли уже офлайн-вопросы
    $count = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type LIKE '%_offline'")->fetchColumn();
    
    if ($count > 0) {
        echo "<div style='background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107; border-radius: 8px; padding: 20px; color: #fff;'>";
        echo "<strong>⚠️ Офлайн-вопросы уже существуют ($count шт.). Пропускаем.</strong>";
        echo "</div>";
    } else {
        // Вопросы для общего офлайн-теста (8 вопросов)
        $generalOfflineQuestions = [
            ["В интернете можно анонимно оскорблять других, и за это ничего не будет.", "bullying", "disagree", "general_offline"],
            ["Если друг просит перевести деньги через соцсеть, нужно сначала позвонить и уточнить.", "fraud", "agree", "general_offline"],
            ["Лайки и комментарии в соцсетях не влияют на самооценку подростков.", "etiquette", "disagree", "general_offline"],
            ["Если новость выглядит шокирующей, её нужно перепроверить на других сайтах.", "fakes", "agree", "general_offline"],
            ["Кибербуллинг — это когда кто-то постоянно пишет неприятные сообщения в интернете.", "bullying", "agree", "general_offline"],
            ["Банк никогда не просит назвать код из СМС по телефону.", "fraud", "agree", "general_offline"],
            ["Можно публиковать личные данные друзей без их разрешения.", "etiquette", "disagree", "general_offline"],
            ["Фотографии в интернете могут быть подделаны с помощью нейросетей.", "fakes", "agree", "general_offline"]
        ];
        
        // Вопросы для офлайн-теста "Охотник за фейками" (8 вопросов)
        $fakeHunterOfflineQuestions = [
            ["Вам прислали ссылку на 'выигрыш в лотерею' от незнакомого номера. Нужно перейти и проверить.", "fraud", "disagree", "fake_hunter_offline"],
            ["Если друг просит срочно перевести деньги в соцсети, это может быть взлом.", "fraud", "agree", "fake_hunter_offline"],
            ["Новость без ссылки на источник — скорее всего фейк.", "fakes", "agree", "fake_hunter_offline"],
            ["Сайт просит ввести данные карты для 'подтверждения возраста' — это нормально.", "fraud", "disagree", "fake_hunter_offline"],
            ["Шокирующие новости нужно проверять на 2-3 авторитетных сайтах.", "fakes", "agree", "fake_hunter_offline"],
            ["Незнакомец предлагает 'легкий заработок' за перевод денег через вашу карту — это мошенничество.", "fraud", "agree", "fake_hunter_offline"],
            ["QR-код для 'получения подарка' может вести на вредоносный сайт.", "fraud", "agree", "fake_hunter_offline"],
            ["Сообщение от 'банка' с просьбой назвать код из СМС — это мошенники.", "fraud", "agree", "fake_hunter_offline"]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
        
        foreach ($generalOfflineQuestions as $q) {
            $stmt->execute($q);
        }
        
        foreach ($fakeHunterOfflineQuestions as $q) {
            $stmt->execute($q);
        }
        
        echo "<div style='background: rgba(40, 167, 69, 0.2); border: 2px solid #28a745; border-radius: 8px; padding: 20px; color: #fff;'>";
        echo "<strong>✅ Добавлено 16 офлайн-вопросов (8 general + 8 fake_hunter)</strong>";
        echo "</div>";
    }
    
    echo "<div style='background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107; border-radius: 8px; padding: 20px; color: #fff; margin-top: 20px;'>";
    echo "<strong>⚠️ Теперь удалите этот файл с сервера!</strong>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545; border-radius: 8px; padding: 20px; color: #ff6b6b;'>";
    echo "❌ Ошибка: " . e($e->getMessage());
    echo "</div>";
}
?>