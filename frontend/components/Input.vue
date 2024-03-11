<script setup lang="ts">
import { Field, ErrorMessage } from "vee-validate";
defineProps(["id", "name", "modelValue", "validation", "translationKey"]);
</script>

<template>
  <div class="relative my-5">
    <Field
      type="text"
      :id="id"
      :name="name"
      :value="modelValue"
      class="block rounded-lg px-2.5 pb-1 pt-4 w-full text-md text-text font-bold bg-input border-2 border-border focus:outline-none focus:ring-1 peer"
      placeholder=" "
      :rules="validation"
    />
    <ErrorMessage :name="name" class="text-error" />
    <label
      :for="id"
      class="absolute text-sm text-input-placeholder duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] start-2.5 peer-focus:text-input-label peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto"
      ><T :keyName="translationKey"
    /></label>
  </div>
</template>
