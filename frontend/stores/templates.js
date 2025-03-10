import { defineStore } from "pinia";
import { ref } from "vue";

export const useTemplateStore = defineStore("templates", () => {
    const updateTemplates = ref(false);
    const templateWasEdited = ref(false);
    const editedTemplate = ref();

    function changeUpdate(shouldUpdate) {
        this.updateTemplates = shouldUpdate;
    }

    return {
        updateTemplates,
        changeUpdate,
        editedTemplate,
        templateWasEdited,
    };
});
