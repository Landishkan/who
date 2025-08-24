

// Соответствие лекций и их ID
const LECTURE_IDS = {
    'lecture_1': 'Кибербуллинг', // поменять
    'lecture_2': 'Сетевой этикет',
    'lecture_3': 'Кибертерроризм',  // поменять       
    'lecture_4': 'Мошенничество',
    'lecture_5': 'Фишинг'
};

// Функция для получения ID лекции по названию
function getLectureId(lectureName) {
    for (const [id, name] of Object.entries(LECTURE_IDS)) {
        if (name === lectureName) {
            return id;
        }
    }
    return null;
}

// Функция для получения названия лекции по ID
function getLectureName(lectureId) {
    return LECTURE_IDS[lectureId] || 'Неизвестная лекция';
}