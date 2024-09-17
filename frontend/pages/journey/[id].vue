<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { differenceInDays, differenceInHours, format } from "date-fns";
import JSConfetti from "js-confetti";
import QRCode from "qrcode";
import resolveConfig from "tailwindcss/resolveConfig";
import TemplateDialog from "~/components/TemplateDialog.vue";
import tailwindConfig from "~/tailwind.config.js";

import scroll from "../../utils/scroll";

const fullConfig = resolveConfig(tailwindConfig);
const confirm = useConfirm();
const route = useRoute();
const store = useJourneyStore();
const activityStore = useActivityStore();
const journeyId = route.params.id;
const activityDataLoaded = ref(false);
const qrcode = ref("");
const duringJourney = ref(false);
const journeyEnded = ref(false);
const day = ref(0);
const tensDays = ref(0);
const hundredsDays = ref(0);
const jsConfetti = new JSConfetti();
const isMemberSidebarVisible = ref(false);
const isMenuSidebarVisible = ref(false);
const toast = useToast();
const { t } = useTranslate();
const op = ref();
const toggle = (event: Event) => {
    op.value.toggle(event);
};
const editEnabled = ref(false);
const isActivityDialogVisible = ref(false);
const uploadResult = ref();
const upload = ref();
const calendar = ref();

const isCreateTemplateVisible = ref(false);
const isJourneyEditMenuVisible = ref(false);

definePageMeta({
    middleware: ["sanctum:auth"],
});

interface Journey {
    name: string;
    invite: string;
    destination: string;
    from: string;
    to: string;
}

interface User {
    id: string;
    firstName: string;
    lastName: string;
    role: number;
}

const client = useSanctumClient();
const { data, error } = await useAsyncData("journey", () =>
    client(`/api/journey/${journeyId}`),
);

if (error.value) {
    throw createError({
        statusCode: 404,
        statusMessage: "Journey not found",
        fatal: true,
    });
}

await client(`/api/journey/${journeyId}/activity`, {
    async onResponse({ response }) {
        if (response.ok) {
            activityStore.setActivities(response._data);
            activityDataLoaded.value = true;
        }
    },
});

const { data: users } = await useAsyncData("users", () =>
    client(`/api/journey/${journeyId}/user`),
);

const { data: currUser } = await useAsyncData("userRole", () =>
    client(`/api/journey/${journeyId}/user/me`),
);

const journeyData = data as Ref<Journey>;
store.setJourney(journeyData);

const title = journeyData.value.name;
journeyData.value.invite =
    window.location.origin + "/invite/" + journeyData.value.invite;
useHead({
    title: `${title} | JourneyPlanner`,
});

const colorMode = useColorMode();
const darkThemeMq = window.matchMedia("(prefers-color-scheme: dark)");
let darkColor = fullConfig.theme.accentColor["text"] as string;
let lightColor = fullConfig.theme.accentColor["background"] as string;

if (
    colorMode.preference === "dark" ||
    (darkThemeMq.matches && colorMode.preference === "system")
) {
    darkColor = fullConfig.theme.accentColor["background"] as string;
    lightColor = fullConfig.theme.accentColor["text"] as string;
}

const opts = {
    margin: 0,
    color: {
        dark: darkColor,
        light: lightColor,
    },
};
QRCode.toDataURL(journeyData.value.invite, opts, function (error, url) {
    qrcode.value = url;
});

const fromDate = ref(new Date(journeyData.value.from.split("T")[0]));
const toDate = ref(new Date(journeyData.value.to.split("T")[0]));
const currentDate = new Date();

const days = ref(
    Math.ceil(differenceInHours(fromDate.value, currentDate) / 24),
);
const daystoEnd = ref(
    Math.ceil(differenceInHours(toDate.value, currentDate) / 24),
);

onMounted(() => {
    calculateDays(journeyData.value.from, journeyData.value.to);
});

const isFlipped = ref(false);
const flip = () => {
    isFlipped.value = !isFlipped.value;
};

