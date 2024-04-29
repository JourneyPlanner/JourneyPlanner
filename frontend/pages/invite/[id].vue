<script setup lang="ts">
const title = "Invite";
useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:auth"],
});

const invite = useRoute().params.id;

const client = useSanctumClient();
const { data, error } = await useAsyncData("journey", () =>
    client(`/api/invite/${invite}`, {
        method: "POST",
    }),
);

if (error.value) {
    showError({
        message: "No journey found for this invite",
        status: 404,
        fatal: true,
    });
}

await navigateTo(`/journey/${data.value.journey.id}`);
</script>

<template>
    <div>
        <p>Joining...</p>
    </div>
</template>
