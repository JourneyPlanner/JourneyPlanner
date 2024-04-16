<script setup lang="ts">
import { ErrorMessage, Field } from "vee-validate";

defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, default: "" },
    customClass: { type: String, default: "" },
    inputType: { type: String, default: "" },
    icon: { type: String, default: "" },
});
</script>

<template>
    <div class="mb-2">
        <label v-if="translationKey" :for="id" class="font-medium">
            <T :key-name="translationKey" />
        </label>
        <IconField>
            <InputIcon class="pi text-border" :class="icon" />
            <Field
                :id="id"
                type="text"
                :as="inputType"
                :name="name"
                class="text-md peer block w-full rounded-lg border-2 border-border bg-input px-2.5 pb-1 pt-1 font-normal text-text focus:outline-none focus:ring-1"
                :class="customClass"
                placeholder=" "
            />
        </IconField>
        <ErrorMessage :name="name" class="text-sm text-error" />
    </div>
</template>
