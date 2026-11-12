// src/pages/TeachersPage.tsx
import { useState } from 'react';
import { motion, type Variants, AnimatePresence } from 'framer-motion';
import { 
  BookOpen, Users, Clock, Gift, Filter, Mail, 
  GraduationCap, FileText, Search, Download, 
  ExternalLink, X, CheckCircle2, ListChecks
} from 'lucide-react';

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};

// ===== ДАННЫЕ СЦЕНАРИЕВ =====
const scenarios = [
  {
    id: 1,
    icon: Search,
    title: 'Охотник за фейками',
    gradeLabel: '5–11 класс',
    duration: '45–60 мин',
    topic: 'Фейки и медиа',
    desc: 'Интерактивный классный час по медиаграмотности: учимся отличать правду от фейка, распознаём признаки ложных новостей и тренируем критическое мышление.',
    includes: ['Платформа', 'Групповая работа', 'Голосование', 'Чек-лист'],
    goals: [
      'Познакомить с понятием фейковой новости и её признаками',
      'Научить применять алгоритм СТОП → ПРОВЕРЬ → ПОДУМАЙ → ДЕЛИСЬ',
      'Развивать критическое мышление через анализ реальных постов',
      'Отработать навык проверки источника, логики, эксперта и эмоций'
    ],
    plan: [
      {
        title: 'Этап 1. Вводная часть (5–7 мин)',
        text: 'Приветствие и актуализация темы. Учитель задаёт вопрос: «Кто из вас за последнюю неделю пересылал новость, в которой не был уверен на 100%?» Короткое обсуждение.'
      },
      {
        title: 'Этап 2. Вводное обучение «Ловушка доверия» (10–12 мин)',
        text: 'На экране 3 новости. Задание: «Найди правдивую новость». Класс голосует. Проверка выбора — все три новости оказываются фейками. Разбор по 4 пунктам: Источник, Логика, Эксперт, Эмоция.'
      },
      {
        title: 'Этап 3. Игра «Охота начинается» (25–30 мин)',
        text: 'Выбор категории: Общие, Тренды, Образование, Игры. Учитель зачитывает задание, ученики голосуют за вариант А/Б/В или «Фейков нет». Разбор результата с акцентом на вопрос для обсуждения.'
      },
      {
        title: 'Этап 4. Рефлексия и итоги (5–7 мин)',
        text: 'Просмотр общей статистики. Обсуждение: «В какой категории были увереннее?». Финальное задание — каждый ученик называет один признак фейка, который запомнил.'
      }
    ],
    materials: 'Компьютер/ноутбук учителя с доступом в интернет, проектор, стабильное интернет-соединение, распечатанный сценарий, доска и маркеры.',
    link: 'https://cyberquestor.ru/scen/scenario-fact.html',
    pdf: 'scenarios-teach/scen-fake.pdf'
  }
];

const ageGroups = ['Все', '1–4 классы', '5–8 классы', '9–11 классы'];
const topics = ['Все темы', 'Фейки и медиа', 'Фишинг', 'Пароли', 'Кибербуллинг', 'Личные данные', 'Соцсети', 'Мошенники'];

