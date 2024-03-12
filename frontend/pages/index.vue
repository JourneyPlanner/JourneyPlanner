<script setup>
const colorMode = useColorMode();
import { useTolgee } from "@tolgee/vue";
const tolgee = useTolgee(["language"]);
const changeLanguage = (e) => {
  tolgee.value.changeLanguage(e.target.value);
};
const { logout } = useSanctumAuth();

async function logoutUser() {
  await logout();
}
</script>

<template>
  <div
    class="flex flex-col justify-center items-center text-center font-nunito"
  >
    <div class="text-center mt-10">
      <h1 class="text-4xl font-bold text-text dark:text-white">
        JourneyPlanner
      </h1>
    </div>
    <div class="flex flex-row gap-5 mt-5">
      <select v-model="colorMode.preference" class="border w-28 h-8">
        <option value="system"><T keyName="common.system" /></option>
        <option value="light">
          <T keyName="common.light" />
        </option>
        <option value="dark"><T keyName="common.dark" /></option>
      </select>

      <select
        :value="tolgee.getLanguage()"
        v-on:change="changeLanguage"
        class="border w-28 h-8"
      >
        <option value="en">English</option>
        <option value="de">Deutsch</option>
      </select>
    </div>
    <div class="mt-5 flex gap-5">
      <NuxtLink to="/register" class="">
        <button
          class="hover:bg-cta-bg dark:hover:bg-cta-bg-dark border-2 border-cta-border text-text min-w-28 py-2 rounded-lg font-bold"
        >
          <T keyName="form.button.register" />
        </button>
      </NuxtLink>
      <button
        class="hover:bg-cta-bg dark:hover:bg-cta-bg-dark border-2 border-cta-border text-text min-w-28 py-2 rounded-lg font-bold"
        @click="logoutUser"
      >
        <T keyName="form.button.logout" />
      </button>
    </div>
  </div>
</template>
