<script setup lang="ts">
import type { NuxtError } from "#app";

const props = defineProps({
    error: Object as () => NuxtError,
});
</script>

<template>
    <div class="font-nunito text-text dark:text-white">
        <h1 v-if="error">There was an error.</h1>
        <h1 v-if="error">{{ error.statusCode }}</h1>
        <NuxtLink to="/">Go back home</NuxtLink>
    </div>
</template>
