<script setup lang="ts">
// this is not a reusuable component, it's a checkbox for the registration form

import { useField } from "vee-validate";
import { useTolgee } from "@tolgee/vue";

const tolgee = useTolgee();

const language = tolgee.value.getLanguage();

const props = defineProps({
  name: { type: String, required: true },
  id: String,
  type: String,
});

const { value, errorMessage } = useField(() => props.name);
</script>

<template>
  <div class="mt-2 flex w-full items-center text-left">
    <div class="">
      <label class="relative flex cursor-pointer items-center rounded-md p-1">
        <input
          type="checkbox"
          :id="id"
          :name="name"
          v-model="value"
          class="peer relative h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-border bg-input transition-all checked:border-border checked:bg-border dark:bg-input-dark checked:dark:bg-border"
        />
        <div
          class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 transition-opacity peer-checked:opacity-100"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-3.5 w-3.5 fill-background"
            viewBox="0 0 20 20"
            stroke-width="1"
          >
            <path
              fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd"
            ></path>
          </svg>
        </div>
      </label>
    </div>

    <label
      :for="id"
      class="text-gray-900 dark:text-gray-300 ms-2 select-none font-nunito text-sm font-light dark:text-white"
    >
      <T keyName="form.input.text.privacypolicy" />
      <NuxtLink
        to="/privacy-policy"
        class="text-input-label underline dark:text-border"
      >
        <T keyName="common.privacypolicy" />
      </NuxtLink>
      <span v-if="language === 'de'"> gelesen und akzeptiere sie.</span>
    </label>
  </div>
  <div class="mb-2 h-3 text-left">
    <p
      class="ml-9 text-left text-xs text-error dark:font-bold dark:text-error-dark"
    >
      {{ errorMessage }}
    </p>
  </div>
</template>
