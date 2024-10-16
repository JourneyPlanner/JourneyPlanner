<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import QRCode from "qrcode";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const props = defineProps({
    isUnlockDialogVisible: { type: Boolean, required: true },
});

const emit = defineEmits(["closeUnlockDialog"]);

const { t } = useTranslate();
const toast = useToast();
const fullConfig = resolveConfig(tailwindConfig);
const journey = useJourneyStore();

const invite = ref(journey.getInvite());
const isVisible = ref();
const qrcode = ref("");
const isQRCodeVisible = ref(false);

onMounted(() => {
    const colorMode = useColorMode();
    const darkThemeMq = window.matchMedia("(prefers-color-scheme: dark)");
    let darkColor = fullConfig.theme.accentColor["text"] as string;
    let lightColor = fullConfig.theme.accentColor["background"] as string;

    if (
        colorMode.preference === "dark" ||
        (darkThemeMq.matches && colorMode.preference === "system")
    ) {
        darkColor = fullConfig.theme.accentColor["background"] as string;
        lightColor = fullConfig.theme.accentColor["text"] as string;
    }

    const opts = {
        margin: 0,
        color: {
            dark: darkColor,
            light: lightColor,
        },
    };
    QRCode.toDataURL(invite.value, opts, function (error, url) {
        qrcode.value = url;
    });
});

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
            class="z-50 mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-6/12 xl:w-5/12"
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
            <div class="flex flex-col gap-y-5 text-natural-700">
                <p><T key-name="journey.unlock.detail" /></p>
                <p><T key-name="journey.unlock.link.info" /></p>
                <div class="flex items-center justify-center">
                    <input
                        id="invite-link"
                        class="w-3/6 rounded-md bg-natural-100 px-1 pb-1 pt-1 text-base focus:outline-none focus:ring-1 dark:bg-natural-600"
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
                        @click="isQRCodeVisible = true"
                    >
                        <span class="pi pi-qrcode" />
                    </button>
                </div>
                <div class="flex justify-center">
                    <NuxtLink
                        :to="invite"
                        class="mr-4 w-32 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-center font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                    >
                        <T key-name="journey.unlock.button" />
                    </NuxtLink>
                </div>
            </div>
        </Dialog>
        <JourneyIdDialogsQRCodeDialog
            :qrcode="qrcode"
            :visible="isQRCodeVisible"
            :tolgee-key="'journey.unlock.qr'"
            @close="isQRCodeVisible = false"
        />
    </div>
</template>
