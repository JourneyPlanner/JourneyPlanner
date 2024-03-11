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

<style lang="postcss">
/* default background in light and dark: mode  */
body {
  @apply min-h-screen bg-background dark:bg-background-dark;
}
</style>
