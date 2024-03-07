import { Tolgee, DevTools, VueTolgee, FormatSimple } from "@tolgee/vue";

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();

  const tolgee = Tolgee().use(DevTools()).use(FormatSimple()).init({
    language: "en",

    // for development
    apiUrl: config.public.NUXT_TOLGEE_API_URL,
    apiKey: config.public.NUXT_TOLGEE_API_KEY,

    // for production
    staticData: {},
  });

  nuxtApp.vueApp.use(VueTolgee, { tolgee });
});
