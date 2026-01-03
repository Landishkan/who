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
    
    if (topic && level) {
        startTest(topic, level);
    } else if (topic && action === 'select-level') {
        showLevelSelection(topic);
    } else {
        window.location.href = 'index.html';
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
        console.error('Тест не найден');
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
    document.getElementById('testLevel').textContent = 
        level === 'basic' ? 'Базовый уровень' : 'Углубленный уровень';
    
    // Переключение экранов
    levelSelectionScreen.style.display = 'none';
    testScreen.style.display = 'block';
    
    // Запуск таймера
    startTimer();
    
    // Загрузка первого вопроса
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
    nextButton.disabled = userAnswers[currentQuestionIndex] === null;
    
    if (currentQuestionIndex === currentTest.questions.length - 1) {
        nextButton.innerHTML = 'Завершить тест <i class="fas fa-flag-checkered"></i>';
    } else {
        nextButton.innerHTML = 'Далее <i class="fas fa-arrow-right"></i>';
    }
}

// Выбрать ответ
function selectAnswer(answerIndex) {
    if (userAnswers[currentQuestionIndex] !== null) {
        return; // Ответ уже выбран
    }
    
    userAnswers[currentQuestionIndex] = answerIndex;
    
    // Обновление отображения выбранного ответа
    const optionItems = questionContainer.querySelectorAll('.option-item');
    optionItems.forEach((item, index) => {
        if (index === answerIndex) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
    
    // Активация кнопки "Далее"
    nextButton.disabled = false;
    
    // Автоматический переход к следующему вопросу через 1 секунду
    setTimeout(() => {
        if (currentQuestionIndex < currentTest.questions.length - 1) {
            nextQuestion();
        }
    }, 1000);
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
    // Остановка таймера
    stopTimer();
    
    // Расчет результатов
    const correctAnswers = calculateCorrectAnswers();
    const totalQuestions = currentTest.questions.length;
    const percentage = Math.round((correctAnswers / totalQuestions) * 100);
    
    // Сохранение результатов
    const results = {
        topic: currentTest.title.split(' - ')[0],
        topicId: window.currentTopic,
        level: currentTest.title.includes('Углубленный') ? 'advanced' : 'basic',
        correct: correctAnswers,
        total: totalQuestions,
        percentage: percentage,
        time: formatTime(timeSpent),
        timestamp: new Date().toISOString()
    };
    
    localStorage.setItem('lastTestResults', JSON.stringify(results));
    
    // Показать экран завершения
    testScreen.style.display = 'none';
    completionScreen.style.display = 'block';
}

// Показать результаты
function showResults() {
    window.location.href = 'results.html';
}

// Перезапустить тест
function restartTest() {
    completionScreen.style.display = 'none';
    testScreen.style.display = 'block';
    
    currentQuestionIndex = 0;
    userAnswers = new Array(currentTest.questions.length).fill(null);
    startTime = new Date();
    timeSpent = 0;
    timerRunning = true;
    
    startTimer();
    loadQuestion();
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
        'online-fraud': 'Онлайн-мошенничество',
        'phishing': 'Фишинг',
        'destructive-content': 'Деструктивный контент'
    };
    
    return names[topicId] || topicId;
}