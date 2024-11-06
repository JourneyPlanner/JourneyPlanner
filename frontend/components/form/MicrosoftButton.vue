<script setup lang="ts">
const client = useSanctumClient();

async function loginWithMicrosoft() {
    const response = await client("/auth/redirect/microsoft");
    navigateTo(response.url, {
        external: true,
    });
}
</script>

<template>
    <button @click="loginWithMicrosoft">
        <SvgMicrosoft />
    </button>
</template>
