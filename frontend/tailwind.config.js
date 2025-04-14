/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
    './error.vue',
  ],
  theme: {
    container: {
      center: true,
      padding: {
        DEFAULT: '20px',
      },
    },
    screens: {
      xs: '360px',
      sm: '768px',
      md: '1024px',
      lg: '1280px',
      xl: '1440px',
      '2xl': '1920px',
    },
    fontSize: {
      xs: '0.75rem',
      sm: '0.825rem',
      base: '1rem',
      xl: '1.25rem',
      '2xl': '1.563rem',
      '3xl': '1.953rem',
      '4xl': '2.441rem',
      '5xl': '3.052rem',
    },
    fontFamily: {
      sans: ["\"RobotoMono\", monospace"],
      serif: ["\"RobotoMono\", monospace"],
      mono: ["\"RobotoMono\", monospace"],
      display: ["\"RobotoMono\", monospace"],
      body: ["\"RobotoMono\", monospace"]
    },
    extend: {
      colors: {
        primary: '#3A2B6A',
        accent: '#584499',
      },
      fontFamily: {
        'sans-serif': ['Jost', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
