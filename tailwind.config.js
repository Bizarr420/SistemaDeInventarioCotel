// tailwind.config.js
module.exports = {
    darkMode: 'class',
    content: [
      './resources/**/*.blade.php',
      './resources/**/*.js',
      './resources/**/*.vue',
    ],
    theme: {
      extend: {
        fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
      },
    },
    plugins: [],
  }
  