<script setup lang="ts">
import { useTolgee } from "@tolgee/vue";

const colorMode = useColorMode();

useHead({
    title: "JourneyPlanner",
});

const tolgee = useTolgee(["language"]);
const changeLanguage = (e: Event) => {
    tolgee.value.changeLanguage((e?.target as HTMLInputElement)?.value);
};
const { logout } = useSanctumAuth();

async function logoutUser() {
    await logout();
}
</script>

<template>
    <div
        class="flex flex-col items-center justify-center text-center font-nunito"
    >
        <div class="mt-10 text-center">
            <h1 class="text-4xl font-bold text-text dark:text-white">
                JourneyPlanner
            </h1>
        </div>
        <div class="mt-5 flex flex-row gap-5">
            <select v-model="colorMode.preference" class="h-8 w-28 border">
                <option value="system">
                    <T key-name="common.system" />
                </option>
                <option value="light">
                    <T key-name="common.light" />
                </option>
                <option value="dark">
                    <T key-name="common.dark" />
                </option>
            </select>

            <select
                :value="tolgee.getLanguage()"
                class="h-8 w-28 border"
                @change="changeLanguage"
            >
                <option value="en">English</option>
                <option value="de">Deutsch</option>
            </select>
        </div>
        <div class="mt-5 flex gap-5">
            <NuxtLink to="/register" class="">
                <button
                    class="min-w-28 rounded-lg border-2 border-cta-border py-2 font-bold text-text hover:bg-cta-bg dark:hover:bg-cta-bg-dark"
                >
                    <T key-name="form.button.register" />
                </button>
            </NuxtLink>
            <button
                class="min-w-28 rounded-lg border-2 border-cta-border py-2 font-bold text-text hover:bg-cta-bg dark:hover:bg-cta-bg-dark"
                @click="logoutUser"
            >
                <T key-name="form.button.logout" />
            </button>
        </div>
    </div>
</template>
