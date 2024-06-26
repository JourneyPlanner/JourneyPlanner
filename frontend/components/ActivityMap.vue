<script setup lang="ts">
import {
    MapboxGeolocateControl,
    MapboxMap,
    MapboxMarker,
    MapboxNavigationControl,
} from "@studiometa/vue-mapbox-gl";
import { useTranslate } from "@tolgee/vue";
import "mapbox-gl/dist/mapbox-gl.css";

const journey = useJourneyStore();
const activitiesStore = useActivityStore();

const config = useRuntimeConfig();
const { t } = useTranslate();

const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");

let colorAdded = "#50A1C0";
let colorNotAdded = "#9BBAC5";

if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    colorAdded = "#50A1C0";
    colorNotAdded = "#9BBAC5";
} else {
    colorAdded = "#3485A6";
    colorNotAdded = "#6A95A6";
}

const activities = ref();
const activitiesWithLocation = ref();
const activitiesWithoutLocation = ref();
const isNotFoundActivitiesDialogVisible = ref(false);

watch(
    activitiesStore.activityData,
    () => {
        activities.value = activitiesStore.activityData;

        activitiesWithLocation.value = activities.value.filter(
            (activity: Activity) => activity.latitude && activity.longitude,
        );

        activitiesWithoutLocation.value = activities.value.filter(
            (activity: Activity) => !activity.latitude || !activity.longitude,
        );
    },
    { immediate: true },
);

function markerColor(activity: Activity) {
    return activity.calendar_activities?.length > 0
        ? colorAdded
        : colorNotAdded;
}

const lat = Number(journey.getLat());
const long = Number(journey.getLong());
const zoom = computed(() => ((long || lat) === null ? 1 : 8));
const style = computed(() =>
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
        ? "mapbox://styles/mathematti/clw4z6v0s028p01o0askbhfh9"
        : "mapbox://styles/mathematti/clw4znxzh02mh01qz5hgk3qb6",
);
</script>

<template>
    <div class="flex items-center justify-center md:justify-start">
        <div
            class="relative -mb-1 mt-5 flex w-[90%] items-center sm:-mb-0 sm:mt-10 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
        >
            <div class="-mb-2.5 text-2xl font-semibold">
                <T key-name="journey.map" />
            </div>
            <span
                v-if="activitiesWithoutLocation.length > 0"
                v-tooltip.top="{
                    value: t('journey.map.notfound.tooltip'),
                    pt: { root: 'font-nunito' },
                }"
                class="pi pi-exclamation-circle -mb-2.5 ml-auto text-xl text-cancel-border hover:cursor-pointer"
                @click="isNotFoundActivitiesDialogVisible = true"
            />
        </div>
    </div>
    <div class="flex justify-center md:justify-start">
        <div
            class="relative mt-5 flex h-44 w-[90%] items-end sm:h-[13rem] sm:w-5/6 md:ml-[10%] md:h-[17rem] md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:h-96 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
        >
            <div>
                <MapboxMap
                    style="position: absolute; top: 0; bottom: 0"
                    class="h-full w-full rounded-xl"
                    :access-token="config.public.NUXT_MAPBOX_API_KEY"
                    :zoom="zoom"
                    :map-style="style"
                    :center="[long, lat]"
                >
                    <MapboxMarker
                        v-for="activity in activitiesWithLocation"
                        :key="activity.id"
                        :lng-lat="[activity.longitude, activity.latitude]"
                        :color="markerColor(activity)"
                        popup
                    >
                        <template #popup>
                            <div
                                class="flex flex-col font-nunito text-text dark:text-natural-50"
                            >
                                <h1 class="font-bold">
                                    {{ activity.name }}
                                </h1>
                                <p>{{ activity.mapbox_full_address }}</p>
                            </div>
                        </template>
                    </MapboxMarker>
                    <MapboxGeolocateControl />
                    <MapboxNavigationControl position="top-left" />
                </MapboxMap>
            </div>
            <Dialog
                v-model:visible="isNotFoundActivitiesDialogVisible"
                modal
                :header="t('journey.map.notfound.title')"
                :draggable="false"
                :style="{ width: '35rem' }"
                class="bg-input dark:bg-input-dark"
                :pt="{
                    root: {
                        class: 'font-nunito text-text bg-input dark:bg-input-dark',
                    },
                    header: {
                        class: 'pb-2 h-15 bg-input dark:bg-input-dark text-text dark:text-input',
                    },
                    title: { class: 'text-xl mt-0.5' },
                    content: {
                        class: 'bg-input dark:bg-input-dark text-text dark:text-input',
                    },
                    closeButtonIcon: {
                        class: 'text-input-placeholder hover:text-text dark:text-input-placeholder dark:hover:text-input h-5 w-5',
                    },
                }"
            >
                <p
                    class="border-b-2 border-border-gray pb-3 dark:border-input-placeholder"
                >
                    <T key-name="journey.map.notfound.description" />
                </p>

                <ScrollPanel
                    class="relative h-[15rem]"
                    :pt="{
                        barY: 'w-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                        barX: 'h-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                    }"
                >
                    <div
                        class="mx-4 font-nunito text-text dark:text-input lg:mx-8"
                    >
                        <table
                            class="w-full table-fixed text-left text-sm md:text-base"
                        >
                            <thead
                                class="border-b border-text text-xs uppercase dark:border-input"
                            >
                                <tr>
                                    <th scope="col" class="w-10 py-3 pr-5">
                                        <T
                                            key-name="journey.map.notfound.name"
                                        />
                                    </th>
                                    <th scope="col" class="w-20 py-3">
                                        <T
                                            key-name="journey.map.notfound.address"
                                        />
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="activity in activitiesWithoutLocation"
                                    :key="activity.id"
                                >
                                    <th
                                        scope="row"
                                        class="w-10 truncate py-2 pr-5"
                                    >
                                        {{ activity.name }}
                                    </th>
                                    <td class="w-20 truncate py-2">
                                        {{
                                            activity.address ||
                                            "-----------------"
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </ScrollPanel>
            </Dialog>
        </div>
    </div>
</template>

<style>
.mapboxgl-ctrl-top-left,
.mapboxgl-ctrl-top-right {
    @apply z-0;
}

.mapboxgl-popup-content {
    @apply bg-background dark:bg-background-dark;
}

.mapboxgl-popup-anchor-top .mapboxgl-popup-tip,
.mapboxgl-popup-anchor-top-left .mapboxgl-popup-tip,
.mapboxgl-popup-anchor-top-right .mapboxgl-popup-tip {
    @apply border-b-background dark:border-b-background-dark;
}

.mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip,
.mapboxgl-popup-anchor-bottom-left .mapboxgl-popup-tip,
.mapboxgl-popup-anchor-bottom-right .mapboxgl-popup-tip {
    @apply border-t-background dark:border-t-background-dark;
}

.mapboxgl-popup-anchor-left .mapboxgl-popup-tip {
    @apply border-r-background dark:border-r-background-dark;
}

.mapboxgl-popup-anchor-right .mapboxgl-popup-tip {
    @apply border-l-background dark:border-l-background-dark;
}

.mapboxgl-popup-close-button {
    @apply mr-1.5 text-xl;
}
</style>
