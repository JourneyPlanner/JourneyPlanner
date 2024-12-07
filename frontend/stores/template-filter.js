import { defineStore } from "pinia";
import { ref } from "vue";

//template store for the template tab on the dashboard
export const useTemplateFilterStore = defineStore("template-filter", () => {
    const TEMPLATE_MAX_LENGTH = 31;
    const PER_PAGE = 40;

    const DEFAULT_FILTERS = {
        TEMPLATE_MAX_LENGTH: TEMPLATE_MAX_LENGTH,
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
    };

    const filters = ref({
        moreTemplatesAvailable: DEFAULT_FILTERS.moreTemplatesAvailable,
        TEMPLATE_MAX_LENGTH: DEFAULT_FILTERS.TEMPLATE_MAX_LENGTH,
        PER_PAGE: DEFAULT_FILTERS.PER_PAGE,
        templateJourneyLengthMinMax:
            DEFAULT_FILTERS.templateJourneyLengthMinMax,
        sortby: DEFAULT_FILTERS.sortby,
        sortorder: DEFAULT_FILTERS.sortorder,
        templateDestinationInput: DEFAULT_FILTERS.templateDestinationInput,
        templateDestinationName: DEFAULT_FILTERS.templateDestinationName,
        templateCreator: DEFAULT_FILTERS.templateCreator,
        cursor: DEFAULT_FILTERS.cursor,
        nextCursor: DEFAULT_FILTERS.nextCursor,
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
        this.filters = DEFAULT_FILTERS;
        return this.filters;
    }

    return { filters, setFilters, setFilter, getFilter, resetFilters };
});
