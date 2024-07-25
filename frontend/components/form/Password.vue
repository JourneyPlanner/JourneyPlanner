<script setup lang="ts">
import { useTolgee } from "@tolgee/vue";
import Password from "primevue/password";
import { useField } from "vee-validate";

const tolgee = useTolgee();
const language = tolgee.value.getLanguage();

const german = computed(() => {
    return language === "de";
});

const isFocused = ref(false);

const handleFocus = () => {
    isFocused.value = true;
};

const handleBlur = () => {
    isFocused.value = false;
};

const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    feedback: Boolean,
    feedbackStyle: Boolean,
    translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField<string>(() => props.name);
</script>

<template>
    <div class="relative">
        <Password
            :id="id"
            v-model="value"
            panel-class="dark:bg-natural-800 dark:text-natural-50"
            :name="name"
            toggle-mask
            class="w-full"
            :feedback="feedback"
            input-class="block rounded-lg px-2.5 pb-1 pt-4 w-[100%] text-md text-text dark:text-natural-50 bg-natural-50 border-2 border-calypso-300 dark:border-calypso-400 focus:outline-none focus:ring-1 dark:bg-natural-800"
            :prompt-label="$t('form.input.password.label.prompt')"
            :weak-label="$t('form.input.password.label.weak')"
            :medium-label="$t('form.input.password.label.medium')"
            :strong-label="$t('form.input.password.label.strong')"
            :pt="{
                input: { class: 'font-medium' },
            }"
            @focus="handleFocus"
            @focusout="handleBlur"
            @input="handleFocus"
            @date-select="handleFocus"
            @clear-click="handleBlur"
            @hide="handleBlur"
        >
            <template #footer>
                <Divider
                    type="solid"
                    class="border-10 border text-natural-200"
                />
                <p class="mt-2">
                    <T key-name="form.input.password.prompt.suggestion" />
                </p>
                <ul class="ml-2 mt-0 list-disc pl-2" style="line-height: 1.5">
                    <li>
                        <T
                            key-name="form.input.password.prompt.suggestion.lowercase"
                        />
                    </li>
                    <li>
                        <T
                            key-name="form.input.password.prompt.suggestion.uppercase"
                        />
                    </li>
                    <li>
                        <T
                            key-name="form.input.password.prompt.suggestion.numeric"
                        />
                    </li>
                    <li>
                        <T
                            key-name="form.input.password.prompt.suggestion.minimum.characters"
                        />
                    </li>
                </ul>
            </template>
        </Password>
        <div class="h-3 text-left">
            <span
                class="ml-3 text-left text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300"
                >{{ errorMessage }}</span
            >
        </div>
        <label
            :for="id"
            class="pointer-events-none absolute left-0 top-0 py-4 pl-3 text-sm transition-all duration-300"
            :class="{
                'text-natural-400': !isFocused,
                'text-calypso-600': isFocused,
                '-translate-y-4  scale-75': isFocused || value,
                'translate-y-0 scale-100': !isFocused && !value,
                '-translate-x-2':
                    (feedbackStyle && isFocused) || (feedbackStyle && value),
                '-translate-x-3.5':
                    (!feedbackStyle && isFocused) || (!feedbackStyle && value),
                'pl-1.5':
                    (!feedbackStyle && isFocused && german) ||
                    (!feedbackStyle && value && german),
            }"
        >
            <T :key-name="translationKey" />
        </label>
    </div>
</template>
