<script setup lang="ts">
import { Draggable } from "@fullcalendar/interaction";
import { useTranslate } from "@tolgee/vue";
import { format, parse } from "date-fns";
import type { MenuItemCommandEvent } from "primevue/menuitem";
import { useActivityStore } from "~/stores/activities";

const store = useActivityStore();
const { t } = useTranslate();
const menu = ref();

const toggle = (event: Event) => {
    menu.value[0].toggle(event);
};

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    journeyStart: { type: Date, required: true },
    journeyEnd: { type: Date, required: true },
    inTemplate: {
        type: Boolean,
        required: false,
        default: false,
    },
});

defineEmits(["openNewActivityDialog"]);

const activityId = ref("");
const containerElement = ref();
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

const isActivityInfoVisible = ref(false);
const { activityData } = storeToRefs(store);
const activities = computed(() => store.activityData as Activity[]);
const activityCount = computed(
    () =>
        activities.value.length -
        activities.value.filter(
            (activity) => activity.calendar_activities?.length > 0,
        ).length,
);

const client = useSanctumClient();
const toast = useToast();
const confirm = useConfirm();
let draggableInstance: Draggable | null = null;

onMounted(() => {
    watch(
        activityData,
        async () => {
            if (props.inTemplate === false) {
                await nextTick();
                if (containerElement.value) {
                    if (draggableInstance) {
                        draggableInstance.destroy();
                        draggableInstance = null;
                    }
                    try {
                        draggableInstance = new Draggable(
                            containerElement.value,
                            {
                                itemSelector: ".fc-event",
                            },
                        );
                    } catch (error) {
                        console.error("Failed to initialize Draggable:", error);
                    }
                }
            }
        },
        { immediate: true, deep: true },
    );
});

/**
 * set values for activity info dialog
 * @param id - activity id
 * @param showOnly - if dialog should just display info or allow editing
 */
function showInfo(id: string) {
    activities.value.forEach((activity: Activity) => {
        if (activity.id === id) {
            onlyShow.value = false;
            update.value = true;
            address.value = activity.address;
            cost.value = activity.cost;
            created_at.value = activity.created_at;
            description.value = activity.description;
            email.value = activity.email;
            estimated_duration.value = activity.estimated_duration;
            journey_id.value = activity.journey_id;
            latitude.value = activity.latitude?.toString();
            longitude.value = activity.longitude?.toString();
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

const confirmDelete = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        group: "journey",
        header: t.value("activity.delete.header"),
        message: t.value("activity.delete.confirm"),
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline",
        acceptClass:
            "text-mahagony-400 dark:text-mahagony-600 hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("journey.delete"),
        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("journey.delete"),
                detail: t.value("journey.delete.detail"),
                life: 3000,
            });
            deleteActivity(activityId.value);
        },
    });
};

/*
 * delete activity
 */
