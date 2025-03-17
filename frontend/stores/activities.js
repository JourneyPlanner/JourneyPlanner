import { UTCDate } from "@date-fns/utc";
import { add } from "date-fns";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useActivityStore = defineStore("activities", () => {
    const activityData = ref([]);
    const addedActivity = ref([]);
    const newCalendarActivity = ref();
    const removedCalendarActivity = ref();
    const oldActivity = ref([]);

    function setActivities(activityData) {
        this.activityData = [];
        this.activityData = activityData;
    }

    function addActivity(activity) {
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === activity.id,
        );
        if (activityIndex == -1) {
            this.activityData.push(activity);
        }
    }

    function setNewActivity(activity) {
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === activity.id,
        );
        if (activityIndex == -1) {
            this.addedActivity = activity;
        }
    }

    function updateActivity(activity) {
        const basicActivity = activity;
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === activity.id,
        );
        if (activityIndex == -1) {
            this.activityData.push(activity);
        } else {
            Object.assign(activity, {
                calendar_activities: [],
            });
            activity.calendar_activities =
                this.activityData[activityIndex].calendar_activities;
            this.activityData[activityIndex] = activity;
            activity.calendar_activities?.forEach((calendarActivity) => {
                Object.assign(calendarActivity, {
                    activity: basicActivity,
                });
                setTimeout(() => {
                    createOrUpdateCalendarActivity(calendarActivity);
                }, 50);
            });
            this.setNewActivity(activity);
        }
    }

    function findBaseActivity(activity) {
        if (!activity) return null;
        let newActivity = activity;
        while (newActivity.parent_id != null) {
            newActivity =
                activityData.value[
                    activityData.value.findIndex(
                        (obj) => obj.id === newActivity.parent_id,
                    )
                ];
        }
        return newActivity;
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
        return activityData.value[
            activityData.value.findIndex((obj) => obj.id === id)
        ];
    }

    function removeActivity(removedActivity) {
        return activityData.value
            .filter((activity) => activity.id === removedActivity.id)
            .forEach(async (activity) => {
                activityData.value.splice(
                    activityData.value.indexOf(activity),
                    1,
                );
                setActivities(activityData.value);
            });
    }

    function createOrUpdateCalendarActivity(addCalendarActivity) {
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === addCalendarActivity.activity_id,
        );
        if (activityIndex == -1) {
            activityData.value.push(addCalendarActivity.activity);
            activityIndex = activityData.value.findIndex(
                (obj) => obj.id === addCalendarActivity.activity_id,
            );
        }

        if (!activityData.value[activityIndex].calendar_activities) {
            Object.assign(activityData.value[activityIndex], {
                calendar_activities: [],
            });
        }
        const calendarActivityIndex = activityData.value[
            activityIndex
        ].calendar_activities.findIndex(
            (obj) => obj.id === addCalendarActivity.id,
        );

        const newEnd = add(new UTCDate(addCalendarActivity.start), {
            hours: parseInt(
                addCalendarActivity.activity.estimated_duration.split(":")[0],
            ),
            minutes: parseInt(
                addCalendarActivity.activity.estimated_duration.split(":")[1],
            ),
        }).toISOString();
        addCalendarActivity.end = newEnd;
        addCalendarActivity.title = addCalendarActivity.activity.name;
        const { activity, ...calendarActivityWithoutMainActivity } =
            addCalendarActivity;
        console.log(activity);
        if (calendarActivityIndex != -1) {
            activityData.value[activityIndex].calendar_activities[
                calendarActivityIndex
            ] = calendarActivityWithoutMainActivity;
        } else {
            activityData.value[activityIndex].calendar_activities.push(
                calendarActivityWithoutMainActivity,
            );
        }

        newCalendarActivity.value = calendarActivityWithoutMainActivity;
    }

    function removeCalendarActivity(rmdCalendarActivity) {
        const activityIndex = activityData.value.findIndex(
            (obj) => obj.id === rmdCalendarActivity.activity_id,
        );
        if (!activityData.value[activityIndex].calendar_activities) {
            Object.assign(activityData.value[activityIndex], {
                calendar_activities: [],
            });
        }
        const calendarActivityIndex = activityData.value[
            activityIndex
        ].calendar_activities.findIndex(
            (obj) => obj.id === rmdCalendarActivity.id,
        );
        const { activity, ...calendarActivityWithoutMainActivity } =
            rmdCalendarActivity;
        console.log(activity);
        activityData.value[activityIndex].calendar_activities.splice(
            calendarActivityIndex,
            1,
        );

        removedCalendarActivity.value = calendarActivityWithoutMainActivity;
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
        removeActivity,
        createOrUpdateCalendarActivity,
        removeCalendarActivity,
        newCalendarActivity,
        removedCalendarActivity,
    };
});
