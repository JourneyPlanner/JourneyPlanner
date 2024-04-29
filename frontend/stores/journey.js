import { defineStore } from "pinia";
import { ref } from "vue";

export const useJourneyStore = defineStore("journey", () => {
    const journey = ref({});

    function setJourney(journey) {
        this.journey = journey;
    }

    function getFromDate() {
        return this.journey.from;
    }

    function getToDate() {
        return this.journey.to;
    }

    return { journey, setJourney, getFromDate, getToDate };
});
