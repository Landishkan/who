// Данные игры
const gameData = [
    {
        title: "Вакцины Pfizer содержат наночипы для слежения?",
        text: "По данным «анонимных исследователей», вакцины Pfizer содержат графеновые наночипы, позволяющие отслеживать передвижения людей. В статье приводятся «результаты спектрального анализа»",
        isFake: true,
        explanation: "<i class='fas fa-microscope'></i><strong>Научный фейк!</strong><br>• FDA опубликовало полный состав вакцин<br>• Графеновые наночипы не используется в производстве<br>• Это является конспирологической теорией "
    },
    {
        title: "OpenAI запускает платный ChatGPT Professional",
        text: "С 15 января 2024 года OpenAI вводит профессиональную подписку за $42/месяц с гарантированной доступностью 99.9%. Бесплатная версия останется, но с ограничением 20 запросов в час. Об этом сообщил CTO OpenAI Грег Брокман в официальном блоге компании.",
        isFake: false,
        explanation: "<i class='fas fa-robot'></i><strong>Факт подтверждён</strong><br>• Анонс в блоге openai.com<br>• Цены соответствуют пресс-релизу<br>• Грег Брокман действительно CTO<br>• Лимиты указаны точно"
    },
    {
        title: "Apple раздаёт MacBook за репосты",
        text: "В честь 45-летия компании Apple запустила акцию: 1000 новых MacBook Pro M3 достанется пользователям, сделавшим репост этой новости и отметившим 5 друзей. Акция якобы одобрена Тином Куком.",
        isFake: true,
        explanation: "<i class='fas fa-laptop'></i><strong>Мошенническая схема!</strong><br>• Apple не проводит такие акции<br>• Официальные акции только на apple.com<br>• Нет подтверждения от Тина Кука"
    },
    {
        title: "NASA скрывает артефакты на Луне",
        text: "Бывший сотрудник NASA опубликовал «рассекреченные фото» с аномальными структурами в кратере Тихо. В интервью он утверждает, что агентство 20 лет скрывает доказательства внеземной цивилизации.",
        isFake: true,
        explanation: "<i class='fas fa-moon'></i><strong>Фальшивка!</strong><br>• «Сотрудник» не числится в базе NASA<br>• Кратер Тихо постоянно мониторится"
    },
    {
        title: "WhatsApp вводит плату за ЛЮБОЕ сообщение в бизнес-чатах",
        text: "С апреля 2025 года WhatsApp начнёт взимать плату за каждое сообщение в бизнес-переписке — даже ответы клиентов. Тариф: $0.10 за текст, $0.30 за медиафайл. Личные чаты также попадут под платную модель, если в них есть контакт бизнес-аккаунта. Решение уже вызвало бурю негодования у предпринимателей.",
        isFake: true,
        explanation: "<i class='fas fa-comment-dots'></i><strong>Фейк</strong><br>• WhatsApp НЕ будет брать плату с клиентов за их сообщения<br>• Личные чаты остаются бесплатными даже при контакте с бизнесом<br>• Нет подтверждения от Meta или авторитетных СМИ<br>• Реальные изменения касаются только шаблонных рассылок от бизнеса"
    },
    {
        title: "5G превращает вакцинированных в разносчиков COVID-19, утверждают «учёные»",
        text: "Анонимный «исследовательский коллектив» опубликовал доклад, в котором заявляет, что излучение 5G «перепрограммирует» вакцины от коронавируса, превращая привитых людей в активных распространителей мутировавшего штамма. В качестве доказательства приводятся «анализы крови» неизвестного происхождения.",
        isFake: true,
        explanation: "<i class='fas fa-virus-slash'></i><strong>Фейк</strong><br>• Нет подтверждённых научных данных о влиянии 5G на вирусы или вакцины<br>• Вакцины не могут «перепрограммироваться» из-за радиоволн<br>• «Исследование» анонимное, без рецензирования и публикации в научных журналах<br>• ВОЗ и CDC неоднократно опровергали подобные мифы"
    },
    {
        title: "ЕС вводит обязательную маркировку ИИ-контента",
        text: "С 2023 года согласно новому регламенту ЕС все материалы, созданные искусственным интеллектом, должны маркироваться специальным знаком. Это касается текстов, изображений и видео, сгенерированных такими системами как ChatGPT и Midjourney. Правило уже одобрено Европарламентом.",
        isFake: false,
        explanation: "<i class='fas fa-flag-eu'></i><strong>Подтверждённый факт</strong><br>• Регламент EU AI Act от декабря 2023<br>• Опубликовано в Official Journal EU"
    },
    {
        title: "Windows 12 будет подпиской за $7/мес",
        text: "На внутренней встрече Microsoft объявила о переходе на модель подписки. «Windows 12 Subscription» заменит лицензии с ежемесячной платой. В сети появились «скриншоты» панели управления с новой опцией оплаты.",
        isFake: true,
        explanation: "<i class='fas fa-window-restore'></i><strong>Фейковые утечки</strong><br>• Microsoft продолжает разовые продажи<br> Нет упоминания на microsoft.com<br>• Цена не соответствует политике компании"
    },
    {
        title: "Tesla Model π — смартфон с нейроинтерфейсом",
        text: "Илон Маск представил смартфон Tesla Model π с «прямым нейроподключением». Устройство якобы позволяет набирать текст силой мысли. Предзаказ открыт на «особых условиях» с оплатой в криптовалюте.",
        isFake: true,
        explanation: "<i class='fas fa-brain'></i><strong>Глубокий фейк</strong><br>• Tesla не производит смартфоны<br>• Технология не запатентована<br>• Нет презентации на канале Маска<br>• Домен teslamodelpi.com зарегистрирован мошенниками"
    },
    {
        title: "С 2025 года вход на «Госуслуги» только по биометрии",
        text: "Минцифры России объявило, что с января 2025 года вход на портал «Госуслуги» будет возможен исключительно через Единую биометрическую систему (ЕБС). Пароли и SMS-подтверждения отключат. Те, кто не сдал биометрию, потеряют доступ к сервису.",
        isFake: true,
        explanation: "<i class='fas fa-fingerprint'></i><strong>Фейк</strong><br>• Нет официального заявления о полном отказе от паролей и SMS<br>• Биометрия — лишь дополнительный, а не обязательный способ входа<br>• Доступ к базовым функциям останется без ЕБС<br>• Ведомство лишь рекомендует биометрию для усиления защиты"
    }
];

