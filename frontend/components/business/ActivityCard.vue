<script setup lang="ts">
import { format, parse } from "date-fns";

const props = defineProps({
    activity: { type: Object as PropType<Activity>, required: true },
});

const emit = defineEmits(["open-activity-dialog"]);

function showInfo() {
    emit("open-activity-dialog", props.activity);
}
</script>

<template>
    <div class="cursor-pointer" @click="showInfo()">
        <div
            class="fc-event relative col-span-1 mx-1 my-1 h-14 overflow-hidden overflow-ellipsis rounded-md border border-calypso-400 bg-light px-2 py-1 text-base font-normal dark:border-calypso-600 dark:bg-dark sm:h-16 sm:text-base lg:rounded-xl"
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
</template>
