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
<<<<<<< HEAD
      class="peer w-full rounded-lg placeholder:text-transparent px-2.5 pb-1 pt-4 text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
      placeholder=" "
      @focus="isFocused = true"
      @blur="isFocused = false"
      @input="$emit('input', $event)"
    />
    <label
      :for="id"
      class="absolute text-input-placeholder left-0 ml-1.5 mt-1 transition-transform -translate-y-0.5 bg-white px-1 text-xs duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-focus:text-input-label peer-placeholder-shown:text-input-placeholder peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs"
    >
      <T :keyName="translationKey"
    /></label>
    <br v-if="errorMessage" />
    <div class="h-1.5">
      <span
        class="ml-4 text-error text-xs"
        :class="{
          invisible: !errorMessage,
          visible: errorMessage,
        }"
        >{{ errorMessage }}</span
      >
    </div>
=======
      class="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
      placeholder=" "
      @focus="isFocused = true"
      @blur="isFocused = false"
    />
    <!-- <ErrorMessage :name="name" class="text-error" /> -->
    <span class="text-error">{{ errorMessage }}</span>
    <label
      :for="id"
      class="absolute text-sm top-0 left-0 px-4 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-4 -translate-x-4 scale-75': isFocused || value,
        'translate-y-0 scale-100': !isFocused && !value,
      }"
    >
      <T :keyName="translationKey"
    /></label>
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
  </div>
</template>
