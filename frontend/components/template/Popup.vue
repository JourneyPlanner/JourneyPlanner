<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
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
    namePrefill: {
        type: String,
        default: "",
    },
    datePrefill: {
        type: Array as PropType<string[]>,
        default: null,
    },
});

const emit = defineEmits(["close", "opened-preview"]);

const client = useSanctumClient();
const { t } = useTranslate();
const toast = useToast();

const isVisible = ref(false);
const activityCount = ref(null);
const activities = ref();
const numRating = ref(0);
const rating = ref(3);
const avgRating = ref(1);
const isRatingVisible = ref(false);
const user = useSanctumUser<User>();
console.log(user);

const { data } = await useAsyncData("userRating", () =>
    client(`/api/template/${props.template.id}/rate`),
);
console.log(data);

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
            numRating.value = props.template.total_ratings;
            avgRating.value = props.template.average_rating;
        }
    },
    { immediate: true },
);

console.log(props.template);
const close = (): void => {
    activities.value = [];
    activityCount.value = null;
    emit("close");
};

async function changeRating() {
    const userRating = {
        rating: rating.value,
    };
    await client(`/api/template/${props.template.id}/rate`, {
        method: "POST",
        body: userRating,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value(
                        "form.template.create.toast.success.heading",
                    ),
                    detail: t.value(
                        "form.template.create.toast.success.detail",
                    ),
                    life: 3000,
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
    });
}

function removeRating() {
    rating.value = 0;
    changeRating();
}
</script>

