<script setup lang="ts">
import { ErrorMessage, Field } from "vee-validate";

const props = defineProps({
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

const isValidUrl = computed(() => {
    try {
        new URL(props.value);
        return true;
    } catch (_) {
        return false;
    }
});
</script>

<template>
    <div class="mb-2 flex flex-col">
        <label :for="id" class="text-sm font-medium md:text-base">
            <T :key-name="translationKey" />
        </label>
        <InputGroup>
            <InputGroupAddon
                :disabled="disabled"
                class="border-2 border-calypso-300 bg-natural-50 disabled:bg-natural-100 dark:border-calypso-400 dark:bg-natural-900"
            >
                <i class="pi text-calypso-400" :class="icon" />
            </InputGroupAddon>

            <NuxtLink
                v-if="name === 'link' && isValidUrl && disabled"
                :to="value"
                target="_blank"
                class="w-full overflow-hidden overflow-ellipsis whitespace-nowrap rounded-r-md border-2 border-b-2 border-l-0 border-r-2 border-t-2 border-calypso-300 px-2.5 pb-1 pt-1 font-nunito font-normal text-text hover:underline focus:outline-none focus:ring-1 dark:border-calypso-400 dark:text-natural-50"
                :class="{
                    'bg-natural-100 text-ronchi-500 dark:bg-natural-800':
                        disabled,
                    'bg-natural-50 dark:bg-natural-900': !disabled,
                }"
                >{{ value }}</NuxtLink
            >

            <Field
                v-else
                :id="id"
                type="text"
                :as="inputType"
                :name="name"
                :disabled="disabled"
                :value="value"
                :placeholder="placeholder"
                class="block w-full border-2 border-l-0 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:bg-natural-100 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50 disabled:dark:bg-natural-800"
                :class="customClass"
            />
        </InputGroup>
        <ErrorMessage
            :name="name"
            class="text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300 sm:text-sm"
        />
    </div>
</template>
