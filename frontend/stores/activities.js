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
        this.activityData.push(activity);
    }

    function setNewActivity(activity) {
        this.addedActivity = activity;
    }

    function updateActivity(activity, shouldDelete = false) {
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
        if (!shouldDelete) {
            this.activityData.push(...activity);
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

    function updateCalendarActivity(newCalendarActivity) {
        const activityIndex = activityData.value.findIndex(
            (obj) => obj.id === newCalendarActivity.activity.id,
        );
        activityData.value[activityIndex] = newCalendarActivity.activity;
        activityData.value[activityIndex].calendar_activities.forEach(
            (calendarActivity) => {
                if (calendarActivity.id == newCalendarActivity.id) {
                    const { activity, ...calendarActivityWithoutActivity } =
                        newCalendarActivity;
                    console.log(activity);
                    calendarActivity = calendarActivityWithoutActivity;
                }
            },
        );
    }

    function createOrUpdateCalendarActivity(addCalendarActivity) {
        const activityIndex = activityData.value.findIndex(
            (obj) => obj.id === addCalendarActivity.activity_id,
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
        const { activity, ...calendarActivityWithoutMainActivity } =
            addCalendarActivity;
        console.log(activity);
        console.log(calendarActivityWithoutMainActivity);
        console.log(activityData.value[activityIndex]);
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
        console.log(calendarActivityWithoutMainActivity);
        console.log(activityData.value[activityIndex]);
        activityData.value[activityIndex].calendar_activities[
            calendarActivityIndex
        ] = calendarActivityWithoutMainActivity;

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
        updateCalendarActivity,
        newCalendarActivity,
        removedCalendarActivity,
    };
});
