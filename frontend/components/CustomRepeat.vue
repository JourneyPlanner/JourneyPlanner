<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { add, format } from "date-fns";

const props = defineProps({
    visible: { type: Boolean, required: true },
    startDate: { type: Date, required: true },
    endDate: { type: Date, required: true },
    daysInJourney: { type: Number, required: true },
    repeatInterval: { type: Number, default: 1 },
    repeatOn: { type: Array, default: () => [] },
    repeatEndOccurences: { type: Number, default: 1 },
    repeatIntervalUnit: { type: String, default: "" },
    repeatEndDate: { type: String, default: "" },
});

const toast = useToast();
const isVisible = ref(props.visible);
const emit = defineEmits(["close", "cancel", "createCustomRepeat"]);
const { t } = useTranslate();
const repeatNumber = ref(props.repeatInterval);
const timeModeselected = ref();
const days = ref([
    {
        label: t.value("activity.repeat.monday.short"),
        active: false,
        day: "Mon",
    },
    {
        label: t.value("activity.repeat.tuesday.short"),
        active: false,
        day: "Tue",
    },
    {
        label: t.value("activity.repeat.wednesday.short"),
        active: false,
        day: "Wed",
    },
    {
        label: t.value("activity.repeat.thursday.short"),
        active: false,
        day: "Thu",
    },
    {
        label: t.value("activity.repeat.friday.short"),
        active: false,
        day: "Fri",
    },
    {
        label: t.value("activity.repeat.saturday.short"),
        active: false,
        day: "Sat",
    },
    {
        label: t.value("activity.repeat.sunday.short"),
        active: false,
        day: "Sun",
    },
]);
if (props.repeatIntervalUnit == "weeks") {
    timeModeselected.value = {
        name: t.value("activity.repeat.week"),
        value: 7,
    };
    days.value.forEach((element) => {
        if (props.repeatOn.includes(element.day)) {
            element.active = true;
        }
    });
} else {
    timeModeselected.value = {
        name: t.value("activity.repeat.day"),
        value: 1,
    };
}

const selectedTimeUnit = ref("days");
const endingDate = ref();
if (props.repeatEndDate != "") {
    endingDate.value = new Date(props.repeatEndDate);
}
const timeMode = ref(t.value("activity.repeat.day"));
const endOption = ref("date");
const occurrenceCount = ref(props.repeatEndOccurences);
const timeModes = ref([
    { name: t.value("activity.repeat.day"), value: 1 },
    { name: t.value("activity.repeat.week"), value: 7 },
]);

watch(
    () => props.repeatEndDate,
    (value) => {
        endingDate.value = new Date(value);
    },
);

watch(
    () => props.repeatIntervalUnit,
    (value) => {
        if (value == "weeks") {
            timeModeselected.value = {
                name: t.value("activity.repeat.week"),
                value: 7,
            };
            days.value.forEach((element) => {
                if (props.repeatOn.includes(element.day)) {
                    element.active = true;
                }
            });
        } else {
            timeModeselected.value = {
                name: t.value("activity.repeat.day"),
                value: 1,
            };
        }
    },
);

watch(
    () => props.repeatEndOccurences,
    (value) => {
        occurrenceCount.value = value;
    },
);

watch(
    () => props.repeatInterval,
    (value) => {
        repeatNumber.value = value;
    },
);

const createCustomRepeat = () => {
    if (endingDate.value) {
        endingDate.value = add(endingDate.value, {
            minutes: endingDate.value.getTimezoneOffset() * -1,
        });
    }
    const activeDays = ref<string[]>([]);
    days.value.forEach((day) => {
        if (day.active) {
            activeDays.value.push(day.day);
        }
    });
    if (
        (timeModeselected.value.name === t.value("activity.repeat.weeks") ||
            timeModeselected.value.name === t.value("activity.repeat.week")) &&
        activeDays.value.length == 0
    ) {
        toast.add({
            severity: "error",
            summary: t.value("repeat.select.weekday"),
            detail: t.value("repeat.select.weekday.detail"),
            life: 3000,
        });
        return;
    } else if (
        timeModeselected.value.name === t.value("activity.repeat.days") ||
        timeModeselected.value.name === t.value("activity.repeat.day")
    ) {
        activeDays.value = [];
    }
    emit(
        "createCustomRepeat",
        repeatNumber.value,
        selectedTimeUnit.value,
        activeDays.value,
        endOption.value,
        endingDate.value,
        occurrenceCount.value,
    );
};

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const cancel = () => {
    emit("cancel");
};

