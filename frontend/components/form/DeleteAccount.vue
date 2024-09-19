<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "changeEmail"]);
const { t } = useTranslate();
const password = ref("");
const client = useSanctumClient();
const toast = useToast();

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};

async function deleteAccount() {
    await client(`/api/user/delete-account`, {
        method: "DELETE",
        body: {
            password: password.value,
        },
        async onResponseError({ response }) {
            if (response.status === 401) {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("wrong.password.toast.error"),
                    life: 6000,
                });
            }
        },
    });
    await navigateTo("/");
}
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        :header="t('dashboard.user.settings')"
        class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-1/4 md:rounded-xl"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex justify-start pb-2 font-nunito bg-background dark:bg-background-dark',
            },
            title: {
                class: 'font-nunito text-4xl font-semibold',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-12 h-full',
            },
            footer: { class: 'h-0' },
            icons: {
                class: 'justify-end items-center w-fit pl-10',
            },
            closeButtonIcon: {
                class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10 ',
            },
        }"
        @hide="close"
    >
        <template #header>
            <div class="flex w-[90%] items-center">
                <div class="bg h-0.5 w-5 bg-mahagony-500" />
                <div
                    class="flex-grow-5 dark:text-white mx-3 text-3xl text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.delete.account" />
                </div>
                <div class="bg h-0.5 flex-grow bg-mahagony-500" />
            </div>
        </template>
        <div class="pl-4">
            <div class="-pt-4 text-sm text-natural-700 dark:text-natural-300">
                <T
                    key-name="dashboard.user.settings.delete.account.description.part1"
                />
                <b class="dark:text-natural-50">
                    <T
                        key-name="dashboard.user.settings.delete.account.description.part2"
                /></b>
                <T
                    key-name="dashboard.user.settings.delete.account.description.part3"
                />
                <br />
                <br />
                <T
                    key-name="dashboard.user.settings.delete.account.description.part4"
                />
            </div>
            <div class="flex items-center pl-6 pt-4">
                <div class="flex w-full flex-col items-center">
                    <div
                        class="flex w-2/3 items-start text-text dark:text-natural-50"
                    >
                        <T key-name="common.enter.password" />
                    </div>
                    <input
                        id="password"
                        v-model="password"
                        name="password"
                        type="password"
                        class="w-2/3 rounded-md border-2 border-natural-400 bg-natural-50 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                    />
                </div>
            </div>
            <div class="flex w-full justify-center pb-2 pt-6">
                <button
                    class="w-40 rounded-md bg-natural-50 px-2 hover:underline dark:bg-background-dark dark:text-natural-50"
                    @click="$emit('close')"
                >
                    <T key-name="common.button.cancel" />
                </button>
                <button
                    class="w-40 rounded-md border-2 border-mahagony-500 bg-natural-50 px-2 hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                    @click="deleteAccount"
                >
                    <T key-name="dashboard.user.settings.delete.account" />
                </button>
            </div>
        </div>
    </Dialog>
</template>
