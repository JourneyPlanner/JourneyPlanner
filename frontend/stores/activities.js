import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activities", () => {
    const activityData = ref([]);
    const addedActivity = ref([]);

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

    function updateActivity(activity) {
        activity.forEach((element) => {
            const index = this.activityData.findIndex(
                (obj) => obj.id === element.id,
            );
            this.activityData[index] = element;
        });
    }

    function findBaseActivity(id) {
        let newActiviy =
            this.activityData[
                this.activityData.findIndex((obj) => obj.id === id)
            ];
        while (newActiviy.parent_id != null) {
            newActiviy =
                this.activityData[
                    this.activityData.findIndex(
                        (obj) => obj.id === newActiviy.parent_id,
                    )
                ];
        }
        return newActiviy;
    }

    function getAllChildren(id) {
        let children = [];
        activityData.value.forEach((item) => {
            if (item.parent_id == id) {
                children.push(item);
            }
        });
        return children;
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
        findBaseActivity,
        getAllChildren,
    };
});
