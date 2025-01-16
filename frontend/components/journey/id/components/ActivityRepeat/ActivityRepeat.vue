<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { differenceInHours } from "date-fns";
import { useField } from "vee-validate";

const toast = useToast();
const { t } = useTranslate();
const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    journeyStart: { type: Date, required: true },
    journeyEnd: { type: Date, required: true },
    repeatType: { type: String, required: true },
    repeatIntervalPrefill: { type: Number, default: 1 },
    repeatOnPrefill: { type: Array, default: () => [] },
    repeatEndOccurencesPrefill: { type: Number, default: 1 },
    repeatIntervalUnitPrefill: { type: String, default: "" },
    repeatEndDatePrefill: { type: String, default: "" },
});

const emit = defineEmits(["input", "customInput"]);

const repeatModes = ref([
    { name: t.value("activity.repeat.not"), value: 0 },
    { name: t.value("activity.repeat.daily"), value: 1 },
    { name: t.value("activity.repeat.weekly"), value: 7 },
    { name: t.value("activity.repeat.custom"), value: 2 },
]);

const showCustomizeRepeat = ref(false);
const repeatInterval = ref();
const repeatIntervalUnit = ref();
const repeatOn = ref();
const repeatEndDate = ref();
const repeatEndOccurences = ref();
const daysInJourney = ref(
    Math.ceil(differenceInHours(props.journeyEnd, props.journeyStart) / 24) + 1,
);

const { value, errorMessage } = useField<RepeatType>(() => props.name);
if (props.repeatType == "custom") {
    value.value = { name: t.value("activity.repeat.custom"), value: 2 };
} else if (props.repeatType == "daily") {
    value.value = { name: t.value("activity.repeat.daily"), value: 1 };
} else if (props.repeatType == "weekly") {
    value.value = { name: t.value("activity.repeat.weekly"), value: 7 };
} else {
    value.value = { name: t.value("activity.repeat.not"), value: 0 };
}

const input = () => {
    emit("input", value.value.name);
};

const customInput = () => {
    emit(
        "customInput",
        value.value.name,
        repeatInterval.value,
        repeatIntervalUnit.value,
        repeatOn.value,
        repeatEndDate.value,
        repeatEndOccurences.value,
    );
};

function changeRepeat() {
    if (value.value.value > daysInJourney.value) {
        value.value.name = t.value("activity.repeat.not");
        toast.add({
            severity: "error",
            summary: t.value("journey.too.short"),
            detail: t.value("journey.too.short.detail"),
            life: 3000,
        });
    } else {
        input();
        if (value.value.name == t.value("activity.repeat.custom")) {
            showCustomizeRepeat.value = true;
        }
    }
}

function createCustomRepeat(
    repeatNumber: number,
    timeModeselected: string,
    activeDays: string[],
    endOption: string,
    endingDate: Date,
    occurrenceCount: number,
) {
    repeatInterval.value = repeatNumber;
    repeatIntervalUnit.value = timeModeselected;
    repeatOn.value = activeDays;
    if (endOption == "date") {
        repeatEndDate.value = endingDate;
    } else {
        repeatEndOccurences.value = occurrenceCount;
    }
    customInput();
    showCustomizeRepeat.value = false;
}

const getItemClass = (option: RepeatType) => {
    return option.value > daysInJourney.value
        ? "text-text dark:text-natural-50 bg-natural-100 dark:bg-natural-900 !cursor-not-allowed "
        : "hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600";
};
</script>

<template>
    <div>
        <div>
            <label for="repeat" class="text-sm font-medium md:text-base">
                <T key-name="activity.repeat" />
            </label>
            <Dropdown
                :id="id"
                v-model="value"
                :name="name"
                :options="repeatModes"
                option-label="name"
                :placeholder="value.name"
                :highlight-on-select="false"
                :focus-on-hover="false"
                :unstyled="true"
                class="w-full"
                :pt="{
                    root: {
                        class: 'flex overflow-ellipsis cursor-pointer overflow-hidden text-nowrap font-nunito block rounded-lg px-2.5 pb-1 pt-1 text-text dark:text-natural-50 font-normal border-2 border-calypso-300 dark:border-calypso-600 focus:outline-none focus:ring-1 bg-natural-50 dark:bg-natural-800 text-text dark:text-natural-50 disabled:bg-natural-100 disabled:dark:bg-natural-800',
                    },
                    input: {
                        class: 'text-text dark:text-natural-50 rounded-md w-[90%] cursor-pointer',
                    },
                    item: {
                        class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                    },
                    wrapper: {
                        class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                    },
                    trigger: {
                        class: 'w-fit ml-auto flex items-center justify-center',
                    },
                }"
                @change="changeRepeat"
            >
                <template #value="slotProps">
                    <div
                        v-if="slotProps.value"
                        class="align-items-center flex w-full"
                    >
                        <div
                            v-tooltip.top="slotProps.value.name"
                            class="w-full overflow-hidden overflow-ellipsis"
                        >
                            {{ slotProps.value.name }}
                        </div>
                    </div>
                    <div v-else class="flex w-full">
                        <span
                            v-tooltip.top="slotProps.placeholder"
                            class="w-[90%] overflow-hidden overflow-ellipsis"
                        >
                            {{ slotProps.placeholder }}
                        </span>
                    </div>
                </template>
                <template #option="slotProps">
                    <div
                        :class="getItemClass(slotProps.option)"
                        class="cursor-pointer px-4 py-2"
                    >
                        {{ slotProps.option.name }}
                    </div>
                </template>
                <template #dropdownicon>
                    <div class="flex items-center justify-center">
                        <InputIcon class="pi pi-sync text-calypso-400" />
                    </div>
                </template>
            </Dropdown>
        </div>
        <CustomRepeat
            :visible="showCustomizeRepeat"
            :start-date="props.journeyStart"
            :end-date="props.journeyEnd"
            :days-in-journey="daysInJourney"
            :repeat-end-date="props.repeatEndDatePrefill"
            :repeat-end-occurences="props.repeatEndOccurencesPrefill"
            :repeat-interval="props.repeatIntervalPrefill"
            :repeat-interval-unit="props.repeatIntervalUnitPrefill"
            :repeat-on="props.repeatOnPrefill"
            @close="showCustomizeRepeat = false"
            @cancel="
                showCustomizeRepeat = false;
                value = { name: t('activity.repeat.not'), value: 0 };
            "
            @create-custom-repeat="createCustomRepeat"
        />
        <span
            class="text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300 sm:text-sm"
            >{{ errorMessage }}</span
        >
    </div>
</template>
