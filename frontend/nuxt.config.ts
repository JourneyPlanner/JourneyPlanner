// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  css: ["@/assets/css/fonts/fonts.css"],
  modules: ["@nuxtjs/tailwindcss", "nuxt-primevue"],
  runtimeConfig: {
    public: {
      NUXT_TOLGEE_API_KEY: process.env.NUXT_TOLGEE_API_KEY,
      NUXT_TOLGEE_API_URL: process.env.NUXT_TOLGEE_API_URL,
    },
  },
  primevue: {
    components: {
      include: ["Password"],
    },
  },
});
