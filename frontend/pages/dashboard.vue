<script setup lang="ts">
definePageMeta({
  middleware: ["sanctum:auth"],
});
</script>

<template>
  <div class="font-nunito bg-surface flex justify-between">
    <h1 class="mt-10 ml-10 h-20 font-bold text-4xl">
      <T keyName="common.dashboard" />
    </h1>
    <NuxtLink to="/journey/new" class="mt-10">
      <button
        class="bg-cta-bg border-2 border-cta-border text-text min-w-44 py-2 rounded-lg font-bold mr-10"
      >
        <T keyName="form.header.journey.create" />
      </button>
    </NuxtLink>
  </div>
</template>
