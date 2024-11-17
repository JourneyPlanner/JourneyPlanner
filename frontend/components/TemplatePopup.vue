<script setup lang="ts">
import { format, parse } from "date-fns";

const props = defineProps({
    template: {
        type: Object as PropType<Template>,
        required: true,
    },
    isTemplateDialogVisible: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const client = useSanctumClient();

const isVisible = ref(false);
const activityCount = ref(null);
const activities = ref();

watch(
    () => props.isTemplateDialogVisible,
    async (value) => {
        isVisible.value = value;
        if (value) {
            await client(`/api/template/${props.template.id}/activity`, {
                async onResponse({ response }) {
                    if (response.ok) {
                        activities.value = response._data.activities;
                        activityCount.value = response._data.count;
                    }
                },
            });
        }
    },
    { immediate: true },
);

function close() {
    activities.value = [];
    activityCount.value = null;
    emit("close");
}
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        dismissable-mask
        close-on-escape
        class="z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-6/12 md:rounded-xl"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10 px-0',
            },
            header: {
                class: 'flex pt-3 pb-2 font-nunito bg-background dark:bg-background-dark pl-2',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 h-full',
            },
            footer: { class: 'h-0' },
            closeButtonIcon: {
                class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10',
            },
        }"
        @hide="close"
    >
        <template #header>
            <div class="flex w-full flex-row items-center">
                <div class="flex flex-row items-center">
                    <span
                        class="mr-2 h-0.5 w-2 bg-calypso-400 xs:mr-2 xs:w-4 sm:mr-3.5 sm:w-8 md:mr-1.5 md:w-5"
                    />
                    <h1
                        v-tooltip.top="{
                            value: template.name,
                            pt: { root: 'font-nunito' },
                        }"
                        class="max-w-36 truncate text-nowrap text-2xl font-medium text-text dark:text-natural-50 xs:max-w-48 sm:max-w-72 md:max-w-80 lg:max-w-96 xl:max-w-[28rem] 2xl:max-w-[32rem]"
                    >
                        {{ template.name }}
                    </h1>
                </div>
                <span
                    class="ml-2 mr-2 h-0.5 w-full bg-calypso-400 xs:ml-2 sm:ml-3.5 md:ml-1.5"
                />
            </div>
        </template>
        <div
            id="template-content"
            class="mx-4 mt-2 h-full xs:mx-8 sm:mx-12 md:mx-8"
        >
            <div id="details" class="flex h-32 gap-x-4">
                <div id="facts" class="flex w-1/2 flex-col gap-y-3">
                    <div
                        id="destination"
                        v-tooltip.top="{
                            value: template.destination,
                            pt: { root: 'font-nunito' },
                        }"
                        class="flex flex-row items-center gap-x-1"
                    >
                        <i
                            class="pi pi-map-marker mr-2 text-xl text-calypso-600"
                        />
                        <h5 class="truncate text-xl">
                            {{ template.destination }}
                        </h5>
                    </div>
                    <div id="length" class="flex flex-row items-center gap-x-1">
                        <i
                            class="pi pi-calendar mr-2 text-xl text-calypso-600"
                        />
                        <h5 class="truncate text-xl">
                            {{ template.length }}
                            <T
                                :key-name="
                                    template.length === 1
                                        ? 'template.day'
                                        : 'template.days'
                                "
                            />
                        </h5>
                    </div>
                    <div
                        id="activity-count"
                        class="flex flex-row items-center gap-x-1"
                    >
                        <i class="pi pi-ticket mr-2 text-xl text-calypso-600" />

                        <span
                            class="flex flex-row items-center gap-x-1 truncate text-xl"
                        >
                            <Skeleton
                                v-if="activityCount === null"
                                width="1rem"
                                height="1.25rem"
                            />
                            <span v-else>
                                {{ activityCount }}
                            </span>
                            <T
                                :key-name="
                                    activityCount === 1
                                        ? 'template.activity'
                                        : 'template.activities'
                                "
                            />
                        </span>
                    </div>
                </div>
                <div
                    id="description"
                    class="w-1/2 overflow-y-scroll rounded-lg border border-natural-100 p-1 text-lg font-normal text-text dark:text-natural-50"
                >
                    <p v-if="template.description">
                        {{ template.description }}
                    </p>
                    <span v-else class="italic">
                        <T key-name="template.description.none" />
                    </span>
                </div>
            </div>
            <div id="activities" class="mt-2.5">
                <h3 class="text-lg text-text">
                    <T :key-name="'template.activity.pool'" />
                </h3>
                <div
                    class="h-40 w-full rounded-2xl border-2 border-dashed border-calypso-400 dark:border-calypso-600 dark:bg-background-dark max-lg:mt-5 md:h-[12rem] lg:rounded-3xl"
                >
                    <Skeleton
                        v-if="activityCount === null"
                        height="11.7rem"
                        class="rounded-2xl lg:rounded-3xl"
                    />
                    <ScrollPanel
                        v-else
                        class="h-[9.7rem] w-full sm:h-[12.7rem] md:h-[11.7rem]"
                        :pt="{
                            bary: 'invisible hover:hidden',
                            wrapper: 'z-0',
                        }"
                    >
                        <div
                            class="mx-2 my-2 grid grid-cols-2 gap-1 md:grid-cols-3 lg:grid-cols-4"
                        >
                            <div
                                v-for="activity in activities"
                                :key="activity.id"
                                class="empty:hidden"
                            >
                                <div
                                    :key="activity.id"
                                    class="relative col-span-1 mx-1 my-1 h-14 overflow-hidden overflow-ellipsis rounded-md border border-calypso-400 bg-light px-2 py-1 text-base font-normal dark:border-calypso-600 dark:bg-dark sm:h-16 sm:text-base lg:rounded-xl"
                                >
                                    <div class="flex sm:pt-1">
                                        <p
                                            v-tooltip.top="{
                                                value: activity.name,
                                                pt: { root: 'font-nunito' },
                                            }"
                                            class="w-[98%] overflow-hidden truncate"
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
                        </div>
                    </ScrollPanel>
                </div>
            </div>
            <div id="buttons" class="mt-2 flex justify-center">
                <NuxtLink
                    :to="'/template/' + template.id"
                    class="mt-auto flex h-9 w-40 flex-row items-center justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:cursor-pointer hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                >
                    <T key-name="template.preview" />
                </NuxtLink>
            </div>
        </div>
    </Dialog>
</template>
