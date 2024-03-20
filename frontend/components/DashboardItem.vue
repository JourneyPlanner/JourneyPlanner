<script setup lang="ts">
import { format } from 'date-fns';

const props = defineProps({
    name: { type: String, required: true },
    destination: { type: String, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true },
    role: { type: Number, required: true }
});

const roleType = computed(() => { return props.role === 1 ? "dashboard.role.guide" : "dashboard.role.member" });

</script>

<template>
    <div class="relative">
        <SvgDashboardJourney class="" />
        <div class="absolute top-6 left-12">
            <h1 class="font-semibold text-3xl overflow-hidden whitespace-nowrap overflow-ellipsis w-64"
                v-tooltip.top="name">{{
                    name }}</h1>
            <h2 class="text-2xl font-medium" v-tooltip.bottom="destination">{{ destination }}</h2>
            <h3 class="border-b-2 border-dashed border-border-grey w-60">
                <span class="text-border mr-1">
                    <T keyName="dashboard.date" />
                </span>
                <span class="text-text">{{ format(from, "dd/MM/yyyy") }}-{{ format(to,
                    "dd/MM/yyyy") }}</span>
            </h3>
            <h3 class="mt-1 border-b-2 border-dashed border-border-grey w-60 text-sm">
                <span class="text-border mr-1">
                    <T keyName="dashboard.role" />
                </span>
                <span class="text-text">
                    <T :keyName="roleType" />
                </span>
            </h3>
        </div>
    </div>
</template>