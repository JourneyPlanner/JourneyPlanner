<script setup lang="ts">
import { useField } from "vee-validate";
import Password from "primevue/password";
import { useTolgee } from "@tolgee/vue";

const tolgee = useTolgee();
const language = tolgee.value.getLanguage();

const german = computed(() => {
  return language === "de";
});

let isFocused = ref(false);

const handleFocus = () => {
  isFocused.value = true;
};

const handleBlur = () => {
  isFocused.value = false;
};

const props = defineProps({
  name: { type: String, required: true },
  type: String,
  id: String,
  feedback: Boolean,
  translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField(() => props.name);
</script>

<template>
  <div class="relative">
    <Password
      panelClass="dark:bg-background-dark dark:text-white"
      :id="id"
      :name="name"
      v-model="value"
      toggleMask
      class="w-full"
      :feedback="feedback"
      inputClass="block rounded-lg px-2.5 pb-1 pt-4 w-[100%] text-md text-text dark:text-white bg-input border-2 border-border focus:outline-none focus:ring-1 dark:bg-input-dark"
      :prompt-label="$t('form.input.password.label.prompt')"
      :weak-label="$t('form.input.password.label.weak')"
      :medium-label="$t('form.input.password.label.medium')"
      :strong-label="$t('form.input.password.label.strong')"
      @focus="handleFocus"
      @focusout="handleBlur"
      @input="handleFocus"
      @date-select="handleFocus"
      @clear-click="handleBlur"
      @hide="handleBlur"
      :pt="{
        input: { class: 'font-medium' },
      }"
    >
      <template #footer>
        <Divider type="solid" class="text-input-placeholder border border-10" />
        <p class="mt-2">
          <T keyName="form.input.password.prompt.suggestion" />
        </p>
        <ul class="pl-2 ml-2 mt-0 list-disc" style="line-height: 1.5">
          <li>
            <T keyName="form.input.password.prompt.suggestion.lowercase" />
          </li>
          <li>
            <T keyName="form.input.password.prompt.suggestion.uppercase" />
          </li>
          <li><T keyName="form.input.password.prompt.suggestion.numeric" /></li>
          <li>
            <T
              keyName="form.input.password.prompt.suggestion.minimum.characters"
            />
          </li>
        </ul>
      </template>
    </Password>
    <div class="h-3 text-left">
      <span
        class="ml-3 text-error dark:text-error-dark dark:font-bold text-left text-xs"
        >{{ errorMessage }}</span
      >
    </div>
    <label
      :for="id"
      class="absolute text-sm top-0 left-0 pl-3 py-4 pointer-events-none transition-all duration-300"
      :class="{
        'text-input-placeholder': !isFocused,
        'text-input-label': isFocused,
        '-translate-y-4  scale-75': isFocused || value,
        'translate-y-0 scale-100': !isFocused && !value,
        '-translate-x-2': (feedback && isFocused) || (feedback && value),
        '-translate-x-3.5': (!feedback && isFocused) || (!feedback && value),
        'pl-1.5':
          (!feedback && isFocused && german) || (!feedback && value && german),
      }"
    >
      <T :keyName="translationKey" />
    </label>
  </div>
</template>
