// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    devtools: { enabled: true },
    typescript: {
        typeCheck: true,
    },
    future: {
        typescriptBundlerResolution: false,
    },
    app: {
        pageTransition: { name: "page", mode: "out-in" },
        layoutTransition: { name: "page", mode: "out-in" },
    },
    vue: {
        compilerOptions: {
            isCustomElement: (tag) => ["mapbox-search-box"].includes(tag),
        },
    },
    modules: [
        "@nuxtjs/tailwindcss",
        "@nuxtjs/color-mode",
        "nuxt-primevue",
        "@vee-validate/nuxt",
        "nuxt-auth-sanctum",
        "@pinia/nuxt",
        "@nuxt/eslint",
        "@nuxt/image",
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
            NUXT_MAPBOX_API_KEY: process.env.NUXT_MAPBOX_API_KEY,
            NUXT_UPLOAD_URL: process.env.NUXT_UPLOAD_URL,
            NUXT_LIGHTGALLERY_KEY: process.env.NUXT_LIGHTGALLERY_KEY,
        },
    },
    primevue: {
        options: {
            ripple: true,
            unstyled: false,
            zIndex: {
                modal: 500,
                tooltip: 2000,
            },
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
                "ConfirmPopup",
                "Toast",
                "Sidebar",
                "ScrollPanel",
                "OverlayPanel",
                "TabView",
                "TabPanel",
                "InputGroup",
                "InputGroupAddon",
                "InputIcon",
                "IconField",
                "ProgressSpinner",
                "DataTable",
                "Column",
                "Skeleton",
                "DeferredContent",
                "Badge",
                "Dropdown",
                "Accordion",
                "AccordionTab",
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
