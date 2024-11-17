<script setup lang="ts">
// this is not a reusuable component, it's a checkbox for the registration form
import { useTolgee } from "@tolgee/vue";
import { useField } from "vee-validate";

const tolgee = useTolgee();

const language = tolgee.value.getLanguage();

const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, default: "checkbox" },
    type: { type: String, default: "checkbox" },
});

const { value, errorMessage } = useField(() => props.name);
</script>

<template>
    <div class="mt-2 flex w-full items-center text-left">
        <div class="">
            <label
                class="relative flex cursor-pointer items-center rounded-md p-1"
            >
                <input
                    :id="id"
                    v-model="value"
                    type="checkbox"
                    :name="name"
                    class="peer relative h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-calypso-400 bg-natural-50 transition-all checked:border-calypso-400 checked:bg-calypso-400 dark:bg-natural-800 checked:dark:bg-calypso-500"
                />
                <div
                    class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-natural-50 opacity-0 transition-opacity peer-checked:opacity-100"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5 fill-natural-50"
                        viewBox="0 0 20 20"
                        stroke-width="1"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
            </label>
        </div>

        <label
            :for="id"
            class="ms-2 select-none font-nunito text-sm font-light text-text dark:text-natural-50"
        >
            <T key-name="form.input.text.privacypolicy" />
            <NuxtLink
                to="/privacy"
                class="text-text underline hover:text-calypso-500 dark:text-natural-50 dark:hover:text-calypso-300"
            >
                <T key-name="common.privacypolicy" />
            </NuxtLink>
            <span v-if="language === 'de'"> gelesen und akzeptiere sie.</span>
        </label>
    </div>
    <div class="mb-2 h-3 text-left">
        <p
            class="ml-9 text-left text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300"
        >
            {{ errorMessage }}
        </p>
    </div>
</template>
