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
        <div class="mb-40 min-h-72 sm:mb-80 md:mb-72 xl:min-h-[40rem]">
            <div>
                <SvgStartpagePlaneSmall
                    class="absolute top-48 -z-10 sm:hidden"
                />
                <SvgStartpagePlane
                    class="absolute -right-28 top-48 -z-10 hidden sm:-right-48 sm:top-20 sm:block sm:w-[130%] md:top-16 md:w-[110%] lg:-right-20 lg:top-10 lg:w-full"
                />
            </div>
            <header
                class="relative z-10 mx-5 mt-3 flex flex-row items-center justify-between text-base font-medium text-text"
            >
                <NuxtLink to="/">
                    <h3
                        class="text-sm font-bold text-calypso-400 dark:text-gothic-400 sm:text-base"
                    >
                        JourneyPlanner
                    </h3>
                </NuxtLink>
                <nav class="flex flex-row gap-4">
                    <ul
                        class="hidden flex-row items-center gap-4 text-natural-500 dark:text-natural-600 md:flex"
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
                    <ul
                        class="flex flex-row items-center gap-2 md:gap-4 md:pl-5"
                    >
                        <li class="flex">
                            <button
                                class="group hidden md:flex"
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
                                class="text-sm text-text hover:underline dark:text-natural-50 md:text-base"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 p-1 text-sm text-text hover:bg-dandelion-200 dark:text-natural-50 dark:hover:bg-pesto-600 md:text-base"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <div class="ml-5 mt-8 md:mt-14 lg:ml-20 lg:mt-24">
                <h1 class="text-3xl font-bold sm:text-4xl lg:text-5xl">
                    JourneyPlanner
                </h1>
                <h2
                    class="mt-1 text-xl font-bold text-calypso-500 dark:text-gothic-400 sm:text-2xl lg:text-3xl"
                >
                    <T key-name="startpage.subheading" />
                </h2>
                <p class="mt-2 w-64 text-base sm:w-96 md:text-lg">
                    <T key-name="startpage.text" />
                </p>
            </div>
        </div>
        <div id="features" class="mb-10 xs:pt-[25vw] sm:pt-0 md:pt-10">
            <div class="relative flex flex-row">
                <div class="w-[40rem] max-lg:hidden">
                    <SvgStartpagePeople class="mt-10" />
                </div>
                <div
                    class="flex flex-col max-lg:w-full sm:flex-row sm:justify-center sm:gap-6 lg:ml-20 lg:w-auto lg:flex-col"
                >
                    <div class="flex flex-col max-lg:items-center lg:flex-row">
                        <SvgStartpageCalendar
                            class="w-24 hover:animate-wiggle"
                        />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-xl font-semibold md:text-2xl">
                                <T key-name="startpage.calendar.header" />
                            </h4>
                            <p
                                class="mt-0.5 w-[15rem] text-base sm:w-[12.5rem] md:text-lg lg:w-[30rem]"
                            >
                                <T key-name="startpage.calendar.text" />
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 flex flex-col max-lg:items-center md:mt-16 lg:ml-16 lg:flex-row"
                    >
                        <SvgStartpageShare class="w-24 hover:animate-wiggle" />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-2xl font-semibold">
                                <T key-name="startpage.share.header" />
                            </h4>
                            <p
                                class="mt-0.5 w-[15rem] text-base sm:w-[12.5rem] md:text-lg lg:w-[30rem]"
                            >
                                <T key-name="startpage.share.text" />
                            </p>
                        </div>
                    </div>
                    <div
                        class="mt-8 flex flex-col max-lg:items-center md:mt-0 lg:ml-32 lg:mt-16 lg:flex-row"
                    >
                        <SvgStartpageGlobe class="w-24 hover:animate-wiggle" />
                        <div class="flex flex-col max-lg:text-center lg:ml-4">
                            <h4 class="text-2xl font-semibold">
                                <T key-name="startpage.experience.header" />
                            </h4>
                            <p
                                class="mt-0.5 w-[15rem] text-base sm:w-[12.5rem] md:text-lg lg:w-[30rem]"
                            >
                                <T key-name="startpage.experience.text" />
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-10 flex justify-center lg:mb-20 lg:justify-end">
            <NuxtLink
                to="/journey/new"
                class="rounded-lg border-2 border-dandelion-300 bg-dandelion-200 px-4 py-2 text-sm font-bold text-text dark:bg-pesto-600 dark:text-natural-50 md:text-base lg:mr-64 lg:text-lg"
            >
                <T key-name="startpage.button.create.journey" />
            </NuxtLink>
        </div>
        <div id="team" class="mb-20">
            <ScrollPanel
                :pt="{
                    barX: 'hidden',
                }"
            >
                <div class="flex items-center justify-center font-nunito">
                    <div
                        class="align-center flex w-full items-center gap-0 lg:grid lg:grid-cols-4 lg:gap-4"
                    >
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageSeverin class="w-72" />
                            <p class="mt-3 text-xl">Severin Rosner</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.scrummaster" />
                            </div>
                        </div>
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageRaven class="w-72" />
                            <p class="mt-3 text-xl">Raven Burkard</p>
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
                            <p class="mt-3 text-xl">Roman Krebs</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.developer" />
                            </div>
                        </div>
                        <div
                            class="flex w-72 flex-col items-center justify-center lg:w-full"
                        >
                            <SvgStartpageStefi class="w-72" />
                            <p class="mt-3 text-xl">Stefania Manastirska</p>
                            <div
                                class="text-xl font-bold text-calypso-500 dark:text-gothic-400"
                            >
                                <T key-name="startpage.people.designer" />
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollPanel>
            <h5 class="ml-14 block text-sm text-natural-400 lg:hidden">
                <T key-name="startpage.team.info" />
            </h5>
        </div>
    </div>
</template>
