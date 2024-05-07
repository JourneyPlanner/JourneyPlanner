import { defineStore } from "pinia";
import { ref } from "vue";

export const useJourneyStore = defineStore("journey", () => {
    const journey = ref({});

    function setJourney(journey) {
        this.journey = journey;
    }

    function getId() {
        return this.journey.id;
    }

    function getFromDate() {
        return this.journey.from;
    }

    function getToDate() {
        return this.journey.to;
    }

    return { journey, setJourney, getId, getFromDate, getToDate };
});
