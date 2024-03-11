<script setup>
const colorMode = useColorMode();
</script>

<template>
  <div class="flex flex-col justify-center text-center font-nunito">
    <div class="text-center mt-10">
      <h1 class="text-4xl font-bold">JourneyPlanner</h1>
    </div>
    <p class="p-4 pb-2">
      <select v-model="colorMode.preference" class="border w-24 h-8">
        <option value="system">System</option>
        <option value="light">Light</option>
        <option value="dark">Dark</option>
      </select>
    </p>
  </div>
</template>
