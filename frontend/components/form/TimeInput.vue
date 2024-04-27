<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, required: true },
    defaultTime: { type: Array<number>, default: null },
    disabled: { type: Boolean, default: false },
});

const { value, errorMessage } = useField<Date>(() => props.name);

if (props.defaultTime !== null) {
    value.value = new Date();
    value.value.setHours(props.defaultTime[0]);
    value.value.setMinutes(props.defaultTime[1]);
}

const defaultClass =
    "font-nunito block rounded-lg px-2.5 pb-1 pt-1 text-text dark:text-input font-normal border-2 border-border focus:outline-none focus:ring-1 ";
const computedClass = computed(() => {
    return props.disabled === true
        ? defaultClass +
              "bg-input-disabled dark:bg-input-disabled-dark cursor-not-allowed"
        : defaultClass + "bg-input dark:bg-input-dark";
});
</script>

<template>
    <div class="mb-0 flex flex-col sm:mb-2">
        <label :for="id" class="text-sm font-medium md:text-base">
            <T :key-name="translationKey" />
        </label>
        <Calendar
            :id="id"
            v-model="value"
            show-icon
            icon-display="input"
            time-only
            name="time"
            placeholder="hh:mm"
            :disabled="disabled"
            :pt="{ timePicker: { class: 'font-nunito' } }"
            :input-class="computedClass"
            panel-class="bg-input dark:bg-input-dark text-text dark:text-input"
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
