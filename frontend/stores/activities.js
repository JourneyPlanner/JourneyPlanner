import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activities", () => {
    const activityData = ref([]);
    const addedActivity = ref([]);
    const oldActivity = ref([]);

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
        oldActivity.value = [];
        activity.forEach((element) => {
            if (
                element.parent_id == null &&
                this.activityData.findIndex((obj) => obj.id === element.id) !=
                    -1
            ) {
                const baseActivity = this.findBaseActivity(element);
                const activities = this.getAllChildren(baseActivity.id);
                activities.push(baseActivity);
                activities.forEach((activity) => {
                    const index = this.activityData.findIndex(
                        (obj) => obj.id === activity.id,
                    );
                    oldActivity.value.push(this.activityData[index]);
                    this.activityData = this.activityData
                        .slice(0, index)
                        .concat(this.activityData.slice(index + 1));
                });
            }
        });
        this.activityData.push(...activity);
        this.setNewActivity(activity);
    }

    function findBaseActivity(activity) {
        let newActiviy = activity;
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
                children.push(...getAllChildren(item.id));
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
        oldActivity,
        setNewActivity,
        updateActivity,
        findBaseActivity,
        getAllChildren,
    };
});
