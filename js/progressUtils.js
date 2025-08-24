

// Функция для обновления прогресса пользователя
async function updateUserProgress(userId, lectureId, correctAnswers) {
    try {
        console.log('Updating progress for user:', userId, 'lecture:', lectureId, 'score:', correctAnswers);
        
        const percentage = (correctAnswers / 5) * 100;
        
        await db.collection("users").doc(userId).update({
            [`progress.${lectureId}`]: percentage,
            [`quizResults.${lectureId}`]: {
                score: correctAnswers,
                total: 5,
                percentage: percentage,
                completedAt: firebase.firestore.FieldValue.serverTimestamp()
            },
            lastActivity: firebase.firestore.FieldValue.serverTimestamp()
        });
        
        console.log('Progress updated successfully:', percentage + '%');
        return true;
    } catch (error) {
        console.error('Error updating progress:', error);
        alert('Ошибка сохранения прогресса: ' + error.message);
        return false;
    }
}

// Функция для создания начальной структуры прогресса
async function initializeUserProgress(userId) {
    try {
        const defaultProgress = {
            'lecture_1': 0,
            'lecture_2': 0,
            'lecture_3': 0, 
            'lecture_4': 0,
            'lecture_5': 0
        };
        
        await db.collection("users").doc(userId).set({
            progress: defaultProgress,
            quizResults: {},
            createdAt: firebase.firestore.FieldValue.serverTimestamp(),
            lastLogin: firebase.firestore.FieldValue.serverTimestamp()
        }, { merge: true });
        
        console.log('User progress initialized');
        return true;
    } catch (error) {
        console.error('Error initializing progress:', error);
        return false;
    }
}


// Функция для получения прогресса пользователя
async function getUserProgress(userId) {
    try {
        const doc = await db.collection("users").doc(userId).get();
        if (doc.exists) {
            return doc.data().progress || {};
        }
        return {};
    } catch (error) {
        console.error('Error getting progress:', error);
        return {};
    }
}