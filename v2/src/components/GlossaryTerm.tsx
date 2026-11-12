// src/components/GlossaryTerm.tsx
import { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Info } from 'lucide-react';

interface GlossaryTermProps {
  term: string;
  definition: string;
}

export default function GlossaryTerm({ term, definition }: GlossaryTermProps) {
  const [isOpen, setIsOpen] = useState(false);
  const buttonRef = useRef<HTMLButtonElement>(null);

  const handleClick = () => {
    setIsOpen(!isOpen);
  };

  // Закрытие при клике вне
  useEffect(() => {
    if (!isOpen) return;

    function handleClickOutside(event: MouseEvent) {
      if (buttonRef.current && !buttonRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [isOpen]);

  // Закрытие при Escape
  useEffect(() => {
    if (!isOpen) return;
    function handleEsc(e: KeyboardEvent) {
      if (e.key === 'Escape') setIsOpen(false);
    }
    document.addEventListener('keydown', handleEsc);
    return () => document.removeEventListener('keydown', handleEsc);
  }, [isOpen]);

  return (
    <span className="relative inline-block">
      <button
        ref={buttonRef}
        onClick={handleClick}
        className="group relative inline-flex items-center gap-1.5 text-accent font-medium 
                   border-b border-dashed border-accent/50 hover:border-accent 
                   transition-all duration-300 cursor-pointer bg-transparent p-0 align-baseline"
        type="button"
      >
        {term}
        <span className="relative flex h-1.5 w-1.5">
          <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75" />
          <span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-accent" />
        </span>
      </button>

      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, y: -8, height: 0 }}
            animate={{ opacity: 1, y: 0, height: 'auto' }}
            exit={{ opacity: 0, y: -8, height: 0 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
            className="absolute left-0 top-full z-50 mt-2 w-80 md:w-96 
                       rounded-lg border border-accent/30 bg-background-light 
                       shadow-2xl shadow-accent/10 overflow-hidden"
          >
            <div className="flex items-center gap-2 px-4 py-2.5 border-b border-white/5 bg-accent/5">
              <Info className="w-4 h-4 text-accent" strokeWidth={2} />
              <span className="text-xs font-bold uppercase tracking-wider text-accent">
                Определение
              </span>
            </div>

            <div className="p-4 max-h-60 overflow-y-auto custom-scrollbar">
              <p className="text-sm text-text-secondary leading-relaxed">
                {definition}
              </p>
            </div>

            <div className="h-[1px] w-full bg-gradient-to-r from-transparent via-accent/50 to-transparent" />
          </motion.div>
        )}
      </AnimatePresence>
    </span>
  );
}