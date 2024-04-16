<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField<Date>(() => props.name);
</script>

<template>
    <div class="mb-2 flex flex-col">
        <label :for="id" class="font-medium">
            <T :key-name="translationKey" />
        </label>
        <Calendar
            id="calendar-time"
            v-model="value"
            show-icon
            icon-display="input"
            time-only
            name="time"
            input-class="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text font-normal bg-input border-2 border-border focus:outline-none focus:ring-1"
        >
            <template #inputicon="{ clickCallback }">
                <InputIcon
                    class="pi pi-clock cursor-pointer text-border"
                    @click="clickCallback"
                />
            </template>
        </Calendar>
        <span class="text-sm text-error">{{ errorMessage }}</span>
    </div>
</template>
