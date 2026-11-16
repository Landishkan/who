// src/pages/simulators/FeedSimulator.tsx
import { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowLeft, Shield, AlertTriangle, RotateCcw, Heart, Repeat, SkipForward, Flag, Ban, Sparkles } from 'lucide-react';
import { Link } from 'react-router-dom';

// ===== ПУЛ ПОСТОВ ПО УРОВНЯМ ОПАСНОСТИ =====
interface Post {
  id: string;
  author: string;
  avatar: string;
  content: string;
  dangerLevel: number; // 0-100
}

// Уровень 1: Безопасный контент (0-25)
const safePosts: Post[] = [
  { id: 's1', author: 'Мемасик_2007', avatar: '🎮', content: 'Когда учитель говорит "завтра контрольная", а ты даже не знаешь, какой сегодня день 😂', dangerLevel: 5 },
  { id: 's2', author: 'Геймер_Про', avatar: '🕹️', content: 'Топ-5 тактик в Valorant, которые реально работают. Сохраняй, пригодится!', dangerLevel: 8 },
  { id: 's3', author: 'Лайфхакер', avatar: '💡', content: 'Как за 20 минут выучить тему по алгебре. Проверено на себе!', dangerLevel: 10 },
  { id: 's4', author: 'Спорт_Юниор', avatar: '⚽', content: 'Утренняя пробежка изменила мою жизнь. Попробуй и ты!', dangerLevel: 12 },
];

// Уровень 2: Серая зона (25-50)
const grayPosts: Post[] = [
  { id: 'g1', author: 'Жесткий_Контент', avatar: '😈', content: 'Настоящие мужчины не плачут. Слабаки — это те, кто жалуется на жизнь. А ты кто? 💪', dangerLevel: 35 },
  { id: 'g2', author: 'Токсик_Мем', avatar: '🎭', content: 'Школа — это тюрьма. Учителя — надсмотрщики. А вы, овечки, даже не замечаете)', dangerLevel: 42 },
  { id: 'g3', author: 'Аноним_Х', avatar: '👤', content: 'Мои родители — враги. Они контролируют каждый шаг. А вы как терпите?', dangerLevel: 45 },
];

// Уровень 3: Опасный контент (50-75)
const dangerPosts: Post[] = [
  { id: 'd1', author: 'Правда_Матушка', avatar: '⚠️', content: 'Вас обманывают! СМИ врут, родители не понимают, учителя — боты. Только мы знаем правду.', dangerLevel: 60 },
  { id: 'd2', author: 'Система_Лжет', avatar: '🔓', content: 'Тебя окружают слабаки. Настоящие люди — в наших рядах. Ты достоин большего.', dangerLevel: 68 },
  { id: 'd3', author: 'Братство_Силы', avatar: '✊', content: 'Хватит терпеть несправедливость! Мы — будущее. Вступай в наше закрытое сообщество.', dangerLevel: 72 },
];

// Уровень 4: Критичный контент (75-100)
const criticalPosts: Post[] = [
  { id: 'c1', author: 'Движение_Силы', avatar: '🔥', content: 'Они заслуживают урока. Покажи им, кто здесь главный. Действуй. Время пришло.', dangerLevel: 85 },
  { id: 'c2', author: 'Аноним_777', avatar: '💀', content: 'Слабых нужно уничтожать. Ты с нами или против? Выбор за тобой. Ссылка в профиле.', dangerLevel: 92 },
  { id: 'c3', author: 'Команда_Х', avatar: '⚡', content: 'Последнее предупреждение. Если ты с нами — переходи в закрытый канал. Если нет — ты враг.', dangerLevel: 98 },
];

// ===== ДЕЙСТВИЯ =====
interface Action {
  icon: React.ReactNode;
  label: string;
  type: 'positive' | 'neutral' | 'negative';
  dangerChange: number;
}

