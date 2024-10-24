<script setup lang="ts">
import { T } from "@tolgee/vue";

const title = "Invite";
useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:auth"],
});

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
    localStorage.removeItem("JP_invite_journey_id");
    localStorage.removeItem("JP_guest_journey_id");
    await navigateTo(`/journey/${data.value.journey}`);
}
</script>

<template>
    <div class="flex h-screen items-center justify-center">
        <span class="text-3xl"><T key-name="invite.joining" /></span>
    </div>
</template>