export default function TeachersPage() {
  const [selectedScenario, setSelectedScenario] = useState<typeof scenarios[0] | null>(null);

  return (
    <motion.div initial="hidden" animate="visible" className="relative">
      
      {/* ЗАГОЛОВОК СТРАНИЦЫ */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-4 tracking-tight text-text">
          Сценарии для классных часов
        </h2>
        <p className="text-text-secondary text-lg max-w-3xl mx-auto font-light leading-relaxed">
          Готовые методические разработки по цифровой безопасности. 
          Каждый сценарий — это полноценный урок с презентацией, раздаточными материалами и интерактивными заданиями.
        </p>
      </motion.section>

      <div className="w-full h-px bg-white/5 mb-16" />

      {/* СТАТИСТИКА */}
      <motion.section variants={staggerContainer} className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
        {[
          { icon: FileText, value: '1', label: 'готовый сценарий' },
          { icon: GraduationCap, value: '5–11', label: 'классы' },
          { icon: Clock, value: '45–60', label: 'минут на урок' },
          { icon: Gift, value: '100%', label: 'бесплатно' },
        ].map((stat, idx) => (
          <motion.div key={idx} variants={fadeInUp} className="p-6 rounded-xl border border-white/5 bg-background-light/20 text-center">
            <stat.icon className="w-6 h-6 text-accent mx-auto mb-3" strokeWidth={1.5} />
            <div className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-1">{stat.value}</div>
            <div className="text-text-secondary text-xs uppercase tracking-wider">{stat.label}</div>
          </motion.div>
        ))}
      </motion.section>

      {/* ФИЛЬТРЫ */}
      <motion.section variants={fadeInUp} className="mb-16">
        <div className="flex items-center gap-3 mb-6">
          <Filter className="w-5 h-5 text-accent" strokeWidth={1.5} />
          <h3 className="font-orbitron text-lg font-semibold text-text">Фильтры сценариев</h3>
        </div>
        <div className="space-y-6">
          <div>
            <h4 className="text-text-muted text-xs font-medium uppercase tracking-wider mb-3">Возрастная группа</h4>
            <div className="flex flex-wrap gap-2">
              {ageGroups.map((group, idx) => (
                <button key={idx} className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 ${idx === 0 ? 'bg-accent/20 text-accent border border-accent/30' : 'bg-background-light/30 text-text-secondary border border-white/5 hover:border-white/10 hover:bg-background-light/50'}`}>
                  {group}
                </button>
              ))}
            </div>
          </div>
          <div>
            <h4 className="text-text-muted text-xs font-medium uppercase tracking-wider mb-3">Тема</h4>
            <div className="flex flex-wrap gap-2">
              {topics.map((topic, idx) => (
                <button key={idx} className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 ${idx === 0 ? 'bg-accent/20 text-accent border border-accent/30' : 'bg-background-light/30 text-text-secondary border border-white/5 hover:border-white/10 hover:bg-background-light/50'}`}>
                  {topic}
                </button>
              ))}
            </div>
          </div>
        </div>
      </motion.section>

      {/* СЕТКА СЦЕНАРИЕВ (То, чего не хватало!) */}
      <motion.section variants={staggerContainer} className="mb-24">
        <div className="flex items-center gap-3 mb-8">
          <BookOpen className="w-5 h-5 text-accent" strokeWidth={1.5} />
          <h3 className="font-orbitron text-lg font-semibold text-text">Доступные сценарии</h3>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {scenarios.map((scenario) => (
            <motion.div
              key={scenario.id}
              variants={fadeInUp}
              whileHover={{ y: -4 }}
              onClick={() => setSelectedScenario(scenario)}
              className="group relative flex flex-col p-6 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-accent/30 transition-all duration-300 cursor-pointer"
            >
              <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center mb-5 group-hover:bg-accent/15 transition-colors">
                <scenario.icon className="w-6 h-6 text-accent" strokeWidth={1.5} />
              </div>
              
              <div className="flex flex-wrap gap-2 mb-3">
                <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/5 text-text-muted border border-white/10">
                  <Users className="w-3 h-3" /> {scenario.gradeLabel}
                </span>
                <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/5 text-text-muted border border-white/10">
                  <Clock className="w-3 h-3" /> {scenario.duration}
                </span>
              </div>

              <h3 className="font-orbitron text-xl font-bold text-text mb-3 group-hover:text-accent transition-colors">
                {scenario.title}
              </h3>
              <p className="text-text-secondary text-sm leading-relaxed mb-6 flex-grow">
                {scenario.desc}
              </p>

              <div className="flex items-center text-sm font-medium text-accent group-hover:text-accent-light transition-colors mt-auto pt-4 border-t border-white/5">
                Открыть подробный план
                <ExternalLink className="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" />
              </div>
            </motion.div>
          ))}
        </div>
      </motion.section>

      {/* БЛОК ПРЕДЛОЖЕНИЯ ТЕМЫ */}
      <motion.section variants={fadeInUp} className="mb-24">
        <div className="p-8 md:p-12 rounded-2xl border border-white/5 bg-background-light/20 max-w-4xl mx-auto">
          <div className="flex flex-col md:flex-row items-start gap-6">
            <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center flex-shrink-0">
              <Mail className="w-7 h-7 text-accent" strokeWidth={1.5} />
            </div>
            <div className="flex-grow">
              <h3 className="font-orbitron text-xl md:text-2xl font-bold text-text mb-3">
                Нужен сценарий под вашу тему?
              </h3>
              <p className="text-text-secondary text-sm md:text-base leading-relaxed mb-6">
                Если вы не нашли подходящий сценарий — напишите нам, и мы подготовим методичку под ваш запрос. 
                Также принимаем предложения от преподавателей-практиков.
              </p>
              <a href="mailto:teachers@cyberquestor.ru" className="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-accent/10 text-accent border border-accent/30 hover:bg-accent/20 transition-all duration-300 font-medium text-sm">
                <Mail className="w-4 h-4" />
                Предложить тему
              </a>
            </div>
          </div>
        </div>
      </motion.section>

      {/* ===== МОДАЛЬНОЕ ОКНО СЦЕНАРИЯ ===== */}
      <AnimatePresence>
        {selectedScenario && (
          <motion.div 
            initial={{ opacity: 0 }} 
            animate={{ opacity: 1 }} 
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-background-dark/80 backdrop-blur-sm"
            onClick={() => setSelectedScenario(null)}
          >
            <motion.div 
              initial={{ opacity: 0, scale: 0.95, y: 20 }} 
              animate={{ opacity: 1, scale: 1, y: 0 }} 
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="relative w-full max-w-3xl max-h-[85vh] bg-background-light border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
              onClick={(e) => e.stopPropagation()} // Чтобы клик внутри не закрывал модалку
            >
              {/* Хедер модалки */}
              <div className="flex items-start justify-between p-6 border-b border-white/5 bg-background-dark/50">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center">
                    <selectedScenario.icon className="w-6 h-6 text-accent" strokeWidth={1.5} />
                  </div>
                  <div>
                    <h2 className="font-orbitron text-xl md:text-2xl font-bold text-text">{selectedScenario.title}</h2>
                    <div className="flex gap-2 mt-2">
                      <span className="text-xs font-medium px-2 py-1 rounded bg-accent/10 text-accent border border-accent/20">
                        {selectedScenario.gradeLabel}
                      </span>
                      <span className="text-xs font-medium px-2 py-1 rounded bg-white/5 text-text-muted border border-white/10">
                        {selectedScenario.duration}
                      </span>
                    </div>
                  </div>
                </div>
                <button 
                  onClick={() => setSelectedScenario(null)}
                  className="p-2 rounded-lg hover:bg-white/10 text-text-secondary hover:text-text transition-colors"
                >
                  <X className="w-6 h-6" />
                </button>
              </div>

              {/* Тело модалки (скроллируемое) */}
              <div className="flex-grow overflow-y-auto p-6 space-y-8 custom-scrollbar">
                
                {/* Описание и включено */}
                <div>
                  <p className="text-text-secondary leading-relaxed mb-4">{selectedScenario.desc}</p>
                  <div className="flex flex-wrap gap-2">
                    {selectedScenario.includes.map((item, idx) => (
                      <span key={idx} className="text-xs px-3 py-1.5 rounded-full bg-background-dark border border-white/10 text-text-secondary flex items-center gap-1.5">
                        <CheckCircle2 className="w-3 h-3 text-accent" /> {item}
                      </span>
                    ))}
                  </div>
                </div>

                {/* Цели */}
                <div>
                  <h4 className="font-orbitron text-sm font-bold text-text uppercase tracking-wider mb-3 flex items-center gap-2">
                    <ListChecks className="w-4 h-4 text-accent" /> Цели урока
                  </h4>
                  <ul className="space-y-2">
                    {selectedScenario.goals.map((goal, idx) => (
                      <li key={idx} className="flex items-start gap-3 text-sm text-text-secondary">
                        <span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-accent flex-shrink-0" />
                        {goal}
                      </li>
                    ))}
                  </ul>
                </div>

                {/* План урока */}
                <div>
                  <h4 className="font-orbitron text-sm font-bold text-text uppercase tracking-wider mb-4 flex items-center gap-2">
                    <Clock className="w-4 h-4 text-accent" /> План проведения
                  </h4>
                  <div className="space-y-4">
                    {selectedScenario.plan.map((step, idx) => (
                      <div key={idx} className="p-4 rounded-lg bg-background-dark/50 border border-white/5">
                        <h5 className="font-semibold text-text mb-2 text-sm">{step.title}</h5>
                        <p className="text-sm text-text-secondary leading-relaxed">{step.text}</p>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Материалы */}
                <div>
                  <h4 className="font-orbitron text-sm font-bold text-text uppercase tracking-wider mb-3 flex items-center gap-2">
                    <BookOpen className="w-4 h-4 text-accent" /> Необходимые материалы
                  </h4>
                  <p className="text-sm text-text-secondary leading-relaxed bg-background-dark/50 p-4 rounded-lg border border-white/5">
                    {selectedScenario.materials}
                  </p>
                </div>
              </div>

              {/* Футер модалки с кнопками */}
              <div className="p-6 border-t border-white/5 bg-background-dark/50 flex flex-col sm:flex-row gap-3 justify-end">
                <a 
                  href={selectedScenario.pdf} 
                  download
                  className="flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 hover:text-text transition-all text-sm font-medium"
                >
                  <Download className="w-4 h-4" />
                  Скачать PDF
                </a>
                <a 
                  href={selectedScenario.link}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-accent text-background-dark hover:bg-accent-light transition-all text-sm font-bold"
                >
                  <ExternalLink className="w-4 h-4" />
                  Открыть платформу
                </a>
              </div>

            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

    </motion.div>
  );
}