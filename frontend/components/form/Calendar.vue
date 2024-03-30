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
    <Calendar :id="id" :name="name" v-model="value" selectionMode="range" :manualInput="true"
      :showButtonBar="true" :numberOfMonths="1" dateFormat="dd/mm/yy"
      panelClass="bg-input dark:bg-background-dark dark:text-white"
      inputClass="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text dark:text-white font-bold bg-input dark:bg-input-dark border-2 border-border focus:outline-none focus:ring-1"
      @focus="handleFocus" @hide="handleBlur" @input="handleFocus" @date-select="$emit('input', $event), handleFocus"
      :pt="{
      panel: { class: 'text-text font-nunito' },
      header: {
        class:
          'flex border-b bg-input dark:bg-background-dark dark:text-white',
      },
      dayLabel: { class: 'text-border' },
      datepickerMask: { class: 'text-text bg-background-dark' },
    }" />
    <br />
    <div class="h-3">
      <span class="ml-2.5 text-error dark:font-bold dark:text-error-dark text-xs">{{ errorMessage }}</span>
    </div>
    <label for="journey-range-calendar"
      class="absolute text-sm top-0 left-0 pl-2.5 py-4 pointer-events-none transition-all duration-300" :class="{
      'text-input-placeholder': !isFocused,
      'text-input-label': isFocused,
      '-translate-y-4 -translate-x-5 scale-75': isFocused || value,
      'translate-y-0 scale-100': !isFocused && !value,
    }">
      <T :keyName="translationKey" />
    </label>
  </div>
</template>
