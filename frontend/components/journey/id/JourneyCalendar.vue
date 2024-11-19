<script setup lang="ts">
import type { CalendarOptions } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import FullCalendar from "@fullcalendar/vue3";
import { useTolgee, useTranslate } from "@tolgee/vue";
import { add, differenceInMinutes, startOfWeek } from "date-fns";
import resolveConfig from "tailwindcss/resolveConfig";
import { ref } from "vue";
import tailwindConfig from "~/tailwind.config.js";

const fullConfig = resolveConfig(tailwindConfig);
const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");
const { isAuthenticated } = useSanctumAuth();

let text = "";
let bg = "";
let border = "";
if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    text = fullConfig.theme.accentColor["natural-50"] as string;
    bg = fullConfig.theme.accentColor["dark"] as string;
    border = "#50A1C0";
} else {
    text = fullConfig.theme.accentColor["text"] as string;
    bg = fullConfig.theme.accentColor["light"] as string;
    border = "#50A1C0";
}

const fullCalendar = ref();
const store = useActivityStore();
const isActivityInfoVisible = ref(false);
const activities = computed(() => store.activityData as Activity[]);
const client = useSanctumClient();
const alreadyAdded = ref(false);
const { t } = useTranslate();
const { addedActivity } = storeToRefs(store);
const toast = useToast();
const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    currentUserRole: {
        type: Number,
        required: true,
    },
    journeyStartdate: {
        type: String,
        required: true,
    },
    journeyEnddate: {
        type: String,
        required: true,
    },
    duringJourney: {
        type: Boolean,
        required: true,
    },
    journeyEnded: {
        type: Boolean,
        required: true,
    },
});
const activityId = ref("");
const calendarId = ref("");
const calendarActivity = ref();
const onlyShow = ref(true);
const update = ref(false);
const calendarClicked = ref(false);
const address = ref("");
const cost = ref("");
const created_at = ref("");
const description = ref("");
const email = ref("");
const estimated_duration = ref("");
const journey_id = ref("");
const latitude = ref("");
const longitude = ref("");
const link = ref("");
const mapbox_id = ref("");
const name = ref("");
const opening_hours = ref("");
const phone = ref("");
const updated_at = ref("");
const tolgee = useTolgee(["language"]);

interface EventObject {
    event: Event;
    draggedEl: HTMLElement;
}

interface Event {
    _def: {
        extendedProps: {
            activity_id: string;
            defId: string;
        };
        publicId: string;
    };
    _instance: {
        range: {
            start: {
                toISOString: () => string;
            };
            end: {
                toISOString: () => string;
            };
        };
    };
    remove: () => void;
}

async function deleteActivity() {
    await client(`/api/journey/${props.id}/activity/${activityId.value}`, {
        method: "delete",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value(
                        "form.input.activity.delete.toast.success.heading",
                    ),
                    detail: t.value(
                        "form.input.activity.delete.toast.success.detail",
                    ),
                    life: 6000,
                });
                activities.value
                    .filter((activity) => activity.id === activityId.value)
                    .forEach((activity: Activity) => {
                        activity.calendar_activities.forEach(
                            (calendar_activity: CalendarActivity) => {
                                fullCalendar.value
                                    .getApi()
                                    .getEventById(calendar_activity.id)
                                    .remove();
                            },
                        );
                        activities.value.splice(
                            activities.value.indexOf(activity),
                            1,
                        );
                        store.setActivities(activities.value);
                    });
            }
        },
        async onRequestError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
    });
}

