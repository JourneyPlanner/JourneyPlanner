// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
<<<<<<< HEAD
  modules: [
    "@nuxtjs/tailwindcss",
    "@nuxtjs/color-mode",
    "nuxt-primevue",
    "@vee-validate/nuxt",
  ],
=======
  modules: ["@nuxtjs/tailwindcss", "nuxt-primevue", "@vee-validate/nuxt"],
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
  css: [
    "@/assets/css/fonts/fonts.css",
    "primevue/resources/themes/aura-light-blue/theme.css",
  ],
<<<<<<< HEAD
  colorMode: {
    classSuffix: "",
    preference: "light",
    fallback: "light",
  },
=======
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
  runtimeConfig: {
    public: {
      NUXT_TOLGEE_API_KEY: process.env.NUXT_TOLGEE_API_KEY,
      NUXT_TOLGEE_API_URL: process.env.NUXT_TOLGEE_API_URL,
      NUXT_BACKEND_URL: process.env.NUXT_BACKEND_URL,
<<<<<<< HEAD
      NUXT_BACKEND_URL: process.env.NUXT_BACKEND_URL,
=======
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
    },
  },
  primevue: {
    options: {
      ripple: true,
      unstyled: false,
    },
    components: {
      include: ["Password", "Calendar", "Divider", "Button"],
<<<<<<< HEAD
      include: ["Password", "Calendar", "Divider", "Button"],
=======
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
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
