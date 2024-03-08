<script setup lang="ts">
import { useField } from "vee-validate";
import Calendar from "primevue/calendar";

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
  translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField(() => props.name);
</script>

<template>
  <div class="relative">
    <!-- TODO: language (de/en)-->
    <Calendar
      :id="id"
      :name="name"
      v-model="value as string"
      selectionMode="range"
      :manualInput="true"
      :showButtonBar="true"
      :numberOfMonths="2"
      dateFormat="dd/mm/yy"
      inputClass="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
      @focus="handleFocus"
      @focusout="handleBlur"
      @input="handleFocus"
      @date-select="handleFocus"
      @clear-click="handleBlur"
      @hide="handleBlur"
      :pt="{
        day: { class: 'text-text font-nunito' },
        panel: { class: 'text-text font-nunito' },
        monthPicker: { class: 'text-cta' },
      }"
    />
    <br />
    <span class="text-error text-xs">{{ errorMessage }}</span>
    <label
      for="journey-range-calendar"
      class="absolute text-sm top-0 left-0 px-4 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-4 -translate-x-7 scale-75': isFocused || value,
        'translate-y-0 scale-100': !isFocused && !value,
      }"
    >
      <T :keyName="translationKey" />
    </label>
  </div>
</template>
