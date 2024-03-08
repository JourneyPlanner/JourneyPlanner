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
      class="peer w-full rounded-lg placeholder:text-transparent px-2.5 pb-1 pt-4 text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
      placeholder=" "
      @focus="isFocused = true"
      @blur="isFocused = false"
    />
    <label
      :for="id"
      class="absolute left-0 ml-1.5 mt-1 transition-transform -translate-y-0.5 bg-white px-1 text-xs duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-focus:text-input-label peer-placeholder-shown:text-input-placeholder peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs"
    >
      <T :keyName="translationKey"
    /></label>
    <span class="text-error text-xs">{{ errorMessage }}</span>
  </div>
</template>
