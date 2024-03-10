<script setup lang="ts">
import { useField } from "vee-validate";
import Password from "primevue/password";
import { bool, boolean } from "yup";

let isFocused = ref(false);

const handleFocus = () => {
  isFocused.value = true;
};

const handleBlur = () => {
  isFocused.value = false;
};

const props = defineProps({
  name: { type: String, required: true },
  type: String,
  id: String,
  feedback: Boolean,
  translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField(() => props.name);
</script>

<template>
  <div class="relative">
    <!-- TODO: language (de/en)-->
    <Password
      :id="id"
      :name="name"
      v-model="value"
      toggleMask
      class="w-full"
      :feedback="feedback"
      inputClass="block rounded-lg px-2.5 pb-1 pt-4 w-[100%] text-md text-text dark:text-white bg-input border-2 border-border focus:outline-none focus:ring-1 dark:bg-input-dark"
      @focus="handleFocus"
      @focusout="handleBlur"
      @input="handleFocus"
      @date-select="handleFocus"
      @clear-click="handleBlur"
      @hide="handleBlur"
      :pt="{
        input: { class: 'font-medium' },
      }"
    />
    <div class="h-3 text-left">
      <span class="ml-2.5 text-error dark:text-error-dark text-left text-xs">{{
        errorMessage
      }}</span>
    </div>
    <label
      :for="id"
      class="absolute text-sm top-0 left-0 pl-4 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-4 -translate-x-3 scale-75': isFocused || value,
        'translate-y-0 scale-100': !isFocused && !value,
      }"
    >
      <T :keyName="translationKey" />
    </label>
  </div>
</template>
