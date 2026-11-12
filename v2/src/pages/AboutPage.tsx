// src/pages/AboutPage.tsx
import { motion, type Variants } from 'framer-motion';
import GlossaryTerm from '../components/GlossaryTerm';
import { 
  Shield, 
  Gamepad2, 
  BookOpen, 
  Target, 
  ExternalLink, 
  Users, 
  GraduationCap 
} from 'lucide-react';

const fadeInUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.6, ease: "easeOut" } }
};

const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.1 } }
};

const features = [
  {
    icon: Shield,
    title: 'Симуляторы и сценарии',
    desc: 'Мы погружаем подростка в реалистичные ситуации. Он может совершить ошибку в безопасной среде, увидеть её последствия и понять, как действовать правильно.'
  },
  {
    icon: Gamepad2,
    title: 'Мини-игры и тесты',
    desc: 'Закрепляем навыки через геймификацию, удерживая внимание там, где обычные уроки его теряют.'
  },
  {
    icon: BookOpen,
    title: 'Готовые уроки для учителей',
    desc: 'Мы берем рутину на себя. Педагогу не нужно искать актуальные кейсы — всё уже готово, современно и работает на удержание внимания класса.'
  }
];

const projects = [
  {
    icon: Users,
    title: 'Мы это мир',
    desc: 'Сообщество, посвященное безопасности и развитию в цифровом пространстве',
    link: 'https://vk.com/we_aretheworld?from=groups'
  }

];

export default function AboutPage() {
  return (
    <motion.div initial="hidden" animate="visible">
      
      {/* HERO SECTION */}
      <motion.section variants={fadeInUp} className="py-16 md:py-24 text-center">
        <h2 className="font-orbitron text-3xl md:text-5xl font-bold mb-6 tracking-tight text-text leading-tight">
          Цифровая безопасность <br />
          <span className="text-accent">без скуки и нравоучений</span>
        </h2>
        <p className="text-text-secondary text-lg md:text-xl max-w-3xl mx-auto font-light leading-relaxed">
          Мы живем в мире, где школьник может потерять деньги, аккаунты или репутацию за пару кликов. 
          Традиционные лекции о безопасности не работают: они скучные, и информация из них выветривается через пять минут. 
          <br /><br />
          <span className="text-text font-medium">«КиберКвестор» ломает этот сценарий.</span> Это интерактивная образовательная платформа, где теория сразу переходит в практику.
        </p>
      </motion.section>

      <div className="w-full h-px bg-white/5 mb-16" />

      {/* ЧТО ВНУТРИ? */}
      <motion.section variants={staggerContainer} className="mb-24">
        <div className="flex items-center gap-3 mb-10">
          <div className="h-px flex-grow bg-white/5" />
          <span className="font-orbitron text-lg font-semibold text-text uppercase tracking-wider">
            Что внутри?
          </span>
          <div className="h-px flex-grow bg-white/5" />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {features.map((feature, idx) => (
            <motion.div
              key={idx}
              variants={fadeInUp}
              className="group relative p-8 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-white/10 transition-all duration-300"
            >
              <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center mb-6 group-hover:bg-accent/15 transition-colors">
                <feature.icon className="w-6 h-6 text-accent" strokeWidth={1.5} />
              </div>
              <h3 className="font-orbitron text-xl font-bold text-text mb-4 group-hover:text-accent transition-colors">
                {feature.title}
              </h3>
              <p className="text-text-secondary text-sm leading-relaxed">
                {feature.desc}
              </p>
            </motion.div>
          ))}
        </div>
      </motion.section>

      {/* НАША ЦЕЛЬ (Mission Statement) */}
   <motion.section variants={fadeInUp} className="mb-24">
 <div className="relative p-8 md:p-12 rounded-2xl border border-accent/20 bg-accent/5 max-w-4xl mx-auto">
    {/* Декоративный фоновый элемент */}
    <div className="absolute top-0 right-0 w-64 h-64 bg-accent/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
    
    <div className="relative z-10 flex flex-col md:flex-row items-start gap-6">
      <div className="w-14 h-14 rounded-lg bg-accent/20 flex items-center justify-center flex-shrink-0">
        <Target className="w-7 h-7 text-accent" strokeWidth={1.5} />
      </div>
      <div>
        <h3 className="font-orbitron text-2xl md:text-3xl font-bold text-text mb-4">
          Наша цель
        </h3>
        <p className="text-text-secondary text-lg leading-relaxed mb-4">
          Чтобы после прохождения сценария подросток не просто «узнал новое», 
          а испытал{' '}
          <GlossaryTerm
            term="инсайт"
            definition="Инсайт (от англ. insight — озарение) — это момент внезапного понимания. 
                        В обучении это происходит, когда теория становится личным опытом: 
                        «Ага, вот как это работает на самом деле! Я ведь почти так же попался бы в жизни»."
          />
          :{' '}
          <span className="text-text font-medium italic">
            «Черт, я ведь почти так попался в жизни!»
          </span>
        </p>
        <p className="text-text text-base font-medium">
          Мы даем иммунитет к цифровым угрозам через личный опыт, а не через зубрежку.
        </p>
      </div>
    </div>
  </div>
</motion.section>

      {/* ДРУГИЕ ПРОЕКТЫ */}
      <motion.section variants={staggerContainer} className="mb-16">
        <div className="flex items-center gap-3 mb-10">
          <div className="h-px flex-grow bg-white/5" />
          <span className="font-orbitron text-lg font-semibold text-text uppercase tracking-wider">
            Другие проекты из сферы безопасности
          </span>
          <div className="h-px flex-grow bg-white/5" />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
          {projects.map((project, idx) => (
            <motion.a
              key={idx}
              href={project.link}
              target="_blank"
              rel="noopener noreferrer"
              variants={fadeInUp}
              whileHover={{ y: -4 }}
              className="group flex flex-col p-6 rounded-xl border border-white/5 bg-background-light/20 hover:bg-background-light/40 hover:border-accent/30 transition-all duration-300"
            >
              <div className="flex items-start justify-between mb-4">
                <div className="w-12 h-12 rounded-lg bg-background-dark flex items-center justify-center border border-white/10 group-hover:border-accent/30 transition-colors">
                  <project.icon className="w-6 h-6 text-text-secondary group-hover:text-accent transition-colors" strokeWidth={1.5} />
                </div>
                <ExternalLink className="w-5 h-5 text-text-muted group-hover:text-accent transition-colors" />
              </div>
              <h3 className="font-orbitron text-lg font-bold text-text mb-2 group-hover:text-accent transition-colors">
                {project.title}
              </h3>
              <p className="text-text-secondary text-sm leading-relaxed">
                {project.desc}
              </p>
            </motion.a>
          ))}
        </div>
      </motion.section>

    </motion.div>
  );
}