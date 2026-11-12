// src/pages/ScenariosPage.tsx
import { motion, type Variants } from 'framer-motion';
import { Link } from 'react-router-dom';
import { 
  MailWarning, 
  Database, 
  Smartphone, 
  Newspaper,
  Play,
  ArrowRight
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

interface Scenario {
  id: number;
  icon: React.ElementType;
  title: string;
  desc: string;
  path: string;
  difficulty: string;
  difficultyColor: string;
}

const scenarios: Scenario[] = [
  { 
    id: 1, 
    icon: MailWarning, 
    title: 'Фишинговая атака', 
    desc: 'Научитесь распознавать мошеннические письма и защищать свои данные.', 
    path: '/scenarios/phishing',
    difficulty: 'Начальный',
    difficultyColor: 'bg-success/10 text-success border-success/20'
  },
  { 
    id: 2, 
    icon: Database, 
    title: 'Утечка данных', 
    desc: 'Что делать, если ваши данные оказались в сети? Пошаговый план действий.', 
    path: '/scenarios/data-leak',
    difficulty: 'Средний',
    difficultyColor: 'bg-accent/10 text-accent border-accent/20'
  },
  { 
    id: 3, 
    icon: Smartphone, 
    title: 'Мобильная безопасность', 
    desc: 'Защитите свое устройство от угроз: приложения, сети, настройки.', 
    path: '/scenarios/mobile-security',
    difficulty: 'Начальный',
    difficultyColor: 'bg-success/10 text-success border-success/20'
  },
  { 
    id: 4, 
    icon: Newspaper, 
    title: 'Охотник за фейками', 
    desc: 'Отличите фейк от правды. Научитесь критически оценивать информацию.', 
    path: '/scenarios/fake-hunter',
    difficulty: 'Продвинутый',
    difficultyColor: 'bg-error/10 text-error border-error/20'
  },
];

export default function ScenariosPage() {
  return (
    <motion.div initial="hidden" animate="visible">
      
      {/* ЗАГОЛОВОК СТРАНИЦЫ */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-4 tracking-tight text-text">
          Интерактивные сценарии
        </h2>
        <p className="text-text-secondary text-lg max-w-2xl mx-auto font-light">
          Выберите сценарий для отработки реальных ситуаций в безопасной среде.
        </p>
      </motion.section>

      {/* Тонкий разделитель */}
      <div className="w-full h-px bg-white/5 mb-16" />

      {/* СЕТКА СЦЕНАРИЕВ */}
      <motion.section 
        variants={staggerContainer} 
        className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-24 max-w-4xl mx-auto"
      >
        {scenarios.map((scenario) => (
          <MotionLink
            key={scenario.id}
            to={scenario.path}
            variants={fadeInUp}
            whileHover={{ y: -4 }}
            className="group relative flex flex-col p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-white/10 transition-all duration-300"
          >
            {/* Иконка */}
            <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
              <scenario.icon className="w-7 h-7 text-accent" strokeWidth={1.5} />
            </div>

            {/* Заголовок */}
            <h3 className="font-semibold text-text text-xl mb-3 group-hover:text-accent transition-colors">
              {scenario.title}
            </h3>

            {/* Описание */}
            <p className="text-text-secondary text-sm leading-relaxed mb-6 flex-grow">
              {scenario.desc}
            </p>

            {/* Бейдж уровня сложности */}
            <div className="flex flex-wrap gap-2 mb-6">
              <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ${scenario.difficultyColor}`}>
                {scenario.difficulty}
              </span>
            </div>

            {/* Кнопка "Начать" */}
            <div className="flex items-center justify-between text-sm font-medium text-text-muted group-hover:text-accent transition-colors mt-auto pt-4 border-t border-white/5">
              <span>Начать сценарий</span>
              <ArrowRight className="w-4 h-4 transform group-hover:translate-x-1 transition-transform" />
            </div>

            {/* Тонкая линия свечения снизу при наведении */}
            <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
          </MotionLink>
        ))}
      </motion.section>

      {/* ПОДСКАЗКА ВНИЗУ */}
      <motion.section 
        variants={fadeInUp}
        className="mb-24"
      >
        <div className="flex items-center gap-3 mb-8">
          <div className="h-px flex-grow bg-white/5" />
          <span className="text-text-muted text-xs font-medium tracking-widest uppercase">
            Как это работает
          </span>
          <div className="h-px flex-grow bg-white/5" />
        </div>

        <div className="p-8 rounded-xl border border-white/5 bg-background-light/20 max-w-3xl mx-auto">
          <div className="flex items-start gap-4">
            <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center flex-shrink-0">
              <Play className="w-6 h-6 text-accent" strokeWidth={1.5} />
            </div>
            <div>
              <h4 className="font-semibold text-text mb-2 text-lg">Погружение в реальную ситуацию</h4>
             <p className="text-text-secondary text-lg max-w-2xl mx-auto font-light">
  Пошаговая отработка алгоритма действий в безопасной учебной среде.
</p> 
            </div>
          </div>
        </div>
      </motion.section>

    </motion.div>
  );
}