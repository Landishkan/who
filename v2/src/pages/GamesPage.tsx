// src/pages/GamesPage.tsx
import { motion, type Variants } from 'framer-motion';
import { Link } from 'react-router-dom';
import { 
  Lock, 
  Search, 
  ShieldCheck, 
  Bot, 
  Inbox, 
  Smartphone, 
  ArrowRight 
} from 'lucide-react';

// Создаем анимированную ссылку
const MotionLink = motion(Link);

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};

interface GameTag {
  text: string;
  icon: React.ElementType;
}

interface Game {
  id: number;
  icon: React.ElementType;
  title: string;
  desc: string;
  path: string;
  tags: GameTag[];
}

const games: Game[] = [
  { 
    id: 1, 
    icon: Lock, 
    title: 'Взлом пароля', 
    desc: 'Попробуйте взломать 8-значный пароль и поймите, как работают брутфорс-атаки.', 
    path: '/games/password', 
    tags: [] 
  },
  { 
    id: 2, 
    icon: Search, 
    title: 'Фейковый детектив', 
    desc: 'Найди фейковые новости и ложную информацию в потоке сообщений.', 
    path: '/games/fake-detective', 
    tags: [] 
  },
  { 
    id: 3, 
    icon: ShieldCheck, 
    title: 'Защита сети', 
    desc: 'Отражай хакерские атаки на свою виртуальную сеть в реальном времени.', 
    path: '/games/network-defense', 
    tags: [] 
  },
  { 
    id: 4, 
    icon: Bot, 
    title: 'Защита пароля от ботов', 
    desc: 'Придумай надежный пароль для защиты, пока боты не взломали его...', 
    path: '/games/password-bot', 
    tags: [] 
  },
  { 
    id: 5, 
    icon: Inbox, 
    title: 'Инбокс: Сортировка', 
    desc: 'Сортируйте письма по этикету и защищайтесь от спама и фишинга.', 
    path: '/games/inbox', 
    tags: [{ text: 'Только для телефона', icon: Smartphone }] 
  },
];

export default function GamesPage() {
  return (
    <motion.div initial="hidden" animate="visible">
      
      {/* ЗАГОЛОВОК СТРАНИЦЫ */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-4 tracking-tight text-text">
          Мини-игры
        </h2>
        <p className="text-text-secondary text-lg max-w-2xl mx-auto font-light">
          Интерактивные симуляторы, которые научат тебя защищать себя в цифровом мире.
        </p>
      </motion.section>

      {/* Тонкий разделитель */}
      <div className="w-full h-px bg-white/5 mb-16" />

      {/* СЕТКА ИГР */}
      <motion.section 
        variants={staggerContainer} 
        className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-24"
      >
        {games.map((game) => (
          <MotionLink
            key={game.id}
            to={game.path}
            variants={fadeInUp}
            whileHover={{ y: -4 }}
            className="group relative flex flex-col p-6 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-white/10 transition-all duration-300"
          >
            {/* Иконка */}
            <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center mb-5 group-hover:bg-accent/15 transition-colors">
              <game.icon className="w-6 h-6 text-accent" strokeWidth={1.5} />
            </div>

            {/* Заголовок */}
            <h3 className="font-semibold text-text text-lg mb-2 group-hover:text-accent transition-colors">
              {game.title}
            </h3>

            {/* Описание */}
            <p className="text-text-secondary text-sm leading-relaxed mb-6 flex-grow">
              {game.desc}
            </p>

            {/* Теги (например, "Только для телефона") */}
            {game.tags.length > 0 && (
              <div className="flex flex-wrap gap-2 mb-4">
                {game.tags.map((tag, idx) => (
                  <span 
                    key={idx} 
                    className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-accent/10 text-accent border border-accent/20"
                  >
                    <tag.icon className="w-3 h-3" />
                    {tag.text}
                  </span>
                ))}
              </div>
            )}

            {/* Кнопка-подсказка "Играть" */}
            <div className="flex items-center text-sm font-medium text-text-muted group-hover:text-accent transition-colors mt-auto">
              Играть
              <ArrowRight className="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" />
            </div>

            {/* Тонкая линия свечения снизу при наведении */}
            <div className="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
          </MotionLink>
        ))}
      </motion.section>

    </motion.div>
  );
}