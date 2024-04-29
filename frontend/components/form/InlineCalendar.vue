<script setup lang="ts">
const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    prefill: { type: Date, default: null },
    from: { type: Date, required: true },
    to: { type: Date, required: true },
});

const emit = defineEmits(["date-selected"]);

interface SlotProps {
    date: {
        day: number;
        month: number;
        year: number;
    };
}

const toDate = props.to.getDate();
const fromDate = props.from.getDate();
const toMonth = props.to.getMonth();
const fromMonth = props.from.getMonth();
const toYear = props.to.getFullYear();
const fromYear = props.from.getFullYear();

const { value } = useField<Date>(() => props.name);

if (props.prefill instanceof Date) {
    value.value = props.prefill;
}

watch(
    () => props.prefill,
    () => {
        value.value = new Date(props.prefill);
    },
);

const dateSelected = () => {
    emit("date-selected", value.value);
};

function checkJourneyRange(slotProp: SlotProps) {
    return (
        slotProp.date.day >= fromDate &&
        slotProp.date.day <= toDate &&
        slotProp.date.month >= fromMonth &&
        slotProp.date.month <= toMonth &&
        slotProp.date.year >= fromYear &&
        slotProp.date.year <= toYear
    );
}
</script>

<template>
    <Calendar
        id="calendar-inline"
        v-model="value"
        :min-date="from"
        :max-date="to"
        inline
        show-week
        name="date"
        class="rounded-lg border border-border-grey"
        date-format="dd/mm/yy"
        :pt="{
            root: { class: 'font-nunito text-text bg-input border-0' },
            title: { class: 'flex gap-1 text-text dark:text-input' },
            header: {
                class: 'bg-input dark:bg-input-dark text-text dark:text-input',
            },
            panel: {
                class: 'font-nunito text-text bg-input dark:bg-input-dark text-border dark:text-border',
            },
        }"
        @date-select="dateSelected"
    >
        <template #date="slotProps">
            <strong
                v-if="checkJourneyRange(slotProps)"
                style="font-weight: 800"
                >{{ slotProps.date.day }}</strong
            >
            <template v-else>{{ slotProps.date.day }}</template>
        </template>
    </Calendar>
</template>
