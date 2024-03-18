<script setup lang="ts">
const route = useRoute();

console.log(route.params.id);
</script>

<template>
  <div class="w-2/3 h-screen flex justify-end">
    <div class="w-2/3 rounded-xl bg-cta-bg h-10"></div>
  </div>
</template>
