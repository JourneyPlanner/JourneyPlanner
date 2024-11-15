<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { format } from "date-fns";

const props = defineProps({
    visible: { type: Boolean, required: true },
    startDate: { type: Date, required: true },
    endDate: { type: Date, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "cancel"]);
const { t } = useTranslate();
const repeatNumber = ref(1);
const timeModeselected = ref({
    name: t.value("activity.repeat.days"),
});
const ending = ref();
const timeMode = ref(t.value("activity.repeat.day"));
const endOption = ref("date");
const occurrenceCount = ref(1);

const timeModes = ref([
    { name: t.value("activity.repeat.day") },
    { name: t.value("activity.repeat.week") },
]);

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};

const days = ref([
    { label: t.value("activity.repeat.monday.short"), active: false },
    { label: t.value("activity.repeat.tuesday.short"), active: false },
    { label: t.value("activity.repeat.wednesday.short"), active: false },
    { label: t.value("activity.repeat.thursday.short"), active: false },
    { label: t.value("activity.repeat.friday.short"), active: false },
    { label: t.value("activity.repeat.saturday.short"), active: false },
    { label: t.value("activity.repeat.sunday.short"), active: false },
]);

function changePlural() {
    if (repeatNumber.value > 1) {
        if (timeModeselected.value.name === t.value("activity.repeat.week")) {
            timeMode.value = t.value("activity.repeat.weeks");
        } else {
            timeMode.value = t.value("activity.repeat.days");
        }

        timeModeselected.value = {
            name: t.value("activity.repeat.days"),
        };
        timeMode.value = t.value("activity.repeat.days");

        timeModes.value = [
            { name: t.value("activity.repeat.days") },
            { name: t.value("activity.repeat.weeks") },
        ];
    } else {
        timeModes.value = [
            { name: t.value("activity.repeat.day") },
            { name: t.value("activity.repeat.week") },
        ];
        if (timeModeselected.value.name === t.value("activity.repeat.weeks")) {
            timeMode.value = t.value("activity.repeat.week");
        } else {
            timeMode.value = t.value("activity.repeat.day");
        }
    }
}

