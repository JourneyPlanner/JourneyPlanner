<script setup lang="ts">
import type { NuxtError } from "#app";

defineProps({
    error: { type: Object as () => NuxtError, required: true },
});
</script>

<template>
    <div class="dark:text-white font-nunito text-text">
        <h1 v-if="error">There was an error.</h1>
        <h1 v-if="error">{{ error.statusCode }}</h1>
        <NuxtLink to="/">Go back home</NuxtLink>
    </div>
</template>
