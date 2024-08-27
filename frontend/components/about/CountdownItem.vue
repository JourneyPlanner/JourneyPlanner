<script setup lang="ts">
defineProps({
    value: { type: Number, required: true },
    pluralKey: { type: String, required: true },
    singularKey: { type: String, required: true },
});
</script>

<template>
    <div class="flex flex-col items-center">
        <h4
            class="text-3xl font-medium hover-scale sm:text-4xl md:text-5xl lg:text-6xl"
        >
            {{ value }}
        </h4>
        <h5
            class="mt-3 text-base font-medium text-natural-600 dark:text-natural-500 sm:text-xl lg:text-2xl"
        >
            <T v-if="value === 1" :key-name="singularKey" />
            <T v-else :key-name="pluralKey" />
        </h5>
    </div>
</template>
