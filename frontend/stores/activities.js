import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activities", () => {
    const activityData = ref([]);
    const addedActivity = ref({});

    function setActivities(activityData) {
        this.activityData = [];
        this.activityData = activityData;
    }

    function addActivity(activity) {
        this.activityData.push(activity);
    }

    function setNewActivity(activity) {
        this.addedActivity = activity;
    }

    function updateActivity(activity, id) {
        const index = this.activityData.findIndex((obj) => obj.id === id);
        this.activityData[index] = activity;
    }

    function getActivity(id) {
        return this.activityData[
            this.activityData.findIndex((obj) => obj.id === id)
        ];
    }

    return {
        activityData,
        setActivities,
        getActivity,
        addActivity,
        addedActivity,
        setNewActivity,
        updateActivity,
    };
});
