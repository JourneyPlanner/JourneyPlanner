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
    const calendarActivityMap = ref(new Map());

    function setActivities(newActivityData) {
        activityData.value.splice(
            0,
            activityData.value.length,
            ...newActivityData,
        );
    }

    function addActivity(activity) {
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === activity.id,
        );
        if (activityIndex == -1) {
            activityData.value.push(activity);
        }
    }

    function setNewActivity(activity) {
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === activity.id,
        );
        if (activityIndex == -1) {
            addedActivity.value = activity;
        }
    }

    function updateActivity(activity) {
        const basicActivity = activity;
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === activity.id,
        );
        if (activityIndex == -1) {
            activityData.push(activity);
            setNewActivity(activity);
        } else {
            Object.assign(activity, {
                calendar_activities: [],
            });
            activity.calendar_activities =
                activityData[activityIndex].calendar_activities;
            activityData.value[activityIndex] = activity;
            activity.calendar_activities?.forEach((calendarActivity) => {
                Object.assign(calendarActivity, {
                    activity: basicActivity,
                });
                setTimeout(() => {
                    createOrUpdateCalendarActivity(calendarActivity);
                }, 50);
            });
            setNewActivity(activity);
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
        activityData.value = activityData.value.filter(
            (activity) => activity.id !== removedActivity.id,
        );
        setActivities(activityData.value);
    }

    function createOrUpdateCalendarActivity(addCalendarActivity) {
        let oldCalendarActivity;
        let activityIndex = activityData.value.findIndex(
            (obj) => obj.id === addCalendarActivity.activity_id,
        );
        if (activityIndex == -1) {
            activityData.value.push(addCalendarActivity.activity);
            activityIndex = activityData.value.findIndex(
                (obj) => obj.id === addCalendarActivity.activity_id,
            );
        }

        oldCalendarActivity = calendarActivityMap.value.get(
            addCalendarActivity.id,
        );

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
        // eslint-disable-next-line no-unused-vars
        const { activity, ...calendarActivityWithoutMainActivity } =
            addCalendarActivity;
        if (calendarActivityIndex != -1) {
            activityData.value[activityIndex].calendar_activities[
                calendarActivityIndex
            ] = calendarActivityWithoutMainActivity;
        } else {
            activityData.value[activityIndex].calendar_activities.push(
                calendarActivityWithoutMainActivity,
            );
        }

        if (
            oldCalendarActivity &&
            oldCalendarActivity !== addCalendarActivity.activity_id
        ) {
            activityIndex = activityData.value.findIndex(
                (obj) => obj.id === oldCalendarActivity,
            );

            const calendarActivityIndex = activityData.value[
                activityIndex
            ].calendar_activities.findIndex(
                (obj) => obj.id === addCalendarActivity.id,
            );

            if (activityIndex != -1 && calendarActivityIndex != -1) {
                activityData.value[activityIndex].calendar_activities.splice(
                    calendarActivityIndex,
                    1,
                );
            }

            calendarActivityMap.value.set(
                addCalendarActivity.id,
                addCalendarActivity.activity_id,
            );
        } else {
            calendarActivityMap.value.set(
                addCalendarActivity.id,
                addCalendarActivity.activity_id,
            );
        }

        newCalendarActivity.value = calendarActivityWithoutMainActivity;
    }

    function removeCalendarActivity(rmdCalendarActivity) {
        let activityIndex = activityData.value.findIndex(
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
        // eslint-disable-next-line no-unused-vars
        const { activity, ...calendarActivityWithoutMainActivity } =
            rmdCalendarActivity;
        activityData.value[activityIndex].calendar_activities.splice(
            calendarActivityIndex,
            1,
        );

        const oldCalendarActivity = calendarActivityMap.value.get(
            rmdCalendarActivity.id,
        );

        if (oldCalendarActivity) {
            activityIndex = activityData.value.findIndex(
                (obj) => obj.id === oldCalendarActivity,
            );

            const calendarActivityIndex = activityData.value[
                activityIndex
            ].calendar_activities.findIndex(
                (obj) => obj.id === rmdCalendarActivity.id,
            );

            activityData.value[activityIndex].calendar_activities.splice(
                calendarActivityIndex,
                1,
            );
        }

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
