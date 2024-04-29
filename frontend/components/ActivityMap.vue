<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const fullConfig = resolveConfig(tailwindConfig);
const { t } = useTranslate();

const journey = useJourneyStore();
const activitiesStore = useActivityStore();

const activities = ref(
    computed(() => activitiesStore.activityData as Activity[]),
);
const activitiesWithLocation = ref(
    computed(() =>
        activities.value.filter(
            (activity) => activity.latitude && activity.longitude,
        ),
    ),
);
const activitiesWithoutLocation = ref(
    computed(() =>
        activities.value.filter(
            (activity) => !activity.latitude || !activity.longitude,
        ),
    ),
);
console.log(activitiesWithLocation);

const colorAdded = fullConfig.theme.accentColor["border"] as string;
const colorNotAdded = fullConfig.theme.accentColor["background"] as string;
const lat = computed(() => journey.getLat());
const long = computed(() => journey.getLong());
const zoom = computed(() => ((long.value || lat) === null ? 1 : 5));

const isNotFoundActivitiesDialogVisible = ref(false);
</script>

<template>
    <div class="flex items-center justify-center md:justify-start">
        <div
            class="relative mt-5 flex w-[90%] items-center sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
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
                class="pi pi-exclamation-circle -mb-2.5 text-xl text-error hover:cursor-pointer"
                @click="isNotFoundActivitiesDialogVisible = true"
            />
        </div>
    </div>
    <div class="flex justify-center md:justify-start">
        <div
            class="relative mt-5 flex h-96 w-[90%] items-end sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
        >
            <MapboxMap
                :map-id="journey.getName()"
                style="position: absolute; top: 0; bottom: 0"
                class="rounded-xl"
                :options="{
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [long, lat],
                    zoom: zoom,
                }"
            >
                <MapboxDefaultMarker
                    v-for="activity in activitiesWithLocation"
                    :key="activity.id"
                    :marker-id="activity.id"
                    :lnglat="[activity.longitude, activity.latitude]"
                    :options="{
                        color:
                            activity.calendar_activities.length > 0
                                ? colorAdded
                                : colorNotAdded,
                    }"
                >
                    <MapboxDefaultPopup
                        :popup-id="activity.id"
                        :lnglat="[activity.longitude, activity.latitude]"
                        :options="{
                            closeOnClick: true,
                        }"
                    >
                        <div class="flex flex-col font-nunito">
                            <h1 class="font-bold">
                                {{ activity.name }}
                            </h1>
                            <p>{{ activity.mapbox_full_address }}</p>
                        </div>
                    </MapboxDefaultPopup>
                </MapboxDefaultMarker>
                <MapboxGeolocateControl />
                <MapboxFullscreenControl />
            </MapboxMap>
        </div>
        <Dialog
            v-model:visible="isNotFoundActivitiesDialogVisible"
            modal
            :header="t('journey.map.notfound.title')"
            :style="{ width: '30rem' }"
            class="bg-input dark:bg-input-dark"
            :pt="{
                root: {
                    class: 'font-nunito text-text bg-input dark:bg-input-dark',
                },
                header: {
                    class: 'h-15 bg-input dark:bg-input-dark text-text dark:text-input',
                },
                title: { class: 'text-xl mt-0.5' },
                content: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-input',
                },
            }"
        >
            <p>
                <T key-name="journey.map.notfound.description" />
            </p>
            <DataTable
                :value="activitiesWithoutLocation"
                striped-rows
                paginator
                :rows="8"
                :rows-per-page-options="[5, 10, 20, 50]"
                :pt="{
                    paginator: {
                        class: 'font-nunito bg-input dark:bg-input-dark',
                    },
                }"
                class="mt-3 bg-input dark:bg-input-dark"
            >
                <Column
                    field="name"
                    :header="t('journey.map.notfound.name')"
                    class="bg-input font-nunito text-text dark:bg-input-dark dark:text-input"
                />
                <Column
                    field="address"
                    :header="t('journey.map.notfound.address')"
                    class="bg-input font-nunito text-text dark:bg-input-dark dark:text-input"
                />
            </DataTable>
        </Dialog>
    </div>
</template>
