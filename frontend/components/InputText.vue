<template>
  <input
    v-model="value"
    :type="type || 'text'"
    class="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
  />
  {{ errorMessage }}
</template>

<script setup lang="ts">
import { useField } from "vee-validate";

const props = defineProps({
  name: String,
  type: String,
});

// The `name` is returned in a function because we want to make sure it stays reactive
// If the name changes you want `useField` to be able to pick it up
const { value, errorMessage } = useField(() => props.name);
</script>
