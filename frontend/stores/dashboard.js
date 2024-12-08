import { defineStore } from "pinia";
import { ref } from "vue";

export const useJourneysStore = defineStore("journeys", () => {
    const journeys = ref([]);

    function setJourneys(journeys) {
        this.journeys = journeys;
    }

    function addJourney(journey) {
        this.journeys.push(journey);
    }

    return { journeys, setJourneys, addJourney };
});
