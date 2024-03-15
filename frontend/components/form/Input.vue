<script setup lang="ts">
import { useField } from "vee-validate";

const isFocused = ref(false);

const props = defineProps({
  name: { type: String, required: true },
  type: String,
  id: String,
  autocomplete: String,
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
      :autocomplete="autocomplete || 'off'"
      class="peer w-full rounded-lg placeholder:text-transparent px-2.5 pb-1 pt-4 text-md text-text dark:text-white font-bold bg-input border-2 border-border focus:outline-none focus:ring-1 dark:bg-input-dark"
      placeholder=" "
      @focus="isFocused = true"
      @blur="isFocused = false"
      @input="$emit('input', $event)"
    />
    <label
      :for="id"
      class="absolute text-input-placeholder left-0 ml-1.5 mt-1 transition-transform -translate-y-0.5 px-1 text-xs duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-focus:text-input-label peer-placeholder-shown:text-input-placeholder peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs"
    >
      <T :keyName="translationKey"
    /></label>
    <br v-if="errorMessage" />
    <div class="h-1.5 text-left">
      <span
        class="ml-3 text-error dark:text-error-dark dark:font-bold text-xs"
        :class="{
          invisible: !errorMessage,
          visible: errorMessage,
        }"
        >{{ errorMessage }}</span
      >
    </div>
  </div>
</template>
