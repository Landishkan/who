// Управление прогрессом и localStorage

// Сохранение результата теста
function saveTestResult(topic, level, score) {
    const progress = getProgress();
    
    if (!progress[topic]) {
        progress[topic] = {};
    }
    
    if (!progress[topic][level]) {
        progress[topic][level] = [];
    }
    
    progress[topic][level].push({
        score: score,
        date: new Date().toISOString()
    });
    
    // Сохраняем только последние 10 результатов
    if (progress[topic][level].length > 10) {
        progress[topic][level] = progress[topic][level].slice(-10);
    }
    
    localStorage.setItem('cyberSecurityProgress', JSON.stringify(progress));
    
    // Обновляем общую статистику
    updateOverallStats();
}

// Получение прогресса
function getProgress() {
    const progress = localStorage.getItem('cyberSecurityProgress');
    return progress ? JSON.parse(progress) : {};
}

// Получение статистики прогресса
function getProgressStats() {
    const progress = getProgress();
    let totalTests = 0;
    let totalScore = 0;
    let bestScore = 0;
    
    Object.values(progress).forEach(topic => {
        Object.values(topic).forEach(levelResults => {
            totalTests += levelResults.length;
            levelResults.forEach(result => {
                totalScore += result.score;
                if (result.score > bestScore) {
                    bestScore = result.score;
                }
            });
        });
    });
    
    return {
        totalTests: totalTests,
        averageScore: totalTests > 0 ? Math.round(totalScore / totalTests) : 0,
        bestScore: bestScore,
        lastTestDate: totalTests > 0 ? new Date().toLocaleDateString('ru-RU') : null
    };
}

// Обновление общей статистики
function updateOverallStats() {
    const stats = getProgressStats();
    localStorage.setItem('cyberSecurityStats', JSON.stringify(stats));
    return stats;
}

// Получение лучших результатов по темам
function getBestResults() {
    const progress = getProgress();
    const bestResults = {};
    
    Object.keys(progress).forEach(topic => {
        Object.keys(progress[topic]).forEach(level => {
            const results = progress[topic][level];
            if (results.length > 0) {
                const maxScore = Math.max(...results.map(r => r.score));
                if (!bestResults[topic]) {
                    bestResults[topic] = {};
                }
                bestResults[topic][level] = maxScore;
            }
        });
    });
    
    return bestResults;
}

// Сброс прогресса
function resetProgress() {
    if (confirm('Вы уверены, что хотите сбросить весь прогресс? Это действие нельзя отменить.')) {
        localStorage.removeItem('cyberSecurityProgress');
        localStorage.removeItem('cyberSecurityStats');
        alert('Прогресс успешно сброшен!');
        window.location.reload();
    }
}