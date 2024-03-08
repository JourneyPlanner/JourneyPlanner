<script setup lang="ts">
import { useField } from "vee-validate";

const isFocused = ref(false);

const props = defineProps({
  name: { type: String, required: true },
  type: String,
  id: String,
  translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField(() => props.name);
</script>

<template>
  <div class="relative my-5">
    <input
      :type="type || 'text'"
      :id="id"
      :name="name"
      v-model="value"
      class="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
      placeholder=" "
      @focus="isFocused = true"
      @blur="isFocused = false"
    />
    <span class="text-error text-xs">{{ errorMessage }}</span>
    <label
      :for="id"
      class="absolute text-sm top-0 left-0 px-4 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-3 -translate-x-4 scale-75': isFocused || value,
        'translate-y-0 scale-100': !isFocused && !value,
      }"
    >
      <T :keyName="translationKey"
    /></label>
  </div>
</template>
