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
<<<<<<< HEAD
<<<<<<< HEAD
      weekLabel="test"
      selectionMode="range"
      :manualInput="true"
      :showButtonBar="true"
      :numberOfMonths="1"
=======
      selectionMode="range"
      :showButtonBar="true"
      :numberOfMonths="2"
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
=======
      selectionMode="range"
      :showButtonBar="true"
      :numberOfMonths="2"
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
      dateFormat="dd/mm/yy"
      inputClass="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1"
      @focus="handleFocus"
      @focusout="handleBlur"
      @input="handleFocus"
<<<<<<< HEAD
<<<<<<< HEAD
      @date-select="$emit('input', $event), handleFocus"
      @clear-click="handleBlur"
      @hide="handleBlur"
      :pt="{
        panel: { class: 'text-text font-nunito' },
        //header: { class: 'flex border-b border-text' },
      }"
    />
    <br />
    <div class="h-3">
      <span class="ml-4 text-error text-xs">{{ errorMessage }}</span>
    </div>
    <label
      for="journey-range-calendar"
      class="absolute text-sm top-0 left-0 pl-4 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-4 -translate-x-7 scale-75': isFocused || value,
=======
=======
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
      @date-select="handleFocus"
      @clear-click="handleBlur"
      @hide="handleBlur"
      :pt="{
        day: { class: 'text-text font-nunito' },
        panel: { class: 'text-text font-nunito' },
        monthPicker: { class: 'text-cta' },
      }"
    />
    <span class="text-error">{{ errorMessage }}</span>
    <label
      for="journey-range-calendar"
      class="absolute text-sm top-0 left-0 px-3.5 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-4 -translate-x-4 scale-75': isFocused || value,
<<<<<<< HEAD
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
=======
>>>>>>> a36efa6789044101f1cc82332b7a4dad2c552c61
        'translate-y-0 scale-100': !isFocused && !value,
      }"
    >
      <T :keyName="translationKey" />
    </label>
  </div>
</template>
