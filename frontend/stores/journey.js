import { defineStore } from "pinia";
import { ref } from "vue";

export const useJourneyStore = defineStore("journey", () => {
    const journey = ref({});
    const media = ref([]);
    const docs = ref([]);

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

    function setMedia(media) {
        this.media = media;
    }

    function getMedia() {
        return this.media;
    }

    function setDocs(docs) {
        this.docs = docs;
    }

    function getDocs() {
        return this.docs;
    }

    return {
        journey,
        media,
        docs,
        setJourney,
        resetJourney,
        getID,
        getName,
        getFromDate,
        getToDate,
        getLong,
        getLat,
        setMedia,
        getMedia,
        setDocs,
        getDocs,
    };
});
