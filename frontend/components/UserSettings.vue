<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const { t } = useTranslate();
const displayname = ref("");
const username = ref("");

const close = () => {
    emit("close");
};

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const emit = defineEmits(["close"]);
</script>
<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        :header="t('dashboard.user.settings')"
        class="z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-4/5 md:rounded-xl"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark',
            },
            title: {
                class: 'font-nunito text-4xl font-semibold',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-12 h-full',
            },
            footer: { class: 'h-0' },
            icons: {
                class: 'justify-end w-full items-center',
            },
            closeButtonIcon: {
                class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10 ',
            },
        }"
        @hide="close"
    >
        <div class="pl-4 pt-4">
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text"
                >
                    <T key-name="dashboard.user.settings.profile" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.display.name" />
                    </div>
                    <T
                        key-name="dashboard.user.settings.display.name.description"
                    />
                </div>
                <div class="flex w-full items-center justify-end">
                    <input
                        id="displayname"
                        :value="displayname"
                        name="displayname"
                        placeholder="irgendwas"
                        class="rounded-md border-2 border-natural-400 bg-natural-100 pl-3 text-text placeholder:text-text hover:border-calypso-400 hover:bg-natural-50"
                    />
                </div>
            </div>
            <div class="flex pb-14 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.user.name" />
                    </div>
                    <T
                        key-name="dashboard.user.settings.user.name.description"
                    />
                </div>
                <div class="flex w-full items-center justify-end">
                    <input
                        id="username"
                        :value="username"
                        name="username"
                        placeholder="irgendwas"
                        class="rounded-md border-2 border-natural-400 bg-natural-100 pl-3 text-text placeholder:text-text hover:border-calypso-400 hover:bg-natural-50"
                    />
                </div>
            </div>
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text"
                >
                    <T key-name="dashboard.user.settings.account.security" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.user.email" />
                    </div>
                    <T
                        key-name="dashboard.user.settings.user.email.description"
                    />
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200"
                    >
                        <T key-name="dashboard.user.settings.email.change" />
                    </button>
                </div>
            </div>
            <div class="flex pb-14 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.password" />
                    </div>
                    <T
                        key-name="dashboard.user.settings.password.description"
                    />
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200"
                    >
                        <T key-name="dashboard.user.settings.password.change" />
                    </button>
                </div>
            </div>
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text"
                >
                    <T key-name="dashboard.user.settings.appearance" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.appearance" />
                    </div>
                    <T
                        key-name="dashboard.user.settings.appearance.description"
                    />
                </div>
                <div class="flex w-full items-center justify-end">
                    <select class="bg-natural-50 px-2">
                        <option>
                            <T
                                key-name="dashboard.user.settings.email.change"
                            />
                        </option>
                        <option>
                            <T
                                key-name="dashboard.user.settings.email.change"
                            />
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </Dialog>
</template>
