<script setup lang="ts">
import Uppy from "@uppy/core";
import "@uppy/core/dist/style.css";
import "@uppy/dashboard/dist/style.css";
import Tus from "@uppy/tus";
import { Dashboard } from "@uppy/vue";

const uppy = new Uppy().use(Tus, {
    endpoint: "https://tusd.tusdemo.net/files/",
});
</script>

<template>
    <Dashboard :uppy="uppy" class="" />
</template>
