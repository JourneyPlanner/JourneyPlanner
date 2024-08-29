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
</script>

<template>
    <div>
        <div class="relative -z-10">
            <SvgAboutCloudRight
                class="absolute right-0 top-20 w-[60%] xs:top-24 sm:hidden"
            />
            <SvgAboutCloudLeft
                class="absolute top-72 w-[95%] xs:top-80 sm:hidden"
            />
            <SvgAboutCloudRight
                class="absolute right-0 w-[60%] xs:top-[33rem] sm:hidden"
            />
            <SvgAboutCloudLeft
                class="absolute w-[95%] xs:top-[66rem] sm:hidden"
            />
            <SvgAboutCloudLeft
                class="absolute hidden w-[55%] xs:top-[70rem] md:block"
            />
            <SvgAboutClouds class="absolute top-48 hidden sm:block lg:top-28" />
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
                        class="hidden flex-row items-center gap-2 text-natural-500 dark:text-natural-600 sm:flex"
                    >
                        <li>
                            <button
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-lg"
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
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-lg"
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
                                class="group flex items-center text-sm hover:text-calypso-500 dark:hover:text-calypso-500 md:text-lg"
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
                                class="group hidden hover:animate-wiggle xs:block"
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
                                class="hidden text-sm text-text hover:underline dark:text-natural-50 xs:block md:text-base"
                            >
                                <T key-name="form.button.login" />
                            </NuxtLink>
                        </li>
                        <li>
                            <NuxtLink
                                to="/register"
                                class="rounded-lg border-2 border-dandelion-300 px-2 py-1 text-xs text-text hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 sm:py-0.5 sm:text-sm md:text-base"
                            >
                                <T key-name="form.button.register" />
                            </NuxtLink>
                        </li>
                    </ul>
                </nav>
            </header>
            <main class="mt-10 flex flex-col font-nunito sm:mt-28">
                <div
                    id="hero"
                    class="flex flex-col items-center justify-center text-center"
                >
                    <h1
                        class="mt-16 text-4xl font-extrabold text-calypso-400 hover-scale hover:cursor-default dark:text-gothic-400 xs:mt-24 xs:text-5xl lg:mt-2 lg:text-7xl"
                    >
                        JourneyPlanner
                    </h1>
                    <h2
                        class="mt-1 text-base font-bold text-text dark:text-natural-200 xs:text-xl sm:text-2xl lg:text-3xl"
                    >
                        <T key-name="startpage.subheading" />
                    </h2>
                    <div
                        class="mt-4 w-[90%] text-sm font-medium text-natural-600 dark:text-natural-50 xs:w-[90%] xs:text-base sm:w-[40%] sm:text-base sm:text-text md:text-lg lg:mt-6"
                    >
                        <p>
                            <T key-name="startpage.text.1" />
                        </p>
                        <p>
                            <T key-name="startpage.text.2" />
                        </p>
                    </div>
                    <NuxtLink
                        to="/journey/new"
                        class="mt-20 rounded-lg border-2 border-dandelion-300 bg-dandelion-100 px-3 py-2 text-sm font-bold text-text hover-scale hover:bg-dandelion-200 dark:bg-pesto-600 dark:text-natural-50 xs:mt-20 sm:mt-16 md:text-base lg:mt-20 lg:text-lg"
                    >
                        <T key-name="startpage.button.create.journey" />
                    </NuxtLink>
                    <div class="hidden lg:mt-2 lg:block">
                        <span
                            class="pi pi-arrow-down mt-[14vh] animate-bounce text-2xl text-calypso-400 hover:cursor-pointer dark:text-gothic-400"
                            @click="scroll(idea)"
                        />
                    </div>
                </div>
                <div
                    id="idea"
                    ref="idea"
                    class="mt-16 space-y-3 pt-10 text-center xs:mt-20 sm:pt-20 md:ml-10 md:text-left lg:mt-7 lg:pt-14"
                >
                    <h4
                        class="font-nunito text-xl font-bold text-text underline dark:text-natural-50 xs:text-2xl lg:text-3xl"
                    >
                        <T key-name="about.projectidea.headline" />
                    </h4>
                    <div
                        class="relative flex justify-center md:grid md:grid-cols-2 md:grid-rows-1"
                    >
                        <p
                            class="w-[85%] text-sm font-medium text-natural-800 dark:text-natural-200 xs:text-lg sm:text-base lg:text-xl"
                        >
                            <T key-name="about.projectidea.text" />
                        </p>
                        <div
                            class="-mr-5 hidden justify-self-end md:mt-10 md:block md:w-[95%] lg:-mt-4 lg:w-[85%]"
                        >
                            <SvgStartpagePeople
                                class="scale-x-[-1] transform"
                            />
                        </div>
                    </div>
                </div>
                <div
                    id="facts"
                    ref="facts"
                    class="flex select-none flex-col justify-center pt-16 xs:mt-4 sm:pt-20 lg:pt-16 xl:mt-10"
                >
                    <h3
                        v-tooltip="{
                            value: '28.3.2024 12:00 Uhr',
                            pt: { text: 'font-nunito' },
                            showDelay: 1000,
                            hideDelay: 300,
                        }"
                        class="-ml-10 cursor-help text-center text-xl font-bold xs:-ml-5 xs:text-2xl lg:text-3xl"
                    >
                        <span
                            class="pi pi-circle-fill mr-2.5 w-2.5 animate-blink text-error-dark lg:mr-4"
                        />
                        <span class="text-text underline dark:text-natural-50"
                            >Deadline</span
                        >
                    </h3>
                    <div class="flex flex-row justify-center">
                        <div
                            class="mt-8 grid grid-cols-4 grid-rows-1 gap-0 sm:gap-8 md:gap-14 lg:mt-7"
                        >
                            <AboutCountdownItem
                                :value="deadline.days"
                                plural-key="about.deadline.days"
                                singular-key="about.deadline.day"
                            />
                            <AboutCountdownItem
                                :value="deadline.hours"
                                plural-key="about.deadline.hours"
                                singular-key="about.deadline.hour"
                            />
                            <AboutCountdownItem
                                :value="deadline.minutes"
                                plural-key="about.deadline.minutes"
                                singular-key="about.deadline.minute"
                            />
                            <AboutCountdownItem
                                :value="deadline.seconds"
                                plural-key="about.deadline.seconds"
                                singular-key="about.deadline.second"
                            />
                        </div>
                    </div>
                </div>
                <div
                    class="-mx-2 mt-10 flex select-none flex-row justify-center xs:mt-10 sm:mt-16"
                >
                    <div
                        class="grid grid-cols-3 grid-rows-1 gap-2 xs:gap-5 sm:gap-14 md:gap-24 lg:gap-32"
                    >
                        <div class="flex flex-col items-center">
                            <SvgAboutTime class="mb-2 w-20 sm:w-24 lg:w-28" />
                            <h4
                                class="flex flex-row text-xl font-bold text-text hover-scale dark:text-natural-50 sm:text-2xl lg:text-3xl"
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
                                class="mt-1 text-sm font-medium text-natural-600 dark:text-natural-500 sm:text-lg lg:text-xl"
                            >
                                <T key-name="about.facts.time" />
                            </h5>
                        </div>
                        <div class="flex flex-col items-center">
                            <SvgAboutCheckMark
                                class="mb-2 w-20 sm:w-24 lg:w-28"
                            />
                            <h4
                                class="flex flex-row text-xl font-bold text-text hover-scale dark:text-natural-50 sm:text-2xl lg:text-3xl"
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
                                class="mt-1 text-sm font-medium text-natural-600 dark:text-natural-500 sm:text-lg lg:text-xl"
                            >
                                <T key-name="about.facts.userstories" />
                            </h5>
                        </div>
                        <div class="flex flex-col items-center">
                            <SvgAboutCalendar
                                class="mb-2 w-20 sm:w-24 lg:w-28"
                            />
                            <h4
                                class="flex flex-row flex-wrap justify-center text-xl font-bold text-text hover-scale dark:text-natural-50 sm:text-2xl lg:text-3xl"
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
                                class="mt-1 text-sm font-medium text-natural-600 dark:text-natural-500 sm:text-lg lg:text-xl"
                            >
                                <T key-name="about.facts.sprints" />
                            </h5>
                        </div>
                    </div>
                </div>
                <div id="team" ref="team" class="sm:mt-16 md:mt-32 lg:mt-28">
                    <h3
                        class="mt-10 cursor-default text-center text-lg font-bold text-text dark:text-natural-50 xs:mt-20 xs:text-xl md:text-2xl lg:mt-4"
                    >
                        <T key-name="about.team.headline" />
                        <NuxtLink
                            to="https://www.htlrennweg.at/"
                            target="_blank"
                            class="cursor-alias whitespace-nowrap hover:text-calypso-500 dark:hover:text-calypso-500"
                        >
                            <span class="whitespace-nowrap underline"
                                >HTL Rennweg</span
                            >
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
                            class="mx-5 mt-5 flex cursor-default items-center justify-center font-nunito lg:mt-10"
                        >
                            <div
                                class="align-center flex w-full items-center gap-4"
                            >
                                <AboutProjectMemberCard
                                    name="Severin Rosner"
                                    position-key="startpage.people.scrummaster"
                                    linkedin-link="https://www.linkedin.com/in/severin-rosner/"
                                    mail="severin.rosner@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgAboutSeverin
                                            class="w-40 hover-scale xs:w-52 lg:w-[75%]"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                                <AboutProjectMemberCard
                                    name="Raven Burkard"
                                    position-key="startpage.people.productowner"
                                    role-key="startpage.people.backend"
                                    linkedin-link="https://www.linkedin.com/in/raven-burkard/"
                                    mail="raven.burkard@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgAboutRaven
                                            class="w-40 hover-scale xs:w-52 lg:w-[75%]"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                                <AboutProjectMemberCard
                                    name="Roman Krebs"
                                    linkedin-link="https://www.linkedin.com/in/roman-krebs/"
                                    mail="roman.krebs@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgAboutRoman
                                            class="w-40 hover-scale xs:w-52 lg:w-[75%]"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                                <AboutProjectMemberCard
                                    name="Stefania Manastirska"
                                    role-key="startpage.people.design"
                                    linkedin-link="https://www.linkedin.com/in/stefania-manastirska/"
                                    mail="stefania.manastirska@journeyplanner.io"
                                >
                                    <template #picture>
                                        <SvgAboutStefi
                                            class="w-40 hover-scale xs:w-52 lg:w-[75%]"
                                        />
                                    </template>
                                </AboutProjectMemberCard>
                            </div>
                        </div>
                    </ScrollPanel>
                </div>
                <div
                    id="sponsors"
                    class="mb-10 mt-10 flex flex-col items-center justify-center sm:mt-20"
                >
                    <h3
                        class="mb-5 cursor-default text-center text-lg font-bold text-text dark:text-natural-50 xs:text-xl md:text-2xl"
                    >
                        <T key-name="about.sponsors.headline" />
                    </h3>
                    <div
                        class="flex flex-col items-center md:flex-row lg:gap-16"
                    >
                        <div
                            class="flex h-24 w-72 flex-col items-center justify-center rounded-lg bg-natural-100 py-2 text-center text-sm font-medium text-natural-700 shadow-md dark:text-natural-300 dark:text-natural-800 sm:w-96 sm:text-base"
                        >
                            <h4 class="font-semibold">
                                <T key-name="about.sponsor" />
                            </h4>
                            <a
                                href="mailto:raven.burkard@journeyplanner.io?cc=contact@journeyplanner.io&subject=Sponsorship"
                                class="text-calypso-400 hover:font-bold dark:text-calypso-600"
                            >
                                <T key-name="about.sponsor.contact" />
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
