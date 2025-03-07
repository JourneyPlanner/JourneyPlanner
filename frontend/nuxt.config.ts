// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    devtools: { enabled: true },
    typescript: {
        typeCheck: true,
    },
    future: {
        typescriptBundlerResolution: false,
    },
    experimental: {
        inlineRouteRules: true,
    },
    app: {
        pageTransition: { name: "page", mode: "out-in" },
        layoutTransition: { name: "page", mode: "out-in" },
        head: {
            titleTemplate: "%s",
            link: [
                {
                    rel: "icon",
                    type: "image/png",
                    href: "/favicon-48x48.png",
                    sizes: "48x48",
                },
                { rel: "icon", type: "image/svg+xml", href: "/favicon.svg" },
                { rel: "shortcut icon", href: "/favicon.ico" },
                {
                    rel: "apple-touch-icon",
                    href: "/apple-touch-icon.png",
                    sizes: "180x180",
                },
                { rel: "manifest", href: "/site.webmanifest" },
            ],
            meta: [
                {
                    name: "description",
                    content: "JourneyPlanner - Travel planning made easy",
                },
                { property: "og:site_name", content: "JourneyPlanner" },
                { property: "og:url", content: process.env.NUXT_FRONTEND_URL },
                { property: "og:type", content: "website" },
                { property: "og:title", content: "JourneyPlanner" },
                {
                    property: "og:description",
                    content: "Travel planning made ease",
                },
            ],
        },
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
        "@nuxtjs/seo",
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
            NUXT_FRONTEND_URL: process.env.NUXT_FRONTEND_URL,
            NUXT_MAPBOX_API_KEY: process.env.NUXT_MAPBOX_API_KEY,
            NUXT_UPLOAD_URL: process.env.NUXT_UPLOAD_URL,
            NUXT_LIGHTGALLERY_KEY: process.env.NUXT_LIGHTGALLERY_KEY,
            NUXT_GOOGLE_CLIENT_ID: process.env.NUXT_GOOGLE_CLIENT_ID,
        },
    },
    primevue: {
        options: {
            ripple: true,
            unstyled: false,
            zIndex: {
                modal: 500,
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
                "InputNumber",
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
                "Slider",
                "AutoComplete",
                "Rating",
            ],
        },
    },
    veeValidate: {
        autoImports: true,
    },
    sanctum: {
        baseUrl: process.env.NUXT_BACKEND_URL, // Laravel API
        origin: process.env.NUXT_FRONTEND_URL, // Nuxt app, by default will be used 'useRequestURL().origin'
        redirectIfAuthenticated: true,
        redirectIfUnauthenticated: true,
        redirect: {
            keepRequestedRoute: true,
            onLogin: "/dashboard",
            onLogout: "/login",
            onAuthOnly: "/login",
            onGuestOnly: "/dashboard",
        },
        endpoints: {
            user: "/api/me",
        },
    },
});