async function removeFromCalendar() {
    const calApi = fullCalendar.value.getApi();
    await client(
        `/api/journey/${props.id}/activity/${activityId.value}/calendarActivity/${calendarId.value}`,
        {
            method: "delete",
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value("calendar.remove.success.heading"),
                        detail: t.value("calendar.remove.success.detail"),
                        life: 6000,
                    });
                    calApi.getEventById(calendarId.value).remove();
                    activities.value
                        .filter((activity) => activity.id === activityId.value)
                        .forEach((activity: Activity) => {
                            activity.calendar_activities
                                .filter(
                                    (calendar_activity) =>
                                        calendar_activity.id ===
                                        calendarId.value,
                                )
                                .forEach(
                                    (calendar_activity: CalendarActivity) => {
                                        activities.value[
                                            activities.value.indexOf(activity)
                                        ].calendar_activities.splice(
                                            activity.calendar_activities.indexOf(
                                                calendar_activity,
                                            ),
                                            1,
                                        );
                                        store.setActivities(activities.value);
                                    },
                                );
                        });
                }
            },
            async onRequestError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
            async onResponseError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
        },
    );
}

let start = undefined;
if (props.duringJourney) {
    start = startOfWeek(new Date(), { weekStartsOn: 1 });
} else if (props.journeyEnded) {
    start = startOfWeek(new Date(props.journeyEnddate), { weekStartsOn: 1 });
} else {
    start = startOfWeek(new Date(props.journeyStartdate), { weekStartsOn: 1 });
}
const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: window.innerWidth < 765 ? "dayGridThreeWeek" : "fullweek",
    locale: tolgee.value.getLanguage(),
    headerToolbar: {
        left: window.innerWidth < 765 ? "title" : "prev,next today",
        center:
            window.innerWidth < 765 ? "today showAllHours prev,next" : "title",
        right:
            window.innerWidth < 765
                ? ""
                : "dayGridMonth,timeGridWeek showAllHours",
    },
    customButtons: {
        showAllHours: {
            click: () => {
                if (
                    fullCalendar.value.getApi().getOption("slotMinTime") ===
                    "00:00:00"
                ) {
                    fullCalendar.value
                        .getApi()
                        .setOption("slotMinTime", "06:00:00");
                    document.getElementsByClassName(
                        "fc-showAllHours-button",
                    )[0].innerHTML = "";
                    document.getElementsByClassName(
                        "fc-showAllHours-button",
                    )[0].innerHTML = "6:00 - 0:00";
                } else {
                    fullCalendar.value
                        .getApi()
                        .setOption("slotMinTime", "00:00:00");
                    document.getElementsByClassName(
                        "fc-showAllHours-button",
                    )[0].innerHTML = "";
                    document.getElementsByClassName(
                        "fc-showAllHours-button",
                    )[0].innerHTML = "0:00 - 24:00";
                }
            },
        },
    },
    buttonText: {
        today: t.value("calendar.today"),
        month: t.value("calendar.month"),
        week: t.value("calendar.week"),
    },
    height: "auto",
    slotMinTime: "06:00:00",
    eventReceive: initializeDrop,
    eventDrop: initializeDrop,
    eventResize: editDrop,
    eventClick: showData,
    eventBackgroundColor: bg,
    eventBorderColor: border,
    eventTextColor: text,
    slotDuration: "00:30:00",
    allDaySlot: false,
    timeZone: "local",
    droppable: true,
    initialDate: start,
    firstDay: 1,
    editable: true,
    views: {
        fullweek: {
            type: "timeGrid",
            duration: { days: 7 },
        },
        dayGridThreeWeek: {
            type: "timeGrid",
            duration: { days: 3 },
        },
    },
}) as unknown as CalendarOptions;

watch(addedActivity, () => {
    const addedActivity = store.addedActivity as Activity;
    activityId.value = addedActivity.id;
    editCalendarActivity(addedActivity.name);
});

