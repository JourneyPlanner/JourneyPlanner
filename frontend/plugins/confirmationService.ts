import ConfirmationService from "primevue/confirmationservice";

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.use(ConfirmationService);
});
