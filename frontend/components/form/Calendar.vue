<script setup lang="ts">
import Calendar from "primevue/calendar";
import { useField } from "vee-validate";

const isFocused = ref(false);

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
    prefill: { type: Array, default: undefined as unknown as Date[] },
});

const { value, errorMessage } = useField<Date[]>(() => props.name);
if (props.prefill) {
    value.value = props.prefill as Date[];
}

defineEmits(["input"]);
</script>

<template>
    <div class="relative">
        <!-- TODO: language (de/en)-->
        <Calendar
            :id="id"
            v-model="value"
            :name="name"
            selection-mode="range"
            :manual-input="true"
            :show-button-bar="true"
            :number-of-months="1"
            date-format="dd/mm/yy"
            panel-class="bg-input dark:bg-background-dark dark:text-white"
            input-class="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text dark:text-white font-bold bg-input dark:bg-input-dark border-2 border-border focus:outline-none focus:ring-1"
            :pt="{
                panel: { class: 'text-text font-nunito' },
                header: {
                    class: 'flex justify-between border-b bg-input dark:bg-background-dark dark:text-white',
                },
                dayLabel: { class: 'text-border' },
                datepickerMask: { class: 'text-text bg-background-dark' },
            }"
            @focus="handleFocus"
            @hide="handleBlur"
            @input="handleFocus"
            @date-select="$emit('input', $event), handleFocus"
        />
        <br />
        <div class="h-3">
            <span
                class="ml-2.5 text-xs text-error dark:font-bold dark:text-error-dark"
                >{{ errorMessage }}</span
            >
        </div>
        <label
            for="journey-range-calendar"
            class="pointer-events-none absolute left-0 top-0 overflow-hidden whitespace-nowrap py-4 pl-2.5 text-sm transition-all duration-300"
            :class="{
                'text-input-placeholder': !isFocused,
                'text-input-label': isFocused,
                '-translate-x-6 -translate-y-4 scale-75': isFocused || value,
                'translate-y-0 scale-100': !isFocused && !value,
            }"
        >
            <T :key-name="translationKey" />
        </label>
    </div>
</template>
