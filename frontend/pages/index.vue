<script setup lang="ts">
useHead({
    title: "JourneyPlanner",
});

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
        <div class="mb-72 min-h-56 md:min-h-72 xl:mb-96 xl:min-h-[40rem]">
            <div class="">
                <SvgStartpagePlane
                    class="absolute -right-40 top-40 -z-10 w-[170%] sm:-right-48 md:top-16 md:w-[110%] lg:-right-20 lg:top-10 lg:w-full"
                />
            </div>
            <header
                class="relative z-10 mx-5 mt-3 flex flex-row justify-between text-base font-medium text-text"
            >
                <NuxtLink to="/">
                    <h3 class="font-bold text-calypso-400 dark:text-gothic-400">
                        JourneyPlanner
                    </h3>
                </NuxtLink>
                <nav class="flex flex-row gap-4">
                    <ul
                        class="flex flex-row gap-4 text-natural-500 dark:text-natural-600"
                    >
                        <li>
                            <NuxtLink
                                to="/#features"
                                class="hover:text-calypso-500 dark:hover:text-calypso-500"
                            >
                                <a>
                                    <T key-name="startpage.features" />
                                </a>
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/#team"
                                class="hover:text-calypso-500 dark:hover:text-calypso-500"
                            >
                                <a>
                                    <T key-name="startpage.team" />
                                </a>
                            </NuxtLink>
                        </li>
                    </ul>
                    <ul class="flex flex-row gap-4 pl-5">
                        <li class="flex">
                            <button class="group" @click="changeColorMode()">
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
                                class="text-text hover:underline dark:text-natural-50"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 p-1 text-text hover:bg-dandelion-200 dark:text-natural-50 dark:hover:bg-pesto-600"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <div class="ml-5 mt-14 lg:ml-20 lg:mt-24">
                <h1 class="text-3xl font-bold lg:text-5xl">JourneyPlanner</h1>
                <h2
                    class="mt-1 text-xl font-bold text-calypso-500 dark:text-gothic-400 lg:text-3xl"
                >
                    <T key-name="startpage.subheading" />
                </h2>
                <p class="mt-2 w-64 text-base md:w-80 lg:w-96 lg:text-lg">
                    <T key-name="startpage.text" />
                </p>
            </div>
        </div>
        <div id="features" class="mb-20 pt-14">
            <div class="relative flex flex-row">
                <div class="w-[40rem] max-lg:hidden">
                    <SvgStartpagePeople />
                </div>
                <div class="max-lg:w-full lg:ml-20">
                    <div class="flex flex-col max-lg:items-center lg:flex-row">
                        <SvgStartpageCalendar
                            class="w-24 hover:animate-wiggle"
                        />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-2xl font-semibold">
                                <T key-name="startpage.calendar.header" />
                            </h4>
                            <p class="mt-0.5 w-[30rem] text-lg">
                                <T key-name="startpage.calendar.text" />
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-16 flex flex-col max-lg:items-center lg:ml-16 lg:flex-row"
                    >
                        <SvgStartpageShare class="w-24 hover:animate-wiggle" />
                        <div class="ml-4 flex flex-col max-lg:text-center">
                            <h4 class="text-2xl font-semibold">
                                <T key-name="startpage.share.header" />
                            </h4>
                            <p class="mt-0.5 w-[30rem] text-lg">
                                <T key-name="startpage.share.text" />
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-16 flex flex-col max-lg:items-center lg:ml-32 lg:flex-row"
                    >
                        <SvgStartpageGlobe class="w-24 hover:animate-wiggle" />
                        <div class="ml-4 flex flex-col max-lg:text-center">
                            <h4 class="text-2xl font-semibold">
                                <T key-name="startpage.experience.header" />
                            </h4>
                            <p class="mt-0.5 w-[30rem] text-lg">
                                <T key-name="startpage.experience.text" />
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="team" class="mb-20">
            <ScrollPanel>
                <div class="flex items-center justify-center">
                    <div
                        class="align-center flex w-full items-center gap-4 lg:grid lg:grid-cols-4"
                    >
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageSeverin class="w-72" />
                            <p class="text-xl">Severin Rosner</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.scrummaster" />
                            </div>
                        </div>
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageSeverin class="w-72" />
                            <p class="text-xl">Raven Burkard</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.prodcutowner" />
                            </div>
                        </div>
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageRoman class="w-72" />
                            <p class="text-xl">Roman Krebs</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.developer" />
                            </div>
                        </div>
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageSeverin class="w-72" />
                            <p class="text-xl">Stefania Manastirska</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.designer" />
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollPanel>
        </div>
    </div>
</template>