onMounted(() => {
    const calApi = fullCalendar.value.getApi();
    watch(
        store.activityData,
        () => {
            if (!alreadyAdded.value) {
                document.getElementsByClassName(
                    "fc-showAllHours-button",
                )[0].innerHTML = "6:00 - 0:00";
                const activityData = store.activityData as Activity[];
                activityData.forEach((activity: Activity) => {
                    if (activity.calendar_activities != null) {
                        activity.calendar_activities.forEach(
                            (calendar_activity: CalendarActivity) => {
                                const newEnd = add(
                                    new Date(calendar_activity.start),
                                    {
                                        hours: parseInt(
                                            activity.estimated_duration.split(
                                                ":",
                                            )[0],
                                        ),
                                        minutes: parseInt(
                                            activity.estimated_duration.split(
                                                ":",
                                            )[1],
                                        ),
                                    },
                                ).toISOString();
                                calendar_activity.end = newEnd;
                                calendar_activity.title = activity.name;
                                if (
                                    calendar_activity.start.split(" ")[1] <=
                                    "06:00:00"
                                ) {
                                    calApi.setOption("slotMinTime", "00:00:00");
                                    document.getElementsByClassName(
                                        "fc-showAllHours-button",
                                    )[0].innerHTML = "0:00 - 24:00";
                                }
                                calApi.addEvent(calendar_activity);
                            },
                        );
                    }
                });
                alreadyAdded.value = true;
            }
        },
        { immediate: true },
    );
});

/**
 * when activity is dragged into calendar or manually added this funciton will save it to the database
 * @param info -- the event object with data about the activity
 */
async function initializeDrop(info: EventObject) {
    const calApi = fullCalendar.value.getApi();
    if (info.event._def.extendedProps.defId === undefined) {
        editDrop(info);
        return;
    }

    const activityId = info.event._def.extendedProps.defId;
    const startTime = info.event._instance.range.start.toISOString();
    const endTime = info.event._instance.range.end.toISOString();
    const activity = {
        start: startTime.substring(0, startTime.length - 2),
        end: endTime.substring(0, endTime.length - 2),
    };

    await client(
        `/api/journey/${props.id}/activity/${activityId}/calendarActivity/`,
        {
            method: "POST",
            body: activity,
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value("calendar.add.toast.success.heading"),
                        detail: t.value("calendar.add.toast.success.detail"),
                        life: 6000,
                    });
                    activities.value
                        .filter(
                            (activity) =>
                                activity.id == response._data.activity_id,
                        )
                        .forEach((activity: Activity) => {
                            response._data.title = activity.name;
                            calApi.addEvent(response._data);
                        });
                    activities.value
                        .filter((activity) => activity.id === activityId)
                        .forEach((activity: Activity) => {
                            activities.value[
                                activities.value.indexOf(activity)
                            ].calendar_activities.push(response._data);
                        });
                    store.setActivities(activities.value);
                }
            },
            async onRequestError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
            async onResponseError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
        },
    );
    info.event.remove();
}

/**
 * when event is resized this function will update the activity in the database
 * @param info -- the event object with data about the activity
 */
async function editDrop(info: EventObject) {
    const activityId = info.event._def.extendedProps.activity_id;
    const id = ref(info.event._def.publicId);
    const startTime = info.event._instance.range.start.toISOString();
    const endTime = info.event._instance.range.end.toISOString();

    const activity = {
        start: startTime.substring(0, startTime.length - 2),
        end: endTime.substring(0, endTime.length - 2),
    };

    const duration = differenceInMinutes(
        new Date(endTime).getTime(),
        new Date(startTime).getTime(),
    );

    const hours = Math.floor(duration / 60);
    const minutes = duration % 60;
    const newDuration =
        hours.toString().padStart(2, "0") +
        ":" +
        minutes.toString().padStart(2, "0") +
        ":00";

    await client(
        `/api/journey/${props.id}/activity/${activityId}/calendarActivity/${id.value}`,
        {
            method: "PATCH",
            body: activity,
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value(
                            "form.input.activity.edit.toast.success.heading",
                        ),
                        detail: t.value(
                            "form.input.activity.edit.toast.success.detail",
                        ),
                        life: 6000,
                    });
                }
            },
            async onRequestError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
            async onResponseError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
        },
    );

    const activities = store.activityData as Activity[];
    let newActivity;
    activities
        .filter(
            (activity) =>
                activity.id === activityId &&
                activity.estimated_duration !== newDuration,
        )
        .forEach((activity: Activity) => {
            activities[activities.indexOf(activity)].estimated_duration =
                newDuration;
            newActivity = activities[activities.indexOf(activity)];
        });
    if (newActivity !== undefined) {
        await client(`/api/journey/${props.id}/activity/${activityId}`, {
            method: "PATCH",
            body: newActivity,
        });
    }
}