function changePlural() {
    if (repeatNumber.value > 1) {
        if (
            timeModeselected.value.name === t.value("activity.repeat.week") ||
            timeModeselected.value.name === t.value("activity.repeat.weeks")
        ) {
            timeMode.value = t.value("activity.repeat.weeks");
            selectedTimeUnit.value = "weeks";
            timeModeselected.value = {
                name: t.value("activity.repeat.weeks"),
                value: 7,
            };
        } else {
            timeMode.value = t.value("activity.repeat.days");
            selectedTimeUnit.value = "days";
            timeModeselected.value = {
                name: t.value("activity.repeat.days"),
                value: 1,
            };
        }

        timeModes.value = [
            { name: t.value("activity.repeat.days"), value: 1 },
            { name: t.value("activity.repeat.weeks"), value: 7 },
        ];

        if (
            timeModeselected.value.value * repeatNumber.value >=
            props.daysInJourney
        ) {
            toast.add({
                severity: "error",
                summary: t.value("journey.too.short"),
                detail: t.value("journey.too.short.detail"),
                life: 3000,
            });
            repeatNumber.value = repeatNumber.value - 1;
            changePlural();
        }
    } else {
        timeModes.value = [
            { name: t.value("activity.repeat.day"), value: 1 },
            { name: t.value("activity.repeat.week"), value: 7 },
        ];
        if (
            timeModeselected.value.name === t.value("activity.repeat.week") ||
            timeModeselected.value.name === t.value("activity.repeat.weeks")
        ) {
            timeMode.value = t.value("activity.repeat.week");
            selectedTimeUnit.value = "weeks";
        } else {
            timeMode.value = t.value("activity.repeat.day");
            selectedTimeUnit.value = "days";
        }
    }
}

function toggleDay(index: number) {
    days.value[index].active = !days.value[index].active;
}
const getItemClass = (option: RepeatType) => {
    return option.value * repeatNumber.value > props.daysInJourney
        ? "text-text dark:text-natural-50 bg-natural-100 dark:bg-natural-900 !cursor-not-allowed"
        : "hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600";
};

function changeRepeat() {
    if (props.daysInJourney <= 1) {
        timeModeselected.value = timeModeselected.value = {
            name: t.value("activity.repeat.day"),
            value: 1,
        };
        toast.add({
            severity: "error",
            summary: t.value("journey.too.short"),
            detail: t.value("journey.too.short.detail"),
            life: 3000,
        });
        changePlural();
    } else if (
        timeModeselected.value.name === t.value("activity.repeat.weeks") &&
        timeModeselected.value.value * repeatNumber.value >= props.daysInJourney
    ) {
        timeModeselected.value = timeModeselected.value = {
            name: t.value("activity.repeat.days"),
            value: 1,
        };
        toast.add({
            severity: "error",
            summary: t.value("journey.too.short"),
            detail: t.value("journey.too.short.detail"),
            life: 3000,
        });
        changePlural();
    }
    changePlural();
}

