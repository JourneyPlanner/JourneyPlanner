<script setup>
import { TolgeeProvider, T } from "@tolgee/vue";
</script>

<template>
  <div>
    <TolgeeProvider>
      <NuxtLayout>
        <NuxtPage />
        <T keyName="translation_key" />
      </NuxtLayout>
      <template v-slot:fallback>
        <div>Loading...</div>
      </template>
    </TolgeeProvider>
  </div>
</template>
