<script setup lang="ts">
import { ErrorMessage, Field } from "vee-validate";

const props = defineProps({
    id: { type: String, required: true },
    value: { type: String, default: "" },
    name: { type: String, required: true },
    translationKey: { type: String, default: "" },
    customClass: { type: String, default: "" },
    inputType: { type: String, default: "" },
    icon: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    iconPosIsLeft: { type: Boolean, default: false },
});

let addClass = props.customClass;
let iconPos: "left" | "right" = "right";
if (props.iconPosIsLeft) {
    iconPos = "left";
    addClass += " pl-10";
}

const refValue = ref(props.value);
</script>

<template>
    <div class="mb-0 flex flex-col sm:mb-2">
        <label
            v-if="translationKey"
            :for="id"
            class="text-sm font-medium md:text-base"
        >
            <T :key-name="translationKey" />
        </label>
        <IconField :class="customClass" :icon-position="iconPos">
            <InputIcon class="pi text-calypso-400" :class="icon" />
            <Field
                :id="id"
                v-model="refValue"
                type="text"
                :disabled="disabled"
                :as="inputType"
                :name="name"
                class="block w-full rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text focus:outline-none focus:ring-1 disabled:cursor-not-allowed disabled:bg-natural-100 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50 disabled:dark:bg-natural-800"
                :class="addClass"
                placeholder=" "
            />
        </IconField>
        <ErrorMessage
            :name="name"
            class="text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300 sm:text-sm"
        />
    </div>
</template>
