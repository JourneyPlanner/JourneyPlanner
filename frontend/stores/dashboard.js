import { defineStore } from "pinia";
import { ref } from "vue";

export const useDashboardStore = defineStore("dashboard", () => {
    const journeys = ref([]);

    function setJourneys(journeys) {
        this.journeys = journeys;
    }

    return { journeys, setJourneys };
});
