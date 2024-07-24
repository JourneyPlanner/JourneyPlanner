<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const { t } = useTranslate();

const title = t.value("startpage.subheading");

useHead({
    title: `JourneyPlanner | ${title}`,
});

const END_DATE = new Date("2025-03-28T12:00:00");

const deadline = ref({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
});

const updateCountdown = () => {
    const time = countdown(new Date(), END_DATE);
    deadline.value = {
        days: time.days,
        hours: time.hours,
        minutes: time.minutes,
        seconds: time.seconds,
    };
};

onMounted(() => {
    updateCountdown();
    const interval = setInterval(() => {
        updateCountdown();
        if (
            deadline.value.days === 0 &&
            deadline.value.hours === 0 &&
            deadline.value.minutes === 0 &&
            deadline.value.seconds === 0
        ) {
            clearInterval(interval);
        }
    }, 1000);
});

const idea = ref();
const facts = ref();
const team = ref();

//TODO: Replace with real data from backend
const hours = ref(60);
const userstories = ref(5);
const sprints = ref(2);

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

//TODOS: add cloud
//TODO change lorem ipsum project idea
//TODO redesign startpage
//TODO darkmode
//TODO responsive
</script>

<template>
    <div>
        <div class="relative -z-10">
            <SvgAboutClouds class="absolute top-32" />
        </div>
        <div class="mx-2 text-text dark:text-natural-50 md:mx-5">
            <header
                class="relative z-10 mt-3 flex flex-row items-center justify-between text-base font-medium text-text"
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
                        class="hidden flex-row items-center gap-4 text-natural-500 dark:text-natural-600 md:flex"
                    >
                        <li>
                            <button
                                class="text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-base"
                                @click="scroll(idea)"
                            >
                                <T key-name="about.idea" />
                            </button>
                        </li>
                        <li>
                            <button
                                class="text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-base"
                                @click="scroll(facts)"
                            >
                                <T key-name="about.facts" />
                            </button>
                        </li>
                        <li>
                            <button
                                class="text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-base"
                                @click="scroll(team)"
                            >
                                <T key-name="about.team" />
                            </button>
                        </li>
                    </ul>
                    <ul
                        class="flex flex-row items-center gap-2 md:gap-4 md:pl-5"
                    >
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
                                class="text-sm hover:underline md:text-lg"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 p-1 px-2 text-sm hover:bg-dandelion-200 dark:hover:bg-pesto-600 md:text-lg"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <main class="mt-28 font-nunito">
                <div
                    class="flex flex-col items-center justify-center text-center"
                >
                    <h1
                        class="text-2xl font-extrabold text-calypso-400 dark:text-gothic-400 xs:text-3xl sm:text-4xl lg:text-6xl"
                    >
                        JourneyPlanner
                    </h1>
                    <h2
                        class="mt-1 text-lg font-bold xs:text-xl sm:text-2xl lg:text-3xl"
                    >
                        <T key-name="startpage.subheading" />
                    </h2>
                    <p
                        class="mt-2 w-[75%] text-sm font-medium xs:w-[60%] xs:text-base sm:w-[40%] sm:text-base md:text-lg"
                    >
                        <T key-name="startpage.text.1" />
                    </p>
                    <p
                        class="w-[75%] text-sm font-medium xs:w-[60%] xs:text-base sm:w-[40%] sm:text-base md:text-lg"
                    >
                        <T key-name="startpage.text.2" />
                    </p>
                    <NuxtLink
                        to="/journey/new"
                        class="mt-10 rounded-lg border-2 border-dandelion-300 bg-natural-50 p-1 px-2 text-sm font-bold text-text hover:bg-dandelion-200 dark:text-natural-50 dark:hover:bg-pesto-600 md:text-base"
                    >
                        <T key-name="startpage.button.create.journey" />
                    </NuxtLink>
                </div>
                <div ref="idea" class="ml-10 mt-28 space-y-3">
                    <h4 class="font-nunito text-xl font-bold">
                        <T key-name="about.projectidea.headline" />
                    </h4>
                    <div class="relative grid grid-cols-2 grid-rows-1">
                        <p class="w-[85%] text-base font-medium">
                            <T key-name="about.projectidea.text" />
                        </p>
                        <div
                            class="-mr-2 -mt-16 w-[85%] justify-self-end md:-mr-5"
                        >
                            <SvgStartpagePeople
                                class="scale-x-[-1] transform"
                            />
                        </div>
                    </div>
                </div>
                <div class="mb-10 mt-20 flex flex-row justify-center">
                    <div
                        class="flex w-96 flex-col items-center justify-center rounded-lg bg-natural-100 py-2 text-center font-medium text-natural-700 shadow-md"
                    >
                        <h4 class="font-semibold">
                            <T key-name="about.sponsor" />
                        </h4>
                        <a
                            href="mailto:raven.burkard@journeyplanner.io?cc=contact@journeyplanner.io&subject=Sponsorship"
                            class="text-calypso-400 hover:font-bold"
                        >
                            <T key-name="about.sponsor.contact" />
                        </a>
                    </div>
                </div>
                <div
                    ref="facts"
                    class="flex select-none flex-row justify-center pt-20"
                >
                    <div class="grid grid-cols-3 grid-rows-1 gap-32">
                        <div class="flex flex-col items-center">
                            <SvgAboutTime class="mb-2 w-20" />
                            <h4 class="flex flex-row text-xl font-bold">
                                <ClientOnly>
                                    <AnimatedCounter
                                        :value="hours"
                                        :duration="1000"
                                        class="counter"
                                    />
                                </ClientOnly>
                                <span class="ml-1">h</span>
                            </h4>
                            <h5 class="font-medium text-natural-600">
                                <T key-name="about.facts.time" />
                            </h5>
                        </div>
                        <div class="flex flex-col items-center">
                            <SvgAboutCheckMark class="mb-2 w-20" />
                            <h4 class="flex flex-row text-xl font-bold">
                                <ClientOnly>
                                    <AnimatedCounter
                                        :value="userstories"
                                        :duration="1000"
                                        class="counter"
                                    />
                                </ClientOnly>
                                <span
                                    v-tooltip="{
                                        value: 'Userstories',
                                        pt: { text: 'font-nunito' },
                                        showDelay: 1000,
                                        hideDelay: 300,
                                    }"
                                    class="ml-1 cursor-help"
                                    >US</span
                                >
                            </h4>
                            <h5 class="font-medium text-natural-600">
                                <T key-name="about.facts.userstories" />
                            </h5>
                        </div>
                        <div class="flex flex-col items-center">
                            <SvgAboutCalendar class="mb-2 w-20" />
                            <h4 class="flex flex-row text-xl font-bold">
                                <ClientOnly>
                                    <AnimatedCounter
                                        :value="sprints"
                                        :duration="1000"
                                        class="counter"
                                    />
                                </ClientOnly>
                                <span class="ml-1">Sprints</span>
                            </h4>
                            <h5 class="font-medium text-natural-600">
                                <T key-name="about.facts.sprints" />
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="my-10 flex select-none flex-col justify-center">
                    <h3
                        v-tooltip="{
                            value: '28.3.2024 12:00 Uhr',
                            pt: { text: 'font-nunito' },
                            showDelay: 1000,
                            hideDelay: 300,
                        }"
                        class="cursor-help text-center text-2xl font-bold underline"
                    >
                        Deadline
                    </h3>
                    <div class="flex flex-row justify-center">
                        <div class="mt-8 grid grid-cols-4 grid-rows-1 gap-14">
                            <div class="flex flex-col items-center">
                                <h4 class="text-5xl font-medium">
                                    {{ deadline.days }}
                                </h4>
                                <h5
                                    class="mt-3 text-xl font-medium text-natural-600"
                                >
                                    <T
                                        v-if="deadline.days === 1"
                                        key-name="about.deadline.day"
                                    />
                                    <T v-else key-name="about.deadline.days" />
                                </h5>
                            </div>
                            <div class="flex flex-col items-center">
                                <h4 class="text-5xl font-medium">
                                    {{ deadline.hours }}
                                </h4>
                                <h5
                                    class="mt-3 text-xl font-medium text-natural-600"
                                >
                                    <T
                                        v-if="deadline.hours === 1"
                                        key-name="about.deadline.hour"
                                    />
                                    <T v-else key-name="about.deadline.hours" />
                                </h5>
                            </div>
                            <div class="flex flex-col items-center">
                                <h4 class="text-5xl font-medium">
                                    {{ deadline.minutes }}
                                </h4>
                                <h5
                                    class="mt-3 text-xl font-medium text-natural-600"
                                >
                                    <T
                                        v-if="deadline.minutes === 1"
                                        key-name="about.deadline.minute"
                                    />
                                    <T
                                        v-else
                                        key-name="about.deadline.minutes"
                                    />
                                </h5>
                            </div>
                            <div class="flex flex-col items-center">
                                <h4 class="text-5xl font-medium">
                                    {{ deadline.seconds }}
                                </h4>
                                <h5
                                    class="mt-3 text-xl font-medium text-natural-600"
                                >
                                    <T
                                        v-if="deadline.seconds === 1"
                                        key-name="about.deadline.second"
                                    />
                                    <T
                                        v-else
                                        key-name="about.deadline.seconds"
                                    />
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="team" ref="team" class="mt-32">
                    <h3 class="mb-5 ml-10 mt-10 text-center text-2xl font-bold">
                        <T key-name="about.team.headline" />
                    </h3>
                    <ScrollPanel
                        :pt="{
                            barX: 'hidden',
                        }"
                    >
                        <div
                            class="mx-5 flex items-center justify-center font-nunito"
                        >
                            <div
                                class="align-center flex w-full items-center gap-4 lg:grid lg:grid-cols-4 lg:gap-4"
                            >
                                <div
                                    class="flex w-72 flex-col items-center justify-center lg:w-full"
                                >
                                    <SvgStartpageSeverin
                                        class="w-40 xs:w-56 lg:w-72"
                                    />
                                    <p class="mt-5 text-base md:text-xl">
                                        Severin Rosner
                                    </p>
                                    <div
                                        class="text-sm font-bold text-calypso-500 dark:text-gothic-400 md:text-xl"
                                    >
                                        <T
                                            key-name="startpage.people.scrummaster"
                                        />
                                    </div>
                                    <div
                                        class="mt-3 flex flex-row items-center gap-1"
                                    >
                                        <a
                                            href="https://www.linkedin.com/in/severin-rosner/"
                                            target="_blank"
                                            class="pi pi-linkedin text-2xl"
                                        />
                                        <a
                                            href="mailto:severin.rosner@journeyplanner.io"
                                            class=""
                                        >
                                            <SvgAboutMail class="w-7" />
                                        </a>
                                    </div>
                                </div>
                                <div
                                    class="flex w-72 flex-col items-center justify-center lg:w-full"
                                >
                                    <SvgStartpageRaven
                                        class="w-40 xs:w-56 lg:w-72"
                                    />
                                    <p class="mt-5 text-base md:text-xl">
                                        Raven Burkard
                                    </p>
                                    <div
                                        class="text-sm font-bold text-calypso-500 dark:text-gothic-400 md:text-xl"
                                    >
                                        <T
                                            key-name="startpage.people.productowner"
                                        />
                                    </div>
                                    <div
                                        class="mt-3 flex flex-row items-center gap-1"
                                    >
                                        <a
                                            href="https://www.linkedin.com/in/raven-burkard/"
                                            target="_blank"
                                            class="pi pi-linkedin text-2xl"
                                        />
                                        <a
                                            href="mailto:raven.burkard@journeyplanner.io"
                                            class=""
                                        >
                                            <SvgAboutMail class="w-7" />
                                        </a>
                                    </div>
                                </div>
                                <div
                                    class="flex w-72 flex-col items-center justify-center lg:w-full"
                                >
                                    <SvgStartpageRoman
                                        class="w-40 xs:w-56 lg:w-72"
                                    />
                                    <p class="textbase mt-5 md:text-xl">
                                        Roman Krebs
                                    </p>
                                    <div
                                        class="text-sm font-bold text-calypso-500 dark:text-gothic-400 md:text-xl"
                                    >
                                        <T key-name="startpage.people.member" />
                                    </div>
                                    <div
                                        class="mt-3 flex flex-row items-center gap-1"
                                    >
                                        <a
                                            href="https://www.linkedin.com/in/roman-krebs/"
                                            target="_blank"
                                            class="pi pi-linkedin text-2xl"
                                        />
                                        <a
                                            href="mailto:roman.krebs@journeyplanner.io"
                                            class=""
                                        >
                                            <SvgAboutMail class="w-7" />
                                        </a>
                                    </div>
                                </div>
                                <div
                                    class="flex w-72 flex-col items-center justify-center lg:w-full"
                                >
                                    <SvgStartpageStefi
                                        class="w-40 xs:w-56 lg:w-72"
                                    />
                                    <p class="mt-5 text-base md:text-xl">
                                        Stefania Manastirska
                                    </p>
                                    <div
                                        class="text-sm font-bold text-calypso-500 dark:text-gothic-400 md:text-xl"
                                    >
                                        <T key-name="startpage.people.member" />
                                    </div>
                                    <div
                                        class="mt-3 flex flex-row items-center gap-1"
                                    >
                                        <a
                                            href="https://www.linkedin.com/in/"
                                            target="_blank"
                                            class="pi pi-linkedin text-2xl"
                                        />
                                        <a
                                            href="mailto:stefania.manastirska@journeyplanner.io"
                                            class=""
                                        >
                                            <SvgAboutMail class="w-7" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </ScrollPanel>
                </div>
            </main>
        </div>
    </div>
</template>
