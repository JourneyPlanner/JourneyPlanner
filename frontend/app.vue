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
