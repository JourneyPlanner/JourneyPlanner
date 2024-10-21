<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const props = defineProps({
    isUnlockDialogVisible: { type: Boolean, required: true },
});

const emit = defineEmits(["closeUnlockDialog", "open-qrcode"]);

const { t } = useTranslate();
const toast = useToast();
const journey = useJourneyStore();

const invite = ref(journey.getInvite());
const isVisible = ref();

watch(
    () => props.isUnlockDialogVisible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("closeUnlockDialog");
};

/**
 * copy the invite link to the clipboard
 */
function copyToClipboard() {
    navigator.clipboard.writeText(invite.value);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}

function openQRCode(tolgeeKey: string) {
    emit("open-qrcode", tolgeeKey);
}
</script>

<template>
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            :block-scroll="true"
            :auto-z-index="true"
            :draggable="false"
            close-on-escape
            dismissable-mask
            class="z-50 mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-6/12 xl:w-5/12"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex gap-x-3 pb-2 font-nunito bg-background dark:bg-background-dark px-4 sm:px-7',
                },
                title: {
                    class: 'font-nunito text-2xl font-semibold text-text dark:text-natural-50',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-4 sm:px-7 h-full',
                },
                footer: { class: 'h-0' },
                closeButtonIcon: {
                    class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10 ',
                },
            }"
            @hide="close"
        >
            <template #header>
                <h3
                    class="text-nowrap text-2xl font-medium text-text dark:text-natural-50"
                >
                    <T key-name="journey.unlock.short" />
                </h3>
                <span
                    class="h-0.5 w-full bg-calypso-300 dark:bg-calypso-600 md:mr-2"
                />
            </template>
            <div
                class="flex flex-col gap-y-5 text-natural-700 dark:text-natural-300"
            >
                <p><T key-name="journey.unlock.detail" /></p>
                <p><T key-name="journey.unlock.link.info" /></p>
                <div class="flex items-center justify-center">
                    <input
                        id="invite-link"
                        class="w-3/6 rounded-md bg-natural-100 px-1 pb-1 pt-1 text-base text-natural-900 focus:outline-none focus:ring-1 dark:bg-natural-900 dark:text-natural-200"
                        disabled
                        :value="invite"
                    />
                    <button
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                        @click="copyToClipboard"
                    >
                        <SvgCopy class="w-4" />
                    </button>
                    <button
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                        @click="openQRCode('journey.unlock.qr')"
                    >
                        <span
                            class="pi pi-qrcode text-text dark:text-natural-50"
                        />
                    </button>
                </div>
                <div class="flex justify-center text-text dark:text-natural-50">
                    <NuxtLink
                        :to="invite"
                        class="mr-4 w-32 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-center font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                    >
                        <T key-name="journey.unlock.button" />
                    </NuxtLink>
                </div>
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="bottom"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-[75%] w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden lg:-z-10"
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
                    <span class="pi pi-angle-down text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="journey.unlock.short" />
                </div>
            </template>
            <div
                class="flex h-full flex-col gap-y-5 pl-6 text-natural-700 dark:text-natural-300"
            >
                <p><T key-name="journey.unlock.detail" /></p>
                <p><T key-name="journey.unlock.link.info" /></p>
                <div class="flex w-full">
                    <input
                        id="invite-link"
                        class="w-4/6 rounded-md bg-natural-100 px-1 pb-1 pt-1 text-base focus:outline-none focus:ring-1 dark:bg-natural-600"
                        disabled
                        :value="invite"
                    />
                    <button
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                        @click="copyToClipboard"
                    >
                        <SvgCopy class="w-4" />
                    </button>
                    <button
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                        @click="openQRCode('journey.unlock.qr')"
                    >
                        <span
                            class="pi pi-qrcode text-text dark:text-natural-50"
                        />
                    </button>
                </div>
                <div
                    class="mt-auto flex w-full justify-center text-text dark:text-natural-50"
                >
                    <NuxtLink
                        class="mt-auto w-full rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-center text-xl font-semibold hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        :to="invite"
                    >
                        <T key-name="journey.unlock.button" />
                    </NuxtLink>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
