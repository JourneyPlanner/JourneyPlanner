<script setup lang="ts">
const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    prefill: { type: Date, default: null },
    from: { type: Date, required: true },
    to: { type: Date, required: true },
    translationKey: { type: String, default: "" },
});

const emit = defineEmits(["date-selected"]);

const { value, errorMessage } = useField<Date>(() => props.name);

const dateSelected = () => {
    emit("date-selected", value.value);
};

const initializeDate = (dateInput: string | Date | null) => {
    if (!dateInput) return;

    try {
        if (dateInput instanceof Date) {
            value.value = dateInput;
            return;
        }

        const parsedDate = new Date(dateInput);
        if (isNaN(parsedDate.getTime())) {
            throw new Error("Invalid date format");
        }
        value.value = parsedDate;
    } catch (error) {
        console.error(`Failed to parse date: ${error}`);
        errorMessage.value = "Invalid date format";
    }
};

watch([() => props.prefill], ([newPrefill]) => {
    initializeDate(newPrefill);
});
</script>

<template>
    <div class="mb-2 flex flex-col">
        <label :for="id" class="text-sm font-medium md:text-base">
            <T :key-name="translationKey" />
        </label>
        <Calendar
            :id="id"
            v-model="value"
            show-icon
            :min-date="from"
            :max-date="to"
            icon-display="input"
            :show-on-focus="false"
            :name="name"
            date-format="dd/mm/yy"
            placeholder="dd/mm/yyyy"
            input-class="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text dark:text-natural-50 font-normal bg-natural-50 dark:bg-natural-800 border-2 border-calypso-300 dark:border-calypso-400 focus:outline-none focus:ring-1"
            @date-select="dateSelected"
            @blur="dateSelected"
        >
            <template #inputicon>
                <InputIcon class="pi pi-calendar text-calypso-400" />
            </template>
        </Calendar>
        <span
            class="text-xs text-mahagony-600 dark:font-medium dark:text-mahagony-300 sm:text-sm"
            >{{ errorMessage }}</span
        >
    </div>
</template>
