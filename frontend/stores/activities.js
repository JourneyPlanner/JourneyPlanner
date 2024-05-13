import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activity", () => {
    const activityData = ref({});
    const addedActivity = ref({});

    function setActivities(activityData) {
        this.activityData = activityData;
    }

    function addActivity(activity) {
        this.activityData.push(activity);
    }

    function setNewActivity(activity) {
        this.addedActivity = activity;
    }

    return {
        activityData,
        setActivities,
        addActivity,
        addedActivity,
        setNewActivity,
    };
});
