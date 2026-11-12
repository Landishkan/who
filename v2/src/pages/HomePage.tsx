// src/pages/HomePage.tsx
import { motion, type Variants } from 'framer-motion';
import { Gamepad2, Shield, BookOpen, CheckCircle } from 'lucide-react';
import { Link } from 'react-router-dom';

// Магия Framer Motion: создаем анимированную версию компонента Link
const MotionLink = motion(Link);

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};

export default function HomePage() {
  const features = [
    { icon: Gamepad2, title: 'Мини-игры', desc: 'Интерактивные задачи', path: '/games' },
    { icon: Shield, title: 'Сценарии', desc: 'Реальные ситуации', path: '/scenarios' },
    { icon: BookOpen, title: 'Обучение', desc: 'Лекции и материалы', path: '/learning' },
    { icon: CheckCircle, title: 'Тесты', desc: 'Проверка знаний', path: '/tests' },
    { icon: CheckCircle, title: 'Для учителей', desc: 'Материалы для уроков', path: '/teachers' },
    { icon: CheckCircle, title: 'Симуляторы', desc: 'Симуляции', path: '/simulators' },
  ];

  return (
    <motion.div initial="hidden" animate="visible">
      {/* HERO */}
      <motion.section variants={fadeInUp} className="py-24 md:py-32 text-center">
        <h2 className="font-orbitron text-4xl md:text-6xl font-bold mb-6 leading-tight tracking-tight">
          Изучай кибербезопасность <br />
          <span className="text-accent neon-glow">через игру</span>
        </h2>
        <p className="text-text-secondary text-lg md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
          Погрузись в мир цифровой защиты через интерактивные симуляторы. 
          Развивай навыки, которые защитят тебя в реальном мире.
        </p>
      </motion.section>

      <div className="w-full h-px bg-white/5 mb-16" />

      {/* FEATURES GRID */}
      <motion.section variants={staggerContainer} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-24">
        {features.map((item) => (
          // Используем созданный MotionLink вместо <motion(Link)>
          // Добавил className "block", чтобы вся карточка была кликабельной
          <MotionLink 
            key={item.title} 
            to={item.path} 
            variants={fadeInUp} 
            whileHover={{ y: -2 }}
            className="group relative block p-6 rounded-lg border border-white/5 bg-background-light/30 hover:bg-background-light hover:border-white/10 transition-all duration-300"
          >
            <div className="w-10 h-10 rounded-md bg-accent/10 flex items-center justify-center mb-5 group-hover:bg-accent/15 transition-colors">
              <item.icon className="w-5 h-5 text-accent" strokeWidth={1.5} />
            </div>
            <h3 className="font-semibold text-text mb-1.5 text-base">{item.title}</h3>
            <p className="text-text-secondary text-sm leading-relaxed">{item.desc}</p>
          </MotionLink>
        ))}
      </motion.section>

      {/* NEWS SECTION */}
      <motion.section variants={fadeInUp} className="mb-24">
        <div className="flex items-center gap-3 mb-8">
          <div className="h-px flex-grow bg-white/5" />
          <span className="text-text-muted text-xs font-medium tracking-widest uppercase">Последние новости</span>
          <div className="h-px flex-grow bg-white/5" />
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="p-6 rounded-lg border border-white/5 bg-background-light/30 hover:border-white/10 transition-colors">
            <span className="inline-block px-2.5 py-1 rounded-full text-xs font-medium border mb-4 bg-accent/10 text-accent border-accent/20">
              Обновление
            </span>
            <h3 className="font-semibold text-text mb-2 text-base">Мини-игра "Фейковый детектив"</h3>
            <p className="text-text-secondary text-sm leading-relaxed">Теперь доступна только для ПК и ноутбуков для полного погружения.</p>
          </div>
          <div className="p-6 rounded-lg border border-white/5 bg-background-light/30 hover:border-white/10 transition-colors">
            <span className="inline-block px-2.5 py-1 rounded-full text-xs font-medium border mb-4 bg-success/10 text-success border-success/20">
              Новинка
            </span>
            <h3 className="font-semibold text-text mb-2 text-base">Инбокс: Сортировка</h3>
            <p className="text-text-secondary text-sm leading-relaxed">Для мобильных устройств доступна новая игра по сортировке писем.</p>
          </div>
        </div>
      </motion.section>
    </motion.div>
  );
}