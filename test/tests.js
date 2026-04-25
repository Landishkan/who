// Основная логика тестов
let currentTest = null;
let currentQuestionIndex = 0;
let userAnswers = [];
let startTime = null;
let timerInterval = null;
let timeSpent = 0;
let timerRunning = true;

// Элементы DOM
const levelSelectionScreen = document.getElementById('levelSelection');
const testScreen = document.getElementById('testScreen');
const completionScreen = document.getElementById('completionScreen');
const timerElement = document.getElementById('timer');
const timerToggle = document.getElementById('timerToggle');
const progressFill = document.getElementById('progressFill');
const progressPercentage = document.getElementById('progressPercentage');
const currentQuestionElement = document.getElementById('currentQuestion');
const questionContainer = document.getElementById('questionContainer');
const nextButton = document.getElementById('nextButton');
const prevButton = document.getElementById('prevButton');

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const topic = urlParams.get('topic');
    const level = urlParams.get('level');
    const action = urlParams.get('action');
    
    // ПРОВЕРКА: Если переданы и топик, и уровень (даже если уровень "special"), 
    // запускаем тест сразу
    if (topic && level) {
        window.currentTopic = topic; // Убеждаемся, что топик сохранен в глобальную переменную
        startTest(level); 
    } else if (topic && action === 'select-level') {
        showLevelSelection(topic);
    } else {
        window.location.href = '../tests.html';
    }
});

// Показать выбор уровня сложности
function showLevelSelection(topic) {
    const topicTitle = document.getElementById('topicTitle');
    const topicData = testData[topic];
    
    if (topicData) {
        topicTitle.innerHTML = `<i class="fas fa-shield-alt"></i> ${topicData.basic.title.split(' - ')[0]}`;
    }
    
    window.currentTopic = topic;
}

// Начать тест
function startTest(level) {
    const topic = window.currentTopic;
    
    if (!topic || !testData[topic] || !testData[topic][level]) {
        console.error('Тест не найден', {topic, level});
        return;
    }
    
    currentTest = testData[topic][level];
    currentQuestionIndex = 0;
    userAnswers = new Array(currentTest.questions.length).fill(null);
    startTime = new Date();
    timeSpent = 0;
    timerRunning = true;
    
    // Настройка интерфейса
    document.getElementById('testTopic').textContent = currentTest.title;
    
    // Логика отображения текста уровня
    const levelElement = document.getElementById('testLevel');
    if (level === 'basic') levelElement.textContent = 'Базовый уровень';
    else if (level === 'advanced') levelElement.textContent = 'Углубленный уровень';
    else if (level === 'special') levelElement.textContent = 'Лимитированный тест'; // Текст для твоего спец-теста
    
    // Скрываем выбор уровня и показываем тест
    levelSelectionScreen.style.display = 'none'; // Это принудительно скроет окно выбора
    testScreen.style.display = 'block';
    
    startTimer();
    loadQuestion();
}

// Загрузить вопрос
function loadQuestion() {
    if (!currentTest || currentQuestionIndex >= currentTest.questions.length) {
        return;
    }
    
    const question = currentTest.questions[currentQuestionIndex];
    
    // Обновление прогресса
    const progress = ((currentQuestionIndex + 1) / currentTest.questions.length) * 100;
    progressFill.style.width = `${progress}%`;
    progressPercentage.textContent = `${Math.round(progress)}%`;
    
    // Обновление номера вопроса
    currentQuestionElement.textContent = 
        `Вопрос ${currentQuestionIndex + 1} из ${currentTest.questions.length}`;
    
    // Создание HTML для вопроса
    questionContainer.innerHTML = `
        <div class="question-card animate-fade">
            <div class="question-text">
                ${question.question}
            </div>
            <div class="options-grid">
                ${question.options.map((option, index) => `
                    <div class="option-item ${userAnswers[currentQuestionIndex] === index ? 'selected' : ''}"
                         onclick="selectAnswer(${index})">
                        <div class="option-text">
                            <span class="option-letter">${String.fromCharCode(65 + index)}</span>
                            <span class="option-content">${option}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    // Обновление состояния кнопок
    prevButton.disabled = currentQuestionIndex === 0;
    // Скрываем кнопку "Назад" только для спец-теста
    if (window.currentTopic === 'limited-event') {
        prevButton.style.display = 'none';
    } else {
        prevButton.style.display = 'block';
        prevButton.disabled = currentQuestionIndex === 0;
    }
    nextButton.disabled = userAnswers[currentQuestionIndex] === null;
    
    if (currentQuestionIndex === currentTest.questions.length - 1) {
        nextButton.innerHTML = 'Завершить тест <i class="fas fa-flag-checkered"></i>';
    } else {
        nextButton.innerHTML = 'Далее <i class="fas fa-arrow-right"></i>';
    }
}