function toggleDay(index: number) {
    days.value[index].active = !days.value[index].active;
}
</script>
<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/4 2xl:w-1/5"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex justify-start pb-2 font-nunito bg-background dark:bg-background-dark',
            },
            title: {
                class: 'font-nunito text-4xl font-semibold',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-5 h-full',
            },
            footer: { class: 'h-0' },
            icons: {
                class: 'justify-end items-center w-fit pl-5',
            },
            closeButtonIcon: {
                class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 focus:outline-none focus-ring-1 h-10 w-10 ',
            },
            mask: {
                class: 'max-sm:collapse',
            },
        }"
        @hide="close"
    >
        <template #header>
            <div class="flex w-[90%] items-center">
                <div class="h-0.5 w-5 bg-calypso-600" />
                <div
                    class="flex-grow-5 mx-3 text-2xl text-text dark:text-natural-50"
                >
                    <T key-name="activity.repeat.custom.dialog" />
                </div>
                <div class="h-0.5 flex-grow bg-calypso-600" />
            </div>
        </template>
        <form class="text-text dark:text-natural-50">
            <div class="flex items-end text-xl">
                <T key-name="activity.repeat.every" />
                <InputNumber
                    v-model="repeatNumber"
                    input-id="stacked-buttons"
                    input-class="w-6 focus:outline-none focus:ring-1 bg-background"
                    increment-button-class="flex flex-col items-end w-6"
                    decrement-button-class="flex flex-col items-end w-6"
                    show-buttons
                    :min="1"
                    class="ml-4 h-8 w-10 rounded-md border border-natural-300 bg-background pl-2 focus:outline-none focus:ring-1"
                    :pt="{
                        root: {
                            class: 'w-12',
                        },
                        buttonGroup: {
                            class: 'flex flex-col items-end w-6',
                        },
                    }"
                    @input="changePlural"
                />
                <Dropdown
                    v-model="timeModeselected"
                    :options="timeModes"
                    option-label="name"
                    :highlight-on-select="false"
                    :placeholder="timeMode"
                    :focus-on-hover="false"
                    :pt="{
                        root: {
                            class: 'ml-4 font-nunito text-text bg-natural-50 border border-natural-300 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-8 flex items-center',
                        },
                        input: {
                            class: 'text-text dark:text-natural-50',
                        },
                        item: {
                            class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                        },
                        wrapper: {
                            class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                        },
                        trigger: {
                            class: 'text-text dark:text-natural-50',
                        },
                    }"
                />
            </div>
            <div
                v-if="
                    timeModeselected.name == t('activity.repeat.week') ||
                    timeModeselected.name == t('activity.repeat.weeks')
                "
                class="space-y-4 pt-4"
            >
                <div class="text-xl"><T key-name="activity.repeat.on" /></div>
                <div class="flex space-x-2">
                    <button
                        v-for="(day, index) in days"
                        :key="index"
                        type="button"
                        :class="{
                            'h-10 w-10 rounded-full': true,
                            'bg-natural-100': !day.active,
                            'text-white border-transparent bg-dandelion-200':
                                day.active,
                        }"
                        class="focus:outline-none"
                        @click="toggleDay(index)"
                    >
                        {{ day.label }}
                    </button>
                </div>
            </div>
            <div class="space-y-4 pt-4">
                <div class="text-xl"><T key-name="activity.repeat.ends" /></div>
                <div class="flex items-center space-x-2">
                    <input
                        id="endDate"
                        v-model="endOption"
                        type="radio"
                        checked
                        value="date"
                        class="form-radio text-blue-600"
                    />
                    <label
                        for="endDate"
                        :class="{
                            'text-gray-600': true,
                            'text-natural-400': endOption !== 'date',
                        }"
                        >On</label
                    >
                    <Calendar
                        v-model="ending"
                        :min-date="props.startDate"
                        :max-date="props.endDate"
                        :disabled="endOption !== 'date'"
                        :placeholder="format(props.endDate, 'dd/MM/yyyy')"
                        class="rounded-lg border placeholder-text"
                        date-format="dd/mm/yy"
                        :pt="{
                            root: {
                                class: 'font-nunito text-text bg-natural-50 border-0 placeholder-text disabled:bg-natural-100',
                            },
                            title: {
                                class: 'flex gap-1 text-text dark:text-natural-50 ',
                            },
                            header: {
                                class: 'bg-natural-50 dark:bg-natural-800 text-text dark:text-natural-50',
                            },
                            panel: {
                                class: 'font-nunito text-text bg-natural-50 dark:bg-natural-800 dark:text-natural-50',
                            },
                            input: {
                                class: 'placeholder-text dark:placeholder-natural-50 border border-natural-300 w-28 text-center rounded-md disabled:bg-natural-100 disabled:text-natural-400 disabled:placeholder-natural-400',
                            },
                        }"
                    />
                </div>

                <div class="flex items-center space-x-2">
                    <input
                        id="occurrences"
                        v-model="endOption"
                        type="radio"
                        value="occurrences"
                        class="form-radio text-blue-600"
                    />

                    <InputNumber
                        v-model="occurrenceCount"
                        input-id="stacked-buttons"
                        input-class="w-6 focus:outline-none focus:ring-1 disabled:bg-natural-100 disabled:text-natural-400 pl-2"
                        increment-button-class="flex flex-col items-end w-6"
                        :disabled="endOption !== 'occurrences'"
                        decrement-button-class="flex flex-col items-end w-6"
                        show-buttons
                        :min="1"
                        class="ml-4 h-8 w-10 rounded-md border border-natural-300 focus:outline-none focus:ring-1 disabled:bg-natural-100"
                        :pt="{
                            root: {
                                class: 'w-12 disabled:bg-natural-100',
                            },
                            buttonGroup: {
                                class: 'flex flex-col items-end w-6',
                            },
                        }"
                        @input="changePlural"
                    />
                    <span
                        :class="{
                            'text-gray-600': true,
                            'text-natural-400': endOption !== 'occurrences',
                        }"
                        >occurrences</span
                    >
                </div>
            </div>
            <div class="-mb-4 mt-6 flex w-full">
                <Button
                    type="button"
                    :label="t('common.button.cancel')"
                    class="mt-auto h-7 w-32 rounded-md border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-mahagony-500030"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    @click="close"
                />
                <Button
                    type="submit"
                    :label="t('common.button.create')"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    class="ml-auto flex h-7 w-32 flex-row justify-center self-end rounded-md border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:bg-dandelion-200 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-pesto-600"
                />
            </div>
        </form>
    </Dialog>
</template>
