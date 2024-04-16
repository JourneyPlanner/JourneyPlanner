// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    devtools: { enabled: true },
    typescript: {
        typeCheck: true,
    },
    app: {
        pageTransition: { name: "page", mode: "out-in" },
        layoutTransition: { name: "page", mode: "out-in" },
    },
    modules: [
        "@nuxtjs/tailwindcss",
        "@nuxtjs/color-mode",
        "nuxt-primevue",
        "@vee-validate/nuxt",
        "nuxt-auth-sanctum",
        "@pinia/nuxt",
        "@nuxt/eslint",
    ],
    css: [
        "@/assets/css/fonts/fonts.css",
        "primevue/resources/themes/aura-light-blue/theme.css",
        "primeicons/primeicons.css",
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
            include: [
                "Password",
                "Calendar",
                "Divider",
                "Button",
                "Checkbox",
                "TieredMenu",
                "Tooltip",
                "Menu",
                "Dialog",
                "ConfirmDialog",
                "Toast",
                "Sidebar",
                "OverlayPanel",
                "TabView",
                "TabPanel",
                "InputGroup",
                "InputGroupAddon",
                "InputIcon",
                "IconField",
            ],
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
        redirectIfAuthenticated: true,
        redirect: {
            keepRequestedRoute: true,
            onLogin: "/dashboard",
            onLogout: "/login",
            onAuthOnly: "/login",
            onGuestOnly: "/dashboard",
        },
    },
});
