import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activity", () => {
    const activityData = ref({});

    function setActivities(activityData) {
        this.activityData = activityData;
    }

    function addActivity(activity) {
        this.activityData.push(activity);
    }

    function clearActivities() {
        this.activityData = [];
    }

    return {
        activityData,
        setActivities,
        addActivity,
        clearActivities,
    };
});
