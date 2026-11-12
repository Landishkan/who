// src/App.tsx
import { HashRouter, Routes, Route, Link, useLocation } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { Menu } from 'lucide-react';
import { Particles } from './components/Particles';

// Импорт  страниц
import HomePage from './pages/HomePage';
import GamesPage from './pages/GamesPage';
import ScenariosPage from './pages/ScenariosPage';
import LearningPage from './pages/LearningPage';
import TestsPage from './pages/TestsPage';
import TeachersPage from './pages/TeachersPage';
import SimulatorsPage from './pages/SimulatorsPage';
import AboutPage from './pages/AboutPage';
// Компонент для анимации переходов между страницами
function AnimatedRoutes() {
  const location = useLocation();
  return (
    <AnimatePresence mode="wait">
      <motion.div
        key={location.pathname}
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        exit={{ opacity: 0, y: -10 }}
        transition={{ duration: 0.3 }}
      >
        <Routes location={location} key={location.pathname}>
          <Route path="/" element={<HomePage />} />
          <Route path="/games" element={<GamesPage />} />
          <Route path="/scenarios" element={<ScenariosPage />} />
          <Route path="/learning" element={<LearningPage />} />
          <Route path="/tests" element={<TestsPage />} />
           <Route path="/teachers" element={<TeachersPage />} />
           <Route path="/simulators" element={<SimulatorsPage />} />
           <Route path="/about" element={<AboutPage />} />
        </Routes>
      </motion.div>
    </AnimatePresence>
  );
}

export default function App() {
  const navItems = [
     { name: 'О нас', path: '/about' }, 
    { name: 'Мини-игры', path: '/games' },
    { name: 'Сценарии', path: '/scenarios' },
    { name: 'Обучение', path: '/learning' },
    { name: 'Тесты', path: '/tests' },
    { name: 'Симуляторы', path: '/simulators' },
     { name: 'Для учителей', path: '/teachers' },
  ];

  return (
    <HashRouter>
      <div className="min-h-screen flex flex-col relative">
        <Particles />

        {/* HEADER */}
        <header className="border-b border-white/5 bg-background-dark/90 backdrop-blur-md sticky top-0 z-50">
          <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <Link to="/" className="font-orbitron text-lg font-bold tracking-wider text-text hover:text-accent transition-colors">
              КИБЕРКВЕСТОР
            </Link>
            
            <nav className="hidden md:flex gap-8">
              {navItems.map((item) => (
                <Link 
                  key={item.name} 
                  to={item.path} 
                  className="text-text-secondary hover:text-accent transition-colors text-sm font-medium tracking-wide"
                >
                  {item.name}
                </Link>
              ))}
            </nav>

            <button className="md:hidden text-text-secondary">
              <Menu size={20} />
            </button>
          </div>
        </header>

        {/* MAIN CONTENT (Здесь меняются страницы) */}
        <main className="flex-grow max-w-6xl mx-auto px-6 w-full relative z-10">
          <AnimatedRoutes />
        </main>

        {/* FOOTER */}
        <footer className="border-t border-white/5 py-12 mt-auto relative z-10">
          <div className="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <p className="font-orbitron text-sm font-bold tracking-wider text-text-muted">КИБЕРКВЕСТОР</p>
            <p className="text-text-muted text-sm">Защити себя в цифровом мире. © 2026</p>
          </div>
        </footer>
      </div>
    </HashRouter>
  );
}