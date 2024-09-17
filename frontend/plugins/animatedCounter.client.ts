import AnimatedCounter from "vue-animated-counter";

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.component("AnimatedCounter", AnimatedCounter);
});
