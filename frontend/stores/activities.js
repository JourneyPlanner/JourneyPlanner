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
        console.log(activity);
        this.addedActivity = activity;
    }

    function updateActivity(activity) {
        oldActivity.value = [];
        activity.forEach((element) => {
            console.log(element);
            console.log(element.parent_id == null);
            if (
                element.parent_id == null &&
                this.activityData.findIndex((obj) => obj.id === element.id) !=
                    -1
            ) {
                const baseActivity = this.findBaseActivity(element);
                console.log(baseActivity);
                const activities = this.getAllChildren(baseActivity.id);
                activities.push(baseActivity);
                console.log(activities);
                activities.forEach((activity) => {
                    const index = this.activityData.findIndex(
                        (obj) => obj.id === activity.id,
                    );
                    console.log(index);
                    console.log(this.activityData[index]);
                    console.log(this.activityData);
                    oldActivity.value.push(this.activityData[index]);
                    this.activityData = this.activityData
                        .slice(0, index)
                        .concat(this.activityData.slice(index + 1));
                    console.log(this.activityData);
                });
            }
            console.log(element);
        });
        this.activityData.push(...activity);
        console.log(activity);
        console.log(this.activityData);
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
            console.log(item);
            console.log(item.parent_id);
            console.log(id);
            console.log(item.parent_id == id);
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