// Переменные игры
let currentQuestionIndex = 0;
let score = 0;
let questionsAnswered = 0;
let gameActive = true;

// DOM элементы
const contentTitle = document.getElementById('content-title');
const contentText = document.getElementById('content-text');
const fakeBtn = document.getElementById('fake-btn');
const trueBtn = document.getElementById('true-btn');
const scoreElement = document.getElementById('score');
const levelElement = document.getElementById('level');
const messagePopup = document.getElementById('messagePopup');
const gameEnd = document.getElementById('gameEnd');
const finalScore = document.getElementById('finalScore');
const restartBtn = document.getElementById('restartBtn');
const gameCard = document.getElementById('gameCard');

// Инициализация игры
function initGame() {
    // Перемешиваем вопросы
    shuffleArray(gameData);
    
    // Сбрасываем переменные
    currentQuestionIndex = 0;
    score = 0;
    questionsAnswered = 0;
    gameActive = true;
    
    // Обновляем интерфейс
    scoreElement.textContent = score;
    levelElement.textContent = `1/${gameData.length}`;
    gameEnd.classList.remove('show');
    gameCard.style.opacity = '1';
    
    // Показываем первый вопрос
    showQuestion();
}

// Перемешивание массива (алгоритм Фишера-Йетса)
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

// Показать текущий вопрос
function showQuestion() {
    if (currentQuestionIndex >= gameData.length) {
        endGame();
        return;
    }
    
    const question = gameData[currentQuestionIndex];
    contentTitle.textContent = question.title;
    contentText.textContent = question.text;
    levelElement.textContent = `${currentQuestionIndex + 1}/${gameData.length}`;
    
    // Включаем кнопки
    fakeBtn.disabled = false;
    trueBtn.disabled = false;
}

// Проверка ответа
function checkAnswer(userAnswer) {
    if (!gameActive) return;
    
    const question = gameData[currentQuestionIndex];
    const isCorrect = (userAnswer === 'fake' && question.isFake) || 
                      (userAnswer === 'true' && !question.isFake);
    
    // Отключаем кнопки во время показа результата
    fakeBtn.disabled = true;
    trueBtn.disabled = true;
    
    if (isCorrect) {
        score++;
        scoreElement.textContent = score;
        showMessage(question.explanation, 'correct');
    } else {
        showMessage(question.explanation, 'incorrect');
    }
    
    questionsAnswered++;
    currentQuestionIndex++;
    
    // Переходим к следующему вопросу через 3 секунды или по клику
    setTimeout(() => {
        if (currentQuestionIndex < gameData.length) {
            showQuestion();
        } else {
            endGame();
        }
    }, 3000);
}

// Показать сообщение с результатом
function showMessage(message, type) {
    messagePopup.innerHTML = message;
    messagePopup.className = `message-popup ${type}`;
    messagePopup.classList.add('show');
    
    // Скрываем сообщение через 2.5 секунды
    setTimeout(() => {
        messagePopup.classList.remove('show');
    }, 2500);
}

// Завершение игры
function endGame() {
    gameActive = false;
    finalScore.textContent = `Вы обнаружили ${score} из ${gameData.length} фейков`;
    
    // Показываем экран завершения с задержкой
    setTimeout(() => {
        gameCard.style.opacity = '0.3';
        gameEnd.classList.add('show');
    }, 1000);
}

// Обработчики событий
fakeBtn.addEventListener('click', () => checkAnswer('fake'));
trueBtn.addEventListener('click', () => checkAnswer('true'));
restartBtn.addEventListener('click', initGame);

// Запуск игры при загрузке страницы
document.addEventListener('DOMContentLoaded', initGame);

// Адаптация для мобильных устройств
function handleMobileView() {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Дополнительные настройки для мобильных устройств
        document.body.style.overflowX = 'hidden';
    }
}

// Обработчик изменения размера окна
window.addEventListener('resize', handleMobileView);

// Инициализация мобильного вида
handleMobileView();
