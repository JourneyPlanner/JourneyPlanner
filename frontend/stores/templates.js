import { defineStore } from "pinia";
import { ref } from "vue";

export const useTemplateStore = defineStore("templates", () => {
    const updateTemplates = ref(false);

    function changeUpdate(shouldUpdate) {
        this.updateTemplates = shouldUpdate;
    }

    return { updateTemplates, changeUpdate };
});
