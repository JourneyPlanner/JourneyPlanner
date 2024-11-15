<script setup lang="ts">
import { useField } from "vee-validate";

const isFocused = ref(false);

const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    type: { type: String, default: "" },
    autocomplete: { type: String, default: "" },
    translationKey: { type: String, required: true },
    prefill: { type: String, default: "" },
});

const { value, errorMessage } = useField(() => props.name);

if (props.prefill) {
    value.value = props.prefill;
}

defineEmits(["input"]);
</script>

<template>
    <div class="relative mb-4">
        <input
            :id="id"
            v-model="value"
            :type="type || 'text'"
            :name="name"
            :autocomplete="autocomplete || 'off'"
            class="placeholder:text-transparent peer w-full rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-4 text-base font-bold text-text focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50"
            placeholder=" "
            @focus="isFocused = true"
            @blur="isFocused = false"
            @input="$emit('input', $event)"
        />
        <label
            :for="id"
            class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-natural-400 transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-natural-400 peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-calypso-600"
        >
            <T :key-name="translationKey" />
        </label>
        <div
            class="w-full text-left leading-3"
            :class="errorMessage ? '-mb-1.5 mt-0.5 block' : 'hidden'"
        >
            <span
                class="ml-0.5 text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300"
                >{{ errorMessage }}</span
            >
        </div>
    </div>
</template>
