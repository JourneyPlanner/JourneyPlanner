<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const { t } = useTranslate();

const title = t.value("startpage.subheading");

useHead({
    title: `JourneyPlanner | ${title}`,
});

const END_DATE = new Date("2025-03-28T12:00:00");

const idea = ref();
const facts = ref();
const team = ref();

const time = ref(0);
const userstories = ref(0);
const sprints = ref(0);

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

interface Facts {
    time: number;
    userstories: number;
    sprints: number;
}

const client = useSanctumClient();
const { data } = await useAsyncData<Facts>("facts", () =>
    client("/api/project"),
);
time.value = data?.value?.time || 0;
userstories.value = data?.value?.userstories || 0;
sprints.value = data?.value?.sprints || 0;

onMounted(async () => {
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

//TODO redesign startpage
//TODO darkmode
//TODO responsive
</script>

<template>
    <div>
        <div class="relative -z-10">
            <SvgAboutCloudRight
                class="absolute right-0 top-48 w-[60%] xs:top-40 sm:hidden"
            />
            <SvgAboutCloudLeft
                class="absolute top-64 w-[95%] xs:top-56 sm:hidden"
            />
            <SvgAboutClouds class="absolute top-28 hidden sm:block" />
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
                <nav class="flex flex-row items-center gap-4">
                    <ul
                        class="hidden flex-row items-center gap-2 text-natural-500 dark:text-natural-600 md:flex"
                    >
                        <li>
                            <button
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-base"
                                @click="scroll(idea)"
                            >
                                <T key-name="about.idea" />
                                <span
                                    class="pi pi-arrow-down invisible ml-1 mt-0.5 text-sm group-hover:visible group-hover:animate-bounce"
                                />
                            </button>
                        </li>
                        <li>
                            <button
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-base"
                                @click="scroll(facts)"
                            >
                                <T key-name="about.facts" />
                                <span
                                    class="pi pi-arrow-down invisible ml-1 mt-0.5 text-sm group-hover:visible group-hover:animate-bounce"
                                />
                            </button>
                        </li>
                        <li>
                            <button
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-base"
                                @click="scroll(team)"
                            >
                                <T key-name="about.team" />
                                <span
                                    class="pi pi-arrow-down invisible ml-1 mt-0.5 text-sm group-hover:visible group-hover:animate-bounce"
                                />
                            </button>
                        </li>
                    </ul>
                    <ul class="flex flex-row items-center gap-2 md:gap-4">
                        <li class="flex">
                            <button
                                class="group hidden hover:animate-wiggle sm:block"
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
                                class="hidden text-sm hover:underline sm:block md:text-base"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 px-2 py-1 text-xs hover:bg-dandelion-200 dark:hover:bg-pesto-600 sm:py-0.5 sm:text-sm md:text-base"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <main class="mt-10 font-nunito sm:mt-28">
                <div
                    class="flex flex-col items-center justify-center text-center"
                >
                    <h1
                        class="text-3xl font-extrabold text-calypso-400 hover-scale hover:cursor-default dark:text-gothic-400 xs:text-4xl lg:text-6xl"
                    >
                        JourneyPlanner
                    </h1>
                    <h2
                        class="mt-1 text-base font-bold xs:text-xl sm:text-2xl lg:text-3xl"
                    >
                        <T key-name="startpage.subheading" />
                    </h2>
                    <p
                        class="mt-2 w-[90%] text-sm font-medium text-natural-600 xs:w-[90%] sm:w-[40%] sm:text-base sm:text-text md:text-lg"
                    >
                        <T key-name="startpage.text.1" />
                    </p>
                    <p
                        class="w-[85%] text-sm font-medium text-natural-600 xs:w-[90%] sm:w-[40%] sm:text-base sm:text-text md:text-lg"
                    >
                        <T key-name="startpage.text.2" />
                    </p>
                    <NuxtLink
                        to="/journey/new"
                        class="mt-8 rounded-lg border-2 border-dandelion-300 bg-natural-50 px-3 py-2 text-sm font-bold text-text hover:bg-dandelion-200 dark:text-natural-50 dark:hover:bg-pesto-600 sm:mt-16 md:text-base"
                    >
                        <T key-name="startpage.button.create.journey" />
                    </NuxtLink>
                </div>
                <div
                    ref="idea"
                    class="mt-10 space-y-3 pt-12 text-center sm:ml-10 sm:text-left"
                >
                    <h4 class="font-nunito text-xl font-bold">
                        <T key-name="about.projectidea.headline" />
                    </h4>
                    <div
                        class="relative flex justify-center text-justify sm:grid sm:grid-cols-2 sm:grid-rows-1"
                    >
                        <p class="w-[85%] text-sm font-medium sm:text-base">
                            <T key-name="about.projectidea.text" />
                        </p>
                        <div
                            class="-mr-2 -mt-16 hidden w-[85%] justify-self-end sm:block md:-mr-5"
                        >
                            <SvgStartpagePeople
                                class="scale-x-[-1] transform"
                            />
                        </div>
                    </div>
                </div>
                <div class="mb-10 mt-10 flex flex-row justify-center sm:mt-20">
                    <div
                        class="flex w-72 flex-col items-center justify-center rounded-lg bg-natural-100 py-2 text-center text-sm font-medium text-natural-700 shadow-md sm:w-96 sm:text-base"
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
                    class="-mx-2 flex select-none flex-row justify-center pt-6 sm:pt-20"
                >
                    <div class="grid grid-cols-3 grid-rows-1 gap-2 sm:gap-32">
                        <div class="flex flex-col items-center">
                            <SvgAboutTime class="mb-2 w-20 hover-scale" />
                            <h4
                                class="flex flex-row text-xl font-bold hover-scale"
                            >
                                <ClientOnly v-if="time !== 0">
                                    <AnimatedCounter
                                        :value="time"
                                        :duration="2000"
                                        class="counter"
                                    />
                                </ClientOnly>
                                <span v-else>0</span>
                                <span class="ml-1">h</span>
                            </h4>
                            <h5
                                class="text-sm font-medium text-natural-600 sm:text-base"
                            >
                                <T key-name="about.facts.time" />
                            </h5>
                        </div>
                        <div class="flex flex-col items-center">
                            <SvgAboutCheckMark class="mb-2 w-20 hover-scale" />
                            <h4
                                class="flex flex-row text-xl font-bold hover-scale"
                            >
                                <ClientOnly v-if="userstories !== 0">
                                    <AnimatedCounter
                                        :value="userstories"
                                        :duration="2000"
                                        class="counter"
                                    />
                                </ClientOnly>
                                <span v-else>0</span>
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
                            <h5
                                class="text-sm font-medium text-natural-600 sm:text-base"
                            >
                                <T key-name="about.facts.userstories" />
                            </h5>
                        </div>
                        <div class="flex flex-col items-center">
                            <SvgAboutCalendar class="mb-2 w-20 hover-scale" />
                            <h4
                                class="flex flex-row text-xl font-bold hover-scale"
                            >
                                <ClientOnly v-if="sprints !== 0">
                                    <AnimatedCounter
                                        :value="sprints"
                                        :duration="2000"
                                        class="counter"
                                    />
                                </ClientOnly>
                                <span v-else>0</span>
                                <span class="ml-1">Sprints</span>
                            </h4>
                            <h5
                                class="text-sm font-medium text-natural-600 sm:text-base"
                            >
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
                        class="-ml-10 cursor-help text-center text-2xl font-bold"
                    >
                        <span
                            class="pi pi-circle-fill w-10 animate-blink text-error-dark"
                        />
                        <span class="underline">Deadline</span>
                    </h3>
                    <div class="flex flex-row justify-center">
                        <div
                            class="mt-8 grid grid-cols-4 grid-rows-1 gap-2 sm:gap-14"
                        >
                            <div class="flex flex-col items-center">
                                <h4
                                    class="text-4xl font-medium hover-scale sm:text-5xl"
                                >
                                    {{ deadline.days }}
                                </h4>
                                <h5
                                    class="mt-3 text-base font-medium text-natural-600 sm:text-xl"
                                >
                                    <T
                                        v-if="deadline.days === 1"
                                        key-name="about.deadline.day"
                                    />
                                    <T v-else key-name="about.deadline.days" />
                                </h5>
                            </div>
                            <div class="flex flex-col items-center">
                                <h4
                                    class="text-4xl font-medium hover-scale sm:text-5xl"
                                >
                                    {{ deadline.hours }}
                                </h4>
                                <h5
                                    class="mt-3 text-base font-medium text-natural-600 sm:text-xl"
                                >
                                    <T
                                        v-if="deadline.hours === 1"
                                        key-name="about.deadline.hour"
                                    />
                                    <T v-else key-name="about.deadline.hours" />
                                </h5>
                            </div>
                            <div class="flex flex-col items-center">
                                <h4
                                    class="text-4xl font-medium hover-scale sm:text-5xl"
                                >
                                    {{ deadline.minutes }}
                                </h4>
                                <h5
                                    class="mt-3 text-base font-medium text-natural-600 sm:text-xl"
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
                                <h4
                                    class="text-4xl font-medium hover-scale sm:text-5xl"
                                >
                                    {{ deadline.seconds }}
                                </h4>
                                <h5
                                    class="mt-3 text-base font-medium text-natural-600 sm:text-xl"
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
                <div id="team" ref="team" class="sm:mt-32">
                    <h3
                        class="mt-10 cursor-default text-center text-lg font-bold xs:text-xl sm:ml-10 sm:text-2xl"
                    >
                        <T key-name="about.team.headline" />
                        <NuxtLink
                            to="https://www.htlrennweg.at/"
                            target="_blank"
                            class="cursor-alias hover:text-calypso-500 dark:hover:text-calypso-500"
                            ><span class="underline">HTL Rennweg</span>
                            <span
                                class="pi pi-external-link ml-0.5 text-xs xs:text-sm"
                            />
                        </NuxtLink>
                    </h3>
                    <ScrollPanel
                        :pt="{
                            barX: 'hidden',
                        }"
                    >
                        <div
                            class="mx-5 mt-5 flex cursor-default items-center justify-center font-nunito"
                        >
                            <div
                                class="align-center flex w-full items-center gap-4 lg:grid lg:grid-cols-4 lg:gap-4"
                            >
                                <AboutProjectMemberCard
                                    name="Severin Rosner"
                                    role-key="startpage.people.scrummaster"
                                    linkedin-link="https://www.linkedin.com/in/severin-rosner/"
                                    mail="severin.rosner@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgStartpageSeverin
                                            class="w-40 hover-scale xs:w-52 lg:w-72"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                                <AboutProjectMemberCard
                                    name="Raven Burkard"
                                    role-key="startpage.people.productowner"
                                    linkedin-link="https://www.linkedin.com/in/raven-burkard/"
                                    mail="raven.burkard@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgStartpageRaven
                                            class="w-40 hover-scale xs:w-52 lg:w-72"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                                <AboutProjectMemberCard
                                    name="Roman Krebs"
                                    linkedin-link="https://www.linkedin.com/in/roman-krebs/"
                                    mail="roman.krebs@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgStartpageRoman
                                            class="w-40 hover-scale xs:w-52 lg:w-72"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                                <AboutProjectMemberCard
                                    name="Stefania Manastirska"
                                    linkedin-link="https://www.linkedin.com/in/stefania-manastirska/"
                                    mail="stefania.manastirska@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgStartpageStefi
                                            class="w-40 hover-scale xs:w-52 lg:w-72"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                            </div>
                        </div>
                    </ScrollPanel>
                </div>
            </main>
        </div>
    </div>
</template>
