// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },

  css: [
    '~/assets/scss/app.scss',
    '~/assets/scss/fonts.scss',
  ],

  modules: [
    '@nuxtjs/svg-sprite',
    '@nuxt/image',
  ],

  svgSprite: {
    input: '~/assets/sprite/input',
    output: '~/assets/sprite/output',
  },

  runtimeConfig: {
    public: {
      baseUrl: process.env.API_BASE_URL
    },
    session: {
      password: process.env.SESSION_PASSWORD || '20aea585-457a-4502-b473-cacb2ed9b31b',
      maxAge: process.env.SESSION_MAX_AGE || 172800,
      name: process.env.SESSION_NAME || 'SESSID'
    },
  },

  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
})
