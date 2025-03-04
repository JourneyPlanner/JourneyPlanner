<script setup lang="ts">
import { useField } from "vee-validate";

const isFocused = ref<boolean>(false);

const handleFocus = () => {
    isFocused.value = true;
};

const handleBlur = () => {
    isFocused.value = false;
};

const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    translationKey: { type: String, required: true },
    prefill: { type: Array as PropType<Date[]>, default: undefined },
});

const { value, errorMessage } = useField<Date[]>(() => props.name);
if (props.prefill) {
    value.value = props.prefill as Date[];
}

const emit = defineEmits(["changeInput"]);

function handleSelect() {
    handleFocus();
    emit("changeInput", value.value);
}
</script>

<template>
    <div class="relative">
        <Calendar
            :id="id"
            v-model="value"
            :name="name"
            selection-mode="range"
            :manual-input="false"
            :number-of-months="1"
            show-other-months
            select-other-months
            hide-on-range-selection
            date-format="dd/mm/yy"
            panel-class="bg-natural-50 dark:bg-natural-900 dark:text-natural-50"
            input-class="border-natural-300 hover:border-calypso-400 font-nunito  dark:hover:border-calypso-400 block rounded-lg px-2.5 pt-4 pb-1  w-full text-md text-text dark:text-natural-50 font-bold bg-natural-50 border-2 dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 focus:outline-none focus:border-calypso-400 dark:focus:border-calypso-400 dark:hover:border-calypso-400"
            :pt="{
                root: { class: 'lg:w-4/5 w-11/12' },
                panel: {
                    class: 'text-text font-nunito z-50',
                },
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
            @focus="handleFocus"
            @hide="handleBlur"
            @input="handleFocus"
            @date-select="handleSelect()"
        />
        <br />
        <div class="ml-0.5 mt-1 h-3 leading-3">
            <span
                class="text-xs text-mahagony-600 dark:font-medium dark:text-mahagony-300"
                >{{ errorMessage }}</span
            >
        </div>
        <label
            for="journey-range-calendar"
            class="pointer-events-none absolute left-0 top-0 overflow-hidden whitespace-nowrap py-4 pl-2.5 text-sm transition-all duration-300"
            :class="{
                'text-natural-600': !isFocused,
                'dark:text-natural-200': !isFocused,
                'text-calypso-600': isFocused,
                '-translate-x-7 -translate-y-4 scale-75': isFocused || value,
                'translate-y-0 scale-100': !isFocused && !value,
            }"
        >
            <T :key-name="translationKey" />
        </label>
    </div>
</template>
