<script setup>
const colorMode = useColorMode();
</script>

<template>
  <div>
    <p class="p-4 pb-2">
      <select
        v-model="colorMode.preference"
        class="border w-24 h-8 dark:bg-cta dark:text-text dark:border-cta"
      >
        <option value="system">System</option>
        <option value="light">Light</option>
        <option value="dark">Dark</option>
      </select>
    </p>
  </div>
</template>
