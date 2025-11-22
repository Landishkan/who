class InboxGame {
    constructor() {
        this.score = 0;
        this.lives = 3;
        this.timer = 60;
        this.level = 1;
        this.processedMessages = 0;
        this.totalMessages = 10;
        this.isPlaying = false;
        this.currentMessage = null;
        this.timerInterval = null;
        
        this.messages = [
            // Уровень 1: Базовый этикет
            {
                id: 1,
                sender: "Анна Петрова",
                time: "10:30",
                content: "Привет! Извини за беспокойство, не могла бы ты посмотреть мой вопрос, когда будет время?",
                correct: "answer-later",
                explanation: "Вежливая просьба без срочности - можно ответить позже."
            },
            {
                id: 2,
                sender: "Мария Сидорова",
                time: "10:25",
                content: "Здравствуйте, Иван. Направляю вам исправленный документ по проекту 'Х'. Жду ваших комментариев.",
                correct: "answer-now",
                explanation: "Важное рабочее сообщение требует срочного ответа."
            },
            {
                id: 3,
                sender: "Неизвестный",
                time: "10:15",
                content: "СРОЧНО!!! ДЛЯ ТЕБЯ АКЦИЯ!!! КУПИ!!! ВЫИГРАЙ МИЛЛИОН!!!",
                correct: "trash",
                explanation: "Явный спам с кричащим заголовком и требованиями срочных действий."
            },
            {
                id: 4,
                sender: "Петр Иванов",
                time: "09:45",
                content: "Коллеги, у кого есть доступ к отчету за прошлый квартал? Нужно для совещания в 15:00.",
                correct: "forward",
                explanation: "Вопрос ко всем коллегам - лучше переслать ответственному лицу."
            },
            // Уровень 2: Более сложные случаи
            {
                id: 5,
                sender: "ООО 'БыстрыеДеньги'",
                time: "11:20",
                content: "Уважаемый клиент! Ваш кредит одобрен. Для получения перейдите по ссылке: fast-money.ru/credit",
                correct: "trash",
                explanation: "Фишинговое сообщение от неизвестной организации."
            },
            {
                id: 6,
                sender: "IT Отдел",
                time: "11:15",
                content: "Запланированы технические работы на завтра с 22:00 до 02:00. Системы будут недоступны.",
                correct: "answer-now",
                explanation: "Важное уведомление от IT отдела требует внимания."
            },
            {
                id: 7,
                sender: "Алексей К.",
                time: "11:10",
                content: "Привет! Можешь скинуть контакты того дизайнера, с которым мы работали в прошлом проекте?",
                correct: "answer-later",
                explanation: "Личный запрос, не требующий срочного ответа."
            },
            {
                id: 8,
                sender: "HR Отдел",
                time: "10:55",
                content: "Напоминаем о корпоративном обучении по кибербезопасности в эту пятницу в 15:00.",
                correct: "forward",
                explanation: "Информационное сообщение, которое стоит переслать команде."
            },
            // Уровень 3: Сложные дилеммы
            {
                id: 9,
                sender: "support@yourbank.ru",
                time: "12:30",
                content: "Обнаружена подозрительная активность в вашем аккаунте. Срочно подтвердите данные: your-bank-security.ru",
                correct: "trash",
                explanation: "Фишинг! Настоящий банк никогда не попросит подтвердить данные по ссылке в письме."
            },
            {
                id: 10,
                sender: "Директор по развитию",
                time: "12:25",
                content: "Срочно нужна презентация по проекту 'Y' к 14:00. Готовы ли вы её предоставить?",
                correct: "answer-now",
                explanation: "Срочный запрос от руководства требует немедленного ответа."
            }
        ];

        this.initializeGame();
    }

    initializeGame() {
        this.bindEvents();
        this.updateUI();
    }

    bindEvents() {
        // Кнопка начала игры
        document.getElementById('start-button').addEventListener('click', () => this.startGame());
        
        // Кнопка подсказки
        document.getElementById('hint-button').addEventListener('click', () => this.showHint());
        
        // Кнопки в модальном окне
        document.getElementById('play-again-button').addEventListener('click', () => this.restartGame());
        document.getElementById('menu-button').addEventListener('click', () => window.location.href = '../games.html');
        
        // Обработчики для категорий
        document.querySelectorAll('.category-card').forEach(category => {
            category.addEventListener('click', () => {
                if (this.currentMessage && this.isPlaying) {
                    this.checkAnswer(this.currentMessage, category.dataset.category);
                }
            });
            
            // Drag and drop
            category.addEventListener('dragover', (e) => {
                e.preventDefault();
            });
            
            category.addEventListener('drop', (e) => {
                e.preventDefault();
                if (this.currentMessage && this.isPlaying) {
                    this.checkAnswer(this.currentMessage, category.dataset.category);
                }
            });
        });

        // Touch события для мобильных
        this.setupTouchEvents();
    }

    setupTouchEvents() {
        let touchStartX, touchStartY;
        
        document.addEventListener('touchstart', (e) => {
            if (e.target.closest('.message')) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
            }
        }, { passive: true });

        document.addEventListener('touchend', (e) => {
            if (!this.currentMessage || !this.isPlaying) return;
            
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            
            const diffX = touchEndX - touchStartX;
            const diffY = touchEndY - touchStartY;
            
            // Если свайп значительный по горизонтали
            if (Math.abs(diffX) > 50 && Math.abs(diffY) < 100) {
                // Можно добавить логику для свайпов
            }
        }, { passive: true });
    }

    startGame() {
        this.isPlaying = true;
        this.score = 0;
        this.lives = 3;
        this.timer = 60;
        this.level = 1;
        this.processedMessages = 0;
        
        document.getElementById('start-button').style.display = 'none';
        document.getElementById('hint-button').disabled = false;
        
        this.startTimer();
        this.generateMessage();
        this.updateUI();
    }

    startTimer() {
        this.timerInterval = setInterval(() => {
            this.timer--;
            document.getElementById('timer').textContent = this.timer;
            
            if (this.timer <= 0) {
                this.endGame();
            }
        }, 1000);
    }

    generateMessage() {
        if (!this.isPlaying || this.processedMessages >= this.totalMessages) {
            this.endGame();
            return;
        }

        const availableMessages = this.messages.filter(msg => 
            !document.querySelector(`[data-message-id="${msg.id}"]`)
        );
        
        if (availableMessages.length === 0) return;

        const randomMessage = availableMessages[Math.floor(Math.random() * availableMessages.length)];
        this.createMessageElement(randomMessage);
        this.processedMessages++;
        
        // Обновляем прогресс
        const progress = (this.processedMessages / this.totalMessages) * 100;
        document.getElementById('game-progress').style.width = `${progress}%`;
        
        this.updateUI();
    }

   createMessageElement(messageData) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message';
    messageDiv.dataset.messageId = messageData.id;
    messageDiv.dataset.correct = messageData.correct;
    messageDiv.draggable = true;
    
    messageDiv.innerHTML = `
        <div class="message-header">
            <div class="sender">${messageData.sender}</div>
            <div class="time">${messageData.time}</div>
        </div>
        <div class="message-content">${messageData.content}</div>
    `;

    // Drag events для десктопа
    messageDiv.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', messageData.id);
        messageDiv.classList.add('dragging');
    });

    messageDiv.addEventListener('dragend', () => {
        messageDiv.classList.remove('dragging');
    });

    // Touch events для мобильных
    messageDiv.addEventListener('touchstart', (e) => {
        e.preventDefault();
        messageDiv.classList.add('dragging');
    }, { passive: false });

    messageDiv.addEventListener('touchend', (e) => {
        e.preventDefault();
        messageDiv.classList.remove('dragging');
    }, { passive: false });

    const inbox = document.getElementById('inbox');
    
    // Очищаем инструкцию при первом сообщении
    const instruction = inbox.querySelector('.game-instruction');
    if (instruction) {
        instruction.remove();
    }
    
    // Добавляем сообщение в начало контейнера
    inbox.insertBefore(messageDiv, inbox.firstChild);
    
    // Ограничиваем количество видимых сообщений на мобильных
    if (window.innerWidth <= 768) {
        const messages = inbox.querySelectorAll('.message');
        if (messages.length > 3) {
            messages[messages.length - 1].remove();
        }
    }
    
    this.currentMessage = messageDiv;
    
    // Автоматическое удаление через 15 секунд на мобильных (вместо 10)
    const removeTimeout = window.innerWidth <= 768 ? 15000 : 10000;
    
    setTimeout(() => {
        if (messageDiv.parentNode && this.isPlaying) {
            this.handleIncorrect();
            messageDiv.remove();
            // Если это было текущее сообщение, генерируем новое
            if (this.currentMessage === messageDiv) {
                setTimeout(() => this.generateMessage(), 500);
            }
        }
    }, removeTimeout);
}
    checkAnswer(messageElement, selectedCategory) {
        const correctCategory = messageElement.dataset.correct;
        const messageId = parseInt(messageElement.dataset.messageId);
        const messageData = this.messages.find(msg => msg.id === messageId);

        if (selectedCategory === correctCategory) {
            // Правильный ответ
            this.score += 10 * this.level;
            messageElement.classList.add('correct');
            this.showFeedback(true, messageData.explanation);
        } else {
            // Неправильный ответ
            this.lives--;
            messageElement.classList.add('incorrect');
            this.showFeedback(false, messageData.explanation);
            
            if (this.lives <= 0) {
                setTimeout(() => this.endGame(), 1000);
                return;
            }
        }

        // Удаляем сообщение и генерируем новое
        setTimeout(() => {
            messageElement.remove();
            this.generateMessage();
        }, 1500);

        this.updateUI();
    }

    showFeedback(isCorrect, explanation) {
        // Можно добавить всплывающие уведомления
        console.log(isCorrect ? 'Правильно!' : 'Неправильно!', explanation);
    }

    showHint() {
        if (!this.currentMessage) return;
        
        const messageId = parseInt(this.currentMessage.dataset.messageId);
        const messageData = this.messages.find(msg => msg.id === messageId);
        
        alert(`Подсказка: ${messageData.explanation}`);
    }

    updateUI() {
        document.getElementById('score').textContent = this.score;
        document.getElementById('lives').textContent = this.lives;
        document.getElementById('timer').textContent = this.timer;
        document.getElementById('level').textContent = this.level;
        document.getElementById('processed').textContent = this.processedMessages;
        document.getElementById('total').textContent = this.totalMessages;
    }

    endGame() {
        this.isPlaying = false;
        clearInterval(this.timerInterval);
        
        const finalScore = this.score;
        const correctAnswers = Math.floor(this.score / 10);
        const wrongAnswers = 3 - this.lives;
        
        document.getElementById('final-score').textContent = finalScore;
        document.getElementById('correct-answers').textContent = correctAnswers;
        document.getElementById('wrong-answers').textContent = wrongAnswers;
        
        let feedback = '';
        if (finalScore >= 80) {
            feedback = 'Отлично! Вы мастер сетевого этикета! 🎉';
        } else if (finalScore >= 50) {
            feedback = 'Хорошо! Вы разбираетесь в основах этикета. 👍';
        } else {
            feedback = 'Есть куда расти! Изучите материалы по сетевому этикету. 📚';
        }
        
        document.getElementById('result-feedback').textContent = feedback;
        document.getElementById('results-modal').style.display = 'flex';
    }

    restartGame() {
        document.getElementById('results-modal').style.display = 'none';
        document.getElementById('inbox').innerHTML = `
            <div class="game-instruction">
                <h3><i class="fas fa-inbox"></i> Сортируйте входящие сообщения</h3>
                <p>Перетащите сообщение в нужную категорию или нажмите на категорию</p>
            </div>
        `;
        document.getElementById('start-button').style.display = 'block';
        this.startGame();
    }
}

// Инициализация игры когда DOM загружен
document.addEventListener('DOMContentLoaded', () => {
    new InboxGame();
});