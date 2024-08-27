<script setup lang="ts">
defineProps({
    name: { type: String, required: true },
    positionKey: { type: String, default: "startpage.people.member" },
    roleKey: { type: String, default: "startpage.people.frontend" },
    linkedinLink: { type: String, required: true },
    mail: { type: String, required: true },
});
</script>

<template>
    <div class="flex w-72 flex-col items-center justify-center lg:w-full">
        <slot name="picture" />
        <p class="mt-5 text-base xs:text-lg md:text-xl">
            {{ name }}
        </p>
        <div
            class="text-sm font-bold text-calypso-500 dark:text-gothic-400 xs:text-base md:text-xl"
        >
            <T :key-name="positionKey" />
        </div>
        <div class="text-sm xs:text-base md:text-lg">
            <T :key-name="roleKey" />
        </div>
        <div class="mt-3 flex flex-row items-center gap-1">
            <NuxtLink
                :to="linkedinLink"
                target="_blank"
                class="pi pi-linkedin text-lg text-text hover-scale dark:text-natural-200 sm:text-2xl"
            />
            <NuxtLink :to="'mailto:' + mail" class="hover-scale">
                <SvgAboutMail class="w-5 sm:w-7" />
            </NuxtLink>
        </div>
    </div>
</template>
