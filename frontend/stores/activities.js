import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activity", () => {
    const activityData = ref({});

    function setActivities(activityData) {
        this.activityData = activityData;
    }

    return { activityData, setActivities };
});
