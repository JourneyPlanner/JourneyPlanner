<script setup lang="ts">
import { useField } from "vee-validate";
// this is not a reusuable component, it's a checkbox for the registration form
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
  <div class="flex w-full items-center text-left mt-2">
    <div class="">
      <label class="relative flex cursor-pointer items-center p-1 rounded-md">
        <input
          type="checkbox"
          :id="id"
          :name="name"
          v-model="value"
          class="peer cursor-pointer appearance-none relative h-5 w-5 bg-input dark:bg-input-dark border-2 border-border transition-all checked:border-border checked:bg-border checked:dark:bg-border rounded-md"
        />
        <div
          class="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 transition-opacity peer-checked:opacity-100"
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
      for="link-checkbox"
      class="ms-2 text-sm text-gray-900 dark:text-gray-300 select-none dark:text-white font-nunito font-light"
      ><T keyName="form.input.text.privacypolicy" />
      <NuxtLink
        to="/privacypolicy"
        class="text-input-label underline dark:text-border"
        ><T keyName="common.privacypolicy"
      /></NuxtLink>
      <span v-if="language === 'de'"> &#0020; gelesen und akzeptiere sie</span>
    </label>
  </div>
  <div class="h-3 text-left">
    <span class="ml-2.5 text-error dark:text-error-dark text-left text-xs">{{
      errorMessage
    }}</span>
  </div>
</template>
