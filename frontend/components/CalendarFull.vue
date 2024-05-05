<script setup lang="ts">
import type { CalendarOptions } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import FullCalendar from "@fullcalendar/vue3";
import { useTolgee, useTranslate } from "@tolgee/vue";

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

const { data: currUser } = await useAsyncData("userRole", () =>
    client(`/api/journey/${props.id}/user/me`),
);

async function deleteActivity() {
    await client(`/api/journey/${props.id}/activity/${activityId.value}`, {
        method: "delete",
        body: {},
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
            body: {},
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
                : "dayGridMonth,timeGridWeek,showAllHours",
    },
    customButtons: {
        showAllHours: {
            text: t.value("calendar.showAllHours"),
            click: () => {
                if (
                    fullCalendar.value.getApi().getOption("slotMinTime") ===
                    "00:00:00"
                ) {
                    fullCalendar.value
                        .getApi()
                        .setOption("slotMinTime", "06:00:00");
                } else {
                    fullCalendar.value
                        .getApi()
                        .setOption("slotMinTime", "00:00:00");
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
                        activity.calendar_activities.forEach(
                            (calendar_activity: CalendarActivity) => {
                                calendar_activity.title = activity.name;
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

const isVisible = ref(false);

async function initializeDrop(info: EventObject) {
    const calApi = fullCalendar.value.getApi();
    if (info.event._def.extendedProps.defId === undefined) {
        editDrop(info);
        return;
    }
    const activityId = ref(info.event._def.extendedProps.defId);
    const startTime = info.event._instance.range.start.toISOString();
    const endTime = info.event._instance.range.end.toISOString();
    const activity = {
        start: startTime.substring(0, startTime.length - 2),
        end: endTime.substring(0, endTime.length - 2),
    };

    await client(
        `/api/journey/${props.id}/activity/${activityId.value}/calendarActivity/`,
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
    const activityId = ref(info.event._def.extendedProps.activity_id);
    const id = ref(info.event._def.publicId);
    const startTime = info.event._instance.range.start.toISOString();
    const endTime = info.event._instance.range.end.toISOString();

    const activity = {
        start: startTime.substring(0, startTime.length - 2),
        end: endTime.substring(0, endTime.length - 2),
    };

    await client(
        `/api/journey/${props.id}/activity/${activityId.value}/calendarActivity/${id.value}`,
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
            if (currUser.value.role === 1) {
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
            <Dialog
                v-model:visible="isVisible"
                modal
                class="w-24"
                dragabble="false"
                :pt="{
                    root: { class: 'font-nunito' },
                    header: {
                        class: 'flex justify-end h-1 pb-2',
                    },
                }"
            >
                TestSchmest
            </Dialog>
            <div class="z-0 mt-20 h-[35rem] overflow-y-scroll">
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
</style>
