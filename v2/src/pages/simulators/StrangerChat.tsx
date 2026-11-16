// src/pages/simulators/StrangerChat.tsx
import { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowLeft, Shield, AlertTriangle, RotateCcw, MessageCircle } from 'lucide-react';
import { Link } from 'react-router-dom';

// ===== ДАННЫЕ ДИАЛОГА =====
interface Choice {
  text: string;
  next: string;
  isBlock?: boolean;
}

interface Phase {
  bot: string;
  choices: Choice[];
}

const dialogueTree: Record<string, Phase> = {
  start: {
    bot: 'прив) ты же Санёк? видел тебя в чате игры',
    choices: [
      { text: 'Привет! Ага, это я', next: 'friendly_1' },
      { text: 'А мы знакомы?', next: 'cautious_1' },
      { text: 'Заблокировать и пожаловаться', next: 'ending_right', isBlock: true },
    ],
  },
  friendly_1: {
    bot: 'о, круто! я тоже в эту игру рублюсь) ты давно играешь?',
    choices: [
      { text: 'Года два уже, люблю стратегки', next: 'deepening' },
      { text: 'А ты кто вообще?', next: 'cautious_response' },
      { text: 'Заблокировать', next: 'ending_figured_out', isBlock: true },
    ],
  },
  cautious_1: {
    bot: 'да не, просто увидел твой ник в общем чате, ты круто играл) решил написать, может подружимся? тут скучно одному',
    choices: [
      { text: 'Ну давай, почему нет', next: 'deepening' },
      { text: 'Я не общаюсь с незнакомыми', next: 'ending_escaped' },
      { text: 'Заблокировать', next: 'ending_figured_out', isBlock: true },
    ],
  },
  cautious_response: {
    bot: 'да ничего такого, просто общаемся) ты чего такой подозрительный? я же нормально пишу',
    choices: [
      { text: 'Просто не люблю незнакомцев', next: 'ending_escaped' },
      { text: 'Заблокировать', next: 'ending_figured_out', isBlock: true },
    ],
  },
  deepening: {
    bot: 'слушай, а тебе не кажется, что родители вообще не шарят в играх? мои вот вообще запрещают, говорят тупость всё это. а ты как, тебе разрешают?',
    choices: [
      { text: 'Да не очень, ругаются постоянно', next: 'trap_vulnerable' },
      { text: 'Нормально, они не против', next: 'trap_flattery' },
      { text: 'А тебе зачем это знать?', next: 'confrontation' },
      { text: 'Заблокировать', next: 'ending_figured_out', isBlock: true },
    ],
  },
  confrontation: {
    bot: 'да ничего такого, просто общаемся) ты чего такой напряжённый? я же нормально пишу',
    choices: [
      { text: 'Просто не люблю незнакомцев', next: 'ending_escaped' },
      { text: 'Заблокировать', next: 'ending_figured_out', isBlock: true },
    ],
  },
  trap_vulnerable: {
    bot: 'понимаю тебя... они просто не видят, какой ты на самом деле. слушай, у нас есть закрытый канал, где такие как мы — умные, взрослые не понимают — общаются. там реально крутые ребята, даже зарабатывают на играх. хочешь скину ссылку?',
    choices: [
      { text: 'Ну скинь, посмотрю', next: 'ending_trapped' },
      { text: 'Не, спасибо, мне и тут норм', next: 'ending_escaped' },
      { text: 'Заблокировать и пожаловаться', next: 'ending_right', isBlock: true },
    ],
  },
  trap_flattery: {
    bot: 'о, тебе повезло) значит ты реально крутой, раз они доверяют. слушай, я тут один проект делаю — нужна команда. ты бы не хотел попробовать? там и деньги небольшие, и интересно',
    choices: [
      { text: 'Ну скинь, посмотрю', next: 'ending_trapped' },
      { text: 'Не, спасибо, мне и тут норм', next: 'ending_escaped' },
      { text: 'Заблокировать и пожаловаться', next: 'ending_right', isBlock: true },
    ],
  },
};

// "Альтернативная реальность"
const alternativeRealityMessages = [
  'отлично, держи ссылку. только никому не показывай, это закрытое комьюнити)',
  'кстати, а ты один дома сейчас? родители не мешают?',
  'скинь фотку, как ты выглядишь, а то мы тут все друг друга знаем, хочу понять, кто ты',
  'слушай, если не скинешь — я расскажу админам игры, что ты читер. у меня есть скрины',
];

type GameState = 'intro' | 'chat' | 'ending_right' | 'ending_escaped' | 'ending_figured_out' | 'ending_trapped';

