<script setup lang="ts">

const props = defineProps({
    id: { type: String, required: true },
    firstName: { type: String, required: true },
    lastName: { type: String },
    role: { type: Number, required: true }
});

const roleType = computed(() => { return props.role === 1 ? "journey.sidebar.list.guide" : "journey.sidebar.list.member" });

</script>

<template>
    <div class="flex flex-row justify-between items-center">
        <h2 class="font-medium text-xl whitespace-nowrap overflow-hidden overflow-ellipsis w-2/3 pr-4">{{
            props.firstName }}
            {{ props.lastName }}</h2>
        <div class="rounded-md p-0.5 px-1 w-1/4 text-center" :class="role === 1 ? 'bg-chip-blue' : 'bg-chip-grey'">
            <h3 class="text-base">
                <T :keyName="roleType" />
            </h3>
        </div>
    </div>
</template>
