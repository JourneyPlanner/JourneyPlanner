<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    visible: { type: Boolean, required: true },
    username: { type: String, required: true },
    usernameRegex: { type: RegExp, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "changeUsername"]);
const { t } = useTranslate();

const { errors, handleSubmit, defineField, handleReset } = useForm({
    validationSchema: yup.object({
        newUsername: yup
            .string()
            .matches(props.usernameRegex, () =>
                t.value("dashboard.user.settings.username.invalid"),
            )
            .required(() => t.value("form.input.required")),
    }),
});

const [newUsername] = defineField("newUsername");
newUsername.value = props.username;

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

watch(
    () => props.username,
    (value) => {
        newUsername.value = value;
    },
);

const onSubmit = handleSubmit(() => {
    changeUsername();
});

const close = () => {
    newUsername.value = props.username;
    emit("close");
};

/**
 * emits to userSettings so that the username can be changed there
 */
function changeUsername() {
    emit("changeUsername", newUsername.value);
    handleReset();
}
</script>

<template>
    <div>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="right"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-[95%] w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden ',
                },
                header: {
                    class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50 rounded-3xl',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 -ml-2 sm:pr-12 h-full',
                },
                footer: { class: 'h-0' },
                closeButton: {
                    class: 'justify-start w-full h-full items-center collapse',
                },
                mask: {
                    class: 'sm:collapse bg-natural-50',
                },
            }"
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-left text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="dashboard.user.settings.user.name" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-pt-4 overflow-hidden overflow-ellipsis text-[0.95rem] text-natural-700 dark:text-natural-200"
                >
                    <T
                        key-name="dashboard.user.settings.change.username.description.part1"
                    />
                    <br />
                    <T
                        key-name="dashboard.user.settings.change.username.description.part2"
                    />
                    <br />
                    <T
                        key-name="dashboard.user.settings.change.username.description.part3"
                    />
                    <b> {{ username }} </b>
                </div>
                <div class="flex items-center pl-6 pt-4">
                    <div class="flex w-full flex-col items-center">
                        <div
                            class="mb-2 mr-10 flex w-full items-start text-[0.95rem] text-text dark:text-natural-200"
                        />
                        <input
                            id="username"
                            v-model="newUsername"
                            class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                            @keyup.enter="onSubmit"
                        />
                        <span
                            class="-ml-4 flex justify-start text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.newUsername }}</span
                        >
                    </div>
                </div>
                <div class="mt-auto flex w-full justify-center">
                    <button
                        class="ml-1 mr-6 mt-auto w-full rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-xl font-semibold hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="onSubmit"
                    >
                        <T key-name="common.save" />
                    </button>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
