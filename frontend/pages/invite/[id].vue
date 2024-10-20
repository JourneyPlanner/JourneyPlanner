<script setup lang="ts">
const title = "Invite";
useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:auth"],
});

onMounted(async () => {
    const invite = useRoute().params.id;
    const client = useSanctumClient();
    const { data, error } = await useAsyncData("invite", () =>
        client(`/api/invite/${invite}`, {
            method: "POST",
        }),
    );

    if (error.value) {
        throw createError({
            message: "No journey found for this invite",
            status: 404,
            fatal: true,
        });
    } else {
        await navigateTo(`/journey/${data.value.journey}`);
    }
});
</script>

<template>
    <div class="flex justify-center">
        <p>Joining...</p>
    </div>
</template>
