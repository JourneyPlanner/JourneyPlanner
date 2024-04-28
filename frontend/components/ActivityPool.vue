<script setup lang="ts">
import { Draggable } from "@fullcalendar/interaction";
import { format, parse } from "date-fns";
import { useActivityStore } from "~/stores/activities";

import ActivityDialog from "./ActivityDialog.vue";

const store = useActivityStore();
const menu = ref();
const toggle = (event: Event) => {
    menu.value.toggle(event);
};

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const containerElement = ref();
const onlyShow = ref(true);
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

interface Activity {
    address: string;
    mapbox_full_address: string;
    calendar_activities: [];
    id: string;
    cost: string;
    created_at: string;
    description: string;
    email: string;
    estimated_duration: string;
    journey_id: string;
    latitude: string;
    longitude: string;
    link: string;
    mapbox_id: string;
    name: string;
    opening_hours: string;
    phone: string;
    updated_at: string;
}
const isActivityInfoVisible = ref(false);
const activities = computed(() => store.activityData as Activity[]);
const activityCount = computed(() => activities.value.length);

onMounted(() => {
    new Draggable(containerElement.value, {
        itemSelector: ".fc-event",
    });
});
function showInfo(id: string) {
    activities.value.forEach((activity: Activity) => {
        if (activity.id === id) {
            onlyShow.value = true;
            address.value =
                activity.address == ""
                    ? activity.mapbox_full_address
                    : activity.address;
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
</script>

<template>
    <div
        class="flex w-full justify-center md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
    >
        <div
            class="h-40 w-[90%] rounded-2xl border-[3px] border-dashed border-border dark:bg-text max-lg:mt-5 sm:h-[13rem] sm:w-5/6 md:ml-[10%] md:h-[17rem] md:w-[calc(50%+16rem)] lg:ml-0 lg:w-full lg:rounded-3xl"
        >
            <ScrollPanel
                class="h-[9.7rem] w-full sm:h-[12.7rem] md:h-[16.7rem]"
                :pt="{
                    bary: 'invisible hover:hidden',
                }"
            >
                <div
                    id="calendar-container"
                    ref="containerElement"
                    class="mx-2 my-2 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-5"
                >
                    <div
                        v-for="activity in activities"
                        id="draggable-el"
                        :key="activity.id"
                        class="fc-event col-span-1 mx-1 my-1 h-14 overflow-hidden overflow-ellipsis rounded-md border border-border bg-input-grey px-2 py-1 text-base font-normal dark:bg-card-dark sm:h-16 sm:text-base lg:rounded-xl"
                        :data-event="
                            JSON.stringify({
                                title: activity.name,
                                duration: activity.estimated_duration,
                                editable: true,
                                defId: activity.id,
                                timeZone: 'local',
                            })
                        "
                        @click="showInfo(activity.id)"
                    >
                        <div class="flex sm:pt-1">
                            <p
                                v-tooltip.top="{
                                    value: activity.name,
                                    pt: { root: 'font-nunito' },
                                }"
                                class="w-[98%] overflow-hidden truncate overflow-ellipsis"
                            >
                                {{ activity.name }}
                            </p>
                            <button
                                class="pi pi-ellipsis-v"
                                aria-haspopup="true"
                                aria-controls="overlay_menu"
                                @click="toggle"
                            />
                        </div>
                        <div class="flex items-center pb-4">
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
                    </div>
                </div>
                <div
                    v-if="activityCount <= 0"
                    class="invisible col-span-full flex h-[92%] items-center justify-center text-input-placeholder md:visible"
                >
                    <T key-name="activityPool.placeholder" />
                </div>

                <div
                    v-else-if="activityCount <= 3"
                    class="invisible col-span-full flex items-center justify-center text-input-placeholder md:visible lg:pt-4"
                >
                    <T key-name="activityPool.placeholder" />
                </div>

                <div
                    v-else-if="activityCount <= 5"
                    class="invisible col-span-full flex items-center justify-center text-input-placeholder lg:visible lg:pt-4"
                >
                    <T key-name="activityPool.placeholder" />
                </div>
            </ScrollPanel>
        </div>
        <ActivityDialog
            :id="$props.id.toString()"
            :visible="isActivityInfoVisible"
            :only-show="onlyShow"
            :address="address"
            :cost="cost"
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
            @close="isActivityInfoVisible = false"
        />
    </div>
</template>
