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
    <div class="relative my-5">
        <input
            :id="id"
            v-model="value"
            :type="type || 'text'"
            :name="name"
            :autocomplete="autocomplete || 'off'"
            class="placeholder:text-transparent text-md peer w-full rounded-lg border-2 border-border bg-input px-2.5 pb-1 pt-4 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-dark dark:text-white"
            placeholder=" "
            @focus="isFocused = true"
            @blur="isFocused = false"
            @input="$emit('input', $event)"
        />
        <label
            :for="id"
            class="absolute left-0 ml-1.5 mt-1 -translate-y-0.5 px-1 text-xs text-input-placeholder transition-transform duration-100 ease-linear peer-placeholder-shown:translate-y-2.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-input-placeholder peer-focus:ml-1.5 peer-focus:-translate-y-0.5 peer-focus:px-1 peer-focus:text-xs peer-focus:text-input-label"
        >
            <T :key-name="translationKey" />
        </label>
        <br v-if="errorMessage" />
        <div class="h-1.5 w-full text-left">
            <span
                class="ml-3 text-xs text-error dark:font-bold dark:text-error-dark"
                :class="{
                    invisible: !errorMessage,
                    visible: errorMessage,
                }"
                >{{ errorMessage }}</span
            >
        </div>
    </div>
</template>