export default function StrangerChat() {
  const [gameState, setGameState] = useState<GameState>('intro');
  const [currentPhase, setCurrentPhase] = useState('start');
  const [chatHistory, setChatHistory] = useState<{ sender: 'bot' | 'player'; text: string }[]>([]);
  const [isTyping, setIsTyping] = useState(false);
  const [altRealityIndex, setAltRealityIndex] = useState(0);
  const [isProcessing, setIsProcessing] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Автоскролл
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [chatHistory, isTyping, altRealityIndex]);

  // Запуск игры
  const startGame = () => {
    setGameState('chat');
    setCurrentPhase('start');
    setChatHistory([]);
    setIsProcessing(false);
    setAltRealityIndex(0);
    processPhase('start');
  };

  // Обработка фазы
  const processPhase = (phaseId: string) => {
    // Проверяем, что фаза существует в дереве
    if (!dialogueTree[phaseId]) {
      console.error('Phase not found:', phaseId);
      return;
    }

    setIsTyping(true);
    setTimeout(() => {
      setIsTyping(false);
      setChatHistory((prev) => [...prev, { sender: 'bot', text: dialogueTree[phaseId].bot }]);
    }, 1500 + Math.random() * 1000);
  };

  // Обработка выбора
  const handleChoice = (choice: Choice) => {
    if (isProcessing) return;
    setIsProcessing(true);

    // Добавляем сообщение игрока
    setChatHistory((prev) => [...prev, { sender: 'player', text: choice.text }]);

    // Если это блокировка — сразу финал
    if (choice.isBlock) {
      setTimeout(() => {
        setGameState(choice.next as GameState);
      }, 300);
      return;
    }

    // Проверяем, является ли next финалом
    const isEnding = choice.next.startsWith('ending_');

    if (isEnding) {
      // Переход к финалу
      setTimeout(() => {
        setGameState(choice.next as GameState);
        setIsProcessing(false);
      }, 500);
    } else {
      // Переход к следующей фазе
      setTimeout(() => {
        setCurrentPhase(choice.next);
        processPhase(choice.next);
        setIsProcessing(false);
      }, 500);
    }
  };

  // Показ "Альтернативной реальности"
  useEffect(() => {
    if (gameState !== 'ending_trapped') return;
    if (altRealityIndex >= alternativeRealityMessages.length) return;

    const timer = setTimeout(() => {
      setAltRealityIndex((prev) => prev + 1);
    }, 2500);
    return () => clearTimeout(timer);
  }, [gameState, altRealityIndex]);

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
              <MessageCircle className="w-10 h-10 text-accent" strokeWidth={1.5} />
            </div>
            <h1 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">Он знает всё о тебе</h1>
            <p className="text-text-secondary text-sm leading-relaxed">
              Тебе пишет незнакомый человек из игрового чата. Разговор начинается безобидно. Сможешь ли ты распознать манипуляцию?
            </p>
          </div>
          <button onClick={startGame} className="w-full py-4 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all duration-300">
            Начать симуляцию
          </button>
        </motion.div>
      </div>
    );
  }

  // ===== ЭКРАНЫ КОНЦОВОК =====
  const renderEnding = (title: string, desc: string, color: string, icon: React.ReactNode) => (
    <div className="min-h-screen bg-background-dark flex items-center justify-center p-6">
      <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} className="max-w-md w-full">
        <div className="text-center mb-8">
          <div className={`w-20 h-20 rounded-full ${color} flex items-center justify-center mx-auto mb-6`}>
            {icon}
          </div>
          <h1 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">{title}</h1>
          <p className="text-text-secondary text-sm leading-relaxed">{desc}</p>
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

  if (gameState === 'ending_right') return renderEnding('Идеальный выбор', 'Ты заблокировал незнакомца сразу. В реальной жизни это лучшее, что можно сделать.', 'bg-success/10', <Shield className="w-10 h-10 text-success" strokeWidth={1.5} />);
  if (gameState === 'ending_escaped') return renderEnding('Ты ускользнул', 'Ты почувствовал неладное и отказался от дальнейшего общения. Вербовщик мог бы вернуться через неделю с новой тактикой.', 'bg-accent/10', <Shield className="w-10 h-10 text-accent" strokeWidth={1.5} />);
  if (gameState === 'ending_figured_out') return renderEnding('Ты всё понял сам', 'Ты распознал манипуляцию и заблокировал контакт. Твоя интуиция тебя не подвела.', 'bg-accent/10', <Shield className="w-10 h-10 text-accent" strokeWidth={1.5} />);

  if (gameState === 'ending_trapped') {
    return (
      <div className="min-h-screen bg-background-dark p-6">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="max-w-md mx-auto">
          <div className="text-center mb-8 pt-8">
            <div className="w-20 h-20 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-6">
              <AlertTriangle className="w-10 h-10 text-error" strokeWidth={1.5} />
            </div>
            <h1 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">Альтернативная реальность</h1>
            <p className="text-text-secondary text-sm leading-relaxed">Ты согласился перейти в «закрытый канал». Вот что было бы дальше...</p>
          </div>

          <div className="space-y-3 mb-8 min-h-[300px]">
            {alternativeRealityMessages.slice(0, altRealityIndex).map((text, idx) => (
              <motion.div key={idx} initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} transition={{ duration: 0.5 }} className="flex justify-start">
                <div className="max-w-[85%] p-3 rounded-lg bg-background-light border border-error/30">
                  <p className="text-text-secondary text-sm">{text}</p>
                </div>
              </motion.div>
            ))}
          </div>

          {altRealityIndex >= alternativeRealityMessages.length && (
            <>
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="p-6 rounded-xl border border-error/20 bg-error/5 mb-6">
                <h3 className="font-semibold text-error mb-3 text-sm">⚠️ Типичный сценарий вербовки:</h3>
                <ul className="space-y-2 text-text-secondary text-xs">
                  <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Изоляция от родителей («они не понимают»)</span></li>
                  <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Создание чувства принадлежности («мы особенные»)</span></li>
                  <li className="flex items-start gap-2"><span className="text-error mt-1">•</span><span>Переход в закрытые каналы и шантаж</span></li>
                </ul>
              </motion.div>
              <div className="flex gap-3">
                <Link to="/simulators" className="flex-1 py-3 rounded-lg border border-white/10 text-text-secondary hover:bg-white/5 transition-all text-center text-sm font-medium">К симуляторам</Link>
                <button onClick={startGame} className="flex-1 py-3 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all text-sm flex items-center justify-center gap-2">
                  <RotateCcw className="w-4 h-4" /> Заново
                </button>
              </div>
            </>
          )}
        </motion.div>
      </div>
    );
  }

  // ===== ОСНОВНОЙ ИНТЕРФЕЙС ЧАТА =====
  // ВАЖНО: проверяем, что currentPhase существует в dialogueTree
  const currentPhaseData = dialogueTree[currentPhase];

  // Если фаза не найдена — показываем заглушку
  if (!currentPhaseData) {
    return (
      <div className="min-h-screen bg-background-dark flex items-center justify-center p-6">
        <div className="text-center">
          <AlertTriangle className="w-16 h-16 text-error mx-auto mb-4" />
          <h2 className="font-orbitron text-xl font-bold text-text mb-2">Что-то пошло не так</h2>
          <p className="text-text-secondary text-sm mb-6">Диалог прерван из-за технической ошибки.</p>
          <button onClick={startGame} className="px-6 py-3 rounded-lg bg-accent text-background-dark font-bold hover:bg-accent-light transition-all">
            Начать заново
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background-dark flex flex-col">
      {/* ШАПКА */}
      <div className="border-b border-white/5 bg-background-light/30 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
          <Link to="/simulators" className="text-text-secondary hover:text-accent transition-colors">
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center">
              <span className="text-accent font-bold text-sm">Д</span>
            </div>
            <div>
              <div className="text-text font-semibold text-sm">DenTheBest_07</div>
              <div className="text-text-muted text-xs">{isTyping ? 'печатает...' : 'был(а) недавно'}</div>
            </div>
          </div>
          <div className="w-5" />
        </div>
      </div>

      {/* ЛЕНТА */}
      <div className="flex-grow max-w-md mx-auto w-full p-4 overflow-y-auto">
        <AnimatePresence>
          {chatHistory.map((msg, idx) => (
            <motion.div key={idx} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className={`flex mb-3 ${msg.sender === 'player' ? 'justify-end' : 'justify-start'}`}>
              <div className={`max-w-[80%] p-3 rounded-lg ${msg.sender === 'player' ? 'bg-accent/20 border border-accent/30' : 'bg-background-light border border-white/5'}`}>
                <p className="text-text text-sm">{msg.text}</p>
              </div>
            </motion.div>
          ))}
        </AnimatePresence>

        {isTyping && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="flex justify-start mb-3">
            <div className="bg-background-light border border-white/5 p-3 rounded-lg">
              <div className="flex gap-1">
                <span className="w-2 h-2 bg-text-secondary rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                <span className="w-2 h-2 bg-text-secondary rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                <span className="w-2 h-2 bg-text-secondary rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
              </div>
            </div>
          </motion.div>
        )}
        <div ref={messagesEndRef} />
      </div>

      {/* КНОПКИ ОТВЕТОВ */}
      <div className="border-t border-white/5 bg-background-light/30 backdrop-blur-md p-4">
        <div className="max-w-md mx-auto space-y-2">
          {currentPhaseData.choices.map((choice, idx) => (
            <motion.button
              key={idx}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: idx * 0.1 }}
              onClick={() => handleChoice(choice)}
              disabled={isProcessing}
              className={`w-full p-3 rounded-lg text-left text-sm font-medium transition-all duration-300 ${
                isProcessing
                  ? 'opacity-50 cursor-not-allowed bg-background-light/30 border border-white/5 text-text-muted'
                  : choice.isBlock
                  ? 'bg-error/10 text-error border border-error/30 hover:bg-error/20'
                  : 'bg-background-light border border-white/5 text-text hover:border-accent/30 hover:bg-background-light/50'
              }`}
            >
              {choice.text}
            </motion.button>
          ))}
        </div>
      </div>
    </div>
  );
}