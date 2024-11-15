<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, required: true },
    defaultTime: { type: Array<number>, default: null },
    disabled: { type: Boolean, default: false },
    value: { type: String, default: null },
});

const { value, errorMessage } = useField<Date>(() => props.name);

if (props.value !== null && props.value !== "") {
    value.value = new Date();
    value.value.setHours(parseInt(props.value.split(":")[0]));
    value.value.setMinutes(parseInt(props.value.split(":")[1]));
} else if (props.defaultTime !== null) {
    value.value = new Date();
    value.value.setHours(props.defaultTime[0]);
    value.value.setMinutes(props.defaultTime[1]);
}

const defaultClass =
    "font-nunito block rounded-lg px-2.5 pb-1 pt-1 text-text dark:text-natural-50 font-normal border-2 border-calypso-300 focus:outline-none focus:ring-1 ";
const computedClass = computed(() => {
    return props.disabled === true
        ? defaultClass + "bg-natural-100 dark:bg-natural-800 cursor-not-allowed"
        : defaultClass + "bg-natural-50 dark:bg-natural-900";
});
</script>

<template>
    <div class="mb-2 flex flex-col sm:mb-2">
        <label :for="id" class="text-sm font-medium md:text-base">
            <T :key-name="translationKey" />
        </label>
        <Calendar
            :id="id"
            v-model="value"
            show-icon
            value=""
            icon-display="input"
            time-only
            name="time"
            placeholder="hh:mm"
            :disabled="disabled"
            :pt="{ timePicker: { class: 'font-nunito' } }"
            :input-class="computedClass"
            panel-class="bg-natural-50 dark:bg-natural-800 text-text dark:text-natural-50 disabled:bg-natural-100 disabled:dark:bg-natural-800"
        >
            <template #inputicon="{ clickCallback }">
                <InputIcon
                    class="pi pi-clock cursor-pointer text-calypso-400"
                    @click="clickCallback"
                />
            </template>
        </Calendar>
        <span
            class="text-sm text-mahagony-600 dark:font-bold dark:text-mahagony-300"
            >{{ errorMessage }}</span
        >
    </div>
</template>
