import BadgeDirective from "primevue/badgedirective";

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.directive("badge", BadgeDirective);
});
