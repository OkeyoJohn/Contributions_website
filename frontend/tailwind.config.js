export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        forest: '#173B2D', leaf: '#269B65', mist: '#F8FAF6',
        cream: '#FFF9EF', gold: '#E6B65C', ink: '#24352D',
      },
      fontFamily: { sans: ['"DM Sans"', 'system-ui', 'sans-serif'] },
      boxShadow: { soft: '0 10px 30px -12px rgba(23,59,45,.18)' },
    },
  },
  plugins: [],
};
