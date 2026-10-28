import { motion, type Variants } from 'framer-motion';
import { Gamepad2, Shield, BookOpen, CheckCircle, Bell, Menu } from 'lucide-react';
import { Particles } from './components/Particles'; // <-- Добавили импорт

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.1 }
  }
};

function App() {
  return (
    <div className="min-h-screen flex flex-col relative">
      
      {/* Частицы (звездочки/пыль) на фоне */}
      <Particles />

      {/* ===== HEADER ===== */}
      <header className="border-b border-white/5 bg-background-dark/90 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
          <motion.h1 
            initial={{ opacity: 0, x: -10 }}
            animate={{ opacity: 1, x: 0 }}
            className="font-orbitron text-lg font-bold tracking-wider text-text"
          >
            КИБЕРКВЕСТОР
          </motion.h1>
          
          <nav className="hidden md:flex gap-8">
            {['О нас', 'Мини-игры', 'Сценарии', 'Обучение', 'Тесты'].map((item) => (
              <a 
                key={item} 
                href="#" 
                className="text-text-secondary hover:text-text transition-colors text-sm font-medium tracking-wide"
              >
                {item}
              </a>
            ))}
          </nav>

          <button className="md:hidden text-text-secondary">
            <Menu size={20} />
          </button>
        </div>
      </header>

      {/* ===== MAIN ===== */}
      <main className="flex-grow max-w-6xl mx-auto px-6 w-full relative z-10">
        
        {/* HERO */}
        <motion.section 
          variants={fadeInUp}
          initial="hidden"
          animate="visible"
          className="py-24 md:py-32 text-center"
        >
          <h2 className="font-orbitron text-4xl md:text-6xl font-bold mb-6 leading-tight tracking-tight">
            Изучай кибербезопасность <br />
            <span className="text-accent neon-glow">через игру</span> {/* <-- Добавили neon-glow */}
          </h2>
          <p className="text-text-secondary text-lg md:text-xl max-w-2xl mx-auto font-light leading-relaxed">
            Погрузись в мир цифровой защиты через интерактивные симуляторы. 
            Развивай навыки, которые защитят тебя в реальном мире.
          </p>
        </motion.section>

        {/* Тонкий разделитель */}
        <div className="w-full h-px bg-white/5 mb-16" />

        {/* FEATURES GRID */}
        <motion.section 
          variants={staggerContainer}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, margin: "-100px" }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-24"
        >
          {[
            { icon: Gamepad2, title: 'Мини-игры', desc: 'Интерактивные задачи' },
            { icon: Shield, title: 'Сценарии', desc: 'Реальные ситуации' },
            { icon: BookOpen, title: 'Обучение', desc: 'Лекции и материалы' },
            { icon: CheckCircle, title: 'Тесты', desc: 'Проверка знаний' },
          ].map((item) => (
            <motion.a
              key={item.title}
              href="#"
              variants={fadeInUp}
              whileHover={{ y: -2 }}
              className="group relative p-6 rounded-lg border border-white/5 bg-background-light/30 hover:bg-background-light hover:border-white/10 transition-all duration-300"
            >
              <div className="w-10 h-10 rounded-md bg-accent/10 flex items-center justify-center mb-5 group-hover:bg-accent/15 transition-colors">
                <item.icon className="w-5 h-5 text-accent" strokeWidth={1.5} />
              </div>
              
              <h3 className="font-semibold text-text mb-1.5 text-base">
                {item.title}
              </h3>
              <p className="text-text-secondary text-sm leading-relaxed">
                {item.desc}
              </p>
            </motion.a>
          ))}
        </motion.section>

        {/* NEWS SECTION */}
        <motion.section 
          variants={fadeInUp}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          className="mb-24"
        >
          <div className="flex items-center gap-3 mb-8">
            <div className="h-px flex-grow bg-white/5" />
            <span className="text-text-muted text-xs font-medium tracking-widest uppercase">
              Последние новости
            </span>
            <div className="h-px flex-grow bg-white/5" />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {[
              { 
                tag: 'Обновление', 
                tagColor: 'bg-accent/10 text-accent border-accent/20', 
                title: 'Мини-игра "Фейковый детектив"', 
                desc: 'Теперь доступна только для ПК и ноутбуков для полного погружения.' 
              },
              { 
                tag: 'Новинка', 
                tagColor: 'bg-success/10 text-success border-success/20', 
                title: 'Инбокс: Сортировка', 
                desc: 'Для мобильных устройств доступна новая игра по сортировке писем.' 
              },
            ].map((news) => (
              <div 
                key={news.title} 
                className="p-6 rounded-lg border border-white/5 bg-background-light/30 hover:border-white/10 transition-colors"
              >
                <span className={`inline-block px-2.5 py-1 rounded-full text-xs font-medium border mb-4 ${news.tagColor}`}>
                  {news.tag}
                </span>
                <h3 className="font-semibold text-text mb-2 text-base">
                  {news.title}
                </h3>
                <p className="text-text-secondary text-sm leading-relaxed">
                  {news.desc}
                </p>
              </div>
            ))}
          </div>
        </motion.section>

      </main>

      {/* ===== FOOTER ===== */}
      <footer className="border-t border-white/5 py-12 mt-auto relative z-10">
        <div className="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="font-orbitron text-sm font-bold tracking-wider text-text-muted">
            КИБЕРКВЕСТОР
          </p>
          <p className="text-text-muted text-sm">
            Защити себя в цифровом мире. © 2026
          </p>
        </div>
      </footer>
    </div>
  );
}

export default App;