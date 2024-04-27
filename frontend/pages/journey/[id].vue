<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { differenceInDays, format } from "date-fns";
import JSConfetti from "js-confetti";
import Toast from "primevue/toast";
import QRCode from "qrcode";

const confirm = useConfirm();
const route = useRoute();
const store = useJourneyStore();
const journeyId = route.params.id;
const qrcode = ref("");
const duringJourney = ref(false);
const journeyEnded = ref(false);
const day = ref(0);
const tensDays = ref(0);
const hundredsDays = ref(0);
const jsConfetti = new JSConfetti();
const visibleSidebar = ref(false);
const toast = useToast();
const { t } = useTranslate();
const op = ref();
const toggle = (event: Event) => {
    op.value.toggle(event);
};
const editEnabled = ref(false);
const isActivityDialogVisible = ref(false);

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
    throw showError({
        statusCode: 404,
        statusMessage: "Page Not Found",
        fatal: true,
    });
}

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
let darkColor = "#333333";
let lightColor = "#fcfcfc";

if (colorMode.preference === "dark") {
    darkColor = "#ffffff";
    lightColor = "#353f44";
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

const fromDate = new Date(journeyData.value.from);
const toDate = new Date(journeyData.value.to);
const currentDate = new Date();
const days = ref(differenceInDays(fromDate, currentDate));
const daystoEnd = ref(differenceInDays(toDate, currentDate));

if (days.value > 0) {
    day.value = Math.floor(days.value % 10);
    days.value = days.value / 10;
    tensDays.value = Math.floor(days.value % 10);
    days.value = days.value / 10;
    hundredsDays.value = Math.floor(days.value % 10);
} else if (days.value <= 0 && daystoEnd.value > 0) {
    duringJourney.value = true;
    const journeyEnds = ref(differenceInDays(toDate, currentDate));
    day.value = Math.floor(journeyEnds.value % 10);
    journeyEnds.value = journeyEnds.value / 10;
    tensDays.value = Math.floor(journeyEnds.value % 10);
    journeyEnds.value = journeyEnds.value / 10;
    hundredsDays.value = Math.floor(journeyEnds.value % 10);
} else {
    journeyEnded.value = true;
}

const isFlipped = ref(false);
const flip = () => {
    isFlipped.value = !isFlipped.value;
};

const confirmLeave = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
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
            console.log(response);

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

function copyToClipboard() {
    navigator.clipboard.writeText(journeyData.value.invite);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}

async function changeRole(userid: string, selectedRole: number) {
    await client(`/api/journey/${journeyId}/user/${userid}`, {
        method: "PATCH",
        body: {
            role: selectedRole,
            random: 1,
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
</script>

<template>
    <div class="flex flex-col font-nunito text-text dark:text-white">
        <Toast class="w-3/4 sm:w-auto" />
        <ConfirmDialog
            :draggable="false"
            :pt="{
                header: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-white font-nunito',
                },
                content: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-white font-nunito',
                },
                footer: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-white font-nunito',
                },
            }"
        />
        <Sidebar
            v-model:visible="visibleSidebar"
            position="right"
            :pt="{
                closeButton: { class: 'w-9 h-9 dark:fill-white' },
                closeIcon: {
                    class: 'w-7 h-7 text-text-disabled dark:text-white',
                },
                header: { class: 'p-2 flex items-center' },
                content: { class: 'pl-3 pr-2 py-2' },
                root: { class: ' dark:bg-background-dark font-nunito' },
            }"
        >
            <template #header>
                <span
                    class="pi pi-sign-out order-1 pr-2 text-xl text-text hover:cursor-pointer hover:text-error dark:text-input dark:hover:text-error-dark"
                    @click="confirmLeave($event)"
                />
            </template>
            <div class="text-xl font-medium text-text dark:text-white">
                <T key-name="sidebar.invite.link" />
            </div>
            <div
                class="flex items-center border-b-2 border-border-grey pb-4 dark:border-text-disabled"
            >
                <input
                    class="w-5/6 rounded-md bg-input-disabled px-1 pb-1 pt-1 text-base text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white"
                    disabled
                    :value="journeyData.invite"
                />
                <div class="flex w-1/5 justify-end">
                    <button
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-cta-border hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark"
                        @click="copyToClipboard"
                    >
                        <SvgCopy class="w-4" />
                    </button>
                </div>
            </div>
            <div
                class="flex flex-row items-center justify-center border-b border-border-grey pb-1 pt-1 dark:border-input-placeholder"
            >
                <h1 class="w-4/5 text-xl text-footer dark:text-border-grey">
                    <T key-name="journey.sidebar.list.header" />
                </h1>
                <div class="mb-1 mt-1 flex w-1/5 items-center justify-end">
                    <button
                        v-if="currUser.role === 1"
                        class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-cta-border hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark"
                        @click="editEnabled = !editEnabled"
                    >
                        <SvgEdit v-if="!editEnabled" class="w-4" />
                        <SvgEditOff v-if="editEnabled" class="w-4" />
                    </button>
                </div>
            </div>
            <div id="list" class="mt-3 flex flex-col gap-3">
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
        </Sidebar>
        <div
            class="absolute right-0 mt-5 flex h-10 w-full items-center justify-end font-semibold lg:w-1/3"
        >
            <NuxtLink to="/dashboard" class="flex items-center">
                <SvgDashboardIcon class="h-6 w-6" />
                <p class="text-2xl hover:underline">Dashboard</p>
            </NuxtLink>
            <SvgMenu
                class="mx-5 h-8 w-8 hover:cursor-pointer md:mx-10 md:h-10 md:w-10"
                @click="visibleSidebar = true"
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
                                class="relative flex h-10 items-center rounded-t-2xl border-x-2 border-t-2 border-border-darker bg-border dark:border-border-blue-dark dark:bg-ticket-top-dark-bg"
                            >
                                <div
                                    class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-border-gray"
                                />
                                <p class="ml-16 text-xl font-bold text-white">
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
                                    class="-mr-1 h-fit w-full rounded-b-2xl border-x-2 border-b-2 border-border-gray bg-background text-sm dark:border-form-input-dark dark:bg-border-dark"
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
                                                class="text-md mb-2 w-full rounded-md bg-input-gray px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white"
                                                disabled
                                                :value="journeyData.name"
                                            />
                                            <T
                                                key-name="form.input.journey.destination"
                                            />
                                            <input
                                                class="text-md mb-2 w-full rounded-md bg-input-gray px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white"
                                                disabled
                                                :value="journeyData.destination"
                                            />
                                            <T
                                                key-name="form.input.journey.date"
                                            />
                                            <input
                                                class="text-md mb-2 w-5/6 rounded-md bg-input-gray px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white md:w-4/5"
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
                                                class="absolute bottom-2 right-2 ml-10 flex h-16 w-16 items-center justify-center self-center rounded-full border-2 border-dashed border-input-placeholder pl-1.5 pr-1.5 text-center text-xs text-input-placeholder dark:border-white dark:text-white"
                                            >
                                                <T key-name="journey.turn" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="rounded-b-r-3xl h-[90%] w-0 border-r-2 border-dashed border-border-gray"
                                />
                            </div>
                        </div>
                        <div
                            class="absolute inset-0 h-full w-full rounded-xl bg-white text-center text-text [backface-visibility:hidden] [transform:rotateX(180deg)] dark:bg-background-dark"
                        >
                            <div
                                class="relative flex h-10 items-center rounded-t-2xl border-x-2 border-t-2 border-border-darker bg-border dark:border-border-blue-dark dark:bg-ticket-top-dark-bg"
                            >
                                <div
                                    class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-border-gray"
                                />
                                <p class="ml-16 text-xl font-bold text-white">
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
                                    class="flex h-full w-full justify-center rounded-b-2xl border-x-2 border-b-2 border-border-gray bg-background dark:border-form-input-dark dark:bg-border-dark"
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
                                            class="absolute bottom-4 right-2 z-40 ml-10 flex h-16 w-16 items-center justify-center self-center rounded-full border-2 border-dashed border-input-placeholder pl-1.5 pr-1.5 text-xs text-input-placeholder dark:border-white dark:text-white"
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
                    class="relative flex h-10 items-center rounded-t-3xl border-x-2 border-t-2 border-border-darker bg-border dark:border-border-blue-dark dark:bg-ticket-top-dark-bg"
                >
                    <div
                        class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-border-gray"
                    />
                    <p class="ml-14 text-xl font-bold text-white">
                        JourneyPlanner
                    </p>
                    <div class="flex h-full w-full items-center justify-end">
                        <SvgAirplaneIcon class="mr-5 w-7" />
                    </div>
                </div>
                <div class="flex h-[13.5rem] lg:h-[15.5rem]">
                    <div
                        class="w-full rounded-b-3xl border-b-2 border-l-2 border-border-gray bg-background dark:border-form-input-dark dark:bg-border-dark"
                    >
                        <div class="relative grid w-full grid-cols-4">
                            <div
                                class="col-span-3 flex h-[120%] w-full flex-col justify-center pl-10 font-semibold"
                            >
                                <T key-name="form.input.journey.name" />
                                <input
                                    class="text-md mb-2 w-full rounded-md bg-input-gray px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white"
                                    disabled
                                    :value="journeyData.name"
                                />
                                <T key-name="form.input.journey.destination" />
                                <input
                                    class="text-md mb-2 w-full rounded-md bg-input-gray px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white"
                                    disabled
                                    :value="journeyData.destination"
                                />
                                <T key-name="form.input.journey.date" />
                                <input
                                    class="text-md mb-2 rounded-md bg-input-gray px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-input-disabled-dark dark:text-white md:w-5/6 lg:w-2/3"
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
                        class="rounded-b-r-3xl h-[90%] w-0 border-r-2 border-dashed border-border-gray"
                    />
                </div>
            </div>
            <div
                class="invisible w-0 rounded-3xl border-solid bg-background dark:bg-border-dark max-md:h-0 md:visible md:h-64 md:w-64 lg:h-72 lg:w-72"
            >
                <div
                    class="h-10 rounded-t-3xl border-x-2 border-t-2 border-border-darker bg-border dark:border-border-blue-dark dark:bg-ticket-top-dark-bg"
                >
                    <div class="flex h-full w-full items-center justify-end">
                        <SvgAirplaneIcon class="mr-5 w-7" />
                    </div>
                </div>
                <div class="flex h-[13.5rem] lg:h-[15.5rem]">
                    <div
                        class="rounded-b-l-3xl h-[90%] w-0 border-l-2 border-dashed border-border-gray"
                    />
                    <div
                        class="flex h-full w-full justify-center rounded-b-3xl border-b-2 border-r-2 border-border-gray dark:border-form-input-dark"
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
                                class="absolute right-[50%] top-[80%] flex h-1/6 w-2/5 translate-x-[50%] items-center justify-center rounded-xl border-2 border-cta-border bg-background font-bold hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark md:-translate-y-[30%] lg:-translate-y-[2%]"
                                @click="toggle"
                            >
                                <T key-name="journey.button.invite" />
                                <SvgShare class="ml-2 w-3" />
                            </button>
                            <OverlayPanel
                                ref="op"
                                class="rounded-lg bg-input font-nunito text-text dark:bg-input-dark dark:text-white"
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
                                                class="w-full rounded-l-md border-2 border-border-gray bg-input-disabled pb-1 pl-2.5 pt-1 text-base font-medium text-text shadow-sm focus:outline-none focus:ring-1 dark:border-input-disabled-dark-grey dark:bg-color-gray-200 dark:text-white"
                                                disabled
                                                :value="journeyData.invite"
                                            />
                                            <button
                                                class="flex h-9 w-9 items-center justify-center rounded-r-md border-2 border-y-2 border-r-2 border-cta-border bg-input-disabled shadow-sm hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark"
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
                    class="w-[90%] rounded-2xl border-2 border-solid border-border bg-countdown-bg dark:bg-surface-dark max-lg:mt-5 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] lg:ml-0 lg:w-full lg:rounded-3xl"
                >
                    <div
                        class="from-indigo-500 to-indigo-800 flex h-full flex-wrap items-center justify-center bg-gradient-to-br xs:justify-start lg:flex-col lg:justify-center"
                    >
                        <!-- flip clock container -->
                        <div
                            v-if="hundredsDays <= 0"
                            class="relative mx-3 my-2 grid grid-cols-2 gap-x-1 text-4xl font-bold text-text dark:text-white lg:text-6xl"
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
                                        class="h-px w-full bg-border dark:bg-countdown-stroke-dark"
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
                                        class="h-px w-full bg-border dark:bg-countdown-stroke-dark"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="relative mx-3 my-2 grid grid-cols-3 gap-x-1 text-4xl font-bold text-text dark:text-white lg:gap-x-2 lg:text-6xl"
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
                                        class="h-px w-full bg-border dark:bg-countdown-stroke-dark"
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
                                        class="h-px w-full bg-border dark:bg-countdown-stroke-dark"
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
                                        class="h-px w-full bg-border dark:bg-countdown-stroke-dark"
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
                                v-if="duringJourney"
                                class="mt-6 h-0 w-0 rounded-xl border-2 border-cta-border bg-background py-2 font-bold hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[80%] xl:w-[110%]"
                            >
                                <T
                                    key-name="journey.button.countdown.calendar"
                                />
                            </button>
                            <button
                                v-else-if="journeyEnded"
                                class="mt-6 h-0 w-0 rounded-xl border-2 border-cta-border bg-background py-2 font-bold hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                                @click="jsConfetti.addConfetti()"
                            >
                                <T
                                    key-name="journey.button.countdown.celebrate"
                                />
                            </button>
                            <button
                                v-else
                                class="mt-6 h-0 w-0 rounded-xl border-2 border-cta-border bg-background py-2 font-bold hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                            >
                                <T
                                    key-name="journey.button.countdown.planning"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button @click="isActivityDialogVisible = !isActivityDialogVisible">
            Create activity
        </button>
        <ActivityDialog
            :id="journeyId.toString()"
            :visible="isActivityDialogVisible"
            @close="isActivityDialogVisible = false"
        />
    </div>
</template>
