<script setup lang="ts">
import Uppy from "@uppy/core";
import "@uppy/core/dist/style.css";
import "@uppy/dashboard/dist/style.css";
import Tus from "@uppy/tus";
import { Dashboard } from "@uppy/vue";

const journey = useJourneyStore();
const config = useRuntimeConfig();

function getCookieValue(a: string): string {
    const b = document.cookie.match("(^|;)\\s*" + a + "\\s*=\\s*([^;]+)");
    return b ? (b.pop() as string) : "";
}

const uppy = new Uppy({
    meta: { journey: journey.getId() },
}).use(Tus, {
    endpoint: config.public.NUXT_BACKEND_URL + "/tus/",
    headers: {
        "X-XSRF-TOKEN": decodeURIComponent(getCookieValue("XSRF-TOKEN")),
    },
    withCredentials: true,
    removeFingerprintOnSuccess: true,
    chunkSize: 5 * 1024 * 1024,
});
</script>

<template>
    <div class="w-full">
        <Dashboard :uppy="uppy" :props="{}" />
    </div>
</template>

<style>
.uppy-Root {
    @apply font-nunito !important;
}

.uppy-Dashboard .uppy-Dashboard-inner {
    @apply h-40 w-full rounded-2xl border-[3px] border-dashed border-border text-text dark:bg-text dark:text-input sm:h-[13rem] md:h-[17rem] lg:rounded-3xl !important;
}

.uppy-Dashboard-AddFiles {
    @apply border-none !important;
}

.uppy-Dashboard-AddFiles-title {
    @apply font-nunito text-text dark:text-input !important;
}

.uppy-Dashboard-browse {
    @apply text-link decoration-link !important;
}

.uppy-c-btn-primary {
    @apply border-2 border-cta-border bg-cta-bg font-bold text-text dark:border-cta-bg-fill dark:bg-cta-bg-fill !important;
}

.uppy-DashboardContent-bar {
    @apply rounded-t-2xl border-b border-input-placeholder bg-background dark:bg-text lg:rounded-t-3xl !important;
}

.uppy-StatusBar.is-waiting .uppy-StatusBar-actions {
    @apply rounded-b-2xl bg-background dark:bg-text lg:rounded-b-3xl !important;
}

.uppy-Dashboard-progressindicators {
    @apply border-t border-input-placeholder !important;
}
</style>
