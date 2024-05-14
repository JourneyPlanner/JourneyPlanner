import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activities", () => {
    const activityData = ref([]);

    function setActivities(activityData) {
        this.activityData = [];
        this.activityData = activityData;
    }

    function addActivity(activity) {
        this.activityData.push(activity);
    }

    function updateActivity(activity, id) {
        const index = this.activityData.findIndex((obj) => obj.id === id);
        this.activityData[index] = activity;
    }

    return {
        activityData,
        setActivities,
        addActivity,
        updateActivity,
    };
});
