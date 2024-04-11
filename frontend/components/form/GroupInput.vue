<script setup lang="ts">
import { Field } from "vee-validate";
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
        <label :for="id" class="font-medium">
            <T :keyName="translationKey" />
        </label>
        <InputGroup>
            <InputGroupAddon class="bg-input border-border border-2">
                <i class="pi text-border" :class="icon"></i>
            </InputGroupAddon>
            <Field type="text" :as="inputType" :id="id" :name="name"
                class="block px-2.5 pb-1 pt-1 w-full text-md text-text font-normal bg-input border-r-2 border-t-2 border-b-2 border-border focus:outline-none focus:ring-1"
                :class="customClass" placeholder=" " />
        </InputGroup>
    </div>
</template>
