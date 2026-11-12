// src/pages/TestsPage.tsx
import { motion, type Variants } from 'framer-motion';
import { Link } from 'react-router-dom';
import { 
  ClipboardList, 
  ShieldCheck, 
  Key, 
  Layers, 
  Timer, 
  EyeOff,
  ArrowRight,
  Quote
} from 'lucide-react';

const MotionLink = motion(Link);

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};

interface TestTopic {
  id: number;
  icon: React.ElementType;
  title: string;
  desc: string;
  path: string;
}

const testTopics: TestTopic[] = [
  { 
    id: 1, 
    icon: ClipboardList, 
    title: 'Основы цифровой гигиены', 
    desc: 'Базовые правила безопасности в интернете.', 
    path: '/tests/digital-hygiene' 
  },
  { 
    id: 2, 
    icon: ShieldCheck, 
    title: 'Распознавание фишинга', 
    desc: 'Умение отличать мошеннические письма и сайты.', 
    path: '/tests/phishing' 
  },
  { 
    id: 3, 
    icon: Key, 
    title: 'Надежность паролей', 
    desc: 'Знание принципов создания и хранения паролей.', 
    path: '/tests/passwords' 
  },
];

export default function TestsPage() {
  return (
    <motion.div initial="hidden" animate="visible">
      
      {/* ЗАГОЛОВОК СТРАНИЦЫ */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-4 tracking-tight text-text">
          Тесты
        </h2>
        <p className="text-text-secondary text-lg max-w-2xl mx-auto font-light">
          Проверьте свои знания и узнайте, насколько хорошо вы защищены в цифровом мире.
        </p>
      </motion.section>

      {/* Тонкий разделитель */}
      <div className="w-full h-px bg-white/5 mb-16" />

      {/* СЕТКА ТЕМ ТЕСТОВ */}
      <motion.section 
        variants={staggerContainer} 
        className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-24 max-w-5xl mx-auto"
      >
        {testTopics.map((topic) => (
          <MotionLink
            key={topic.id}
            to={topic.path}
            variants={fadeInUp}
            whileHover={{ y: -4 }}
            className="group relative flex flex-col p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-white/10 transition-all duration-300"
          >
            <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
              <topic.icon className="w-7 h-7 text-accent" strokeWidth={1.5} />
            </div>

            <h3 className="font-semibold text-text text-xl mb-3 group-hover:text-accent transition-colors">
              {topic.title}
            </h3>

            <p className="text-text-secondary text-sm leading-relaxed mb-8 flex-grow">
              {topic.desc}
            </p>

            <div className="flex items-center text-sm font-medium text-text-muted group-hover:text-accent transition-colors mt-auto pt-4 border-t border-white/5">
              Начать тест
              <ArrowRight className="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" />
            </div>

            <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
          </MotionLink>
        ))}
      </motion.section>

      {/* БЛОК "КАК ЭТО РАБОТАЕТ" */}
      <motion.section variants={fadeInUp} className="mb-24">
        <div className="flex items-center gap-3 mb-10">
          <div className="h-px flex-grow bg-white/5" />
          <span className="text-text-muted text-xs font-medium tracking-widest uppercase">
            Как это работает
          </span>
          <div className="h-px flex-grow bg-white/5" />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
          {/* Карточка 1 */}
          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center mb-4">
              <Layers className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h4 className="font-semibold text-text mb-2">Два уровня сложности</h4>
            <p className="text-text-secondary text-sm leading-relaxed">
              Базовый (10 вопросов) и Углубленный (15 вопросов).
            </p>
          </div>

          {/* Карточка 2 */}
          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center mb-4">
              <Timer className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h4 className="font-semibold text-text mb-2">Таймер и прогресс</h4>
            <p className="text-text-secondary text-sm leading-relaxed">
              Следите за временем и своим прогрессом в реальном времени.
            </p>
          </div>

          {/* Карточка 3 */}
          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center mb-4">
              <EyeOff className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h4 className="font-semibold text-text mb-2">Только результаты</h4>
            <p className="text-text-secondary text-sm leading-relaxed">
              После теста вы увидите только количество правильных ответов, но не сами ответы.
            </p>
          </div>
        </div>
      </motion.section>

      {/* ЦИТАТА ВНИЗУ */}
      <motion.section 
        variants={fadeInUp}
        className="mb-16"
      >
        <div className="max-w-3xl mx-auto text-center px-6 py-12 rounded-2xl border border-white/5 bg-background-light/10">
          <Quote className="w-8 h-8 text-accent/30 mx-auto mb-4" strokeWidth={1.5} />
          <p className="text-text-secondary text-lg md:text-xl font-light italic leading-relaxed">
            Кибербезопасность — это навык, который нужно постоянно развивать.
          </p>
        </div>
      </motion.section>

    </motion.div>
  );
}