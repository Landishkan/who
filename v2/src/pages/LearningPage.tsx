// src/pages/LearningPage.tsx
import { motion, type Variants } from 'framer-motion';
import { Link } from 'react-router-dom';
import { 
  HeartCrack, 
  Handshake, 
  AlertTriangle, 
  DollarSign, 
  Fish, 
  Brain, 
  EyeOff, 
  ShieldAlert, 
  Repeat2, 
  TrendingUp,
  Clock,
  Tag,
  ArrowRight
} from 'lucide-react';

const MotionLink = motion(Link);

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.08 } }
};

interface TopicMeta {
  time: string;
  level: string;
  levelColor: string;
}

interface Topic {
  id: number;
  icon: React.ElementType;
  title: string;
  desc: string;
  path: string;
  meta: TopicMeta;
}

const topics: Topic[] = [
  { 
    id: 1, 
    icon: HeartCrack, 
    title: 'Кибербуллинг', 
    desc: 'Защита от травли в сети', 
    path: '/learning/cyberbullying',
    meta: { time: '~5 мин', level: 'Для всех', levelColor: 'bg-accent/10 text-accent border-accent/20' }
  },
  { 
    id: 2, 
    icon: Handshake, 
    title: 'Сетевой этикет', 
    desc: 'Правила поведения онлайн', 
    path: '/learning/netiquette',
    meta: { time: '~5 мин', level: 'Основы', levelColor: 'bg-success/10 text-success border-success/20' }
  },
  { 
    id: 3, 
    icon: AlertTriangle, 
    title: 'Кибертерроризм', 
    desc: 'Масштабные кибератаки', 
    path: '/learning/cyberterrorism',
    meta: { time: '~5 мин', level: 'Сложно', levelColor: 'bg-error/10 text-error border-error/20' }
  },
  { 
    id: 4, 
    icon: DollarSign, 
    title: 'Мошенничество', 
    desc: 'Онлайн-обман и защита', 
    path: '/learning/fraud',
    meta: { time: '~5 мин', level: 'Важно', levelColor: 'bg-accent/10 text-accent border-accent/20' }
  },
  { 
    id: 5, 
    icon: Fish, 
    title: 'Фишинг', 
    desc: 'Распознавание атак', 
    path: '/learning/phishing',
    meta: { time: '~5 мин', level: 'Защита', levelColor: 'bg-success/10 text-success border-success/20' }
  },
  { 
    id: 6, 
    icon: Brain, 
    title: 'Социальная инженерия', 
    desc: '"Взлом" человека', 
    path: '/learning/social-engineering',
    meta: { time: '~5 мин', level: 'Важно', levelColor: 'bg-accent/10 text-accent border-accent/20' }
  },
  { 
    id: 7, 
    icon: EyeOff, 
    title: 'Фейки', 
    desc: 'Подозрительные новости и информация', 
    path: '/learning/fakes',
    meta: { time: '~5 мин', level: 'Защита', levelColor: 'bg-success/10 text-success border-success/20' }
  },
  { 
    id: 8, 
    icon: ShieldAlert, 
    title: 'Деструктивный контент', 
    desc: 'Распознавание интернет-контента', 
    path: '/learning/destructive-content',
    meta: { time: '~5 мин', level: 'Защита', levelColor: 'bg-success/10 text-success border-success/20' }
  },
  { 
    id: 9, 
    icon: Repeat2, 
    title: 'Эхо-камеры', 
    desc: 'Информационный аквариум', 
    path: '/learning/echo-chambers',
    meta: { time: '~5 мин', level: 'Важно', levelColor: 'bg-accent/10 text-accent border-accent/20' }
  },
  { 
    id: 10, 
    icon: TrendingUp, 
    title: 'Радикализация', 
    desc: 'Погружение в "опасную среду"', 
    path: '/learning/radicalization',
    meta: { time: '~10 мин', level: 'Сложно', levelColor: 'bg-error/10 text-error border-error/20' }
  },
];

export default function LearningPage() {
  return (
    <motion.div initial="hidden" animate="visible">
      
      {/* ЗАГОЛОВОК СТРАНИЦЫ */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-4 tracking-tight text-text">
          Обучающие материалы
        </h2>
        <p className="text-text-secondary text-lg max-w-2xl mx-auto font-light">
          Выберите тему для изучения. Каждая тема — независимый урок.
        </p>
      </motion.section>

      {/* Тонкий разделитель */}
      <div className="w-full h-px bg-white/5 mb-16" />

      {/* СЕТКА ТЕМ */}
      <motion.section 
        variants={staggerContainer} 
        className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-24"
      >
        {topics.map((topic) => (
          <MotionLink
            key={topic.id}
            to={topic.path}
            variants={fadeInUp}
            whileHover={{ y: -4 }}
            className="group relative flex flex-col p-6 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-white/10 transition-all duration-300"
          >
            {/* Иконка */}
            <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center mb-5 group-hover:bg-accent/15 transition-colors">
              <topic.icon className="w-6 h-6 text-accent" strokeWidth={1.5} />
            </div>

            {/* Заголовок */}
            <h3 className="font-semibold text-text text-lg mb-2 group-hover:text-accent transition-colors">
              {topic.title}
            </h3>

            {/* Описание */}
            <p className="text-text-secondary text-sm leading-relaxed mb-6 flex-grow">
              {topic.desc}
            </p>

            {/* Мета-информация (время и уровень) */}
            <div className="flex flex-wrap gap-2 mb-4">
              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/5 text-text-muted border border-white/10">
                <Clock className="w-3 h-3" />
                {topic.meta.time}
              </span>
              <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ${topic.meta.levelColor}`}>
                <Tag className="w-3 h-3" />
                {topic.meta.level}
              </span>
            </div>

            {/* Кнопка-подсказка "Изучить" */}
            <div className="flex items-center text-sm font-medium text-text-muted group-hover:text-accent transition-colors mt-auto">
              Изучить
              <ArrowRight className="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" />
            </div>

            {/* Тонкая линия свечения снизу при наведении */}
            <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
          </MotionLink>
        ))}
      </motion.section>

      {/* ПОДСКАЗКИ ВНИЗУ */}
      <motion.section 
        variants={fadeInUp}
        className="mb-24"
      >
        <div className="flex items-center gap-3 mb-8">
          <div className="h-px flex-grow bg-white/5" />
          <span className="text-text-muted text-xs font-medium tracking-widest uppercase">
            Как учиться
          </span>
          <div className="h-px flex-grow bg-white/5" />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="flex items-start gap-4">
              <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center flex-shrink-0">
                <BookOpen className="w-5 h-5 text-accent" strokeWidth={1.5} />
              </div>
              <div>
                <h4 className="font-semibold text-text mb-2">Начинайте с любых тем</h4>
                <p className="text-text-secondary text-sm leading-relaxed">
                  Каждая тема — независимый урок. Вы можете изучать их в любом порядке, который вам удобен.
                </p>
              </div>
            </div>
          </div>

          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="flex items-start gap-4">
              <div className="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center flex-shrink-0">
                <Gamepad2 className="w-5 h-5 text-success" strokeWidth={1.5} />
              </div>
              <div>
                <h4 className="font-semibold text-text mb-2">Закрепляйте знания в играх</h4>
                <p className="text-text-secondary text-sm leading-relaxed">
                  После изучения теории пройдите интерактивные мини-игры, чтобы закрепить навыки на практике.
                </p>
              </div>
            </div>
          </div>
        </div>
      </motion.section>

    </motion.div>
  );
}

// Импорты для иконок в подсказках
import { BookOpen, Gamepad2 } from 'lucide-react';