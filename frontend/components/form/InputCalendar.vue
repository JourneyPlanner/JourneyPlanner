<script setup lang="ts">
const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    prefill: { type: Date, default: "" },
    from: { type: Date, required: true },
    to: { type: Date, required: true },
});

const emit = defineEmits(["date-selected"]);

const { value } = useField<Date>(() => props.name);

if (props.prefill) {
    value.value = props.prefill;
}

const dateSelected = () => {
    emit("date-selected", value.value);
};

watch(
    () => props.prefill,
    () => {
        value.value = props.prefill;
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
        input-class="block rounded-lg px-2.5 pb-1 pt-1 text-md text-text font-normal bg-input border-2 border-border focus:outline-none focus:ring-1"
        @date-select="dateSelected"
        @blur="dateSelected"
    />
</template>
