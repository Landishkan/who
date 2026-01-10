
const gameData = [
    {
        "title": "В вакцинах Pfizer нашли наночипы? Лабораторный анализ вызвал вопросы",
        "text": "Независимая лаборатория BioCheck Research опубликовала отчёт, в котором утверждает о возможном обнаружении микроскопических проводящих частиц в некоторых партиях вакцины Pfizer. Исследователи пока не подтвердили их происхождение и назначение, но призвали регуляторы к дополнительной проверке. В Pfizer уже назвали эти данные \"ошибочными и научно необоснованными\".",
        "isFake": true,
        "explanation": "<i class='fas fa-microscope'></i><strong>Научный фейк!</strong><br>• FDA опубликовало полный состав вакцин<br>• Графеновые наночипы не используется в производстве<br>• Это является конспирологической теорией "
    },
    {
        "title": "OpenAI запускает платный ChatGPT Professional",
        "text": "С 15 января 2024 года OpenAI вводит профессиональную подписку за $42/месяц с гарантированной доступностью 99.9%. Бесплатная версия останется, но с ограничением 20 запросов в час. Об этом сообщил CTO OpenAI Грег Брокман в официальном блоге компании.",
        "isFake": false,
        "explanation": "<i class='fas fa-robot'></i><strong>Факт подтверждён</strong><br>• Анонс в блоге openai.com<br>• Цены соответствуют пресс-релизу<br>• Грег Брокман действительно CTO<br>• Лимиты указаны точно"
    },
    {
        "title": "Apple раздаёт 1000 MacBook Pro: акция к юбилею компании",
        "text": "В честь 45-летия Apple объявила розыгрыш 1000 ноутбуков MacBook Pro с чипом M3 среди пользователей соцсетей. Для участия нужно сделать репост официального поста и отметить друзей. Об акции лично сообщил гендиректор Тим Кук в своём Twitter-аккаунте. Розыгрыш продлится до конца месяца.",
        "isFake": true,
        "explanation": "<i class='fas fa-laptop'></i><strong>Мошенническая схема!</strong><br>• Apple не проводит такие акции<br>• Официальные акции только на apple.com<br>• Нет подтверждения от Тина Кука"
    },
    {
        "title": "Учёный NASA: \"На Луне есть объекты, которые мы не можем объяснить\"",
        "text": "Доктор Ричард Хейл, бывший сотрудник NASA, в интервью научному каналу заявил, что в архивах агентства есть снимки лунной поверхности с \"аномальными геометрическими структурами\". По его словам, эти объекты в кратере Тихо не похожи на природные образования, но NASA пока не комментирует их происхождение, ссылаясь на необходимость дополнительного изучения.",
        "isFake": true,
        "explanation": "<i class='fas fa-moon'></i><strong>Фальшивка!</strong><br>• «Сотрудник» не числится в базе NASA<br>• Кратер Тихо постоянно мониторится"
    },
    {
        "title": "WhatsApp изменит тарифы для бизнес-аккаунтов с апреля 2025",
        "text": "Meta Platforms объявила о новом тарифном плане для бизнес-коммуникаций в WhatsApp. Сообщения компаний клиентам теперь будут стоить от $0.10 за текст и $0.30 за медиафайл. Однако ответы клиентов и личные чаты останутся полностью бесплатными. Изменения коснутся только официальных бизнес-аккаунтов.",
        "isFake": true,
        "explanation": "<i class='fas fa-comment-dots'></i><strong>Фейк</strong><br>• WhatsApp НЕ будет брать плату с клиентов за их сообщения<br>• Личные чаты остаются бесплатными даже при контакте с бизнесом<br>• Нет подтверждения от Meta или авторитетных СМИ<br>• Реальные изменения касаются только шаблонных рассылок от бизнеса"
    },
    {
        "title": "Исследование: 5G может влиять на иммунитет привитых людей",
        "text": "Группа европейских исследователей опубликовала предварительные данные, согласно которым длительное воздействие высокочастотного излучения (в диапазоне 5G) может незначительно изменять клеточный метаболизм у вакцинированных людей. Учёные подчёркивают, что речь не идёт о \"перепрограммировании\" вакцин или создании \"распространителей вируса\", а лишь о гипотетическом фоновом влиянии, требующем дальнейшего изучения.",
        "isFake": true,
        "explanation": "<i class='fas fa-virus-slash'></i><strong>Фейк</strong><br>• Нет подтверждённых научных данных о влиянии 5G на вирусы или вакцины<br>• Вакцины не могут «перепрограммироваться» из-за радиоволн<br>• «Исследование» анонимное, без рецензирования и публикации в научных журналах<br>• ВОЗ и CDC неоднократно опровергали подобные мифы"
    },
    {
        "title": "ЕС вводит обязательную маркировку ИИ-контента",
        "text": "Согласно новому регламенту ЕС все материалы, созданные искусственным интеллектом, должны маркироваться специальным знаком. Это касается текстов, изображений и видео, сгенерированных такими системами как ChatGPT и Midjourney. Правило уже одобрено Европарламентом.",
        "isFake": false,
        "explanation": "<i class='fas fa-flag-eu'></i><strong>Подтверждённый факт</strong><br>• Регламент EU AI Act от июля 2024<br>• Опубликовано в Official Journal EU"
    },
    {
        "title": "В Microsoft рассматривают модель подписки для будущих версий Windows",
        "text": "По данным инсайдеров из Microsoft, компания обсуждает введение подписочной модели \"Windows 12+\" как дополнительной опции для корпоративных клиентов. Она может включать расширенную поддержку и облачные функции. При этом традиционные разовые лицензии для домашних пользователей останутся. Официального анонса пока не было.",
        "isFake": true,
        "explanation": "<i class='fas fa-window-restore'></i><strong>Фейковые утечки</strong><br>• Microsoft продолжает разовые продажи<br> Нет упоминания на microsoft.com<br>• Цена не соответствует политике компании"
    },
    {
        "title": "Neuralink анонсировала партнёрство со смартфоном Tesla Model π",
        "text": "Илон Маск на конференции по нейротехнологиям заявил о начале сотрудничества между Neuralink и условным \"Tesla Devices Division\" над экспериментальным интерфейсом для смартфона. Прототип якобы позволяет управлять некоторыми функциями телефона через имплант Neuralink. Серийный выпуск устройства пока не планируется, это лишь исследовательский проект.",
        "isFake": true,
        "explanation": "<i class='fas fa-brain'></i><strong>Глубокий фейк</strong><br>• Tesla не производит смартфоны<br>• Технология не запатентована<br>• Нет презентации на канале Маска<br>• Домен teslamodelpi.com зарегистрирован мошенниками"
    },
   {
        "title": "Минцифры меняет правила входа на «Госуслуги»: SMS с телефона больше не работает",
        "text": "С декабря 2025 года для входа в аккаунт на «Госуслугах» со смартфона больше нельзя использовать привычные SMS-коды. Ведомство рекомендует подтверждать вход через мессенджер Max, специальное приложение-генератор (TOTP) или биометрию. Это решение связано с участившимися случаями, когда мошенники под разными предлогами выманивали у людей одноразовые пароли из SMS. Для входа с компьютера или ноутбука вариант с SMS пока остаётся.",
        "isFake": false,
        "explanation": "<i class='fas fa-mobile-alt'></i><strong>Проверенный факт</strong><br>• Информация подтверждена Минцифры и порталом Госуслуг<br>• Изменения вступили в силу с декабря 2025 года<br>• Причина — защита от мошенничества с перехватом кодов<br>• Для ПК старый способ пока доступен"
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