// Выбрать ответ
function selectAnswer(index) {
    if (!timerRunning) return;

    // Сохраняем ответ
    userAnswers[currentQuestionIndex] = index;

    // Если это наш спец-тест, делаем мгновенную проверку
    if (window.currentTopic === 'limited-event') {
        const question = currentTest.questions[currentQuestionIndex];
        const options = document.querySelectorAll('.option-item');
        
        // Блокируем клики по всем вариантам
        options.forEach(opt => opt.style.pointerEvents = 'none');

        // Подсвечиваем результат
        if (index === question.correct) {
            options[index].classList.add('correct');
        } else {
            options[index].classList.add('wrong');
            // Показываем правильный, чтобы было наглядно
            options[question.correct].classList.add('correct');
        }
    } else {
        // Обычная логика: просто подсвечиваем выбранный
        const options = document.querySelectorAll('.option-item');
        options.forEach(opt => opt.classList.remove('selected'));
        options[index].classList.add('selected');
    }

    nextButton.disabled = false;
}

// Следующий вопрос
function nextQuestion() {
    if (currentQuestionIndex >= currentTest.questions.length - 1) {
        finishTest();
        return;
    }
    
    currentQuestionIndex++;
    loadQuestion();
}

// Предыдущий вопрос
function previousQuestion() {
    if (currentQuestionIndex === 0) return;
    
    currentQuestionIndex--;
    loadQuestion();
}

// Завершить тест
function finishTest() {
    stopTimer();
    
    // 1. Получаем реальный уровень из URL (чтобы избежать путаницы с данными)
    const urlParams = new URLSearchParams(window.location.search);
    const actualLevel = urlParams.get('level') || 'basic'; 

    const correctAnswers = calculateCorrectAnswers();
    const totalQuestions = currentTest.questions.length;
    const percentage = Math.round((correctAnswers / totalQuestions) * 100);

    // 2. Собираем подсказки только для ошибок
    const hintsForErrors = [];
    currentTest.questions.forEach((question, index) => {
        if (userAnswers[index] !== question.correct) {
            hintsForErrors.push(question.hint || "Стоит повторить этот раздел.");
        }
    });

    // 3. Формируем объект результатов
    const results = {
        topicId: window.currentTopic,
        topic: getTopicName(window.currentTopic),
        level: actualLevel, // ИСПОЛЬЗУЕМ ПРОВЕРЕННЫЙ УРОВЕНЬ
        correct: correctAnswers,
        total: totalQuestions,
        percentage: percentage,
        time: formatTime(timeSpent),
        hints: hintsForErrors
    };

    // 4. Сохраняем и переходим
    localStorage.setItem('lastTestResults', JSON.stringify(results));
    window.location.href = 'results.html'; // Проверь, result.html или results.html (у тебя в коде было и так, и так)
}

// Показать результаты
function showResults() {
    window.location.href = 'results.html';
}

// Перезапустить тест
function restartTest() {
    // Получаем текущие параметры из URL
    const urlParams = new URLSearchParams(window.location.search);
    const topic = urlParams.get('topic');
    const level = urlParams.get('level');

    // Если это наш спец-тест (у него уже есть параметр level в URL)
    if (topic === 'limited-event' || level === 'special') {
        // Перезагружаем с сохранением уровня, чтобы не вылетало окно выбора
        window.location.href = `test.html?topic=${topic}&level=special`;
    } 
    // Если это обычный тест, где нужно выбрать сложность
    else if (topic) {
        window.location.href = `test.html?topic=${topic}&action=select-level`;
    } 
    // Если параметров нет, просто на главную
    else {
        window.location.href = '../tests.html';
    }
}
// Таймер
function startTimer() {
    if (timerInterval) clearInterval(timerInterval);
    
    timerInterval = setInterval(() => {
        if (timerRunning) {
            timeSpent++;
            updateTimerDisplay();
        }
    }, 1000);
}

function stopTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
}

function toggleTimer() {
    timerRunning = !timerRunning;
    timerToggle.innerHTML = timerRunning ? 
        '<i class="fas fa-pause"></i>' : 
        '<i class="fas fa-play"></i>';
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeSpent / 60);
    const seconds = timeSpent % 60;
    timerElement.innerHTML = `<i class="fas fa-clock"></i> ${padZero(minutes)}:${padZero(seconds)}`;
}

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${padZero(minutes)}:${padZero(secs)}`;
}

function padZero(num) {
    return num.toString().padStart(2, '0');
}

// Расчет правильных ответов
function calculateCorrectAnswers() {
    let correct = 0;
    
    currentTest.questions.forEach((question, index) => {
        if (userAnswers[index] === question.correct) {
            correct++;
        }
    });
    
    return correct;
}

// Вспомогательные функции
function getTopicName(topicId) {
    const names = {
        'cyberbullying': 'Кибербуллинг',
        'social-engineering': 'Социальная инженерия',
        'netiquette': 'Сетевой этикет',
        'cyberterrorism': 'Кибертерроризм',
        'fraud': 'Онлайн-мошенничество',
        'phishing': 'Фишинг',
        'destructive-content': 'Деструктивный контент',
        'echo-chambers': 'Эхо-камера', // изм
        'fakes': 'Фейки и дезинформация',
        'limited-event': 'Тест для участников и посетителей Казанского марафона'
    };
    
    return names[topicId] || topicId;
}

