<template>
  <TolgeeProvider>
    <NuxtLayout>
      <NuxtPage />
      <label for="test"><T keyName="translation_key" /></label>
      <input type="text" id="test" />
    </NuxtLayout>
    <template v-slot:fallback>
      <div>Loading...</div>
    </template>
  </TolgeeProvider>
</template>
