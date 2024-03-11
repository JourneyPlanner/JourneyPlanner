// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: [
    "@nuxtjs/tailwindcss",
    "@nuxtjs/color-mode",
    "nuxt-primevue",
    "@vee-validate/nuxt",
    "nuxt-auth-sanctum",
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
      NUXT_BACKEND_URL: process.env.NUXT_BACKEND_URL,
    },
  },
  primevue: {
    options: {
      ripple: true,
      unstyled: false,
    },
    components: {
      include: ["Password", "Calendar", "Divider", "Button", "Checkbox"],
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
  sanctum: {
    baseUrl: process.env.NUXT_BACKEND_URL, // Laravel API
    origin: process.env.NUXT_FRONTEND_URL, // Nuxt app, by default will be used 'useRequestURL().origin'
    redirect: {
      keepRequestedRoute: true,
      onLogin: "/dashboard",
      onLogout: "/",
      onAuthOnly: "/login",
      onGuestOnly: "/",
    },
  },
});