async function deleteActivity(activity_id: string) {
    const edit_type = {
        edit_type: "all",
    };
    if (!activity_id) {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("common.error.invalid_id"),
            life: 6000,
        });
        return;
    }

    const activityExists = activities.value.some(
        (activity) => activity.id === activity_id,
    );
    if (!activityExists) {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("common.error.not_found"),
            life: 6000,
        });
        return;
    }
    await client(`/api/journey/${props.id}/activity/${activity_id}`, {
        method: "delete",
        body: edit_type,
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
                    .filter((activity) => activity.id === activity_id)
                    .forEach(async (activity: Activity) => {
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

const itemsJourneyGuide = ref([
    {
        label: t.value("dashboard.options.header"),
        items: [
            {
                label: t.value("dashboard.options.edit"),
                icon: "pi pi-pencil",
                className: "text-dandelion-300",
                command: () => {
                    showInfo(activityId.value);
                },
            },
            {
                label: t.value("dashboard.options.delete"),
                icon: "pi pi-trash",
                command: ($event: MenuItemCommandEvent) => {
                    confirmDelete($event.originalEvent);
                },
            },
        ],
    },
]);
</script>

<template>
    <div
        class="flex w-full justify-center overflow-x-hidden md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
    >
        <div
            class="h-40 w-[90%] rounded-2xl border-[3px] border-dashed border-calypso-300 dark:border-calypso-600 dark:bg-background-dark max-lg:mt-5 sm:h-[13rem] sm:w-5/6 md:ml-[10%] md:h-[17rem] md:w-[calc(50%+16rem)] lg:ml-0 lg:w-full lg:rounded-3xl"
        >
            <div
                v-if="activityCount <= 0 && !inTemplate"
                class="flex h-full cursor-pointer items-center justify-center text-center font-nunito text-natural-500"
                @click="$emit('openNewActivityDialog')"
            >
                <T key-name="activityPool.placeholder" />
            </div>
            <ScrollPanel
                v-else
                class="h-[9.7rem] w-full sm:h-[12.7rem] md:h-[16.7rem]"
                :pt="{
                    bary: 'invisible hover:hidden',
                    wrapper: 'z-0',
                }"
            >
                <div
                    id="calendar-container"
                    ref="containerElement"
                    class="mx-2 my-2 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-5"
                >
                    <div
                        v-for="activity in activities"
                        :key="activity.id"
                        class="cursor-pointer empty:hidden"
                    >
                        <div
                            v-if="
                                activity.calendar_activities == undefined ||
                                activity.calendar_activities.length <= 0
                            "
                            id="draggable-el"
                            :key="activity.id"
                            class="fc-event relative col-span-1 mx-1 my-1 h-14 overflow-hidden overflow-ellipsis rounded-md border border-calypso-400 bg-light px-2 py-1 text-base font-normal dark:border-calypso-600 dark:bg-dark sm:h-16 sm:text-base lg:rounded-xl"
                            :data-event="
                                JSON.stringify({
                                    title: activity.name,
                                    duration: activity.estimated_duration,
                                    editable: true,
                                    defId: activity.id,
                                    timeZone: 'local',
                                })
                            "
                            @click="activityId = activity.id"
                        >
                            <div class="flex sm:pt-1">
                                <p
                                    v-tooltip.top="{
                                        value: activity.name,
                                        pt: { root: 'font-nunito' },
                                    }"
                                    class="w-[98%] overflow-hidden truncate overflow-ellipsis"
                                    @click="showInfo(activity.id)"
                                >
                                    {{ activity.name }}
                                </p>
                                <button
                                    v-if="!inTemplate"
                                    class="pi pi-ellipsis-v"
                                    aria-haspopup="true"
                                    aria-controls="overlay_menu"
                                    @click="toggle"
                                />
                            </div>
                            <div
                                class="flex items-center pb-4"
                                @click="showInfo(activity.id)"
                            >
                                <SvgClock class="mr-2 h-4 w-4" />
                                {{
                                    format(
                                        parse(
                                            activity.estimated_duration,
                                            "HH:mm:ss",
                                            new Date(),
                                        ),
                                        "H:mm",
                                    )
                                }}
                            </div>
                            <div class="absolute overflow-hidden">
                                <Menu
                                    id="overlay_menu"
                                    ref="menu"
                                    :model="itemsJourneyGuide"
                                    class="relative -ml-5 rounded-xl border-2 border-natural-200 bg-natural-50 dark:border-natural-900"
                                    :popup="true"
                                    :pt="{
                                        root: {
                                            class: 'relative font-nunito bg-natural-50 dark:bg-natural-800 overflow-hidden',
                                        },
                                        menu: {
                                            class: 'bg-natural-50 dark:bg-natural-800',
                                        },
                                        menuitem: {
                                            class: 'bg-natural-50 dark:bg-natural-800 hover:bg-dandelion-100 dark:hover:bg-pesto-600 rounded-md text-text dark:text-natural-50',
                                        },
                                        content: {
                                            class: 'bg-natural-50 dark:bg-natural-800 hover:bg-dandelion-100 dark:hover:bg-pesto-600 rounded-md text-text dark:text-natural-50',
                                        },
                                        submenuHeader: {
                                            class: 'text-natural-500 dark:text-natural-100 bg-natural-50 dark:bg-natural-800',
                                        },
                                        label: {
                                            class: 'text-text dark:text-natural-50',
                                        },
                                        icon: {
                                            class: 'text-text dark:text-natural-50',
                                        },
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollPanel>
        </div>
        <JourneyIdDialogsActivityDialog
            :id="id.toString()"
            :activity-id="activityId"
            :visible="isActivityInfoVisible"
            :only-show="onlyShow"
            :address="address"
            :cost="cost"
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
            :opening-hours="opening_hours"
            :phone="phone"
            :updated-at="updated_at"
            :update="update"
            :journey-start="props.journeyStart"
            :journey-end="props.journeyEnd"
            @close="isActivityInfoVisible = false"
            @delete-activity="deleteActivity"
        />
    </div>
</template>
