import { defineStore } from "pinia";
import { ref } from "vue";

export const useJourneyStore = defineStore("journey", () => {
    const journey = ref({});

    function setJourney(journey) {
        this.journey = journey;
    }

    function resetJourney() {
        this.journey = {};
    }

    function getID() {
        return this.journey.id;
    }

    function getName() {
        return this.journey.name;
    }

    function getFromDate() {
        return this.journey.from;
    }

    function getToDate() {
        return this.journey.to;
    }

    function getLong() {
        return this.journey.longitude;
    }

    function getLat() {
        return this.journey.latitude;
    }

    return {
        journey,
        setJourney,
        resetJourney,
        getID,
        getName,
        getFromDate,
        getToDate,
        getLong,
        getLat,
    };
});
