import { defineStore } from "pinia";
import { ref } from "vue";

//template store for the template tab on the dashboard
export const useTemplateStore = defineStore("filters", () => {
    const TEMPLATE_MAX_LENGTH = 31;
    const PER_PAGE = 40;

    const filters = ref({
        moreTemplatesAvailable: true,
        TEMPLATE_MAX_LENGTH: TEMPLATE_MAX_LENGTH,
        PER_PAGE: PER_PAGE,
        templateJourneyLengthMinMax: [1, TEMPLATE_MAX_LENGTH],
        sortby: "",
        sortorder: "",
        templateDestinationInput: "",
        templateDestinationName: "",
        templateCreator: "",
        cursor: null,
        nextCursor: null,
    });

    function setFilters(filters) {
        this.filters = filters;
    }

    function setFilter(key, value) {
        this.filters[key] = value;
    }

    function getFilter(key) {
        return this.filters[key];
    }

    function resetFilters() {
        return (this.filters = {
            moreTemplatesAvailable: true,
            templateJourneyLengthMinMax: [1, TEMPLATE_MAX_LENGTH],
            PER_PAGE: PER_PAGE,
            sortby: "",
            sortorder: "",
            templateDestinationInput: "",
            templateDestinationName: "",
            templateCreator: "",
            cursor: null,
            nextCursor: null,
        });
    }

    return { filters, setFilters, setFilter, getFilter, resetFilters };
});
