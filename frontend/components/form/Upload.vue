<script setup lang="ts">
import { useTolgee } from "@tolgee/vue";
import Uppy from "@uppy/core";
import type { Locale } from "@uppy/core";
import "@uppy/core/dist/style.css";
import "@uppy/dashboard/dist/style.css";
import German from "@uppy/locales/lib/de_DE";
import English from "@uppy/locales/lib/en_US";
import Tus from "@uppy/tus";
import { Dashboard } from "@uppy/vue";

const tolgee = useTolgee(["language"]);
const journey = useJourneyStore();
const config = useRuntimeConfig();

const client = useSanctumClient();

let upload_token = localStorage.getItem("upload_token");

if (!upload_token) {
    const { token } = await client("/api/user/tokens/upload");
    localStorage.setItem("upload_token", token || "");
    upload_token = token;
}

let locale: Locale = English;

if (tolgee.value.getLanguage() == "de") {
    locale = German;
}

const uppy = new Uppy({
    meta: { journey: journey.getID() },
    locale: locale,
    restrictions: {
        maxFileSize: 1000000000,
        allowedFileTypes: ["image/*", "video/*", ".pdf", ".txt"],
    },
}).use(Tus, {
    endpoint: config.public.NUXT_UPLOAD_URL as string,
    headers: {
        Authorization: "Bearer " + upload_token,
    },
    removeFingerprintOnSuccess: true,
});
</script>

<template>
    <div class="w-full flex-col">
        <div class="mb-3 flex flex-row justify-between">
            <h1 class="text-2xl font-semibold text-text dark:text-natural-50">
                <T key-name="journey.upload.title" />
            </h1>
            <div class="hidden items-end md:flex">
                <h6
                    class="-mb-3 text-base text-natural-600 dark:text-natural-200"
                >
                    <T key-name="journey.upload.info" />
                </h6>
            </div>
        </div>
        <Dashboard
            :uppy="uppy"
            :props="{
                showProgressDetails: true,
                locale: locale,
            }"
        />
        <div class="flex justify-center md:hidden">
            <h6
                class="absolute bottom-12 text-xs text-natural-600 dark:text-natural-200"
            >
                <T key-name="journey.upload.info" />
            </h6>
        </div>
    </div>
</template>

<style>
.uppy-Root {
    @apply font-nunito !important;
}

.uppy-Dashboard .uppy-Dashboard-inner {
    @apply h-40 w-full rounded-2xl border-[3px] border-dashed border-calypso-300 text-text dark:bg-none dark:text-natural-50 sm:h-[13rem] md:h-[17rem] lg:rounded-3xl !important;
}

.uppy-Dashboard-AddFiles {
    @apply border-none !important;
}

.uppy-Dashboard-AddFiles-title {
    @apply mt-8 font-nunito text-lg text-natural-600 !important;
}

.uppy-StatusBar-status {
    @apply font-nunito text-text dark:text-natural-50 !important;
}

.uppy-Dashboard-browse {
    @apply text-calypso-500 hover:font-semibold hover:underline hover:decoration-calypso-500 dark:text-calypso-600 dark:hover:decoration-calypso-600 !important;
}

.uppy-c-btn-primary {
    @apply bg-dandelion-200 font-nunito text-text dark:bg-natural-900 !important;
}

.uppy-DashboardContent-bar {
    @apply rounded-t-2xl border-b border-natural-600 bg-background dark:bg-text lg:rounded-t-3xl !important;
}

.uppy-StatusBar.is-waiting .uppy-StatusBar-actions {
    @apply rounded-b-2xl bg-background dark:bg-text lg:rounded-b-3xl !important;
}

.uppy-Dashboard-progressindicators {
    @apply border-t border-natural-600 !important;
}

.uppy-StatusBar.is-waiting,
.uppy-StatusBar.is-uploading,
.uppy-StatusBar.is-complete,
.uppy-StatusBar.is-error {
    @apply rounded-b-2xl border-natural-600 bg-natural-50 text-text dark:bg-text dark:text-natural-50 lg:rounded-b-3xl !important;
}

.uppy-DashboardContent-addMore,
.uppy-DashboardContent-back {
    @apply text-link !important;
}

.uppy-StatusBar-actionBtn {
    @apply bg-dandelion-200 font-semibold text-text opacity-100 dark:bg-dandelion-300 !important;
}

.uppy-StatusBar-actionBtn--done {
    @apply text-text !important;
}

.uppy-Dashboard-progressindicators {
    @apply border-none !important;
}

.uppy-Dashboard-AddFilesPanel,
.uppy-Dashboard-innerWrap,
.uppy-Dashboard-inner {
    @apply rounded-2xl bg-natural-50 dark:bg-text lg:rounded-3xl !important;
}

.uppy-Dashboard-AddFilesPanel {
    background: unset !important;
    @apply bg-natural-50 dark:bg-text !important;
}
</style>
