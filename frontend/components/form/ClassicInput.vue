<script setup lang="ts">
import { Field, ErrorMessage } from "vee-validate";
defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, default: "" },
    customClass: { type: String, default: "" },
    inputType: { type: String, default: "" },
});
</script>

<template>
    <div class="mb-2">
        <label v-if="translationKey" :for="id" class="font-medium">
            <T :keyName="translationKey" />
        </label>
        <Field type="text" :as="inputType" :id="id" :name="name"
            class="block rounded-lg px-2.5 pb-1 pt-1 w-full text-md text-text font-normal bg-input border-2 border-border focus:outline-none focus:ring-1 peer"
            :class="customClass" placeholder=" " />
        <ErrorMessage :name="name" class="text-error text-sm" />
    </div>
</template>
