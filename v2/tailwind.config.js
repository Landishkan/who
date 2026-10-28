/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
colors: {
  primary: {
    DEFAULT: '#0f4cff',
    light: '#3a6bff',
    dark: '#0028a8',
  },
  secondary: '#8FB8ED',
  accent: '#5B9CF0',
  success: '#16a541',
  error: '#ff2d75',
  background: {
    dark: '#0a0a20',    // <-- Вернули твой глубокий оригинальный фон
    light: '#111128',   // <-- Чуть светлее для карточек (не резкий контраст)
    lighter: '#16163a', // <-- Для hover-состояний
  },
  text: {
    DEFAULT: '#e2e8f0', // <-- Мягкий белый (не режет глаза)
    secondary: '#8b9bbd', // <-- Приглушённый серо-синий для описаний
    muted: '#5a6a8a',   // <-- Для очень второстепенного текста
  },
},
  fontFamily: {
        orbitron: ['Orbitron', 'sans-serif'],
        rajdhani: ['Rajdhani', 'sans-serif'],
      },
    },
  },
  plugins: [],
}