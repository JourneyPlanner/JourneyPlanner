<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

import scroll from "../utils/scroll";

const { t } = useTranslate();

const title = t.value("startpage.subheading");

useHead({
    title: `JourneyPlanner | ${title}`,
});

const features = ref();

const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");
const icon = computed(() =>
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
        ? "sun"
        : "moon",
);

const changeColorMode = () => {
    if (icon.value === "moon") {
        colorMode.preference = "dark";
    } else {
        colorMode.preference = "light";
    }
};
</script>

<template>
    <div
        class="relative cursor-default overflow-hidden font-nunito text-text dark:text-natural-50"
    >
        <div
            class="mb-10 min-h-72 xs:mb-[30vw] sm:mb-60 md:mb-64 lg:mb-72 xl:mb-[10vw] xl:pb-72"
        >
            <div>
                <SvgStartpagePlaneSmall
                    class="absolute top-36 -z-10 sm:hidden"
                />
                <SvgStartpagePlane
                    class="absolute -right-28 top-48 -z-10 hidden sm:-right-48 sm:top-20 sm:block sm:w-[130%] md:top-16 md:w-[110%] lg:-right-20 lg:top-10 lg:w-full"
                />
            </div>
            <header
                class="relative z-10 mx-2 mt-3 flex flex-row items-center justify-between text-base font-medium text-text md:mx-5"
            >
                <NuxtLink to="/">
                    <h3
                        class="text-sm font-bold text-calypso-400 dark:text-gothic-400 sm:text-base md:text-lg"
                    >
                        JourneyPlanner
                    </h3>
                </NuxtLink>
                <nav class="flex flex-row gap-4">
                    <ul
                        class="hidden flex-row items-center gap-2 text-natural-500 dark:text-natural-600 md:flex"
                    >
                        <li>
                            <button
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-lg"
                                @click="scroll(features)"
                            >
                                <T key-name="startpage.features" />
                                <span
                                    class="pi pi-arrow-down invisible ml-1 mt-0.5 text-sm group-hover:visible"
                                />
                            </button>
                        </li>
                        <li>
                            <NuxtLink
                                to="/about"
                                class="group text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-lg"
                            >
                                <T key-name="startpage.about" />
                                <span
                                    class="pi pi-external-link invisible ml-1.5 mt-0.5 text-sm group-hover:visible"
                                />
                            </NuxtLink>
                        </li>
                    </ul>
                    <ul class="flex flex-row items-center gap-2 xs:gap-4">
                        <li class="flex">
                            <button
                                class="group hover:animate-wiggle"
                                @click="changeColorMode()"
                            >
                                <SvgMoon
                                    v-if="icon === 'moon'"
                                    class="group w-4"
                                />
                                <SvgSun v-else class="group w-4" />
                            </button>
                        </li>
                        <li class="border-l-2 border-natural-300 pl-2">
                            <NuxtLink
                                to="/login"
                                class="rounded-lg border-2 border-dandelion-300 p-1 px-2 text-sm text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 sm:hidden"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                            <NuxtLink
                                to="/login"
                                class="hidden text-sm text-text hover:underline dark:text-natural-50 sm:flex md:text-lg"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li class="hidden sm:flex">
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 p-1 px-2 text-sm text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 md:text-lg"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <div class="ml-5 mt-8 md:mt-14 lg:ml-16 lg:mt-24">
                <h1
                    class="text-2xl font-bold xs:text-3xl sm:text-4xl lg:text-5xl"
                >
                    JourneyPlanner
                </h1>
                <h2
                    class="mt-1 text-lg font-bold text-calypso-500 dark:text-gothic-400 xs:text-xl sm:text-2xl lg:text-3xl"
                >
                    <T key-name="startpage.subheading" />
                </h2>
                <p
                    class="mt-2 w-[75%] text-sm xs:w-[60%] xs:text-base sm:w-96 sm:text-base md:text-lg"
                >
                    <T key-name="startpage.text" />
                </p>
                <div class="mt-2 hidden md:mt-5 lg:flex">
                    <NuxtLink
                        to="/register"
                        class="rounded-lg border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 text-sm font-semibold text-text hover:bg-dandelion-300 dark:bg-ronchi-300 dark:hover:bg-ronchi-400 md:px-4 md:py-2 md:text-base lg:mr-64 lg:text-base lg:font-semibold"
                    >
                        <T key-name="startpage.button.create.journey" />
                    </NuxtLink>
                </div>
            </div>
        </div>
        <div
            id="features"
            ref="features"
            class="mb-16 pt-[10vw] lg:mb-32 lg:pt-10"
        >
            <div class="relative flex flex-row">
                <div class="w-[40rem] max-lg:hidden">
                    <SvgStartpagePeople class="mt-20" />
                </div>
                <div
                    class="flex flex-col max-lg:w-full sm:flex-row sm:justify-center sm:gap-6 lg:ml-20 lg:w-auto lg:flex-col"
                >
                    <div
                        class="mb-16 mt-14 flex justify-center gap-5 xs:mt-5 sm:hidden"
                    >
                        <NuxtLink
                            to="/register"
                            class="rounded-lg border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 text-sm font-semibold text-text hover:bg-dandelion-300 dark:bg-ronchi-300 dark:hover:bg-ronchi-400"
                        >
                            <T key-name="startpage.button.create.journey" />
                        </NuxtLink>
                        <NuxtLink
                            to="/about"
                            class="rounded-lg border-2 border-dandelion-300 bg-natural-50 px-2 py-1 text-sm font-semibold text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                        >
                            <T key-name="startpage.about" />
                        </NuxtLink>
                    </div>
                    <div
                        class="-mt-3 flex flex-col max-lg:items-center xs:-mt-0 lg:flex-row"
                    >
                        <SvgStartpageCalendar
                            class="w-24 hover:animate-wiggle"
                        />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-xl font-semibold md:text-2xl">
                                <T key-name="startpage.calendar.header" />
                            </h4>
                            <p
                                class="mt-0.5 w-[15rem] text-sm sm:w-[12.5rem] md:text-lg lg:w-[30rem]"
                            >
                                <T key-name="startpage.calendar.text" />
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 flex flex-col max-lg:items-center md:mt-6 lg:ml-16 lg:flex-row"
                    >
                        <SvgStartpageShare class="w-24 hover:animate-wiggle" />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-xl font-semibold">
                                <T key-name="startpage.share.header" />
                            </h4>
                            <p
                                class="mt-0.5 w-[15rem] text-sm sm:w-[12.5rem] md:text-lg lg:w-[30rem]"
                            >
                                <T key-name="startpage.share.text" />
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 flex flex-col max-lg:items-center sm:-mt-0 md:mt-0 lg:ml-32 lg:mt-6 lg:flex-row"
                    >
                        <SvgStartpageGlobe class="w-24 hover:animate-wiggle" />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-xl font-semibold">
                                <T key-name="startpage.experience.header" />
                            </h4>
                            <p
                                class="mt-0.5 w-[15rem] text-sm sm:w-[12.5rem] md:text-lg lg:w-[30rem]"
                            >
                                <T key-name="startpage.experience.text" />
                            </p>
                        </div>
                    </div>
                    <div class="mt-5 justify-center max-lg:hidden lg:flex">
                        <NuxtLink
                            to="/register"
                            class="rounded-lg border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 text-sm font-semibold text-text hover:bg-dandelion-300 dark:bg-ronchi-300 dark:hover:bg-ronchi-400 md:px-4 md:py-2 md:text-base lg:text-base lg:font-semibold"
                        >
                            <T key-name="startpage.button.create.journey" />
                        </NuxtLink>
                    </div>
                </div>
            </div>
            <div class="mt-10 justify-center max-sm:hidden sm:flex lg:hidden">
                <NuxtLink
                    to="/register"
                    class="rounded-lg border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 text-sm font-semibold text-text hover:bg-dandelion-300 dark:bg-ronchi-300 dark:hover:bg-ronchi-400 md:px-4 md:py-2 md:text-base lg:mr-[20vw] lg:text-base lg:font-semibold"
                >
                    <T key-name="startpage.button.create.journey" />
                </NuxtLink>
            </div>
        </div>
    </div>
</template>
