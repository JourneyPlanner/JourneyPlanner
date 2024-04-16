<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, required: true },
    defaultTime: { type: Array<number>, default: null },
});

const { value, errorMessage } = useField<Date>(() => props.name);

if (props.defaultTime !== null) {
    value.value = new Date();
    value.value.setHours(props.defaultTime[0]);
    value.value.setMinutes(props.defaultTime[1]);
}
</script>

<template>
    <div class="mb-2 flex flex-col">
        <label :for="id" class="text-sm font-medium md:text-base">
            <T :key-name="translationKey" />
        </label>
        <Calendar
            id="calendar-time"
            v-model="value"
            show-icon
            icon-display="input"
            time-only
            name="time"
            placeholder="hh:mm"
            input-class="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text dark:text-input font-normal bg-input dark:bg-input-dark border-2 border-border focus:outline-none focus:ring-1"
        >
            <template #inputicon="{ clickCallback }">
                <InputIcon
                    class="pi pi-clock cursor-pointer text-border"
                    @click="clickCallback"
                />
            </template>
        </Calendar>
        <span class="text-sm text-error dark:font-bold dark:text-error-dark">{{
            errorMessage
        }}</span>
    </div>
</template>
