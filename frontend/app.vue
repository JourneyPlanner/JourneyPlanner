<template>
  <TolgeeProvider>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <template v-slot:fallback>
      <div>Loading...</div>
    </template>
  </TolgeeProvider>
</template>

<style scoped>
body {
  @apply min-h-screen bg-cta dark:bg-error dark:text-text;
}
</style>
