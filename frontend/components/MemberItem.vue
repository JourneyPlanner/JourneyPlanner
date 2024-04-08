<script setup lang="ts">

const props = defineProps({
    id: { type: String, required: true },
    firstName: { type: String, required: true },
    lastName: { type: String },
    role: { type: Number, required: true }
});

const roleType = computed(() => { return props.role === 1 ? "journey.sidebar.list.guide" : "journey.sidebar.list.member" });
const name = computed(() => {
    if (props.firstName && props.lastName) {
        return props.firstName + " " + props.lastName;
    } else {
        return props.firstName;
    }
});
</script>

<template>
    <div class="flex flex-row justify-between items-center text-text dark:text-input">
        <h2 class="font-medium text-xl  whitespace-nowrap overflow-hidden overflow-ellipsis w-2/3 pr-4 cursor-default"
            v-tooltip.left="name">{{ name }}</h2>
        <div class="rounded-md p-0.5 px-1 w-1/4 text-center"
            :class="role === 1 ? 'bg-chip-blue dark:bg-chip-blue-dark' : 'bg-chip-grey dark:bg-chip-grey-dark'">
            <h3 class="text-base text-text dark:text-input">
                <T :keyName="roleType" />
            </h3>
        </div>
    </div>
</template>
