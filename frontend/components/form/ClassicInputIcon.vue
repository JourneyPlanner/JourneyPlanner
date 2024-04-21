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
    <div class="mb-0 flex flex-col sm:mb-2">
        <label
            v-if="translationKey"
            :for="id"
            class="text-sm font-medium md:text-base"
        >
            <T :key-name="translationKey" />
        </label>
        <IconField :class="customClass">
            <InputIcon class="pi text-border" :class="icon" />
            <Field
                :id="id"
                type="text"
                :as="inputType"
                :name="name"
                class="block w-full rounded-lg border-2 border-border bg-input px-2.5 pb-1 pt-1 font-nunito font-normal text-text focus:outline-none focus:ring-1 dark:bg-input-dark dark:text-input"
                :class="customClass"
                placeholder=" "
            />
        </IconField>
        <ErrorMessage
            :name="name"
            class="text-sm text-error dark:font-bold dark:text-error-dark"
        />
    </div>
</template>
