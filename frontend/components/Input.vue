<script setup lang="ts">
import { ErrorMessage, Field } from "vee-validate";

defineProps(["id", "name", "modelValue", "validation", "translationKey"]);
</script>

<template>
    <div class="relative my-5">
        <Field
            type="text"
            :id="id"
            :name="name"
            :value="modelValue"
            class="text-md peer block w-full rounded-lg border-2 border-border bg-input px-2.5 pb-1 pt-4 font-bold text-text focus:outline-none focus:ring-1"
            placeholder=" "
            :rules="validation"
        />
        <ErrorMessage :name="name" class="text-error" />
        <label
            :for="id"
            class="absolute start-2.5 top-4 z-10 origin-[0] -translate-y-4 scale-75 transform text-sm text-input-placeholder duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:-translate-y-4 peer-focus:scale-75 peer-focus:text-input-label rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4"
            ><T :keyName="translationKey"
        /></label>
    </div>
</template>
