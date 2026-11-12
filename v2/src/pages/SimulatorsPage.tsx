// src/pages/SimulatorsPage.tsx
import { motion, type Variants } from 'framer-motion';
import { Link } from 'react-router-dom';
import { 
  Phone, 
  UserX, 
  AlertTriangle,
  Clock,
  ArrowRight,
  Shield
} from 'lucide-react';

const MotionLink = motion(Link);

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.12 } }
};

interface Simulator {
  id: number;
  icon: React.ElementType;
  title: string;
  subtitle: string;
  desc: string;
  path: string;
  duration: string;
  intensity: string;
  intensityColor: string;
}

const simulators: Simulator[] = [
  { 
    id: 1, 
    icon: Phone, 
    title: 'Голос на том конце провода', 
    subtitle: 'Симуляция телефонного мошенничества',
    desc: 'Вам звонят из "службы безопасности банка". Голос уверен, детали убедительны. Сможете ли вы распознать обман, не поддавшись панике?', 
    path: '/simulators/phone-scam',
    duration: '~10 мин',
    intensity: 'Высокое напряжение',
    intensityColor: 'bg-error/10 text-error border-error/20'
  },
  { 
    id: 2, 
    icon: UserX, 
    title: 'Он знает всё о тебе', 
    subtitle: 'Симуляция общения с незнакомцем в сети',
    desc: 'Новый друг в соцсетях пишет слишком часто. Он знает, где вы учитесь, какие фото лайкали. Это просто совпадение или что-то большее?', 
    path: '/simulators/stranger-online',
    duration: '~15 мин',
    intensity: 'Психологическое давление',
    intensityColor: 'bg-accent/10 text-accent border-accent/20'
  },
];

export default function SimulatorsPage() {
  return (
    <motion.div initial="hidden" animate="visible">
      
      {/* ЗАГОЛОВОК СТРАНИЦЫ */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-4 tracking-tight text-text">
          Симуляторы
        </h2>
        <p className="text-text-secondary text-lg max-w-2xl mx-auto font-light leading-relaxed">
          Погрузитесь в реалистичные сценарии, где каждое ваше решение имеет последствия. 
          Здесь нет правильных ответов — есть только ваш выбор.
        </p>
      </motion.section>

      {/* Тонкий разделитель */}
      <div className="w-full h-px bg-white/5 mb-16" />

      {/* СЕТКА СИМУЛЯТОРОВ */}
      <motion.section 
        variants={staggerContainer} 
        className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-24 max-w-5xl mx-auto"
      >
        {simulators.map((sim) => (
          <MotionLink
            key={sim.id}
            to={sim.path}
            variants={fadeInUp}
            whileHover={{ y: -4 }}
            className="group relative flex flex-col p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-white/10 transition-all duration-300"
          >
            {/* Иконка */}
            <div className="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
              <sim.icon className="w-7 h-7 text-accent" strokeWidth={1.5} />
            </div>

            {/* Подзаголовок */}
            <span className="text-text-muted text-xs uppercase tracking-wider mb-2">
              {sim.subtitle}
            </span>

            {/* Заголовок */}
            <h3 className="font-orbitron text-xl md:text-2xl font-bold text-text mb-4 group-hover:text-accent transition-colors leading-tight">
              {sim.title}
            </h3>

            {/* Описание */}
            <p className="text-text-secondary text-sm leading-relaxed mb-6 flex-grow">
              {sim.desc}
            </p>

            {/* Мета-информация */}
            <div className="flex flex-wrap gap-2 mb-6">
              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/5 text-text-muted border border-white/10">
                <Clock className="w-3 h-3" />
                {sim.duration}
              </span>
              <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ${sim.intensityColor}`}>
                <AlertTriangle className="w-3 h-3" />
                {sim.intensity}
              </span>
            </div>

            {/* Кнопка "Начать" */}
            <div className="flex items-center justify-between text-sm font-medium text-text-muted group-hover:text-accent transition-colors mt-auto pt-4 border-t border-white/5">
              <span>Начать симуляцию</span>
              <ArrowRight className="w-4 h-4 transform group-hover:translate-x-1 transition-transform" />
            </div>

            {/* Тонкая линия свечения снизу при наведении */}
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
              <Shield className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h4 className="font-semibold text-text mb-2">Безопасная среда</h4>
            <p className="text-text-secondary text-sm leading-relaxed">
              Все симуляции проходят в защищённом пространстве. Никаких реальных угроз — только учебный опыт.
            </p>
          </div>

          {/* Карточка 2 */}
          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center mb-4">
              <AlertTriangle className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h4 className="font-semibold text-text mb-2">Реальные последствия</h4>
            <p className="text-text-secondary text-sm leading-relaxed">
              Каждое ваше решение влияет на развитие сценария. Ошибки здесь учат лучше, чем в жизни.
            </p>
          </div>

          {/* Карточка 3 */}
          <div className="p-6 rounded-xl border border-white/5 bg-background-light/20">
            <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center mb-4">
              <Clock className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h4 className="font-semibold text-text mb-2">Разбор результатов</h4>
            <p className="text-text-secondary text-sm leading-relaxed">
              После завершения вы получите подробный анализ ваших действий и рекомендации.
            </p>
          </div>
        </div>
      </motion.section>

    </motion.div>
  );
}