const confirmLeave = (event: Event) => {
    isMemberSidebarVisible.value = false;
    confirm.require({
        target: event.currentTarget as HTMLElement,
        group: "journey",
        header: t.value("journey.leave.header"),
        message: t.value("journey.leave.message"),
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline",
        acceptClass:
            "text-error dark:text-error-dark hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("journey.leave"),
        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("common.toast.info.heading"),
                detail: t.value("leave.journey.toast.message"),
                life: 3000,
            });
            leaveJourney();
        },
    });
};

function calculateDays(from: string, to: string) {
    fromDate.value = new Date(from.split("T")[0]);
    toDate.value = new Date(to.split("T")[0]);
    days.value = Math.ceil(differenceInHours(fromDate.value, currentDate) / 24);
    daystoEnd.value = Math.ceil(
        differenceInHours(toDate.value, currentDate) / 24,
    );

    if (days.value > 0) {
        journeyEnded.value = false;
        duringJourney.value = false;
        day.value = Math.floor(days.value % 10);
        days.value = days.value / 10;
        tensDays.value = Math.floor(days.value % 10);
        days.value = days.value / 10;
        hundredsDays.value = Math.floor(days.value % 10);
    } else if (days.value <= 0 && daystoEnd.value > 0) {
        duringJourney.value = true;
        journeyEnded.value = false;
        const journeyEnds = ref(differenceInDays(toDate.value, currentDate));

        if (Math.floor(journeyEnds.value % 10) == 9) {
            day.value = Math.floor(journeyEnds.value % 10);
            journeyEnds.value = journeyEnds.value / 10;
            tensDays.value = Math.floor(journeyEnds.value % 10) + 1;
        } else {
            day.value = Math.floor(journeyEnds.value % 10) + 1;
            journeyEnds.value = journeyEnds.value / 10;
            tensDays.value = Math.floor(journeyEnds.value % 10);
        }

        journeyEnds.value = journeyEnds.value / 10;
        hundredsDays.value = Math.floor(journeyEnds.value % 10);
    } else {
        journeyEnded.value = true;
        duringJourney.value = false;
        days.value = 0;
        tensDays.value = 0;
        hundredsDays.value = 0;
        day.value = 0;
    }
}

function journeyEdited(journey: Journey) {
    store.setJourney(journey);
    useHead({
        title: `${journey.name} | JourneyPlanner`,
    });
    calculateDays(journey.from, journey.to);
}

/**
 * API call to leave the journey
 * success: toast message and redirect to dashboard
 * error: toast message
 */
async function leaveJourney() {
    await client(`/api/journey/${journeyId}/leave`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("leave.journey.toast.success.heading"),
                    detail: t.value("leave.journey.toast.success.detail"),
                    life: 3000,
                });
                await navigateTo("/dashboard");
            }
        },
        async onResponseError({ response }) {
            if (response.status === 403) {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("leave.journey.toast.error"),
                    life: 6000,
                });
            } else {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            }
        },
    });
}

/**
 * copy the invite link to the clipboard
 */
function copyToClipboard() {
    navigator.clipboard.writeText(journeyData.value.invite);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}

/*
 * Change the role of a user
 * @param userid - the id of the user
 * @param selectedRole - the selected role
 */
