// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: [
    "@nuxtjs/tailwindcss",
    "@nuxtjs/color-mode",
    "nuxt-primevue",
    "@vee-validate/nuxt",
  ],
  css: [
    "@/assets/css/fonts/fonts.css",
    "primevue/resources/themes/aura-light-blue/theme.css",
  ],
  colorMode: {
    classSuffix: "",
    preference: "light",
    fallback: "light",
  },
  runtimeConfig: {
    public: {
      NUXT_TOLGEE_API_KEY: process.env.NUXT_TOLGEE_API_KEY,
      NUXT_TOLGEE_API_URL: process.env.NUXT_TOLGEE_API_URL,
    },
  },
  primevue: {
    options: {
      ripple: true,
      unstyled: false,
    },
    components: {
      include: ["Password", "Calendar", "Divider", "Button"],
    },
  },
  veeValidate: {
    autoImports: true,
    componentNames: {
      Form: "VeeForm",
      Field: "VeeField",
      FieldArray: "VeeFieldArray",
      ErrorMessage: "VeeErrorMessage",
    },
  },
});
