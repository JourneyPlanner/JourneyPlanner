<script setup lang="ts">
import type { CalendarOptions } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import FullCalendar from "@fullcalendar/vue3";
import { useTolgee, useTranslate } from "@tolgee/vue";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const fullConfig = resolveConfig(tailwindConfig);
const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");

let text = "";
let bg = "";
const border = fullConfig.theme.accentColor["border"] as string;
if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    text = fullConfig.theme.accentColor["input"] as string;
    bg = fullConfig.theme.accentColor["card-dark"] as string;
} else {
    text = fullConfig.theme.accentColor["text"] as string;
    bg = fullConfig.theme.accentColor["card"] as string;
}

const fullCalendar = ref();
const store = useActivityStore();
const isActivityInfoVisible = ref(false);
const activities = computed(() => store.activityData as Activity[]);
const client = useSanctumClient();
const alreadyAdded = ref(false);
const { t } = useTranslate();
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
});
const activityId = ref("");
const calendarId = ref("");
const onlyShow = ref(true);
const update = ref(false);
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

interface CalendarActivity {
    id: string;
    title: string;
    start: string;
    end: string;
    allDay: boolean;
    activity_id: string;
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
                activities.value.forEach((activity: Activity) => {
                    if (activity.id === activityId.value) {
                        activities.value.splice(
                            activities.value.indexOf(activity),
                            1,
                        ),
                            store.setActivities(activities.value);
                    }
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
                    )[0].innerHTML = "0:00 - 0:00";
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

onMounted(() => {
    const calApi = fullCalendar.value.getApi();
    watch(
        store.activityData,
        () => {
            if (!alreadyAdded.value) {
                const activityData = store.activityData as Activity[];
                activityData.forEach((activity: Activity) => {
                    if (activity.calendar_activities != null) {
                        document.getElementsByClassName(
                            "fc-showAllHours-button",
                        )[0].innerHTML = "6:00 - 0:00";
                        activity.calendar_activities.forEach(
                            (calendar_activity: CalendarActivity) => {
                                calendar_activity.title = activity.name;
                                if (
                                    calendar_activity.start.split(" ")[1] <=
                                    "06:00:00"
                                ) {
                                    calApi.setOption("slotMinTime", "00:00:00");
                                    document.getElementsByClassName(
                                        "fc-showAllHours-button",
                                    )[0].innerHTML = "0:00 - 0:00";
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
                    const activities = store.activityData as Activity[];
                    activities.forEach((activity: Activity) => {
                        if (activity.id == response._data.activity_id) {
                            response._data.title = activity.name;
                            calApi.addEvent(response._data);
                        }
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
    info.event.remove();
}

async function editDrop(info: EventObject) {
    const activityId = info.event._def.extendedProps.activity_id;
    const id = ref(info.event._def.publicId);
    const startTime = info.event._instance.range.start.toISOString();
    const endTime = info.event._instance.range.end.toISOString();

    const activity = {
        start: startTime.substring(0, startTime.length - 2),
        end: endTime.substring(0, endTime.length - 2),
    };

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
}

function showData(info: EventObject) {
    activityId.value = info.event._def.extendedProps.activity_id;
    calendarId.value = info.event._def.publicId;
    activities.value.forEach((activity: Activity) => {
        if (activity.id === activityId.value) {
            if (props.currentUserRole === 1) {
                update.value = true;
                onlyShow.value = false;
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
        }
    });
}

function editCalendarActivity(name: string) {
    const calApi = fullCalendar.value.getApi();
    activities.value.forEach((activity: Activity) => {
        if (activity.id === activityId.value) {
            activity.calendar_activities.forEach(
                (calendar_activity: CalendarActivity) => {
                    calApi
                        .getEventById(calendar_activity.id)
                        .setProp("title", name);
                },
            );
        }
    });
}
</script>
<template>
    <div class="flex justify-center md:justify-start">
        <div
            class="flex w-[90%] flex-col items-end sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
        >
            <div class="z-0 mt-20 h-[35rem] overflow-y-scroll pr-2">
                <FullCalendar
                    ref="fullCalendar"
                    :options="calendarOptions"
                    class="w-full"
                />
            </div>
            <ActivityDialog
                :id="id.toString()"
                :activity-id="activityId"
                :visible="isActivityInfoVisible"
                :only-show="onlyShow"
                :address="address"
                :cost="cost"
                :calendar-activity="true"
                :created-at="created_at"
                :description="description"
                :email="email"
                :estimated_duration="estimated_duration"
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
                @close="isActivityInfoVisible = false"
                @delete-activity="deleteActivity"
                @remove-from-calendar="removeFromCalendar"
                @edit-calendar-activity="editCalendarActivity"
            />
        </div>
    </div>
</template>
<style>
@media screen and (max-width: 640px) {
    .fc .fc-toolbar {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
}

.light .fc-scrollgrid,
.light .fc-col-header {
    background-color: #f8f8f8;
}

.dark .fc-scrollgrid,
.dark .fc-col-header {
    background-color: #454849;
}

.dark .fc-timegrid-slots td,
.dark .fc-timegrid-slotlanes td {
    border-color: #7b7b7b;
}

.light .fc-timegrid-slots td,
.light .fc-timegrid-slotlanes td {
    border-color: #dcdcdc;
}

/* dark mode */

.dark .fc .fc-prev-button,
.dark .fc .fc-dayGridMonth-button {
    border-width: 0.2rem 0 0.2rem 0.2rem !important;
}

.dark .fc .fc-next-button,
.dark .fc .fc-timeGridWeek-button {
    border-width: 0.2rem 0.2rem 0.2rem 0 !important;
}

.dark .fc .fc-button-group {
    @apply bg-background-dark;
    /*background-color: #2c2c2c;*/
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
    border-width: 0.2rem;
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

.light .fc .fc-prev-button,
.light .fc .fc-dayGridMonth-button {
    border-width: 0.2rem 0 0.2rem 0.2rem !important;
}

.light .fc .fc-next-button,
.light .fc .fc-timeGridWeek-button {
    border-width: 0.2rem 0.2rem 0.2rem 0 !important;
}

.light .fc .fc-button-group {
    background-color: #f8f8f8;
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
    border-width: 0.2rem;
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