<template>
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            block-scroll
            :auto-z-index="true"
            :draggable="false"
            dismissable-mask
            close-on-escape
            class="collapse z-50 w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:visible sm:flex sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-6/12 xl:w-6/12"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-50 px-0',
                },
                header: {
                    class: 'flex pt-3 pb-2 font-nunito bg-background dark:bg-background-dark pl-2',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 h-full',
                },
                footer: { class: 'h-0' },
                closeButtonIcon: {
                    class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10',
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
                            class="max-w-36 truncate text-nowrap text-2xl font-medium text-text dark:text-natural-50 xs:max-w-48 sm:max-w-72 md:max-w-80 lg:max-w-96 xl:max-w-[20rem] 2xl:max-w-[28rem]"
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
                class="mx-4 -mb-2 mt-2 h-full xs:mx-8 sm:mx-12 md:mx-8"
            >
                <div id="details" class="flex h-40 gap-x-4">
                    <div
                        id="facts"
                        class="mb-4 flex w-1/2 flex-col gap-y-3 text-text dark:text-natural-50"
                    >
                        <div
                            id="destination"
                            v-tooltip.top="{
                                value: template.destination,
                                pt: { root: 'font-nunito' },
                            }"
                            class="flex flex-row items-center gap-x-1"
                        >
                            <i
                                class="pi pi-map-marker mr-2 text-xl text-calypso-600 dark:text-calypso-400"
                            />
                            <h5 class="truncate text-xl">
                                {{ template.destination }}
                            </h5>
                        </div>
                        <div
                            id="length"
                            class="flex flex-row items-center gap-x-1"
                        >
                            <i
                                class="pi pi-calendar mr-2 text-xl text-calypso-600 dark:text-calypso-400"
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
                            <i
                                class="pi pi-ticket mr-2 text-xl text-calypso-600 dark:text-calypso-400"
                            />

                            <span
                                class="flex flex-row items-center gap-x-1 truncate text-xl"
                            >
                                <Skeleton
                                    v-if="activityCount === null"
                                    width="1rem"
                                    height="1.25rem"
                                    class="dark:bg-natural-600"
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
                        <div
                            id="rating"
                            class="flex flex-row items-center gap-x-1"
                        >
                            <i
                                class="pi pi-star mr-2 text-xl text-calypso-600 dark:text-calypso-400"
                            />

                            <span
                                class="flex flex-row items-center gap-x-1 truncate text-xl"
                            >
                                <Skeleton
                                    v-if="activityCount === null"
                                    width="1rem"
                                    height="1.25rem"
                                    class="dark:bg-natural-600"
                                />
                                <span v-else>
                                    {{ avgRating }}
                                </span>
                                <T
                                    :key-name="
                                        avgRating === 1
                                            ? 'template.rating.star'
                                            : 'template.rating.stars'
                                    "
                                />
                                ({{ numRating }})
                                <button
                                    @click="isRatingVisible = !isRatingVisible"
                                >
                                    <i
                                        class="pi mr-2 text-xl text-text"
                                        :class="
                                            isRatingVisible
                                                ? 'pi-angle-up'
                                                : 'pi-angle-down'
                                        "
                                    />
                                </button>
                            </span>
                        </div>
                        <div
                            class="-mt-2 ml-1 flex flex-row items-center gap-x-1"
                        >
                            <Rating
                                v-if="isRatingVisible"
                                v-model="rating"
                                :pt="{
                                    cancelItem: {
                                        class: 'hidden',
                                    },
                                }"
                                @change="changeRating"
                            >
                                <template #onicon>
                                    <i
                                        class="pi pi-star-fill -ml-1 mr-2 text-xl text-calypso-600 dark:text-calypso-400"
                                    />
                                </template>
                                <template #officon>
                                    <i
                                        class="pi pi-star -ml-1 mr-2 text-xl text-calypso-600 dark:text-calypso-400"
                                    />
                                </template>
                            </Rating>
                            <i
                                v-if="isRatingVisible"
                                class="pi pi-times -ml-1 cursor-pointer text-xl text-natural-500 hover:text-natural-900 dark:text-natural-400 dark:hover:text-natural-100"
                                @click="removeRating"
                            />
                        </div>
                    </div>
                    <div
                        id="description"
                        class="mb-5 w-1/2 overflow-y-scroll rounded-lg border border-natural-100 p-1 text-lg font-normal text-text dark:border-natural-800 dark:bg-natural-900 dark:text-natural-50"
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
                    <h3 class="text-lg text-text dark:text-natural-50">
                        <T :key-name="'template.activity.pool'" />
                    </h3>
                    <div
                        class="h-[12rem] w-full rounded-2xl border-2 border-dashed border-calypso-400 dark:border-calypso-600 dark:bg-background-dark lg:rounded-3xl"
                    >
                        <Skeleton
                            v-if="activityCount === null"
                            height="11.7rem"
                            class="rounded-2xl dark:bg-natural-600 lg:rounded-3xl"
                        />
                        <ScrollPanel
                            v-else
                            class="h-[11.7rem] w-full"
                            :pt="{
                                bary: 'invisible hover:hidden',
                                wrapper: 'z-0',
                            }"
                        >
                            <div
                                class="mx-2 my-2 grid grid-cols-2 gap-1 text-text dark:text-natural-50 md:grid-cols-3 lg:grid-cols-4"
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
                <div
                    id="buttons"
                    class="mt-4 flex w-full justify-center gap-x-5"
                >
                    <NuxtLink
                        :to="'/template/' + template.id"
                        class="mt-auto flex h-9 w-44 flex-row items-center justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:cursor-pointer hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="$emit('opened-preview')"
                    >
                        <T key-name="template.preview" />
                    </NuxtLink>
                    <NuxtLink
                        :to="{
                            path: '/journey/new/template/' + template.id,
                            query: {
                                name: namePrefill,
                                date: datePrefill,
                            },
                        }"
                        class="mt-auto flex h-9 w-44 flex-row items-center justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 px-2 text-center text-text hover:cursor-pointer hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    >
                        <T key-name="template.use" />
                    </NuxtLink>
                </div>
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            :show-close-icon="false"
            :block-scroll="true"
            position="bottom"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-[85%] flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:hidden ',
                },
                header: {
                    class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50 rounded-3xl',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 -ml-2 sm:pr-12 h-full',
                },
                footer: { class: 'h-0' },
                closeButton: {
                    class: 'justify-start w-full h-full items-center collapse',
                },
                mask: {
                    class: 'sm:collapse bg-natural-50',
                },
            }"
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-down text-xl" />
                </button>
                <div class="w-full">
                    <h1
                        class="mr-3 truncate text-nowrap text-xl font-medium text-text dark:text-natural-50"
                    >
                        {{ template.name }}
                    </h1>
                </div>
            </template>
            <div class="flex h-full flex-col gap-y-5 pl-6 pr-2">
                <div id="details" class="flex h-32 w-full flex-col gap-x-4">
                    <div
                        id="facts"
                        class="flex flex-col gap-y-3 text-text dark:text-natural-50"
                    >
                        <div
                            id="destination"
                            class="flex flex-row items-center gap-x-1"
                        >
                            <i
                                class="pi pi-map-marker mr-2 text-base text-calypso-600 dark:text-calypso-400"
                            />
                            <h5 class="truncate text-base">
                                {{ template.destination }}
                            </h5>
                        </div>
                        <div
                            id="length"
                            class="flex flex-row items-center gap-x-1"
                        >
                            <i
                                class="pi pi-calendar mr-2 text-base text-calypso-600 dark:text-calypso-400"
                            />
                            <h5 class="truncate text-base">
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
                            <i
                                class="pi pi-ticket mr-2 text-base text-calypso-600 dark:text-calypso-400"
                            />
                            <span
                                class="flex flex-row items-center gap-x-1 truncate text-base"
                            >
                                <Skeleton
                                    v-if="activityCount === null"
                                    width="1rem"
                                    height="1.25rem"
                                    class="dark:bg-natural-600"
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
                        class="mb-2 mt-4 max-h-20 min-h-20 w-full overflow-y-scroll rounded-lg border border-natural-100 p-1 text-base font-normal text-text dark:border-natural-800 dark:bg-natural-900 dark:text-natural-50"
                    >
                        <p v-if="template.description" class="h-full">
                            {{ template.description }}
                        </p>
                        <span v-else class="italic">
                            <T key-name="template.description.none" />
                        </span>
                    </div>
                    <div id="activities">
                        <h3 class="text-base text-text dark:text-natural-50">
                            <T :key-name="'template.activity.pool'" />
                        </h3>
                        <div
                            class="h-[11rem] w-full rounded-2xl border-2 border-dashed border-calypso-400 dark:border-calypso-600 dark:bg-background-dark lg:rounded-3xl"
                        >
                            <Skeleton
                                v-if="activityCount === null"
                                height="10.7rem"
                                class="rounded-2xl dark:bg-natural-600 lg:rounded-3xl"
                            />
                            <ScrollPanel
                                v-else
                                class="h-[10.7rem] w-full"
                                :pt="{
                                    bary: 'invisible hover:hidden',
                                    wrapper: 'z-0',
                                }"
                            >
                                <div
                                    class="mx-2 my-2 grid grid-cols-2 gap-1 text-text dark:text-natural-50 md:grid-cols-3 lg:grid-cols-4"
                                >
                                    <div
                                        v-for="activity in activities"
                                        :key="activity.id"
                                        class="empty:hidden"
                                    >
                                        <div
                                            :key="activity.id"
                                            class="relative col-span-1 mx-1 my-1 h-14 rounded-md border border-calypso-400 bg-light px-2 py-1 text-base font-normal dark:border-calypso-600 dark:bg-dark sm:h-16 sm:text-base lg:rounded-xl"
                                        >
                                            <div class="flex sm:pt-1">
                                                <p
                                                    v-tooltip.top="{
                                                        value: activity.name,
                                                        pt: {
                                                            root: 'font-nunito',
                                                        },
                                                    }"
                                                    class="w-[98%] overflow-hidden truncate"
                                                >
                                                    {{ activity.name }}
                                                </p>
                                            </div>
                                            <div class="flex items-center">
                                                <SvgClock
                                                    class="mr-2 h-4 w-4"
                                                />
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
                </div>
                <div
                    class="mt-auto flex w-full justify-between text-text dark:text-natural-50"
                >
                    <NuxtLink
                        class="flex h-10 items-center rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-center text-base font-semibold text-natural-900 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-200 dark:hover:bg-pesto-600"
                        :to="'/template/' + template.id"
                    >
                        <T key-name="template.preview" />
                    </NuxtLink>
                    <NuxtLink
                        class="flex h-10 items-center justify-end rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-center text-base font-semibold text-natural-900 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-200 dark:hover:bg-pesto-600"
                        :to="{
                            path: '/journey/new/template/' + template.id,
                            query: {
                                name: namePrefill,
                                date: datePrefill,
                            },
                        }"
                    >
                        <T key-name="template.use" />
                    </NuxtLink>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
