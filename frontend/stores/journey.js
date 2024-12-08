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

    function getInvite() {
        return this.journey.invite;
    }

    function getDestination() {
        return this.journey.destination;
    }

    function getMapboxFullAddress() {
        return this.journey.mapbox_full_address;
    }

    function getMapboxID() {
        return this.journey.mapbox_id;
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
        getInvite,
        getDestination,
        getMapboxFullAddress,
        getMapboxID,
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
