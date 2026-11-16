// src/pages/scenarios/FactHunter.tsx
import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowLeft, CheckCircle2, XCircle, BookOpen, Gamepad2, Shield, Search, Brain, Share2, RotateCcw, TrendingUp, GraduationCap, Trophy, AlertTriangle } from 'lucide-react';
import { Link } from 'react-router-dom';
import { trainingNews, categories, checklistSteps, type Category, type TrainingNewsItem } from '../../data/factHunterData';

// Маппинг иконок
const iconMap: Record<string, React.ReactNode> = {
  BookOpen: <BookOpen className="w-6 h-6" />,
  TrendingUp: <TrendingUp className="w-6 h-6" />,
  GraduationCap: <GraduationCap className="w-6 h-6" />,
  Gamepad2: <Gamepad2 className="w-6 h-6" />,
  Shield: <Shield className="w-8 h-8" />,
  Search: <Search className="w-8 h-8" />,
  Brain: <Brain className="w-8 h-8" />,
  Share2: <Share2 className="w-8 h-8" />,
  AlertTriangle: <AlertTriangle className="w-8 h-8" />
};

type Screen = 'main' | 'training' | 'checklist' | 'categories' | 'levels' | 'stats';

export default function FactHunter() {
  const [screen, setScreen] = useState<Screen>('main');
  const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);
  const [currentLevel, setCurrentLevel] = useState(0);
  
  // Для обучения
  const [selectedTrainingId, setSelectedTrainingId] = useState<number | null>(null);
  const [showTrainingResult, setShowTrainingResult] = useState(false);
  
  // Для уровней
  const [selectedNewsIndex, setSelectedNewsIndex] = useState<number | null>(null); // 0, 1, 2 или -1 (спец. кнопка)
  const [showLevelResult, setShowLevelResult] = useState(false);
  
  const [progress, setProgress] = useState<Record<string, boolean[]>>({});

  useEffect(() => {
    const saved = localStorage.getItem('factHunterProgress');
    if (saved) setProgress(JSON.parse(saved));
  }, []);

  const saveProgress = (categoryId: string, levelId: number, passed: boolean) => {
    const newProgress = { ...progress };
    if (!newProgress[categoryId]) newProgress[categoryId] = [];
    newProgress[categoryId][levelId - 1] = passed;
    setProgress(newProgress);
    localStorage.setItem('factHunterProgress', JSON.stringify(newProgress));
  };

  // ===== ГЛАВНЫЙ ЭКРАН =====
  if (screen === 'main') {
    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-4xl mx-auto">
          <Link to="/scenarios" className="inline-flex items-center gap-2 text-text-secondary hover:text-accent transition-colors mb-8">
            <ArrowLeft className="w-4 h-4" /> Назад к сценариям
          </Link>

          <div className="text-center mb-12">
            <div className="w-20 h-20 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-6">
              <Search className="w-10 h-10 text-accent" strokeWidth={1.5} />
            </div>
            <h1 className="font-orbitron text-3xl md:text-4xl font-bold text-text mb-4">Охотник за фейками</h1>
            <p className="text-text-secondary text-lg max-w-2xl mx-auto">
              Интерактивный курс цифровой бдительности. Научись отличать правду от мастерски сделанной лжи.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <motion.button
              whileHover={{ y: -4 }}
              onClick={() => {
                setScreen('training');
                setSelectedTrainingId(null);
                setShowTrainingResult(false);
              }}
              className="group relative flex flex-col p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-accent/30 transition-all duration-300 text-left"
            >
              <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
                <BookOpen className="w-7 h-7 text-accent" strokeWidth={1.5} />
              </div>
              <h3 className="font-orbitron text-xl font-bold text-text mb-3 group-hover:text-accent transition-colors">
                🎓 Вводное обучение
              </h3>
              <p className="text-text-secondary text-sm leading-relaxed">
                Интерактивная ловушка: выберите новость, которую считаете настоящей, и получите подробный разбор с чек-листом.
              </p>
              <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
            </motion.button>

            <motion.button
              whileHover={{ y: -4 }}
              onClick={() => setScreen('categories')}
              className="group relative flex flex-col p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-accent/30 transition-all duration-300 text-left"
            >
              <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
                <Gamepad2 className="w-7 h-7 text-accent" strokeWidth={1.5} />
              </div>
              <h3 className="font-orbitron text-xl font-bold text-text mb-3 group-hover:text-accent transition-colors">
                🎮 Уровни и категории
              </h3>
              <p className="text-text-secondary text-sm leading-relaxed">
                Проверь свои навыки в 4 тематических категориях. Будь внимателен: иногда правильной новости нет!
              </p>
              <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
            </motion.button>
          </div>
        </motion.div>
      </div>
    );
  }

  // ===== ЭКРАН ОБУЧЕНИЯ =====
  if (screen === 'training') {
    const selectedData = selectedTrainingId !== null ? trainingNews.find(n => n.id === selectedTrainingId) : null;

    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="max-w-4xl mx-auto">
          <button onClick={() => setScreen('main')} className="inline-flex items-center gap-2 text-text-secondary hover:text-accent transition-colors mb-8">
            <ArrowLeft className="w-4 h-4" /> В главное меню
          </button>

          <div className="mb-8">
            <h2 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-2">🎓 Часть 1: Обучение</h2>
            <p className="text-text-muted">Преподаватель может использовать этот блок для проекции на экран.</p>
          </div>

          <div className="p-5 rounded-xl bg-accent/10 border-l-4 border-accent mb-8">
            <p className="text-text text-base">
              📋 <strong>Задание:</strong> Перед вами три новости. Нажмите на ту, которую считаете настоящей.
            </p>
          </div>

          <div className="space-y-4 mb-8">
            {trainingNews.map((news, idx) => (
              <motion.button
                key={news.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: idx * 0.1 }}
                onClick={() => {
                  setSelectedTrainingId(news.id);
                  setShowTrainingResult(true);
                }}
                className={`w-full p-6 rounded-xl border text-left transition-all duration-300 ${
                  selectedTrainingId === news.id
                    ? 'border-accent/50 bg-accent/10'
                    : 'border-white/5 bg-background-light/20 hover:border-accent/30 hover:bg-background-light/40'
                }`}
              >
                <h3 className="font-semibold text-text text-lg mb-2">{news.title}</h3>
                <p className="text-text-muted text-sm">Источник: {news.source}</p>
              </motion.button>
            ))}
          </div>

          <AnimatePresence>
            {showTrainingResult && selectedData && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="p-6 rounded-xl border border-error/30 bg-error/5 mb-8"
              >
                <div className="flex items-center gap-3 mb-4">
                  <AlertTriangle className="w-6 h-6 text-error" />
                  <h3 className="font-orbitron text-lg font-bold text-error">🎯 Сюрприз! Это мастерски сделанный фейк</h3>
                </div>

                <p className="text-text text-base mb-6">
                  Вы выбрали новость, которая выглядит максимально убедительно. Но это <span className="text-error font-bold">ловушка</span>.
                </p>

                <h4 className="font-orbitron text-sm font-bold text-text-muted uppercase tracking-wider mb-3">🔬 Детальный разбор:</h4>
                <div className="space-y-3 mb-6">
                  {selectedData.analysis.map((item, idx) => (
                    <div key={idx} className="flex items-start gap-3 p-3 rounded-lg bg-background-dark/50 border border-white/5">
                      <span className="text-error mt-0.5">•</span>
                      <span className="text-text-secondary text-sm">{item}</span>
                    </div>
                  ))}
                </div>

                <div className="p-4 rounded-lg bg-accent/5 border border-accent/20 mb-6">
                  <h4 className="font-semibold text-accent mb-2">💬 Вопрос для обсуждения с классом:</h4>
                  <p className="text-text-secondary text-sm italic">"{selectedData.discussion}"</p>
                </div>

                <div className="flex gap-3">
                  <button
                    onClick={() => setScreen('checklist')}
                    className="flex-1 py-3 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all text-sm"
                  >
                    Изучить чек-лист фактчекера ➔
                  </button>
                  <button
                    onClick={() => {
                      setShowTrainingResult(false);
                      setSelectedTrainingId(null);
                    }}
                    className="flex-1 py-3 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 transition-all text-sm font-medium"
                  >
                    Закрыть
                  </button>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>
      </div>
    );
  }

  // ===== ЧЕК-ЛИСТ =====
  if (screen === 'checklist') {
    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="max-w-4xl mx-auto">
          <div className="text-center mb-12">
            <div className="w-20 h-20 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-6">
              <Shield className="w-10 h-10 text-accent" strokeWidth={1.5} />
            </div>
            <h2 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">🛡️ Твой личный щит от дезинформации</h2>
          </div>

          <div className="space-y-6 mb-8">
            {checklistSteps.map((step, idx) => (
              <motion.div
                key={idx}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: idx * 0.1 }}
                className="p-6 rounded-xl border border-white/5 bg-background-light/20"
              >
                <div className="flex items-start gap-4">
                  <div className={`${step.color} flex-shrink-0`}>
                    {iconMap[step.icon]}
                  </div>
                  <div>
                    <h3 className={`font-orbitron text-xl font-bold mb-2 ${step.color}`}>{step.title}</h3>
                    <p className="text-text-secondary text-sm leading-relaxed whitespace-pre-line">{step.desc}</p>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>

          <div className="flex gap-3">
            <button onClick={() => setScreen('training')} className="flex-1 py-3 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 transition-all text-sm font-medium">
              ← Назад к обучению
            </button>
            <button onClick={() => setScreen('categories')} className="flex-1 py-3 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all text-sm">
              Перейти к уровням ➔
            </button>
          </div>
        </motion.div>
      </div>
    );
  }

  // ===== ВЫБОР КАТЕГОРИИ =====
  if (screen === 'categories') {
    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="max-w-4xl mx-auto">
          <button onClick={() => setScreen('main')} className="inline-flex items-center gap-2 text-text-secondary hover:text-accent transition-colors mb-8">
            <ArrowLeft className="w-4 h-4" /> В главное меню
          </button>

          <div className="text-center mb-12">
            <h2 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">📂 Выберите категорию</h2>
            <p className="text-text-secondary">Выберите уровень. Зеленый — пройден успешно, красный — есть ошибки.</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {categories.map((cat, idx) => (
              <motion.button
                key={cat.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: idx * 0.1 }}
                onClick={() => {
                  setSelectedCategory(cat);
                  setCurrentLevel(0);
                  setScreen('levels');
                }}
                className="group relative flex flex-col p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-accent/30 transition-all duration-300 text-left"
              >
                <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
                  <div className="text-accent">{iconMap[cat.icon]}</div>
                </div>
                <h3 className="font-orbitron text-xl font-bold text-text mb-3 group-hover:text-accent transition-colors">
                  {cat.name}
                </h3>
                <p className="text-text-secondary text-sm">
                  {cat.levels.length} {cat.levels.length === 1 ? 'уровень' : 'уровня'}
                </p>
                {progress[cat.id] && (
                  <div className="mt-4 flex gap-1">
                    {cat.levels.map((_, lvlIdx) => (
                      <div
                        key={lvlIdx}
                        className={`w-2 h-2 rounded-full ${
                          progress[cat.id][lvlIdx] ? 'bg-success' : 'bg-white/20'
                        }`}
                      />
                    ))}
                  </div>
                )}
                <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
              </motion.button>
            ))}
          </div>

          <button onClick={() => setScreen('stats')} className="w-full py-3 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 transition-all text-sm font-medium flex items-center justify-center gap-2">
            <Trophy className="w-4 h-4" /> 📊 Общая статистика
          </button>
        </motion.div>
      </div>
    );
  }

  // ===== УРОВНИ =====
  if (screen === 'levels' && selectedCategory) {
    const level = selectedCategory.levels[currentLevel];
    const isLastLevel = currentLevel === selectedCategory.levels.length - 1;
    const conditionText = level.condition === 'find-fake' ? 'Найди фейковую новость' : 'Найди правдивую новость';

    // Проверка правильности
    const isCorrect = level.isTrapLevel 
      ? selectedNewsIndex === -1 
      : selectedNewsIndex === level.correctIndex;

    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="max-w-4xl mx-auto">
          <button onClick={() => setScreen('categories')} className="inline-flex items-center gap-2 text-text-secondary hover:text-accent transition-colors mb-8">
            <ArrowLeft className="w-4 h-4" /> ← К категориям
          </button>

          <div className="text-center mb-8">
            <div className="flex items-center justify-center gap-3 mb-4">
              <div className="text-accent">{iconMap[selectedCategory.icon]}</div>
              <h2 className="font-orbitron text-2xl font-bold text-text">{selectedCategory.name}</h2>
            </div>
            <p className="text-text-muted text-sm">Уровень {currentLevel + 1} из {selectedCategory.levels.length}</p>
          </div>

          <div className="mb-6">
            <div className="h-1 bg-white/5 rounded-full overflow-hidden">
              <div
                className="h-full bg-accent transition-all duration-500"
                style={{ width: `${((currentLevel + 1) / selectedCategory.levels.length) * 100}%` }}
              />
            </div>
          </div>

          <div className="text-center mb-6">
            <h3 className="font-orbitron text-lg font-bold text-text mb-2">Задание: {conditionText}</h3>
            <p className="text-text-secondary text-sm">Внимательно изучи каждую новость и источник.</p>
          </div>

          <div className="space-y-4 mb-6">
            {level.news.map((news, idx) => (
              <motion.button
                key={news.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: idx * 0.1 }}
                onClick={() => {
                  setSelectedNewsIndex(idx);
                  setShowLevelResult(true);
                  saveProgress(selectedCategory.id, level.id, level.isTrapLevel ? false : idx === level.correctIndex);
                }}
                disabled={showLevelResult}
                className={`w-full p-6 rounded-xl border text-left transition-all duration-300 ${
                  showLevelResult
                    ? (!level.isTrapLevel && idx === level.correctIndex)
                      ? 'border-success/50 bg-success/10'
                      : idx === selectedNewsIndex
                      ? 'border-error/50 bg-error/10'
                      : 'border-white/5 bg-background-light/10 opacity-50'
                    : 'border-white/5 bg-background-light/20 hover:border-accent/30 hover:bg-background-light/40'
                }`}
              >
                <h3 className="font-semibold text-text text-lg mb-2">{news.title}</h3>
                <p className="text-text-muted text-sm">Источник: {news.source}</p>
              </motion.button>
            ))}
            
            {/* СПЕЦИАЛЬНАЯ КНОПКА-ЛОВУШКА */}
            <motion.button
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              onClick={() => {
                setSelectedNewsIndex(-1);
                setShowLevelResult(true);
                saveProgress(selectedCategory.id, level.id, level.isTrapLevel);
              }}
              disabled={showLevelResult}
              className={`w-full p-4 rounded-xl border-2 border-dashed text-center font-bold transition-all duration-300 ${
                showLevelResult
                  ? level.isTrapLevel
                    ? 'border-success/50 bg-success/10 text-success'
                    : 'border-error/50 bg-error/10 text-error'
                  : 'border-accent/30 bg-accent/5 text-accent hover:bg-accent/10'
              }`}
            >
              {level.specialButtonText}
            </motion.button>
          </div>

          <AnimatePresence>
            {showLevelResult && selectedNewsIndex !== null && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className={`p-6 rounded-xl border mb-8 ${
                  isCorrect 
                    ? 'border-success/30 bg-success/5' 
                    : 'border-error/30 bg-error/5'
                }`}
              >
                <div className="flex items-center gap-3 mb-4">
                  {isCorrect ? (
                    <>
                      <CheckCircle2 className="w-6 h-6 text-success" />
                      <h3 className="font-orbitron text-lg font-bold text-success">Правильно!</h3>
                    </>
                  ) : (
                    <>
                      <XCircle className="w-6 h-6 text-error" />
                      <h3 className="font-orbitron text-lg font-bold text-error">Неверно</h3>
                    </>
                  )}
                </div>

                {!isCorrect && selectedNewsIndex !== -1 && (
                  <div className="mb-4">
                    <h4 className="text-sm font-bold text-text-muted uppercase tracking-wider mb-2">Красные флаги выбранной новости:</h4>
                    <ul className="space-y-1">
                      {level.news[selectedNewsIndex].flags.map((flag, idx) => (
                        <li key={idx} className="text-sm text-text-secondary flex items-start gap-2">
                          <span className="text-error mt-1">•</span>
                          <span>{flag}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                )}

                {isCorrect && level.isTrapLevel && (
                  <p className="text-text-secondary text-sm mb-4">
                    Отличная работа! Ты заметил, что все представленные новости являются правдивыми, и не поддался на провокацию найти несуществующий фейк.
                  </p>
                )}

                <div className="flex gap-3">
                  {!isLastLevel && (
               
  <button
    onClick={() => {
      setShowLevelResult(false);
      setSelectedNewsIndex(null);
      // Задержка нужна, чтобы AnimatePresence успел завершить exit
      setTimeout(() => {
        setCurrentLevel(currentLevel + 1);
      }, 300);
    }}
    className="flex-1 py-3 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all text-sm"
  >
    Следующий уровень ➔
  </button>
)}
                  
                <button
  onClick={() => {
    // Сначала скрываем результат, даём анимации завершиться
    setShowLevelResult(false);
    setSelectedNewsIndex(null);
    // Потом, с задержкой, меняем экран
    setTimeout(() => {
      setScreen('categories');
    }, 300); // 300мс = длительность exit-анимации
  }}
  className="flex-1 py-3 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 transition-all text-sm font-medium"
>
  К категориям
</button>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>
      </div>
    );
  }

  // ===== СТАТИСТИКА =====
  if (screen === 'stats') {
    const totalLevels = categories.reduce((acc, cat) => acc + cat.levels.length, 0);
    const passedLevels = Object.values(progress).flat().filter(Boolean).length;

    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="max-w-4xl mx-auto">
          <button onClick={() => setScreen('categories')} className="inline-flex items-center gap-2 text-text-secondary hover:text-accent transition-colors mb-8">
            <ArrowLeft className="w-4 h-4" /> ← К категориям
          </button>

          <div className="text-center mb-12">
            <div className="w-20 h-20 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-6">
              <Trophy className="w-10 h-10 text-accent" strokeWidth={1.5} />
            </div>
            <h2 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">📊 Ваша статистика</h2>
            <p className="text-text-secondary">Данные сохраняются в браузере. Очистка кэша удалит прогресс.</p>
            <p className="text-text font-bold mt-2">Пройдено {passedLevels} из {totalLevels} уровней</p>
          </div>

          <div className="space-y-6 mb-8">
            {categories.map((cat) => (
              <div key={cat.id} className="p-6 rounded-xl border border-white/5 bg-background-light/20">
                <div className="flex items-center gap-3 mb-4">
                  <div className="text-accent">{iconMap[cat.icon]}</div>
                  <h3 className="font-orbitron text-lg font-bold text-text">{cat.name}</h3>
                </div>
                <div className="flex gap-2">
                  {cat.levels.map((_, idx) => (
                    <div
                      key={idx}
                      className={`flex-1 h-2 rounded-full ${
                        progress[cat.id]?.[idx] ? 'bg-success' : 'bg-white/10'
                      }`}
                      title={`Уровень ${idx + 1}`}
                    />
                  ))}
                </div>
              </div>
            ))}
          </div>

          <button
            onClick={() => {
              if (window.confirm('Вы уверены? Весь прогресс будет удален.')) {
                localStorage.removeItem('factHunterProgress');
                setProgress({});
              }
            }}
            className="w-full py-3 rounded-lg border border-error/30 text-error hover:bg-error/10 transition-all text-sm font-medium flex items-center justify-center gap-2"
          >
            <RotateCcw className="w-4 h-4" /> 🗑️ Сбросить прогресс
          </button>
        </motion.div>
      </div>
    );
  }

  return null;
}