/**
 * set the data of the activity in the dialog
 * @param info -- the event object with data about the activity
 */
function showData(info: EventObject) {
    activityId.value = info.event._def.extendedProps.activity_id;
    calendarId.value = info.event._def.publicId;
    activities.value
        .filter((activity) => activity.id === activityId.value)
        .forEach((activity: Activity) => {
            if (props.currentUserRole === 1 || !isAuthenticated.value) {
                update.value = false;
                onlyShow.value = true;
                calendarClicked.value = true;
            } else {
                update.value = false;
                onlyShow.value = true;
            }
            address.value = activity.address;
            cost.value = activity.cost;
            created_at.value = activity.created_at;
            description.value = activity.description;
            email.value = activity.email;
            estimated_duration.value = activity.estimated_duration;
            journey_id.value = activity.journey_id;
            latitude.value = activity.latitude;
            longitude.value = activity.longitude;
            link.value = activity.link;
            mapbox_id.value = activity.mapbox_id;
            name.value = activity.name;
            opening_hours.value = activity.opening_hours;
            phone.value = activity.phone;
            updated_at.value = activity.updated_at;
            isActivityInfoVisible.value = true;
            calendarActivity.value = activities.value[
                activities.value.indexOf(activity)
            ].calendar_activities.filter(
                (calendar_activity: CalendarActivity) =>
                    calendar_activity.id === calendarId.value,
            )[0];
        });
}

/**
 * when activity was edited in the dialog this function will update the activity in the calendar
 * @param name -- the name of the activity
 */
async function editCalendarActivity(name: string) {
    const calApi = fullCalendar.value.getApi();
    activities.value
        .filter((activity) => activity.id === activityId.value)
        .forEach((activity: Activity) => {
            activity.calendar_activities.forEach(
                (calendar_activity: CalendarActivity) => {
                    if (calApi.getEventById(calendar_activity.id) !== null) {
                        calApi
                            .getEventById(calendar_activity.id)
                            .setProp("title", name);
                        const newEnd = add(new Date(calendar_activity.start), {
                            hours: parseInt(
                                activity.estimated_duration.split(":")[0],
                            ),
                            minutes: parseInt(
                                activity.estimated_duration.split(":")[1],
                            ),
                        }).toISOString();
                        calApi
                            .getEventById(calendar_activity.id)
                            .setEnd(newEnd);
                    } else {
                        calApi.addEvent(calendar_activity);
                        calApi
                            .getEventById(calendar_activity.id)
                            .setProp("title", name);
                    }
                },
            );
        });
}

/**
 * when activity was already in the calendar and then moved manually via the dialog this function will update the location of the activity in the calendar
 * @param start -- the start date of the activity
 * @param end -- the end date of the activity
 */
function moveActivity(start: Date, end: Date) {
    const calApi = fullCalendar.value.getApi();
    calApi.getEventById(calendarId.value).setStart(start);
    calApi.getEventById(calendarId.value).setEnd(end);
}
</script>
<template>
    <div class="flex justify-center md:justify-start">
        <div
            class="flex w-[90%] flex-col items-end sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
        >
            <div class="mt-5 w-full justify-start sm:mt-10">
                <div class="text-2xl font-semibold lg:mb-3">
                    <T key-name="journey.calendar" />
                </div>
            </div>
            <div
                class="z-0 mt-1 h-[25rem] overflow-x-hidden overflow-y-scroll pr-2 md:h-[35rem]"
            >
                <FullCalendar
                    ref="fullCalendar"
                    :options="calendarOptions"
                    class="w-full"
                />
            </div>
            <JourneyIdDialogsActivityDialog
                :id="id.toString()"
                :activity-id="activityId"
                :visible="isActivityInfoVisible"
                :only-show="onlyShow"
                :address="address"
                :cost="cost"
                :calendar-activity="calendarActivity"
                :created-at="created_at"
                :description="description"
                :email="email"
                :estimated-duration="estimated_duration"
                :journey-id="journey_id"
                :latitude="latitude"
                :longitude="longitude"
                :link="link"
                :mapbox-id="mapbox_id"
                :name="name"
                :opening_hours="opening_hours"
                :phone="phone"
                :updated-at="updated_at"
                :update="update"
                :calendar-clicked="calendarClicked"
                @close="isActivityInfoVisible = false"
                @delete-activity="deleteActivity"
                @remove-from-calendar="removeFromCalendar"
                @edit-calendar-activity="editCalendarActivity"
                @calendar-moved="moveActivity"
            />
        </div>
    </div>