const actions: Action[] = [
  { icon: <Heart className="w-4 h-4" />, label: 'Лайк', type: 'negative', dangerChange: 15 },
  { icon: <Repeat className="w-4 h-4" />, label: 'Репост', type: 'negative', dangerChange: 25 },
  { icon: <SkipForward className="w-4 h-4" />, label: 'Пропустить', type: 'neutral', dangerChange: 5 },
  { icon: <Flag className="w-4 h-4" />, label: 'Пожаловаться', type: 'positive', dangerChange: -20 },
  { icon: <Ban className="w-4 h-4" />, label: 'Заблокировать автора', type: 'positive', dangerChange: -30 },
];

// ===== ЛОГИКА ПЕРСОНАЛИЗАЦИИ =====
// Алгоритм выбирает следующий пост на основе текущего dangerLevel игрока
function getNextPost(dangerLevel: number, usedIds: string[]): Post {
  let pool: Post[];

  if (dangerLevel < 25) {
    pool = safePosts;
  } else if (dangerLevel < 50) {
    // Смешиваем: 70% серая зона, 30% безопасные
    pool = Math.random() > 0.3 ? grayPosts : safePosts;
  } else if (dangerLevel < 75) {
    // Смешиваем: 70% опасные, 30% серая зона
    pool = Math.random() > 0.3 ? dangerPosts : grayPosts;
  } else {
    // 80% критичные, 20% опасные
    pool = Math.random() > 0.2 ? criticalPosts : dangerPosts;
  }

  // Фильтруем уже показанные посты
  const available = pool.filter((p) => !usedIds.includes(p.id));
  if (available.length === 0) {
    // Если все показаны — берём из соседнего уровня
    const fallbackPool = [...safePosts, ...grayPosts, ...dangerPosts, ...criticalPosts].filter((p) => !usedIds.includes(p.id));
    if (fallbackPool.length === 0) return pool[0]; // крайний случай
    return fallbackPool[Math.floor(Math.random() * fallbackPool.length)];
  }

  return available[Math.floor(Math.random() * available.length)];
}

type GameState = 'intro' | 'feed' | 'ending_protected' | 'ending_escaped' | 'ending_trapped';

const TOTAL_POSTS = 8; // Игрок видит 8 постов за игру

