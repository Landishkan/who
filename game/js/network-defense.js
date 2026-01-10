document.addEventListener('DOMContentLoaded', function() {
 
    const gameStats = {
        level: document.querySelector('#level span'),
        health: document.querySelector('#health span'),
        score: document.querySelector('#score span')
    };
    
    const networkElements = {
        server: document.getElementById('main-server'),
        firewall: document.getElementById('firewall'),
        firewallStrength: document.querySelector('.firewall-strength span'),
         attackLog: document.getElementById('attack-log'),
        currentAttack: document.getElementById('current-attack'),
        attackProgress: document.getElementById('attack-progress')
    };
    
    const defenseButtons = document.querySelectorAll('.defense-action');
    const tutorialOverlay = document.getElementById('tutorial');
    const startButton = document.getElementById('start-game');
    
    // Состояние игры
    let gameState = {
        running: false,
        level: 1,
        health: 100,
        firewall: 100,
        score: 0,
        currentAttack: null,
        attackProgress: 0,
        attackInterval: null,
        attackDamage: 0,
        lastDefenseAction: null,
        attacks: [
             { 
          name: "Брутфорс", 
        damage: 5, 
        speed: 50, 
        defense: "scan",  
        message: "Атака брутфорс! Подбор паролей к учётным записям.",
        hint: "Брутфорс подбирает слабые пароли. Защита: сложные пароли + двухфакторная аутентификация."
    },
    { 
        name: "DDoS", 
        damage: 8, 
        speed: 30, 
        defense: "block", 
        message: "DDoS атака! Тысячи запросов перегружают сервер.",
        hint: "DDoS лечится фильтрацией трафика и блокировкой ботнета."
    },
    { 
        name: "Вирус-шифровальщик", 
        damage: 10, 
        speed: 20, 
        defense: "backup", 
        message: "Вирус в системе! Ищет уязвимости для кражи данных.",
        hint: "Антивирусное сканирование обнаруживает и изолирует вредоносные файлы."
    },
    { 
        name: "Взлом", 
        damage: 15, 
        speed: 15, 
        defense: "update", 
        message: "Хакер пытается получить доступ к данным.",
        hint: "Даже при взломе резервные копии позволяют восстановить данные."
    }
        ]
    };
    
    // Показать обучающее окно
    tutorialOverlay.style.display = 'flex';
    
    // Начать игру
    startButton.addEventListener('click', function() {
        tutorialOverlay.style.display = 'none';
        startGame();
    });
    
    function startGame() {
        gameState.running = true;
        updateGameStats();
        startRandomAttack();
        
        // Активировать кнопки защиты
        defenseButtons.forEach(button => {
            button.addEventListener('click', handleDefenseAction);
        });
    }
    
    // Обработка действий защиты
function handleDefenseAction(e) {
    if (!gameState.running || !gameState.currentAttack) return;
    
    const action = e.currentTarget.getAttribute('data-action');
    gameState.lastDefenseAction = action;
    
    addLogEntry(`Выполнено: ${getActionName(action)}`);
    
  
    if (action === gameState.currentAttack.defense) {
        stopCurrentAttack(true);
    addScore(25 * gameState.level);
    
    showAttackHint(gameState.currentAttack.hint); 
    } else {
     
        gameState.firewall = Math.max(0, gameState.firewall - 5);
        addLogEntry(`Ошибка! Фаервол ослаблен: -5%`);
        updateGameStats();
    }
}
    function showAttackHint(hint) {
    // Создаём временное сообщение на экране
    const hintElement = document.createElement('div');
    hintElement.className = 'attack-hint';
    hintElement.textContent = `💡 ${hint}`;
    hintElement.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.9);
        color: #00ff88;
        padding: 20px;
        border-radius: 10px;
        z-index: 1000;
        font-size: 18px;
        text-align: center;
        max-width: 80%;
        animation: fadeInOut 3s forwards;
    `;
    
    document.body.appendChild(hintElement);
    
    // Удаляем через 3 секунды
    setTimeout(() => {
        if (hintElement.parentNode) {
            hintElement.remove();
        }
    }, 3000);
}
    function getActionName(action) {
        const names = {
            'update': 'Обновление ПО',
            'block': 'Блокировка IP',
            'scan': 'Сканирование системы',
            'backup': 'Создание резервной копии'
        };
        return names[action] || action;
    }
    
    // Начать случайную атаку
   function startRandomAttack() {
    // Очистить предыдущий интервал при его наличии 
    if (gameState.attackInterval) {
        clearInterval(gameState.attackInterval);
    }
        if (!gameState.running) return;
        
        // Выбрать случайную атаку с учетом уровня сложности
        const attackPool = gameState.attacks.filter(a => 
            gameState.level >= (a.damage > 10 ? 2 : 1)
        );
        
        const attack = attackPool[Math.floor(Math.random() * attackPool.length)];
        gameState.currentAttack = attack;
        gameState.attackProgress = 0;
        gameState.attackDamage = attack.damage;
        
        // Обновить UI
        networkElements.currentAttack.textContent = attack.name;
        networkElements.attackProgress.style.width = '0%';
        networkElements.server.classList.add('server-under-attack');
        
        addLogEntry(attack.message);
        
        // Запустить прогресс атаки
        gameState.attackInterval = setInterval(() => {
            gameState.attackProgress += 1;
            networkElements.attackProgress.style.width = `${gameState.attackProgress}%`;
            
            // Если атака достигла 100%
            if (gameState.attackProgress >= 100) {
                stopCurrentAttack(false);
                applyAttackDamage();
            }
        }, attack.speed);
    }
    
    // Остановить текущую атаку
    function stopCurrentAttack(success) {
        clearInterval(gameState.attackInterval);
        gameState.currentAttack = null;
        networkElements.currentAttack.textContent = 'Нет';
        networkElements.server.classList.remove('server-under-attack');
        
        if (success) {
            networkElements.attackProgress.style.background = 'linear-gradient(90deg, #00ff88, #00cc66)';
            setTimeout(() => {
                networkElements.attackProgress.style.width = '0%';
                networkElements.attackProgress.style.background = 'linear-gradient(90deg, #ff2d75, #ff6b8b)';
                
                // Начать новую атаку после небольшой паузы
                setTimeout(startRandomAttack, 2000);
            }, 1000);
        }
    }
    
    // Применить урон от атаки
    function applyAttackDamage() {
        // Уменьшить защиту фаервола
        gameState.firewall = Math.max(0, gameState.firewall - 10);
        
        // Если фаервол слабый, урон идет напрямую здоровью
        if (gameState.firewall <= 0) {
            gameState.health = Math.max(0, gameState.health - gameState.attackDamage);
            addLogEntry(`Фаервол пробит! Сервер получил урон: -${gameState.attackDamage}%`);
        } else {
            // Фаервол поглощает часть урона
            const actualDamage = Math.max(1, Math.floor(gameState.attackDamage * (1 - gameState.firewall / 100)));
            gameState.health = Math.max(0, gameState.health - actualDamage);
            addLogEntry(`Атака нанесла урон: -${actualDamage}% (Фаервол поглотил часть урона)`);
        }
        
        // Проверить состояние игры
        updateGameStats();
        
        if (gameState.health <= 0) {
            endGame(false);
        } else {
            // Ослабить фаервол на 5% после каждой атаки
            gameState.firewall = Math.max(0, gameState.firewall - 5);
            updateGameStats();
            
            // Начать новую атаку после небольшой паузы
            setTimeout(startRandomAttack, 3000);
        }
    }
    
   
    function addLogEntry(message) {
     
        console.log('Игра:', message);
    }
    
    // Обновить статистику игры
    function updateGameStats() {
        gameStats.level.textContent = gameState.level;
        gameStats.health.textContent = `${gameState.health}%`;
        gameStats.score.textContent = gameState.score;
        networkElements.firewallStrength.textContent = `${gameState.firewall}%`;
        
        // Визуальные эффекты при низком здоровье/защите
        if (gameState.health < 30) {
            networkElements.server.style.animation = 'serverUnderAttack 0.5s infinite';
        } else {
            networkElements.server.style.animation = '';
        }
        
        if (gameState.firewall < 30) {
            networkElements.firewall.classList.add('firewall-weakened');
        } else {
            networkElements.firewall.classList.remove('firewall-weakened');
        }
    }
    
    // Добавить очки
function addScore(points) {
    if (!gameState.running) return; // Не начислять, если игра окончена
    
    gameState.score += points;
    
    const newLevel = Math.floor(gameState.score / 100) + 1;
    if (newLevel > gameState.level) {
        gameState.level = newLevel;
        addLogEntry(`Уровень ${gameState.level}!`);
        
        if (gameState.level >= 10) {
            endGame(true);
            return; // Важно: прекращаем выполнение
        }
    }
    
    updateGameStats();
}
    
    // Завершить игру
function endGame(win) {
    gameState.running = false;
    clearInterval(gameState.attackInterval);

    const modal = document.getElementById('result-modal');
    const modalIcon = document.getElementById('modal-icon');
    const modalTitle = document.getElementById('modal-title');
    const modalMsg = document.getElementById('modal-message');
    
    // Настраиваем контент модалки
    if (win) {
        modalIcon.textContent = '🏆';
        modalIcon.style.color = '#00ff88';
        modalTitle.textContent = 'КИБЕРПОБЕДА!';
        modalMsg.textContent = `Сервер защищён на уровне ${gameState.level}!`;
        modal.classList.remove('defeat');
    } else {
        modalIcon.textContent = '💀';
        modalIcon.style.color = '#ff2d75';
        modalTitle.textContent = 'ВЗЛОМАНО!';
        modalMsg.textContent = 'Сервер не выдержал атак...';
        modal.classList.add('defeat');
    }

    // Заполняем статистику
    document.getElementById('mod-level').textContent = gameState.level;
    document.getElementById('mod-score').textContent = gameState.score;

    // Показываем модалку
    modal.style.display = 'flex';

    // Рестарт по кнопке
    document.getElementById('modal-restart').onclick = function() {
        modal.style.display = 'none';
        resetGame();
    };
}
   
    // Сбросить игру
   function resetGame() {
    // 1. Очистить ВСЕ возможные интервалы
    if (gameState.attackInterval) {
        clearInterval(gameState.attackInterval);
    }
        // Сбросить состояние
        gameState = {
            ...gameState,
            running: false,
            level: 1,
            health: 100,
            firewall: 100,
            score: 0,
            currentAttack: null,
            attackProgress: 0,
            attackInterval: null
            
        };
        
        // Сбросить UI
        networkElements.server.style.animation = '';
        networkElements.currentAttack.textContent = 'Нет';
        networkElements.attackProgress.style.width = '0%';
        networkElements.server.innerHTML = '<i class="fas fa-server"></i><div class="server-label">Сервер</div>';
        networkElements.server.style.color = '';
        networkElements.server.classList.remove('server-under-attack');
        networkElements.firewall.classList.remove('firewall-weakened');
        networkElements.server.classList.remove('victory-effect');
      
        document.getElementById('result-modal').style.display = 'none';
        updateGameStats();
        startGame();
    }
});