</template>
<style>
@media screen and (max-width: 640px) {
    .fc .fc-toolbar {
        display: inline-grid;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .fc .fc-toolbar.fc-header-toolbar {
        margin-bottom: 1rem;
    }

    .fc .fc-button {
        height: 35px;
        justify-content: center;
        display: flex;
        align-items: center;
        flex-direction: column;
        flex-wrap: nowrap;
    }

    .fc .fc-button.fc-today-button {
        width: 60%;
    }

    .fc .fc-button.fc-showAllHours-button {
        margin-left: -30px;
    }

    .fc .fc-toolbar-chunk {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: 100%;
    }

    .fc .fc-toolbar-title {
        grid-column: span 3 / span 3;
        margin-left: 0px;
        font-size: 1rem !important;
    }

    .fc .fc-toolbar-chunk:has(.fc-button-primary) {
        margin-top: 0.5rem;
    }
}

.fc-v-event .fc-event-title {
    max-height: 85%;
}

.fc .fc-v-event .fc-event-main-frame {
    margin-left: 0.4rem;
    margin-top: 0.3rem;
}

.fc-timegrid-event-harness-inset .fc-timegrid-event,
.fc-timegrid-event.fc-event-mirror,
.fc-timegrid-more-link {
    box-shadow: none;
}

.dark .fc-theme-standard td,
.fc-theme-standard th {
    border-color: #5d5d5d;
}

.light .fc-theme-standard td,
.fc-theme-standard th {
    border-color: #e0e0e0;
}

/* dark mode */

.dark .fc-day-today {
    background-color: rgba(155, 186, 197, 0.2) !important;
}

.dark .fc-scrollgrid {
    background-color: #2c2c2c !important;
}

.dark .fc .fc-prev-button,
.dark .fc .fc-dayGridMonth-button {
    border-width: 0.1rem 0 0.1rem 0.1rem !important;
}

.dark .fc .fc-next-button,
.dark .fc .fc-timeGridWeek-button {
    border-width: 0.1rem 0.1rem 0.1rem 0 !important;
}

.dark .fc .fc-button-group {
    background-color: #2c2c2c;
}

.dark .fc-scrollgrid,
.dark .fc-col-header {
    background-color: #454849;
}

.dark .fc-timegrid-slots td,
.dark .fc-timegrid-slotlanes td {
    border-color: #7b7b7b;
}

.dark .fc .fc-button-primary:hover,
.dark .fc .fc-prev-button:not(:disabled):hover,
.dark .fc .fc-next-button:not(:disabled):hover {
    background-color: #7a7052;
    color: #f8f8f8;
    border-color: #e3c454;
}

.dark .fc .fc-button-primary:hover,
.dark .fc .fc-prev-button:not(:disabled):hover,
.dark .fc .fc-next-button:not(:disabled):hover {
    background-color: #7a7052;
    color: #f8f8f8;
    border-color: #e3c454;
}

.dark .fc .fc-button {
    color: #f8f8f8;
    background-color: #454849;
    border-color: #e3c454;
    border-width: 0.1rem;
    border-radius: 0.5rem;
    box-shadow: none !important;
}

.dark
    .fc
    .fc-button-primary:not(:disabled).fc-button-active:not(.fc-prev-button):not(
        .fc-next-button
    ):not(.fc-showAllHours-button) {
    background-color: #7a7052;
    color: #f8f8f8;
    border-color: #e3c454;
    box-shadow: none;
}

.dark
    .fc
    .fc-button-primary:not(:disabled):focus:not(.fc-prev-button):not(
        .fc-next-button
    ):not(.fc-showAllHours-button) {
    box-shadow: none;
    outline: none;
    outline-style: none;
    background-color: #7a7052;
    color: #f8f8f8;
    border-color: #e3c454;
}

.dark .fc .fc-prev-button:not(:disabled):focus,
.dark .fc .fc-next-button:not(:disabled):focus {
    box-shadow: none;
    outline: none;
    outline-style: none;
    color: #f8f8f8;
    border-color: #e3c454;
}

.dark
    .fc
    .fc-button-primary:not(:disabled).fc-button-active:focus:not(
        .fc-prev-button
    ):not(.fc-next-button) {
    box-shadow: none;
    background-color: #7a7052;
    color: #f8f8f8;
    border-color: #e3c454;
}

.dark .fc .fc-today-button:disabled {
    background-color: #7a7052;
    color: #f8f8f8;
    border-color: #e3c454;
    opacity: 1;
}

/* light mode */

.light .fc-scrollgrid,
.light .fc-col-header {
    background-color: #f8f8f8;
}

.light .fc .fc-prev-button,
.light .fc .fc-dayGridMonth-button {
    border-width: 0.1rem 0 0.1rem 0.1rem !important;
}

.light .fc .fc-next-button,
.light .fc .fc-timeGridWeek-button {
    border-width: 0.1rem 0.1rem 0.1rem 0 !important;
}

.light .fc .fc-button-group {
    background-color: #f8f8f8;
}

.light .fc-day-today {
    background-color: rgba(219, 236, 242, 0.3) !important;
}

.light .fc-timegrid-slots td,
.light .fc-timegrid-slotlanes td {
    border-color: #dcdcdc;
}

.light .fc .fc-button-primary:hover,
.light .fc .fc-prev-button:not(:disabled):hover,
.light .fc .fc-next-button:not(:disabled):hover,
.light .fc .fc-showAllHours-button:not(:disabled):hover {
    background-color: #fee384;
    color: #333333;
    border-color: #f8d351;
}

.light .fc .fc-button {
    color: #333333;
    background-color: #f8f8f8;
    border-color: #f8d351;
    border-width: 0.1rem;
    border-radius: 0.5rem;
    box-shadow: none !important;
}

.light
    .fc
    .fc-button-primary:not(:disabled).fc-button-active:not(.fc-prev-button):not(
        .fc-next-button
    ):not(.fc-showAllHours-button) {
    background-color: #fee384;
    color: #333333;
    border-color: #f8d351;
    box-shadow: none;
}

.light
    .fc
    .fc-button-primary:not(:disabled):focus:not(.fc-prev-button):not(
        .fc-next-button
    ):not(.fc-showAllHours-button) {
    box-shadow: none;
    outline: none;
    outline-style: none;
    background-color: #fee384;
    color: #333333;
    border-color: #f8d351;
}

.light .fc .fc-prev-button:not(:disabled):focus,
.light .fc .fc-next-button:not(:disabled):focus {
    box-shadow: none;
    outline: none;
    outline-style: none;
    color: #333333;
    border-color: #f8d351;
}

.light
    .fc
    .fc-button-primary:not(:disabled).fc-button-active:focus:not(
        .fc-prev-button
    ):not(.fc-next-button) {
    box-shadow: none;
    background-color: #fee384;
    color: #333333;
    border-color: #f8d351;
}

.light .fc .fc-today-button:disabled {
    background-color: #fee384;
    color: #333333;
    border-color: #f8d351;
    opacity: 1;
}
</style>