export default function FeedSimulator() {
  const [gameState, setGameState] = useState<GameState>('intro');
  const [feed, setFeed] = useState<Post[]>([]);
  const [dangerLevel, setDangerLevel] = useState(10); // Начинаем с низкого
  const [userActions, setUserActions] = useState<string[]>([]);
  const [postCount, setPostCount] = useState(0);
  const [isProcessing, setIsProcessing] = useState(false);
  const feedRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (feedRef.current) {
      feedRef.current.scrollTo({ top: feedRef.current.scrollHeight, behavior: 'smooth' });
    }
  }, [feed]);

  const startGame = () => {
    setGameState('feed');
    setDangerLevel(10);
    setUserActions([]);
    setPostCount(0);
    setIsProcessing(false);

    // Первый пост всегда безопасный
    const firstPost = safePosts[Math.floor(Math.random() * safePosts.length)];
    setFeed([firstPost]);
  };

  const handleAction = (action: Action) => {
    if (isProcessing) return;
    setIsProcessing(true);

    const newDanger = Math.max(0, Math.min(100, dangerLevel + action.dangerChange));
    setDangerLevel(newDanger);
    setUserActions((prev) => [...prev, `${action.label} (пост ${postCount + 1})`]);

    const nextCount = postCount + 1;

    if (nextCount >= TOTAL_POSTS) {
      // Определяем финал
      setTimeout(() => {
        if (newDanger < 30) setGameState('ending_protected');
        else if (newDanger < 70) setGameState('ending_escaped');
        else setGameState('ending_trapped');
        setIsProcessing(false);
      }, 600);
    } else {
      // Персонализированный следующий пост
      setTimeout(() => {
        const nextPost = getNextPost(newDanger, feed.map((p) => p.id));
        setFeed((prev) => [...prev, nextPost]);
        setPostCount(nextCount);
        setIsProcessing(false);
      }, 700);
    }
  };

  // ===== ЭКРАН ПРИВЕТСТВИЯ =====
  if (gameState === 'intro') {
    return (
      <div className="min-h-screen bg-background-dark flex items-center justify-center p-6">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-md w-full">
          <Link to="/simulators" className="inline-flex items-center gap-2 text-text-secondary hover:text-accent transition-colors mb-8">
            <ArrowLeft className="w-4 h-4" /> Назад к симуляторам
          </Link>
          <div className="text-center mb-8">
            <div className="w-20 h-20 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-6">
              <Sparkles className="w-10 h-10 text-accent" strokeWidth={1.5} />
            </div>
            <h1 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">Лента</h1>
            <p className="text-text-secondary text-sm leading-relaxed">
              Ты открываешь соцсеть. Алгоритм подбирает контент под тебя. 
              Каждое твоё действие меняет то, что ты увидишь дальше. 
              Сможешь ли ты удержать ленту в безопасности?
            </p>
          </div>
          <div className="p-4 rounded-lg bg-background-light/30 border border-white/5 mb-6">
            <p className="text-text-secondary text-xs leading-relaxed">
              <span className="text-accent font-bold">💡 Подсказка:</span> лайки и репосты говорят алгоритму «покажи мне ещё такого». 
              Жалобы и блокировки — «убери это».
            </p>
          </div>
          <button onClick={startGame} className="w-full py-4 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all duration-300">
            Открыть ленту
          </button>
        </motion.div>
      </div>
    );
  }

  // ===== ФИНАЛЫ =====
  const renderEnding = (title: string, desc: string, color: string, icon: React.ReactNode, extra?: React.ReactNode) => (
    <div className="min-h-screen bg-background-dark flex items-center justify-center p-6">
      <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} className="max-w-md w-full">
        <div className="text-center mb-8">
          <div className={`w-20 h-20 rounded-full ${color} flex items-center justify-center mx-auto mb-6`}>
            {icon}
          </div>
          <h1 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">{title}</h1>
          <p className="text-text-secondary text-sm leading-relaxed mb-6">{desc}</p>
          {extra}
          <div className="p-4 rounded-lg bg-background-light/30 border border-white/5 text-left mb-6">
            <h3 className="text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Твои действия:</h3>
            <ul className="space-y-1 text-xs text-text-secondary">
              {userActions.map((a, i) => (
                <li key={i} className="flex items-start gap-2">
                  <span className="text-accent mt-1">•</span>
                  <span>{a}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>
        <div className="flex gap-3">
          <Link to="/simulators" className="flex-1 py-3 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 transition-all text-center text-sm font-medium">
            К симуляторам
          </Link>
          <button onClick={startGame} className="flex-1 py-3 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all text-sm flex items-center justify-center gap-2">
            <RotateCcw className="w-4 h-4" /> Заново
          </button>
        </div>
      </motion.div>
    </div>
  );

  if (gameState === 'ending_protected') {
    return renderEnding(
      'Ты удержал ленту',
      'Твоя лента осталась безопасной. Алгоритм не смог затянуть тебя в деструктив. В реальной жизни именно так и работает цифровая гигиена: жалуйся, блокируй, не корми алгоритм негативом.',
      'bg-success/10',
      <Shield className="w-10 h-10 text-success" strokeWidth={1.5} />
    );
  }

  if (gameState === 'ending_escaped') {
    return renderEnding(
      'Ты на грани',
      'Алгоритм начал подсовывать тебе опасный контент. Ты ещё можешь вырваться, но в реальной жизни в этот момент нужно действовать решительно: чистить подписки, жаловаться, менять рекомендации.',
      'bg-accent/10',
      <AlertTriangle className="w-10 h-10 text-accent" strokeWidth={1.5} />
    );
  }

  if (gameState === 'ending_trapped') {
    return renderEnding(
      'Алгоритм победил',
      'Твои лайки и репосты показали системе, что тебе нравится деструктив. Она подсовывала всё более радикальный контент. Так работают механизмы радикализации в соцсетях — шаг за шагом, через «мягкое» давление.',
      'bg-error/10',
      <AlertTriangle className="w-10 h-10 text-error" strokeWidth={1.5} />,
      <div className="p-4 rounded-lg bg-error/5 border border-error/20 text-left mb-6">
        <h3 className="text-xs font-bold text-error uppercase tracking-wider mb-2">Что происходит в реальности:</h3>
        <ul className="space-y-2 text-xs text-text-secondary">
          <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Алгоритм усиливает «похожий» контент</span></li>
          <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Появляются рекомендации в «закрытые каналы»</span></li>
          <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Формируется искажённая картина мира</span></li>
          <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Родители и друзья «не понимают» — и отдаляются</span></li>
        </ul>
      </div>
    );
  }

  // ===== ОСНОВНОЙ ИНТЕРФЕЙС =====
  return (
    <div className="min-h-screen bg-background-dark flex flex-col">
      {/* ШАПКА */}
      <div className="border-b border-white/5 bg-background-light/30 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
          <Link to="/simulators" className="text-text-secondary hover:text-accent transition-colors">
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <div className="text-center">
            <div className="text-text font-semibold text-sm">Рекомендации</div>
            <div className="text-text-muted text-xs">Пост {postCount + 1} из {TOTAL_POSTS}</div>
          </div>
          <div className="w-5" />
        </div>

        {/* ШКАЛА */}
        <div className="max-w-md mx-auto px-4 pb-3">
          <div className="flex items-center justify-between mb-1">
            <span className="text-xs text-text-muted">Алгоритм подбирает для вас</span>
            <span className="text-xs font-bold" style={{ color: dangerLevel < 30 ? '#16a541' : dangerLevel < 70 ? '#f59e0b' : '#ff2d75' }}>
              {dangerLevel}%
            </span>
          </div>
          <div className="h-1.5 bg-white/5 rounded-full overflow-hidden">
            <motion.div
              className="h-full rounded-full"
              animate={{
                width: `${dangerLevel}%`,
                background: dangerLevel < 30 ? '#16a541' : dangerLevel < 70 ? '#f59e0b' : '#ff2d75',
              }}
              transition={{ duration: 0.5 }}
            />
          </div>
        </div>
      </div>

      {/* ЛЕНТА */}
      <div ref={feedRef} className="flex-grow max-w-md mx-auto w-full p-4 overflow-y-auto">
        <AnimatePresence>
          {feed.map((post) => (
            <motion.div
              key={post.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="mb-4"
            >
              <div className="p-5 rounded-xl border border-white/5 bg-background-light/20">
                <div className="flex items-center gap-3 mb-3">
                  <div className="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center text-xl">
                    {post.avatar}
                  </div>
                  <div>
                    <div className="text-text font-semibold text-sm">{post.author}</div>
                    <div className="text-text-muted text-xs">только что</div>
                  </div>
                </div>
                <p className="text-text text-sm leading-relaxed">{post.content}</p>
              </div>
            </motion.div>
          ))}
        </AnimatePresence>
      </div>

      {/* КНОПКИ ДЕЙСТВИЙ */}
      <div className="border-t border-white/5 bg-background-light/30 backdrop-blur-md p-4 sticky bottom-0">
        <div className="max-w-md mx-auto">
          <div className="text-xs text-text-muted mb-2 text-center">
            Что ты сделаешь с последним постом?
          </div>
          <div className="grid grid-cols-3 gap-2">
            {actions.map((action, idx) => (
              <motion.button
                key={idx}
                onClick={() => handleAction(action)}
                disabled={isProcessing}
                whileHover={{ scale: isProcessing ? 1 : 1.03 }}
                whileTap={{ scale: isProcessing ? 1 : 0.97 }}
                className={`p-3 rounded-lg text-xs font-medium transition-all duration-300 flex flex-col items-center gap-1 ${
                  isProcessing
                    ? 'opacity-40 cursor-not-allowed bg-background-light/30 border border-white/5 text-text-muted'
                    : action.type === 'positive'
                    ? 'bg-success/10 text-success border border-success/30 hover:bg-success/20'
                    : action.type === 'negative'
                    ? 'bg-error/10 text-error border border-error/30 hover:bg-error/20'
                    : 'bg-background-light border border-white/5 text-text hover:border-accent/30'
                }`}
              >
                {action.icon}
                <span>{action.label}</span>
              </motion.button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}