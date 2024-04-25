<script setup lang="ts">
import { Draggable } from "@fullcalendar/interaction";
import { format, parse } from "date-fns";
import { useActivityStore } from "~/stores/activities";

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

console.log(props.id);
const containerElement = ref();
const client = useSanctumClient();
const { data: activityData, error: activityError } = await useAsyncData(
    "activity",
    () => client(`/api/journey/${props.id}/activity`),
);
const activityCount = activityData.value.length;
console.log(activityCount);

console.log(activityData);
if (activityError !== null) {
    console.error(activityError);
} else {
    store.setActivities(activityData);
    console.log(activityData);
}

onMounted(() => {
    new Draggable(containerElement.value, {
        itemSelector: ".fc-event",
    });
});
</script>

<template>
    <div
        class="flex w-full justify-center md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
    >
        <div
            class="h-52 w-[90%] rounded-2xl border-[3px] border-dashed border-border bg-countdown-bg dark:bg-surface-dark max-lg:mt-5 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] lg:ml-0 lg:w-full lg:rounded-3xl"
        >
            <!-- :pt="{
                    bary: ' my-4 mr-8  hover:bg-border surface-300 bg-input-placeholder opacity-100 mr-10',
                }" -->
            <ScrollPanel class="h-[12rem] w-full">
                <div
                    id="calendar-container"
                    ref="containerElement"
                    class="my-2 ml-2 mr-2 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5"
                >
                    <div
                        v-for="activity in activityData"
                        id="draggable-el"
                        :key="activity.id"
                        class="fc-event col-span-1 mx-2 my-2 h-16 overflow-hidden overflow-ellipsis rounded-md border border-border px-1 py-1"
                        :data-event="
                            JSON.stringify({
                                title: activity.name,
                                duration: activity.estimated_duration,
                                editable: true,
                                defId: activity.id,
                                timeZone: 'local',
                            })
                        "
                    >
                        <div class="flex pt-1">
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
                        <div class="flex items-center">
                            <SvgClock class="mr-2 h-4 w-4" />
                            {{
                                format(
                                    parse(
                                        activity.estimated_duration,
                                        "HH:mm:ss",
                                        new Date(),
                                    ),
                                    "k:mm",
                                )
                            }}
                        </div>
                    </div>
                </div>
                <div
                    v-if="activityCount <= 5"
                    class="col-span-full flex items-center justify-center"
                >
                    <T key-name="activityPool.placeholder" />
                </div>
            </ScrollPanel>
        </div>
    </div>
</template>
