<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

echo "<h2>➕ Добавление вопросов для новых типов тестов</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;font-family:monospace;'>";

// Проверяем, есть ли уже вопросы general_initial
$exists = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'general_initial'")->fetchColumn();

if ($exists > 0) {
    echo "⚠️ Вопросы general_initial уже существуют. Пропускаем.\n";
} else {
    echo "Добавляем general_initial (8 вопросов)...\n";
    
    $questions = [
        ["В интернете можно анонимно оскорблять других, и за это ничего не будет.", "bullying", "disagree", "general_initial"],
        ["Если друг просит перевести деньги через соцсеть, нужно сначала позвонить и уточнить.", "fraud", "agree", "general_initial"],
        ["Лайки и комментарии в соцсетях не влияют на самооценку подростков.", "etiquette", "disagree", "general_initial"],
        ["Если новость выглядит шокирующей, её нужно перепроверить на других сайтах.", "fakes", "agree", "general_initial"],
        ["Кибербуллинг — это когда кто-то постоянно пишет неприятные сообщения в интернете.", "bullying", "agree", "general_initial"],
        ["Банк никогда не просит назвать код из СМС по телефону.", "fraud", "agree", "general_initial"],
        ["Можно публиковать личные данные друзей без их разрешения.", "etiquette", "disagree", "general_initial"],
        ["Фотографии в интернете могут быть подделаны с помощью нейросетей.", "fakes", "agree", "general_initial"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "✅ Добавлено 8 вопросов\n";
}

// general_final
$exists = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'general_final'")->fetchColumn();
if ($exists > 0) {
    echo "⚠️ Вопросы general_final уже существуют. Пропускаем.\n";
} else {
    echo "Добавляем general_final (8 вопросов)...\n";
    
    $questions = [
        ["Анонимность в интернете позволяет безнаказанно оскорблять других.", "bullying", "disagree", "general_final"],
        ["Перевод денег другу по просьбе в соцсети — это безопасно.", "fraud", "disagree", "general_final"],
        ["Публикация чужих фото без разрешения — это нарушение этикета.", "etiquette", "agree", "general_final"],
        ["Любую шокирующую новость можно принимать на веру.", "fakes", "disagree", "general_final"],
        ["Кибербуллинг может привести к серьёзным психологическим проблемам.", "bullying", "agree", "general_final"],
        ["Настоящий банк может попросить код из СМС по телефону.", "fraud", "disagree", "general_final"],
        ["Личные переписки можно показывать другим без разрешения.", "etiquette", "disagree", "general_final"],
        ["Проверка источника новости занимает много времени.", "fakes", "disagree", "general_final"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "✅ Добавлено 8 вопросов\n";
}

// fake_hunter_initial
$exists = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'fake_hunter_initial'")->fetchColumn();
if ($exists > 0) {
    echo "⚠️ Вопросы fake_hunter_initial уже существуют. Пропускаем.\n";
} else {
    echo "Добавляем fake_hunter_initial (8 вопросов)...\n";
    
    $questions = [
        ["Вам прислали ссылку на 'выигрыш в лотерею' от незнакомого номера. Нужно перейти и проверить.", "fraud", "disagree", "fake_hunter_initial"],
        ["Если друг просит срочно перевести деньги в соцсети, это может быть взлом.", "fraud", "agree", "fake_hunter_initial"],
        ["Новость без ссылки на источник — скорее всего фейк.", "fakes", "agree", "fake_hunter_initial"],
        ["Сайт просит ввести данные карты для 'подтверждения возраста' — это нормально.", "fraud", "disagree", "fake_hunter_initial"],
        ["Шокирующие новости нужно проверять на 2-3 авторитетных сайтах.", "fakes", "agree", "fake_hunter_initial"],
        ["Незнакомец предлагает 'легкий заработок' за перевод денег через вашу карту — это мошенничество.", "fraud", "agree", "fake_hunter_initial"],
        ["QR-код для 'получения подарка' может вести на вредоносный сайт.", "fraud", "agree", "fake_hunter_initial"],
        ["Сообщение от 'банка' с просьбой назвать код из СМС — это мошенники.", "fraud", "agree", "fake_hunter_initial"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "✅ Добавлено 8 вопросов\n";
}

// fake_hunter_final
$exists = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'fake_hunter_final'")->fetchColumn();
if ($exists > 0) {
    echo "️ Вопросы fake_hunter_final уже существуют. Пропускаем.\n";
} else {
    echo "Добавляем fake_hunter_final (8 вопросов)...\n";
    
    $questions = [
        ["Какой главный признак фишингового сайта?", "fraud", "agree", "fake_hunter_final"],
        ["Что такое 'социальная инженерия'?", "fraud", "agree", "fake_hunter_final"],
        ["Как лучше всего проверить шокирующую новость?", "fakes", "agree", "fake_hunter_final"],
        ["Вам прислали QR-код для 'получения подарка'. Ваши действия?", "fraud", "disagree", "fake_hunter_final"],
        ["Можно ли доверять сообщению в мессенджере от 'банка' с просьбой назвать код из СМС?", "fraud", "disagree", "fake_hunter_final"],
        ["Что делать, если друг просит деньги в соцсети?", "fraud", "agree", "fake_hunter_final"],
        ["Что означает термин 'фейк'?", "fakes", "agree", "fake_hunter_final"],
        ["Где безопаснее всего хранить пароли?", "fraud", "agree", "fake_hunter_final"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "✅ Добавлено 8 вопросов\n";
}

// general_offline
$exists = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'general_offline'")->fetchColumn();
if ($exists > 0) {
    echo "️ Вопросы general_offline уже существуют. Пропускаем.\n";
} else {
    echo "Добавляем general_offline (8 вопросов)...\n";
    
    $questions = [
        ["В интернете можно анонимно оскорблять других, и за это ничего не будет.", "bullying", "disagree", "general_offline"],
        ["Если друг просит перевести деньги через соцсеть, нужно сначала позвонить и уточнить.", "fraud", "agree", "general_offline"],
        ["Лайки и комментарии в соцсетях не влияют на самооценку подростков.", "etiquette", "disagree", "general_offline"],
        ["Если новость выглядит шокирующей, её нужно перепроверить на других сайтах.", "fakes", "agree", "general_offline"],
        ["Кибербуллинг — это когда кто-то постоянно пишет неприятные сообщения в интернете.", "bullying", "agree", "general_offline"],
        ["Банк никогда не просит назвать код из СМС по телефону.", "fraud", "agree", "general_offline"],
        ["Можно публиковать личные данные друзей без их разрешения.", "etiquette", "disagree", "general_offline"],
        ["Фотографии в интернете могут быть подделаны с помощью нейросетей.", "fakes", "agree", "general_offline"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "✅ Добавлено 8 вопросов\n";
}

// fake_hunter_offline
$exists = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'fake_hunter_offline'")->fetchColumn();
if ($exists > 0) {
    echo "⚠️ Вопросы fake_hunter_offline уже существуют. Пропускаем.\n";
} else {
    echo "Добавляем fake_hunter_offline (8 вопросов)...\n";
    
    $questions = [
        ["Вам прислали ссылку на 'выигрыш в лотерею' от незнакомого номера. Нужно перейти.", "fraud", "disagree", "fake_hunter_offline"],
        ["Если друг просит срочно перевести деньги в соцсети, это может быть взлом.", "fraud", "agree", "fake_hunter_offline"],
        ["Новость без ссылки на источник — скорее всего фейк.", "fakes", "agree", "fake_hunter_offline"],
        ["Сайт просит ввести данные карты для 'подтверждения возраста' — это нормально.", "fraud", "disagree", "fake_hunter_offline"],
        ["Шокирующие новости нужно проверять на 2-3 авторитетных сайтах.", "fakes", "agree", "fake_hunter_offline"],
        ["Незнакомец предлагает 'легкий заработок' за перевод денег через вашу карту.", "fraud", "agree", "fake_hunter_offline"],
        ["QR-код для 'получения подарка' может вести на вредоносный сайт.", "fraud", "agree", "fake_hunter_offline"],
        ["Сообщение от 'банка' с просьбой назвать код из СМС — это мошенники.", "fraud", "agree", "fake_hunter_offline"]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }
    echo "✅ Добавлено 8 вопросов\n";
}

echo "\n✅ ВСЕ ВОПРОСЫ ДОБАВЛЕНЫ!\n";
echo "\n⚠️ УДАЛИ ЭТОТ ФАЙЛ С СЕРВЕРА!\n";
echo "</pre>";
?>