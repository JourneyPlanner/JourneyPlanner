// get toast from app.vue to use it in utils
import type { useToast } from "primevue/usetoast";

export const useToastService = () => {
    const nuxtApp = useNuxtApp();
    const getToast: typeof useToast = () =>
        nuxtApp.vueApp.config.globalProperties.$toast;
    const toastService = getToast();
    return toastService;
};
