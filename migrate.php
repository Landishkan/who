<?php
require_once __DIR__ . '/config.php';

echo "<h2>🚀 Миграция базы данных КиберКвестор</h2>";

try {
    $pdo = getDB();
    echo "<div class='success'>✅ Подключение к БД успешно.</div>";

    // 1. Добавляем speaker_login в sessions (если нет)
    $cols = $pdo->query("SHOW COLUMNS FROM sessions LIKE 'speaker_login'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE sessions ADD COLUMN speaker_login VARCHAR(50) DEFAULT 'speaker' AFTER id");
        // Обновляем старые записи, присваивая им общую учетку 'speaker'
        $pdo->exec("UPDATE sessions SET speaker_login = 'speaker' WHERE speaker_login IS NULL OR speaker_login = ''");
        echo "<div class='success'>✅ Добавлено поле <code>speaker_login</code> и обновлены старые записи.</div>";
    } else {
        echo "<div class='info'>ℹ️ Поле <code>speaker_login</code> уже существует.</div>";
    }

    // 2. Добавляем test_type в sessions (если нет)
    $cols = $pdo->query("SHOW COLUMNS FROM sessions LIKE 'test_type'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE sessions ADD COLUMN test_type VARCHAR(20) DEFAULT 'general' AFTER mode");
        echo "<div class='success'>✅ Добавлено поле <code>test_type</code>.</div>";
    }

    // 3. Добавляем note (примечание) в sessions (если нет)
    $cols = $pdo->query("SHOW COLUMNS FROM sessions LIKE 'note'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE sessions ADD COLUMN note TEXT AFTER test_type");
        echo "<div class='success'>✅ Добавлено поле <code>note</code>.</div>";
    }

    // 4. Добавляем вопросы для "Охотника за фейками" (test_type = 'fake_hunter')
    // Проверяем, есть ли уже такие вопросы, чтобы не дублировать
    $count = $pdo->query("SELECT COUNT(*) FROM questions WHERE test_type = 'fake_hunter'")->fetchColumn();
    if ($count == 0) {
        $fakeHunterQuestions = [
            // Начальный тест (8 вопросов)
            ["Вам прислали ссылку на 'выигрыш в лотерею' от незнакомого номера. Ваши действия?", "Фейк/Мошенничество", "Игнорировать и заблокировать", "fake_hunter_initial"],
            ["Друг просит в соцсети срочно перевести 500 рублей, потому что 'взломали карту'. Что сделаете?", "Фейк/Мошенничество", "Позвонить другу и уточнить", "fake_hunter_initial"],
            ["В новостной группе написали сенсационный заголовок без ссылки на источник. Это...", "Фейк/Мошенничество", "Вероятный фейк", "fake_hunter_initial"],
            ["Сайт просит ввести данные банковской карты для 'подтверждения возраста'. Это...", "Фейк/Мошенничество", "Мошенничество", "fake_hunter_initial"],
            ["Вы увидели пост о том, что 'завтра отменят все уроки'. Как проверить?", "Фейк/Мошенничество", "Проверить на сайте школы/Минобра", "fake_hunter_initial"],
            ["Незнакомец предлагает 'легкий заработок' за перевод денег через вашу карту. Это...", "Фейк/Мошенничество", "Мошенничество (дроп)", "fake_hunter_initial"],
            ["Вам пишут, что вы выиграли iPhone, но нужно оплатить 'доставку'. Это...", "Фейк/Мошенничество", "Мошенничество", "fake_hunter_initial"],
            ["Фотография с текстом 'Шок! Ученые скрыли правду' без указания, каких именно ученых. Это...", "Фейк/Мошенничество", "Фейк", "fake_hunter_initial"],
            
            // Конечный тест (8 вопросов)
            ["Какой главный признак фишингового сайта?", "Фейк/Мошенничество", "Подозрительный адрес (URL) и просьба данных", "fake_hunter_final"],
            ["Что такое 'социальная инженерия'?", "Фейк/Мошенничество", "Манипуляция людьми для получения информации", "fake_hunter_final"],
            ["Как лучше всего проверить шокирующую новость?", "Фейк/Мошенничество", "Найти её на 2-3 авторитетных новостных сайтах", "fake_hunter_final"],
            ["Вам прислали QR-код для 'получения подарка'. Ваши действия?", "Фейк/Мошенничество", "Не сканировать, это может быть ссылка на вредоносный сайт", "fake_hunter_final"],
            ["Можно ли доверять сообщению в мессенджере от 'банка' с просьбой назвать код из СМС?", "Фейк/Мошенничество", "Нет, банк никогда не просит коды из СМС", "fake_hunter_final"],
            ["Что делать, если друг просит деньги в соцсети?", "Фейк/Мошенничество", "Связаться с ним по видеосвязи или телефону", "fake_hunter_final"],
            ["Что означает термин 'фейк'?", "Фейк/Мошенничество", "Заведомо ложная информация, выдаваемая за правду", "fake_hunter_final"],
            ["Где безопаснее всего хранить пароли?", "Фейк/Мошенничество", "В менеджере паролей", "fake_hunter_final"]
        ];

        $stmt = $pdo->prepare("INSERT INTO questions (text, theme, correct_answer, test_type) VALUES (?, ?, ?, ?)");
        foreach ($fakeHunterQuestions as $q) {
            $stmt->execute($q);
        }
        echo "<div class='success'>✅ Добавлено 16 вопросов для теста 'Охотник за фейками'.</div>";
    } else {
        echo "<div class='info'>ℹ️ Вопросы для 'Охотника за фейками' уже существуют.</div>";
    }

    echo "<hr><div class='success'><strong>🎉 Миграция успешно завершена!</strong></div>";
    echo "<div class='warning'>⚠️ Теперь вы можете удалить файл <code>migrate.php</code> с сервера.</div>";

} catch (PDOException $e) {
    echo "<div class='error'>❌ Ошибка БД: " . e($e->getMessage()) . "</div>";
}
?>  