<script setup lang="ts">
import { ErrorMessage, Field } from "vee-validate";

defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, default: "" },
    customClass: { type: String, default: "" },
    inputType: { type: String, default: "" },
    icon: { type: String, default: "" },
    placeholder: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    value: { type: String, default: "" },
});
</script>

<template>
    <div class="mb-2 flex flex-col">
        <label :for="id" class="text-sm font-medium md:text-base">
            <T :key-name="translationKey" />
        </label>
        <InputGroup>
            <InputGroupAddon
                class="border-2 border-border bg-input dark:bg-input-dark"
            >
                <i class="pi text-border" :class="icon" />
            </InputGroupAddon>
            <Field
                :id="id"
                type="text"
                :as="inputType"
                :name="name"
                :disabled="disabled"
                :value="value"
                :placeholder="placeholder"
                class="block w-full border-b-2 border-r-2 border-t-2 border-border bg-input px-2.5 pb-1 pt-1 font-nunito font-normal text-text focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:bg-input-disabled dark:bg-input-dark dark:text-input disabled:dark:bg-input-disabled-dark"
                :class="customClass"
            />
        </InputGroup>
        <ErrorMessage
            :name="name"
            class="text-xs text-error dark:font-bold dark:text-error-dark sm:text-sm"
        />
    </div>
</template>
