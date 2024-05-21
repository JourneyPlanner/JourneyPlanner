<script setup lang="ts">
useHead({
    title: "JourneyPlanner",
});
</script>

<template>
    <div class="font-nunito">
        <div class="min-h-screen">
            <div class="">
                <SvgStartpagePlane class="absolute -right-80" />
            </div>
            <header
                class="mx-5 mt-3 flex flex-row justify-between text-base font-medium text-text"
            >
                <h3 class="font-bold text-calypso-400">JourneyPlanner</h3>
                <nav>
                    <ul class="flex flex-row gap-4">
                        <li>
                            <a>Start</a>
                        </li>
                        <li>
                            <a>Funktionen</a>
                        </li>
                        <li>
                            <a>Team</a>
                        </li>
                    </ul>
                </nav>
                <nav class="border-l-2 border-natural-300 pl-2">
                    <ul class="flex flex-row gap-4">
                        <li>
                            <NuxtLink to="/login" class="hover:underline">
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 p-1 hover:bg-dandelion-200"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <div class="ml-20 mt-24">
                <h1 class="text-5xl font-bold">JourneyPlanner</h1>
                <h2 class="mt-1 text-2xl font-medium">
                    <T key-name="startpage.subheading" />
                </h2>
                <p class="mt-2 w-96 text-lg">
                    <T key-name="startpage.text" />
                </p>
            </div>
        </div>
        <div class="min-h-screen">
            <div class="relative mt-96 flex flex-row">
                <div class="">
                    <SvgStartpagePeople class="" />
                </div>
                <div class="ml-20">
                    <div class="flex flex-row">
                        <SvgStartpageCalendar class="w-24" />
                        <div class="ml-4 flex flex-col">
                            <h4 class="text-lg font-semibold">
                                <T key-name="startpage.calendar.header" />
                            </h4>
                            <p class="mt-0.5">
                                <T key-name="startpage.calendar.text" />
                            </p>
                        </div>
                    </div>
                    <div class="ml-10 mt-10 flex flex-row">
                        <SvgStartpageShare class="w-24" />
                        <div class="ml-4 flex flex-col">
                            <h4 class="text-lg font-semibold">
                                <T key-name="startpage.share.header" />
                            </h4>
                            <p class="mt-0.5">
                                <T key-name="startpage.share.text" />
                            </p>
                        </div>
                    </div>
                    <div class="ml-20 mt-10 flex flex-row">
                        <SvgStartpageGlobe class="w-24" />
                        <div class="ml-4 flex flex-col">
                            <h4 class="text-lg font-semibold">
                                <T key-name="startpage.experience.header" />
                            </h4>
                            <p class="mt-0.5">
                                <T key-name="startpage.experience.text" />
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen">
            <div class="flex">
                <div>
                    <div>
                        <SvgStartpageSeverin class="w-72" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
