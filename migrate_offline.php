<?php
require_once __DIR__ . '/config.php';

echo "<h2>🔧 Миграция для офлайн-калькулятора</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;font-family:monospace;'>";

try {
    $pdo = getDB();

    // 1. Добавляем колонку phase в offline_results (если нет)
    $cols = $pdo->query("SHOW COLUMNS FROM offline_results LIKE 'phase'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE offline_results ADD COLUMN phase VARCHAR(10) DEFAULT 'initial' AFTER verdict");
        echo "✅ Добавлена колонка 'phase' в таблицу offline_results\n";
    } else {
        echo "ℹ️ Колонка 'phase' уже существует\n";
    }

    // 2. Функция для добавления вопросов
    $addQuestions = function($type, $questions) use ($pdo) {
        $count = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = '$type'")->fetchColumn();
        if ($count > 0) {
            echo "ℹ️ Вопросы для '$type' уже есть ($count шт.). Пропускаем.\n";
            return;
        }
        $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
        foreach ($questions as $q) {
            $stmt->execute($q);
        }
        echo "✅ Добавлено 8 вопросов для '$type'\n";
    };

    // 3. Добавляем наборы вопросов
    $generalInitial = [
        ["В интернете можно анонимно оскорблять других, и за это ничего не будет.", "bullying", "disagree", "general_offline_initial"],
        ["Если друг просит перевести деньги через соцсеть, нужно сначала позвонить и уточнить.", "fraud", "agree", "general_offline_initial"],
        ["Лайки и комментарии в соцсетях не влияют на самооценку подростков.", "etiquette", "disagree", "general_offline_initial"],
        ["Если новость выглядит шокирующей, её нужно перепроверить на других сайтах.", "fakes", "agree", "general_offline_initial"],
        ["Кибербуллинг — это когда кто-то постоянно пишет неприятные сообщения.", "bullying", "agree", "general_offline_initial"],
        ["Банк никогда не просит назвать код из СМС по телефону.", "fraud", "agree", "general_offline_initial"],
        ["Можно публиковать личные данные друзей без их разрешения.", "etiquette", "disagree", "general_offline_initial"],
        ["Фотографии в интернете могут быть подделаны с помощью нейросетей.", "fakes", "agree", "general_offline_initial"]
    ];

    $generalFinal = [
        ["Анонимность в интернете позволяет безнаказанно оскорблять других.", "bullying", "disagree", "general_offline_final"],
        ["Перевод денег другу по просьбе в соцсети — это безопасно.", "fraud", "disagree", "general_offline_final"],
        ["Публикация чужих фото без разрешения — это нарушение этикета.", "etiquette", "agree", "general_offline_final"],
        ["Любую шокирующую новость можно принимать на веру.", "fakes", "disagree", "general_offline_final"],
        ["Кибербуллинг может привести к серьёзным психологическим проблемам.", "bullying", "agree", "general_offline_final"],
        ["Настоящий банк может попросить код из СМС по телефону.", "fraud", "disagree", "general_offline_final"],
        ["Личные переписки можно показывать другим без разрешения.", "etiquette", "disagree", "general_offline_final"],
        ["Проверка источника новости занимает много времени.", "fakes", "disagree", "general_offline_final"]
    ];

    $fakeHunterInitial = [
        ["Вам прислали ссылку на 'выигрыш в лотерею' от незнакомого номера. Нужно перейти.", "fraud", "disagree", "fake_hunter_offline_initial"],
        ["Если друг просит срочно перевести деньги в соцсети, это может быть взлом.", "fraud", "agree", "fake_hunter_offline_initial"],
        ["Новость без ссылки на источник — скорее всего фейк.", "fakes", "agree", "fake_hunter_offline_initial"],
        ["Сайт просит ввести данные карты для 'подтверждения возраста' — это нормально.", "fraud", "disagree", "fake_hunter_offline_initial"],
        ["Шокирующие новости нужно проверять на 2-3 авторитетных сайтах.", "fakes", "agree", "fake_hunter_offline_initial"],
        ["Незнакомец предлагает 'легкий заработок' за перевод денег через вашу карту.", "fraud", "agree", "fake_hunter_offline_initial"],
        ["QR-код для 'получения подарка' может вести на вредоносный сайт.", "fraud", "agree", "fake_hunter_offline_initial"],
        ["Сообщение от 'банка' с просьбой назвать код из СМС — это мошенники.", "fraud", "agree", "fake_hunter_offline_initial"]
    ];

    $fakeHunterFinal = [
        ["Фишинговый сайт всегда имеет подозрительный URL и просит личные данные.", "fraud", "agree", "fake_hunter_offline_final"],
        ["Социальная инженерия — это манипуляция людьми для получения информации.", "fraud", "agree", "fake_hunter_offline_final"],
        ["Фейк — это заведомо ложная информация, выдаваемая за правду.", "fakes", "agree", "fake_hunter_offline_final"],
        ["QR-коды от незнакомцев могут быть опасны.", "fraud", "agree", "fake_hunter_offline_final"],
        ["Банк никогда не просит коды из СМС в мессенджерах.", "fraud", "agree", "fake_hunter_offline_final"],
        ["При просьбе друга о деньгах в соцсети нужно позвонить ему.", "fraud", "agree", "fake_hunter_offline_final"],
        ["Менеджеры паролей — самое безопасное место для хранения паролей.", "fraud", "agree", "fake_hunter_offline_final"],
        ["Перед репостом шокирующей новости нужно проверить её на достоверность.", "fakes", "agree", "fake_hunter_offline_final"]
    ];

    $addQuestions('general_offline_initial', $generalInitial);
    $addQuestions('general_offline_final', $generalFinal);
    $addQuestions('fake_hunter_offline_initial', $fakeHunterInitial);
    $addQuestions('fake_hunter_offline_final', $fakeHunterFinal);

    echo "\n🎉 Миграция завершена! Теперь удалите этот файл.\n";

} catch (Exception $e) {
    echo "\n❌ Ошибка: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>