<script setup lang="ts">
const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    prefill: { type: Date, default: null },
    from: { type: Date, required: true },
    to: { type: Date, required: true },
});

const emit = defineEmits(["date-selected"]);

const { value } = useField<Date>(() => props.name);

const dateSelected = () => {
    emit("date-selected", value.value);
};

if (props.prefill instanceof Date) {
    value.value = props.prefill;
}

watch(
    () => props.prefill,
    () => {
        value.value = new Date(props.prefill);
    },
);
</script>

<template>
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
        input-class="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text dark:text-input font-normal bg-input dark:bg-input-dark border-2 border-border focus:outline-none focus:ring-1"
        @date-select="dateSelected"
        @blur="dateSelected"
    >
        <template #inputicon>
            <InputIcon class="pi pi-calendar cursor-pointer text-border" />
        </template>
    </Calendar>
</template>