function changeOccurrences() {
    const activeDays = ref<string[]>([]);
    days.value.forEach((day) => {
        if (day.active) {
            activeDays.value.push(day.day);
        }
    });
    if (
        (timeModeselected.value.name === t.value("activity.repeat.weeks") ||
            timeModeselected.value.name === t.value("activity.repeat.week")) &&
        activeDays.value.length == 0
    ) {
        toast.add({
            severity: "error",
            summary: t.value("repeat.select.weekday"),
            detail: t.value("repeat.select.weekday.detail"),
            life: 3000,
        });
        occurrenceCount.value--;
        return;
    }
    let maxOccurences;
    if (
        timeModeselected.value.name === t.value("activity.repeat.weeks") ||
        timeModeselected.value.name === t.value("activity.repeat.week")
    ) {
        maxOccurences = Math.ceil(
            (props.daysInJourney * activeDays.value.length) /
                (timeModeselected.value.value * repeatNumber.value),
        );
    } else {
        maxOccurences = Math.ceil(
            props.daysInJourney /
                (timeModeselected.value.value * repeatNumber.value),
        );
    }
    if (
        (occurrenceCount.value > maxOccurences &&
            (timeModeselected.value.name === t.value("activity.repeat.days") ||
                timeModeselected.value.name ===
                    t.value("activity.repeat.day"))) ||
        (occurrenceCount.value > maxOccurences &&
            (timeModeselected.value.name === t.value("activity.repeat.weeks") ||
                timeModeselected.value.name ===
                    t.value("activity.repeat.week")))
    ) {
        occurrenceCount.value--;
        toast.add({
            severity: "error",
            summary: t.value("journey.too.short"),
            detail: t.value("journey.too.short.detail"),
            life: 3000,
        });
        if (
            timeModeselected.value.name === t.value("activity.repeat.weeks") ||
            timeModeselected.value.name === t.value("activity.repeat.week")
        ) {
            occurrenceCount.value = Math.ceil(
                (props.daysInJourney * activeDays.value.length) /
                    (timeModeselected.value.value * repeatNumber.value),
            );
        } else {
            occurrenceCount.value = Math.ceil(
                props.daysInJourney /
                    (timeModeselected.value.value * repeatNumber.value),
            );
        }
    }
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
            class="z-50 flex w-1/3 flex-col text-nowrap rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/4"
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
                    class: 'justify-end items-center w-fit pl-5 collapse',
                },
                closeButtonIcon: {
                    class: 'collapse',
                },
                mask: {
                    class: 'max-sm:collapse',
                },
            }"
        >
            <template #header>
                <div class="flex w-full items-center">
                    <div class="h-0.5 w-5 bg-calypso-600" />
                    <div
                        class="flex-grow-5 mx-1 pl-1 text-2xl text-text dark:text-natural-50"
                    >
                        <T key-name="activity.repeat.custom.dialog" />
                    </div>
                    <div class="h-0.5 flex-grow bg-calypso-600" />
                    <button
                        class="focus-ring-1 z-30 flex h-10 w-10 items-center justify-center self-center text-natural-500 hover:text-text focus:outline-none dark:text-natural-400 dark:hover:text-natural-50"
                        @click="cancel"
                    >
                        <span class="pi pi-times text-2xl" />
                    </button>
                </div>
            </template>
            <form class="text-text dark:text-natural-50">
                <div class="flex items-end text-xl">
                    <T key-name="activity.repeat.every" />
                    <InputNumber
                        v-model="repeatNumber"
                        input-id="stacked-buttons"
                        input-class="w-6 focus:outline-none bg-background dark:bg-natural-900"
                        increment-button-class="flex flex-col items-end w-6"
                        decrement-button-class="flex flex-col items-end w-6"
                        show-buttons
                        :min="1"
                        class="ml-4 h-8 w-10 rounded-md border border-natural-300 bg-background pl-2 dark:border-natural-700 dark:bg-natural-800"
                        :pt="{
                            root: {
                                class: 'w-12 dark:bg-natural-900',
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
                        :unstyled="true"
                        class="w-[30%] rounded-md"
                        :pt="{
                            root: {
                                class: 'ml-4 font-nunito text-text text-base bg-natural-50 border border-natural-300 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-8 flex items-center',
                            },
                            input: {
                                class: 'text-text dark:text-natural-50 ml-2 font-medium',
                            },
                            wrapper: {
                                class: 'bg-natural-50 dark:bg-natural-900 border border-natural-200 text-text dark:text-natural-50 rounded-md',
                            },
                            trigger: {
                                class: 'text-text dark:text-natural-50 ml-auto mr-2',
                            },
                        }"
                        @change="changeRepeat"
                    >
                        <template #option="slotProps">
                            <div
                                :class="getItemClass(slotProps.option)"
                                class="cursor-pointer px-4 py-2"
                            >
                                {{ slotProps.option.name }}
                            </div>
                        </template>
                    </Dropdown>
                </div>
                <div
                    v-if="
                        timeModeselected.name == t('activity.repeat.week') ||
                        timeModeselected.name == t('activity.repeat.weeks')
                    "
                    class="space-y-4 pt-4"
                >
                    <div class="text-xl">
                        <T key-name="activity.repeat.on" />
                    </div>
                    <div class="flex space-x-2">
                        <button
                            v-for="(day, index) in days"
                            :key="index"
                            type="button"
                            :class="{
                                'h-10 w-10 rounded-full': true,
                                'bg-natural-100 dark:bg-natural-800':
                                    !day.active,
                                'text-white border-transparent bg-dandelion-200 dark:bg-pesto-600':
                                    day.active,
                            }"
                            class="focus:outline-none"
                            @click="toggleDay(index)"
                        >
                            {{ day.label }}
                        </button>
                    </div>
                </div>
                <div class="space-y-3 pt-4">
                    <div class="text-xl">
                        <T key-name="activity.repeat.ends" />
                    </div>
                    <div class="flex items-center space-x-2">
                        <input
                            id="endDate"
                            v-model="endOption"
                            type="radio"
                            value="date"
                            class="form-radio h-4 w-4 rounded-full border-calypso-600 bg-calypso-600"
                        />
                        <label
                            for="endDate"
                            :class="{
                                'text-gray-600': true,
                                'text-natural-400': endOption !== 'date',
                            }"
                        >
                            <div class="text-lg">
                                <T key-name="activity.repeat.custom.on" /></div
                        ></label>
                        <Calendar
                            v-model="endingDate"
                            :min-date="props.startDate"
                            :max-date="props.endDate"
                            :disabled="endOption !== 'date'"
                            :placeholder="format(props.endDate, 'dd/MM/yyyy')"
                            panel-class="bg-natural-50 dark:bg-natural-900 dark:text-natural-50"
                            input-class=" font-nunito rounded-md dark:border-natural-700 border-natural-200 block w-28 h-8 text-center text-md text-text dark:text-natural-50 disabled:text-natural-400 dark:disabled:text-natural-400 font-medium bg-natural-50 dark:bg-natural-900 border-2 focus:outline-none focus:ring-0"
                            class="rounded-lg border placeholder-text"
                            date-format="dd/mm/yy"
                            :pt="{
                                root: 'border-none',
                                panel: { class: 'text-text font-nunito z-50' },
                                header: {
                                    class: 'flex justify-between border-b bg-natural-50 dark:bg-natural-900 dark:text-natural-50',
                                },
                                title: {
                                    class: 'text-text dark:text-natural-50 flex gap-1 font-nunito',
                                },
                                dayLabel: { class: 'text-calypso-400' },
                                datepickerMask: {
                                    class: 'text-text bg-natural-900',
                                },
                            }"
                        />
                    </div>

                    <div class="flex items-center space-x-3">
                        <input
                            id="occurrences"
                            v-model="endOption"
                            type="radio"
                            value="occurrences"
                            class="form-radio h-4 w-4 rounded-full border-calypso-600 bg-calypso-600"
                        />

                        <InputNumber
                            v-model="occurrenceCount"
                            input-id="stacked-buttons"
                            input-class="w-6 focus:outline-none focus:ring-1 disabled:bg-natural-100 disabled:text-natural-400 pl-2 dark:bg-natural-900"
                            increment-button-class="flex flex-col items-end w-6"
                            :disabled="endOption !== 'occurrences'"
                            decrement-button-class="flex flex-col items-end w-6"
                            show-buttons
                            :min="1"
                            class="ml-4 h-8 w-10 rounded-md border border-natural-300 focus:outline-none focus:ring-1 disabled:bg-natural-100 dark:border-natural-700"
                            :pt="{
                                root: {
                                    class: 'w-12 disabled:bg-natural-100',
                                },
                                buttonGroup: {
                                    class: 'flex flex-col items-end w-6',
                                },
                            }"
                            @input="changeOccurrences"
                            @blur="changeOccurrences"
                        />

                        <span
                            :class="{
                                'text-gray-600': true,
                                'text-lg': true,
                                'text-natural-400': endOption !== 'occurrences',
                            }"
                            >{{ t("activity.repeat.occurrences") }}</span
                        >
                    </div>
                </div>
                <div class="-mb-2 mt-6 flex w-full">
                    <Button
                        type="button"
                        :label="t('common.button.cancel')"
                        class="mt-auto h-9 w-32 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-bold font-nunito',
                            },
                        }"
                        @click="cancel"
                    />
                    <Button
                        type="button"
                        :label="t('common.button.create')"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-bold font-nunito',
                            },
                        }"
                        class="ml-auto flex h-9 w-32 flex-row justify-center self-end rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:bg-dandelion-200 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="createCustomRepeat"
                    />
                </div>
            </form>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="right"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-[60%] w-[100%] flex-col rounded-t-md bg-background pl-5 font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden ',
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
            ><template #header>
                <button class="-ml-12 flex justify-center pr-4" @click="cancel">
                    <span class="pi pi-angle-left text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="activity.repeat.custom.dialog" />
                </div>
            </template>
            <form class="flex h-full flex-col text-text dark:text-natural-50">
                <div class="flex items-end text-xl">
                    <T key-name="activity.repeat.every" />
                    <InputNumber
                        v-model="repeatNumber"
                        input-id="stacked-buttons"
                        input-class="w-6 focus:outline-none focus:ring-1 bg-background dark:bg-natural-900"
                        increment-button-class="flex flex-col items-end w-6"
                        decrement-button-class="flex flex-col items-end w-6"
                        show-buttons
                        :min="1"
                        class="ml-4 h-8 w-10 rounded-md border border-natural-300 bg-background pl-2 focus:outline-none focus:ring-1 dark:border-natural-700 dark:bg-natural-800"
                        :pt="{
                            root: {
                                class: 'w-12 dark:bg-natural-900',
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
                        :unstyled="true"
                        class="w-[30%] rounded-sm"
                        :pt="{
                            root: {
                                class: 'ml-4 font-nunito text-text text-base bg-natural-50 border border-natural-300 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-8 flex items-center',
                            },
                            input: {
                                class: 'text-text dark:text-natural-50 ml-2 font-medium',
                            },
                            wrapper: {
                                class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                            },
                            trigger: {
                                class: 'text-text dark:text-natural-50 ml-auto mr-2',
                            },
                        }"
                        @change="changeRepeat"
                    />
                </div>
                <div
                    v-if="
                        timeModeselected.name == t('activity.repeat.week') ||
                        timeModeselected.name == t('activity.repeat.weeks')
                    "
                    class="space-y-4 pt-4"
                >
                    <div class="text-xl">
                        <T key-name="activity.repeat.on" />
                    </div>
                    <div class="flex space-x-2">
                        <button
                            v-for="(day, index) in days"
                            :key="index"
                            type="button"
                            :class="{
                                'h-10 w-10 rounded-full': true,
                                'bg-natural-100 dark:bg-natural-800':
                                    !day.active,
                                'text-white border-transparent bg-dandelion-200 dark:bg-pesto-600':
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
                    <div class="text-xl">
                        <T key-name="activity.repeat.ends" />
                    </div>
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
                            v-model="endingDate"
                            :min-date="props.startDate"
                            :max-date="props.endDate"
                            :disabled="endOption !== 'date'"
                            :placeholder="format(props.endDate, 'dd/MM/yyyy')"
                            panel-class="bg-natural-50 dark:bg-natural-900 dark:text-natural-50"
                            input-class="dark:border-natural-700 border-natural-300 block rounded-sm w-28 text-center text-md text-text dark:text-natural-50 disabled:text-natural-400 dark:disabled:text-natural-400 font-medium bg-natural-50 dark:bg-natural-900 border-2 focus:outline-none focus:ring-0"
                            class="rounded-lg border placeholder-text"
                            date-format="dd/mm/yy"
                            :pt="{
                                root: 'border-none',
                                panel: { class: 'text-text font-nunito z-50' },
                                header: {
                                    class: 'flex justify-between border-b bg-natural-50 dark:bg-natural-900 dark:text-natural-50',
                                },
                                title: {
                                    class: 'text-text dark:text-natural-50 flex gap-1 font-nunito',
                                },
                                dayLabel: { class: 'text-calypso-400' },

                                datepickerMask: {
                                    class: 'text-text bg-natural-900',
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
                            input-class="w-6 focus:outline-none focus:ring-1 disabled:bg-natural-100 disabled:text-natural-400 pl-2 dark:bg-natural-900"
                            increment-button-class="flex flex-col items-end w-6"
                            :disabled="endOption !== 'occurrences'"
                            decrement-button-class="flex flex-col items-end w-6"
                            show-buttons
                            :min="1"
                            class="ml-4 h-8 w-10 rounded-md border border-natural-300 focus:outline-none focus:ring-1 disabled:bg-natural-100 dark:border-natural-700"
                            :pt="{
                                root: {
                                    class: 'w-12 disabled:bg-natural-100',
                                },
                                buttonGroup: {
                                    class: 'flex flex-col items-end w-6',
                                },
                            }"
                            @input="changeOccurrences"
                            @blur="changeOccurrences"
                        />
                        <span
                            :class="{
                                'text-gray-600': true,
                                'text-natural-400': endOption !== 'occurrences',
                            }"
                            >{{ t("activity.repeat.occurrences") }}</span
                        >
                    </div>
                </div>
                <div class="mb-2 mt-auto flex h-fit w-full">
                    <Button
                        type="button"
                        :label="t('common.button.create')"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-bold font-nunito',
                            },
                        }"
                        class="ml-auto flex h-7 w-full flex-row justify-center self-end rounded-md border-2 border-dandelion-300 bg-natural-50 py-4 text-center text-text hover:bg-dandelion-200 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="createCustomRepeat"
                    />
                </div></form
        ></Sidebar>
    </div>
</template>
