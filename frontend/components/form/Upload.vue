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
    <Dashboard :uppy="uppy" class="" />
</template>