async function changeRole(userid: string, selectedRole: number) {
    await client(`/api/journey/${journeyId}/user/${userid}`, {
        method: "PATCH",
        body: {
            role: selectedRole,
        },
        async onResponse() {
            users.value = users.value.map((user: User) => {
                if (user.id === userid) {
                    user.role = selectedRole;
                }
                return user;
            });
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
    });
}

const handleUpload = (result: string) => {
    uploadResult.value = result;
};
</script>

<template>
    <div class="flex flex-col font-nunito text-text dark:text-natural-50">
        <Sidebar
            id="member-sidebar"
            v-model:visible="isMemberSidebarVisible"
            position="right"
            :block-scroll="true"
            :pt="{
                closeButton: {
                    class: 'w-9 h-9 col-span-2 flex w-full justify-end pr-1',
                },
                closeIcon: {
                    class: 'w-7 h-7 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50',
                },
                header: { class: 'p-2 pl-3 grid grid-rows-1 grid-cols-12' },
                content: { class: 'pl-3 pr-2 py-2 flex flex-col h-full' },
                root: {
                    class: 'dark:bg-background-dark font-nunito relative',
                },
            }"
        >
            <template #header>
                <span class="h-0.5 w-full bg-calypso-400" />
                <div
                    class="col-span-5 flex w-full flex-row justify-center text-2xl font-medium text-text dark:text-natural-50"
                >
                    <h3>
                        <T key-name="journey.sidebar.members" />
                    </h3>
                </div>
                <span
                    class="col-span-4 col-start-7 h-0.5 w-full bg-calypso-400"
                />
            </template>
            <div class="text-xl font-medium text-text dark:text-natural-50">
                <T key-name="sidebar.invite.link" />
            </div>
            <div
                class="flex items-center border-b-2 border-natural-200 pb-4 dark:border-natural-900"
            >
                <input
                    class="w-5/6 rounded-md bg-natural-100 px-1 pb-1 pt-1 text-base text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                    disabled
                    :value="journeyData.invite"
                />
                <div class="flex w-1/5 justify-end">
                    <button
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                        @click="copyToClipboard"
                    >
                        <SvgCopy class="w-4" />
                    </button>
                </div>
            </div>
            <div
                class="flex flex-row items-center justify-center border-b-2 border-natural-200 pb-1 pt-1 dark:border-natural-900"
            >
                <h1
                    class="w-4/5 text-xl text-natural-600 dark:text-natural-200"
                >
                    <T key-name="journey.sidebar.list.header" />
                </h1>
                <div class="mb-1 mt-1 flex w-1/5 items-center justify-end">
                    <button
                        v-if="currUser.role === 1"
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                        @click="editEnabled = !editEnabled"
                    >
                        <SvgEdit v-if="!editEnabled" class="w-4" />
                        <SvgEditOff v-if="editEnabled" class="w-4" />
                    </button>
                </div>
            </div>
            <div
                id="list"
                class="mt-3 flex flex-grow flex-col gap-3 overflow-y-auto pr-0.5"
            >
                <MemberItem
                    v-for="user in users"
                    :id="user.id"
                    :key="user.id"
                    :first-name="user.firstName"
                    :last-name="user.lastName"
                    :role="user.role"
                    :edit="editEnabled"
                    :current-i-d="currUser.user_id"
                    @change-role="changeRole"
                />
            </div>
            <div
                class="sticky bottom-0 border-t-2 border-natural-200 dark:border-natural-900"
            >
                <button
                    class="my-4 w-full rounded-lg border-2 border-mahagony-500 bg-natural-50 py-1 text-base text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                    @click="confirmLeave($event)"
                >
                    <T key-name="journey.leave.short" />
                </button>
            </div>
        </Sidebar>
        <Sidebar
            id="menu-sidebar"
            v-model:visible="isMenuSidebarVisible"
            position="right"
            :block-scroll="true"
            :pt="{
                closeButton: {
                    class: 'w-9 h-9 col-span-2 flex w-full justify-end pr-1',
                },
                closeIcon: {
                    class: 'w-7 h-7 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50',
                },
                header: { class: 'p-2 pl-3 grid grid-cols-12' },
                content: {
                    class: 'pl-3 pr-2 text-text dark:text-natural-50',
                },
                root: {
                    class: 'bg-background dark:bg-background-dark font-nunito',
                },
            }"
        >
            <template #header>
                <span class="h-0.5 w-full bg-calypso-400" />
                <div
                    class="col-span-3 flex w-full flex-row justify-center text-2xl font-medium text-text dark:text-natural-50"
                >
                    <h3>
                        <T key-name="journey.sidebar.menu" />
                    </h3>
                </div>
                <span
                    class="col-span-6 col-start-5 h-0.5 w-full bg-calypso-400"
                />
            </template>
            <div>
                <Accordion class="font-nunito text-xl text-text">
                    <AccordionTab
                        v-if="currUser.role === 1"
                        :header="t('dashboard.edit.header')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: 'pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                        }"
                    >
                        <div>
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="dashboard.edit.detail" />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-dandelion-300 bg-natural-50 py-1 text-base text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="
                                    isJourneyEditMenuVisible =
                                        !isJourneyEditMenuVisible
                                "
                            >
                                <T key-name="dashboard.edit.short" />
                            </button>
                        </div>
                    </AccordionTab>
                    <AccordionTab
                        v-if="currUser.role === 1"
                        :header="t('journey.template.create')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: 'pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                        }"
                    >
                        <div>
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="journey.template.create.detail" />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-dandelion-300 bg-natural-50 py-1 text-base text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="
                                    isCreateTemplateVisible =
                                        !isCreateTemplateVisible
                                "
                            >
                                <T key-name="journey.template.create" />
                            </button>
                        </div>
                    </AccordionTab>
                    <AccordionTab
                        v-if="currUser.role === 1"
                        :header="t('dashboard.options.leave')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: 'pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                        }"
                    >
                        <div>
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="journey.leave.detail" />
                                <T
                                    v-if="currUser.role === 1"
                                    key-name="journey.leave.detail.journeyguide"
                                />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-mahagony-500 bg-natural-50 py-1 text-base text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                                @click="confirmLeave($event)"
                            >
                                <T key-name="journey.leave.short" />
                            </button>
                        </div>
                    </AccordionTab>
                </Accordion>
            </div>
        </Sidebar>
        <div
            class="absolute right-0 mt-5 flex h-10 w-full items-center justify-end font-semibold lg:w-1/3"
        >
            <NuxtLink to="/dashboard" class="flex items-center">
                <SvgDashboardIcon class="h-6 w-6" />
                <p class="text-2xl hover:underline">Dashboard</p>
            </NuxtLink>
            <span
                class="pi pi-users ml-10 mr-5 text-3xl hover:cursor-pointer"
                @click="isMemberSidebarVisible = true"
            />
            <span
                class="pi pi-bars mr-10 text-3xl hover:cursor-pointer"
                @click="isMenuSidebarVisible = true"
            />
        </div>
        <div class="mt-[12vh] flex h-fit flex-wrap">
            <div class="flex w-full items-center justify-center md:hidden">
                <div
                    class="group w-[90%] [perspective:1000px] sm:w-5/6"
                    @click="flip"
                >
                    <div
                        :class="isFlipped ? '[transform:rotateX(180deg)]' : ''"
                        class="relative h-full w-full rounded-2xl transition-all duration-500 [transform-style:preserve-3d]"
                    >
                        <div class="bg-none md:w-2/5 lg:w-1/3">
                            <div
                                class="relative flex h-10 items-center rounded-t-2xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
                            >
                                <div
                                    class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-natural-200"
                                />
                                <p
                                    class="ml-16 text-xl font-bold text-natural-50"
                                >
                                    JourneyPlanner
                                </p>
                                <div
                                    class="flex h-full w-full items-center justify-end"
                                >
                                    <SvgAirplaneIcon class="mr-5 w-7" />
                                </div>
                            </div>
                            <div class="flex h-5/6">
                                <div
                                    class="-mr-1 h-fit w-full rounded-b-2xl border-x-2 border-b-2 border-natural-200 bg-natural-50 text-sm dark:border-gothic-600 dark:bg-dark"
                                >
                                    <div
                                        class="mb-2 mt-1 grid w-full grid-cols-4"
                                    >
                                        <div
                                            class="col-span-3 flex h-full w-full flex-col justify-center pl-5 font-semibold"
                                        >
                                            <T
                                                key-name="form.input.journey.name"
                                            />
                                            <input
                                                class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                                disabled
                                                :value="journeyData.name"
                                            />
                                            <T
                                                key-name="form.input.journey.destination"
                                            />
                                            <input
                                                class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                                disabled
                                                :value="journeyData.destination"
                                            />
                                            <T
                                                key-name="form.input.journey.date"
                                            />
                                            <input
                                                class="text-md mb-2 w-5/6 rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50 md:w-4/5"
                                                disabled
                                                :value="
                                                    format(
                                                        fromDate,
                                                        'dd/MM/yyyy',
                                                    ) +
                                                    ' - ' +
                                                    format(toDate, 'dd/MM/yyyy')
                                                "
                                            />
                                        </div>
                                        <div class="relative -mt-1 w-full">
                                            <SvgStripes
                                                class="absolute right-0 z-0 w-[7.4rem]"
                                            />
                                            <div
                                                class="absolute bottom-2 right-2 ml-10 flex h-16 w-16 items-center justify-center self-center rounded-full border-2 border-dashed border-natural-400 pl-1.5 pr-1.5 text-center text-xs text-natural-400 dark:border-natural-50 dark:text-natural-50"
                                            >
                                                <T key-name="journey.turn" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="rounded-b-r-3xl h-[90%] w-0 border-r-2 border-dashed border-natural-200"
                                />
                            </div>
                        </div>
                        <div
                            class="absolute inset-0 h-full w-full rounded-xl bg-natural-50 text-center text-text [backface-visibility:hidden] [transform:rotateX(180deg)] dark:bg-background-dark"
                        >
                            <div
                                class="relative flex h-10 items-center rounded-t-2xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
                            >
                                <div
                                    class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-natural-200"
                                />
                                <p
                                    class="ml-16 text-xl font-bold text-natural-50"
                                >
                                    JourneyPlanner
                                </p>
                                <div
                                    class="flex h-full w-full items-center justify-end"
                                >
                                    <SvgAirplaneIcon class="mr-5 w-7" />
                                </div>
                            </div>
                            <div class="flex h-5/6">
                                <div
                                    class="flex h-full w-full justify-center rounded-b-2xl border-x-2 border-b-2 border-natural-200 bg-natural-50 text-sm dark:border-gothic-600 dark:bg-dark"
                                >
                                    <div
                                        class="relative flex h-full w-full flex-col items-end"
                                    >
                                        <img
                                            class="absolute right-[50%] top-[25%] z-20 w-40 -translate-y-[25%] translate-x-[50%] max-sm:mt-1"
                                            :src="qrcode"
                                            alt="QR Code"
                                        />
                                        <div
                                            class="absolute bottom-4 right-2 z-40 ml-10 flex h-16 w-16 items-center justify-center self-center rounded-full border-2 border-dashed border-natural-400 pl-1.5 pr-1.5 text-xs text-natural-400 dark:border-natural-50 dark:text-natural-50"
                                        >
                                            <T key-name="journey.turn" />
                                        </div>
                                        <SvgStripes
                                            class="z-0 md:w-2/3 lg:w-1/2"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="invisible ml-[10%] w-0 max-md:h-0 md:visible md:w-[50%] lg:ml-10 lg:w-1/3 xl:ml-[10%]"
            >
                <div
                    class="relative flex h-10 items-center rounded-t-3xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
                >
                    <div
                        class="bg-calypso-300-gray absolute ml-5 inline-block h-7 w-7 self-center rounded-full"
                    />
                    <p class="ml-14 text-xl font-bold text-natural-50">
                        JourneyPlanner
                    </p>
                    <div class="flex h-full w-full items-center justify-end">
                        <SvgAirplaneIcon class="mr-5 w-7" />
                    </div>
                </div>
                <div class="flex h-[13.5rem] lg:h-[15.5rem]">
                    <div
                        class="dark:bg-calypso-300-dark w-full rounded-b-3xl border-b-2 border-l-2 border-natural-200 bg-natural-50 text-sm dark:border-gothic-600 dark:bg-dark"
                    >
                        <div class="relative grid w-full grid-cols-4">
                            <div
                                class="col-span-3 flex h-[120%] w-full flex-col justify-center pl-10 text-base font-semibold"
                            >
                                <T key-name="form.input.journey.name" />
                                <input
                                    class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                    disabled
                                    :value="journeyData.name"
                                />
                                <T key-name="form.input.journey.destination" />
                                <input
                                    class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                    disabled
                                    :value="journeyData.destination"
                                />
                                <T key-name="form.input.journey.date" />
                                <input
                                    class="text-md mb-2 rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50 md:w-5/6 lg:w-2/3"
                                    disabled
                                    :value="
                                        format(fromDate, 'dd/MM/yyyy') +
                                        ' - ' +
                                        format(toDate, 'dd/MM/yyyy')
                                    "
                                />
                            </div>
                            <div
                                class="absolute w-full md:col-span-2 lg:col-span-2 xl:col-span-1 2xl:col-span-1"
                            >
                                <SvgStripes
                                    class="absolute right-0 md:w-[8.8rem] lg:w-[10.15rem]"
                                />
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-b-r-3xl h-[90%] w-0 border-r-2 border-dashed border-natural-200"
                    />
                </div>
            </div>
            <div
                class="invisible w-0 rounded-3xl border-solid bg-background dark:bg-dark max-md:h-0 md:visible md:h-64 md:w-64 lg:h-72 lg:w-72"
            >
                <div
                    class="h-10 rounded-t-3xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
                >
                    <div class="flex h-full w-full items-center justify-end">
                        <SvgAirplaneIcon class="mr-5 w-7" />
                    </div>
                </div>
                <div class="flex h-[13.5rem] lg:h-[15.5rem]">
                    <div
                        class="rounded-b-l-3xl h-[90%] w-0 border-l-2 border-dashed border-natural-200"
                    />
                    <div
                        class="flex h-full w-full justify-center rounded-b-3xl border-b-2 border-r-2 border-natural-200 dark:border-gothic-600 dark:bg-dark"
                    >
                        <div
                            class="relative flex h-full w-full flex-col items-end"
                        >
                            <SvgStripes
                                class="absolute right-0 md:w-[8.8rem] lg:w-[10.15rem]"
                            />
                            <img
                                class="absolute right-[50%] top-[25%] -translate-y-[25%] translate-x-[50%] md:w-[8rem] lg:w-[10rem]"
                                :src="qrcode"
                                alt="QR Code"
                            />
                            <button
                                class="absolute right-[50%] top-[80%] flex h-1/6 w-2/5 translate-x-[50%] items-center justify-center rounded-xl border-2 border-dandelion-300 bg-background font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 md:-translate-y-[30%] lg:-translate-y-[2%]"
                                @click="toggle"
                            >
                                <T key-name="journey.button.invite" />
                                <SvgShare class="ml-2 w-3" />
                            </button>
                            <OverlayPanel
                                ref="op"
                                class="rounded-lg bg-natural-50 font-nunito text-text dark:bg-natural-800 dark:text-natural-50"
                            >
                                <div class="flex-column w-25rem flex gap-3">
                                    <div>
                                        <span
                                            class="mb-1 block text-lg font-medium"
                                        >
                                            <T key-name="sidebar.invite.link" />
                                        </span>
                                        <div class="flex">
                                            <input
                                                class="w-full rounded-l-md border-2 border-natural-200 bg-natural-100 pb-1 pl-2.5 pt-1 text-base font-medium text-text shadow-sm focus:outline-none focus:ring-1 dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50"
                                                disabled
                                                :value="journeyData.invite"
                                            />
                                            <button
                                                class="flex h-9 w-9 items-center justify-center rounded-r-md border-2 border-y-2 border-r-2 border-dandelion-300 bg-natural-100 shadow-sm hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                                                @click="copyToClipboard"
                                            >
                                                <SvgCopy class="w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </OverlayPanel>
                        </div>
                    </div>
                </div>
            </div>
            <div class="basis-0 md:basis-full lg:basis-0" />
            <div
                class="flex w-full justify-center md:justify-start lg:ml-10 lg:w-72 xl:ml-32"
            >
                <div
                    class="w-[90%] rounded-2xl border-2 border-solid border-calypso-300 bg-calypso-50 bg-opacity-20 dark:border-calypso-600 dark:bg-gothic-300 dark:bg-opacity-20 max-lg:mt-5 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] lg:ml-0 lg:w-full lg:rounded-3xl"
                >
                    <div
                        class="from-indigo-500 to-indigo-800 flex h-full flex-wrap items-center justify-center bg-gradient-to-br xs:justify-start lg:flex-col lg:justify-center"
                    >
                        <!-- flip clock container -->
                        <div
                            v-if="hundredsDays <= 0"
                            class="relative mx-3 my-2 grid grid-cols-2 gap-x-1 text-4xl font-bold text-text dark:text-natural-50 lg:text-6xl"
                        >
                            <div class="bg-black relative rounded-xl p-1 py-2">
                                <!-- background grid of black squares -->
                                <div class="absolute inset-0 grid grid-rows-2">
                                    <div
                                        class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                    <div
                                        class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                </div>

                                <!-- time numbers -->
                                <span class="top-50 absolute">{{
                                    tensDays
                                }}</span>

                                <!-- line across the middle -->
                                <div class="absolute inset-0 flex items-center">
                                    <div
                                        class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                    />
                                </div>
                            </div>
                            <div class="bg-black relative rounded-xl p-1 py-2">
                                <!-- background grid of black squares -->
                                <div class="absolute inset-0 grid grid-rows-2">
                                    <div
                                        class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                    <div
                                        class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                </div>

                                <!-- time numbers -->
                                <span class="relative">{{ day }}</span>

                                <!-- line across the middle -->
                                <div class="absolute inset-0 flex items-center">
                                    <div
                                        class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="relative mx-3 my-2 grid grid-cols-3 gap-x-1 text-4xl font-bold text-text dark:text-natural-50 lg:gap-x-2 lg:text-6xl"
                        >
                            <!-- left side -->
                            <div
                                class="bg-black relative rounded-xl p-1 py-2 lg:p-2 lg:py-3"
                            >
                                <!-- background grid of black squares -->
                                <div class="absolute inset-0 grid grid-rows-2">
                                    <div
                                        class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                    <div
                                        class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                </div>

                                <!-- time numbers -->
                                <span class="top-50 absolute">{{
                                    hundredsDays
                                }}</span>

                                <!-- line across the middle -->
                                <div class="absolute inset-0 flex items-center">
                                    <div
                                        class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                    />
                                </div>
                            </div>

                            <div
                                class="bg-black relative rounded-xl p-1 py-2 lg:p-2 lg:py-3"
                            >
                                <!-- background grid of black squares -->
                                <div class="absolute inset-0 grid grid-rows-2">
                                    <div
                                        class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                    <div
                                        class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                </div>

                                <!-- time numbers -->
                                <span class="top-50 absolute">{{
                                    tensDays
                                }}</span>

                                <!-- line across the middle -->
                                <div class="absolute inset-0 flex items-center">
                                    <div
                                        class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                    />
                                </div>
                            </div>
                            <div
                                class="bg-black relative rounded-xl p-1 py-2 lg:p-2 lg:py-3"
                            >
                                <!-- background grid of black squares -->
                                <div class="absolute inset-0 grid grid-rows-2">
                                    <div
                                        class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                    <div
                                        class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                    />
                                </div>

                                <!-- time numbers -->
                                <span class="relative">{{ day }}</span>

                                <!-- line across the middle -->
                                <div class="absolute inset-0 flex items-center">
                                    <div
                                        class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                    />
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-start text-center lg:flex-col"
                        >
                            <p class="text-base font-bold">
                                <T key-name="journey.countdown.days" />
                            </p>
                            <p
                                v-if="duringJourney"
                                class="w-full pl-1 text-base font-bold lg:text-lg"
                            >
                                <T key-name="journey.countdown.ends" />
                            </p>
                            <p
                                v-else-if="journeyEnded"
                                class="w-full pl-1 text-base font-bold lg:text-lg"
                            >
                                <T key-name="journey.countdown.finished" />
                            </p>
                            <p
                                v-else
                                class="w-full pl-1 text-base font-bold lg:text-lg"
                            >
                                <T key-name="journey.countdown.until" />
                            </p>
                            <button
                                v-if="duringJourney || currUser.role !== 1"
                                class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[80%] xl:w-[110%]"
                                @click="scroll(calendar)"
                            >
                                <T
                                    key-name="journey.button.countdown.calendar"
                                />
                            </button>
                            <button
                                v-else-if="journeyEnded"
                                class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                                @click="
                                    scroll(upload);
                                    jsConfetti.addConfetti();
                                "
                            >
                                <T
                                    key-name="journey.button.countdown.celebrate"
                                />
                            </button>
                            <button
                                v-else
                                class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                                @click="
                                    isActivityDialogVisible =
                                        !isActivityDialogVisible
                                "
                            >
                                <T key-name="journey.button.create.activity" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center md:justify-start">
            <div
                class="flex w-[90%] items-end sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <Divider
                    type="solid"
                    class="border text-natural-100 md:hidden md:w-0"
                />
            </div>
        </div>
        <div
            v-if="currUser.role === 1"
            class="flex justify-center md:justify-start"
        >
            <div
                class="-mt-4 flex h-10 w-[90%] items-end sm:w-5/6 md:ml-[10%] md:h-20 md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:h-24 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <div class="-mb-2.5 text-2xl font-semibold lg:mb-3">
                    <T key-name="journey.activities" />
                </div>
                <button
                    class="-mb-3 ml-auto flex items-center rounded-xl border-2 border-dandelion-300 bg-natural-50 px-2 py-1 text-sm font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 sm:text-base lg:mb-4"
                    @click="isActivityDialogVisible = !isActivityDialogVisible"
                >
                    <SvgAddLocation class="h-6 w-6" />
                    <T key-name="journey.button.create.activity" />
                </button>
            </div>
        </div>
        <ActivityDialog
            v-if="currUser.role === 1"
            :id="journeyId.toString()"
            :visible="isActivityDialogVisible"
            :only-show="false"
            :create="true"
            :create-address="true"
            @close="isActivityDialogVisible = false"
        />
        <ActivityPool v-if="currUser.role === 1" :id="journeyId.toString()" />
        <div ref="calendar">
            <CalendarFull
                :id="journeyId.toString()"
                :current-user-role="currUser.role"
                :journey-ended="journeyEnded"
                :during-journey="duringJourney"
                :journey-startdate="journeyData.from"
                :journey-enddate="journeyData.to"
            />
        </div>
        <ActivityMap v-if="activityDataLoaded" />
        <div
            ref="upload"
            class="flex items-center justify-center md:justify-start"
        >
            <div
                class="relative mt-4 flex w-[90%] items-center sm:mt-7 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <FormUpload @uploaded="handleUpload" />
            </div>
        </div>
        <div class="flex items-center justify-center md:justify-start">
            <div
                class="relative mt-4 flex w-[90%] items-center sm:mt-7 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <MediaGallery :upload-data="uploadResult" />
            </div>
        </div>
        <EditJourneyDialog
            :id="store.getID()"
            :is-journey-dialog-visible="isJourneyEditMenuVisible"
            :name="store.getName()"
            :destination="store.getDestination()"
            :from="new Date(store.getFromDate())"
            :to="new Date(store.getToDate())"
            @close-edit-journey-dialog="isJourneyEditMenuVisible = false"
            @journey-edited="journeyEdited"
        />
        <TemplateDialog
            v-if="currUser.role === 1"
            :is-create-template-visible="isCreateTemplateVisible"
            @close-template-dialog="
                isCreateTemplateVisible = false;
                isMemberSidebarVisible = false;
            "
        />
        <ConfirmDialog
            :draggable="false"
            group="journey"
            :pt="{
                header: {
                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito',
                },
                content: {
                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito',
                },
                footer: {
                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito',
                },
            }"
        />
    </div>
</template>
