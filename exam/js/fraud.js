document.addEventListener('DOMContentLoaded', function() {
    // Обновление прогресс-бара при прокрутке
    window.addEventListener('scroll', updateProgressBar);
    
    // Инициализация прогресс-бара
    updateProgressBar();
    
    // Анимация появления элементов
    animateElements();
});

function updateProgressBar() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById("progress").style.width = scrolled + "%";
}

function animateElements() {
    const elements = document.querySelectorAll('.lecture-section, .quiz-block, .conclusion-block');
    
    elements.forEach((element, index) => {
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100 * index);
    });
    
    // Изначальные стили для анимации
    const sections = document.querySelectorAll('.lecture-section');
    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    const quizBlock = document.querySelector('.quiz-block');
    quizBlock.style.opacity = '0';
    quizBlock.style.transform = 'translateY(20px)';
    quizBlock.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    
    const conclusionBlock = document.querySelector('.conclusion-block');
    conclusionBlock.style.opacity = '0';
    conclusionBlock.style.transform = 'translateY(20px)';
    conclusionBlock.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
}

// Функции для теста
let correctAnswers = 0;
let  totalQuestions = 5;
let currentQuestion = 0;
let selectedOptions = [];

function checkAnswer(element, isCorrect) {
    // Сброс всех выделений в этом вопросе
    const options = element.parentElement.querySelectorAll('.quiz-option');
    options.forEach(opt => {
        opt.classList.remove('correct', 'incorrect');
    });
    
    // Выделение выбранного ответа
    if (isCorrect) {
        element.classList.add('correct');
        correctAnswers++;
    } else {
        element.classList.add('incorrect');
        
        // Найдем правильный ответ и выделим его
        options.forEach(opt => {
            if (opt.getAttribute('onclick').includes('true')) {
                opt.classList.add('correct');
            }
        });
    }
    
    // Делаем вопрос неактивным после ответа
    options.forEach(opt => {
        opt.style.pointerEvents = 'none';
    });
}
 function showQuestion(questionIndex) {
    const questions = document.querySelectorAll('.quiz-question');
    
    // Скрываем все вопросы
    questions.forEach(q => q.style.display = 'none');
    
    // Показываем текущий вопрос
    if (questions[questionIndex]) {
        questions[questionIndex].style.display = 'block';
    }
}
const checkResultsBtn = document.getElementById('check-results-btn');
    if (checkResultsBtn) {
        checkResultsBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            console.log('Кнопка нажата!');
            await showResults();
        });
    }
 async function showResults() {
    const resultsElement = document.getElementById('quiz-results');
    let message = '';
    
    if (correctAnswers === totalQuestions) {
        message = `🛡️ Отлично! ${totalQuestions}/${totalQuestions}! Ты отлично разбираешься в защите от мошенников!`;
    } else if (correctAnswers >= totalQuestions * 0.7) {
        message = `👍 Хорошо! ${correctAnswers} из ${totalQuestions}. Ты на верном пути в изучении безопасности!`;
    } else {
        message = `💻 ${correctAnswers} из ${totalQuestions}. Рекомендуем перечитать лекцию - эта тема очень важна!`;
    }
   const user = firebase.auth().currentUser;
    if (user) {
        try {
            console.log('Saving progress for user:', user.uid);
            const success = await updateUserProgress(user.uid, 'lecture_4', correctAnswers);
            if (success) {
                message += `<br><br>💾 <strong>Прогресс сохранен: ${Math.round((correctAnswers / totalQuestions) * 100)}%</strong>`;
            } else {
                message += `<br><br>⚠️ <em>Не удалось сохранить прогресс</em>`;
            }
        } catch (error) {
            console.error('Error saving progress:', error);
            message += `<br><br>❌ <em>Ошибка сохранения прогресса: ${error.message}</em>`;
        }
    } else {
        message += `<br><br>🔒 <em>Войдите в систему чтобы сохранить прогресс</em>`;
    }
    
    resultsElement.innerHTML = `<h4>Твой результат:</h4><p>${message}</p>`;
    resultsElement.style.display = 'block';
    resultsElement.scrollIntoView({ behavior: 'smooth' });
    
  
}

// Добавьте эту функцию для сброса теста
function resetQuiz() {
    currentQuestion = 0;
    correctAnswers = 0;
    selectedOptions = [];
    
    // Сбрасываем все выбранные ответы
    document.querySelectorAll('.quiz-option').forEach(option => {
        option.classList.remove('selected', 'correct', 'incorrect');
    });
    
    // Показываем первый вопрос
   // showQuestion(0);
}
