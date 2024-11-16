<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useField } from "vee-validate";

const { t } = useTranslate();
const props = defineProps({
    name: { type: String, required: true },
    id: { type: String, required: true },
    journeyStart: { type: Date, required: true },
    journeyEnd: { type: Date, required: true },
});

const repeatModes = ref([
    { name: t.value("activity.repeat.not") },
    { name: t.value("activity.repeat.daily") },
    { name: t.value("activity.repeat.weekly") },
    { name: t.value("activity.repeat.custom") },
]);

const showCustomizeRepeat = ref(false);

const { value, errorMessage } = useField<object>(() => props.name);

const input = () => {
    emit("input", value.value.name);
};

const emit = defineEmits(["input"]);
</script>

<template>
    <div>
        <div>
            <label for="repeat" class="text-sm font-medium md:text-base">
                <T key-name="activity.repeat" />
            </label>
            <Dropdown
                :id="id"
                v-model="value"
                :name="name"
                :options="repeatModes"
                option-label="name"
                :placeholder="t('activity.repeat.not')"
                :highlight-on-select="false"
                :focus-on-hover="false"
                :unstyled="true"
                class="w-full"
                :pt="{
                    root: {
                        class: 'flex font-nunito block rounded-lg px-2.5 pb-1 pt-1 text-text dark:text-natural-50 font-normal border-2 border-calypso-600 focus:outline-none focus:ring-1 bg-natural-50 dark:bg-natural-800 text-text dark:text-natural-50 disabled:bg-natural-100 disabled:dark:bg-natural-800',
                    },
                    input: {
                        class: 'text-text w-fit dark:text-natural-50 rounded-md ',
                    },
                    item: {
                        class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                    },
                    wrapper: {
                        class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                    },
                    trigger: {
                        class: 'w-fit ml-auto',
                    },
                }"
                @change="input"
            >
                <template #value="slotProps">
                    <div v-if="slotProps.value" class="align-items-center flex">
                        <div>
                            {{ slotProps.value.name }}
                        </div>
                    </div>
                    <span v-else>
                        {{ slotProps.placeholder }}
                    </span>
                </template>
                <template #option="slotProps">
                    <div class="align-items-center flex items-center">
                        <span :class="slotProps.option.code" class="pr-2" />
                        <div>
                            {{ slotProps.option.name }}
                        </div>
                    </div>
                </template>
                <template #dropdownicon>
                    <InputIcon class="pi pi-calendar text-calypso-400" />
                </template>
            </Dropdown>
        </div>
        <CustomRepeat
            :visible="showCustomizeRepeat"
            :start-date="props.journeyStart"
            :end-date="props.journeyEnd"
            @close="
                showCustomizeRepeat = false;
                value = '';
            "
        />
        <span
            class="text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300 sm:text-sm"
            >{{ errorMessage }}</span
        >
    </div>
</template>
