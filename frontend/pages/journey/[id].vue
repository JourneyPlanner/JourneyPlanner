<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { differenceInDays, differenceInHours } from "date-fns";
import QRCode from "qrcode";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const route = useRoute();
const router = useRouter();
const confirm = useConfirm();
const toast = useToast();
const journeyStore = useJourneyStore();
const activityStore = useActivityStore();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();
const { t } = useTranslate();
const fullConfig = resolveConfig(tailwindConfig);
const echo = useEcho();

const journeyId = route.params.id;
const activityDataLoaded = ref(false);

const duringJourney = ref(false);
const journeyEnded = ref(false);
const day = ref(0);
const tensDays = ref(0);
const hundredsDays = ref(0);

const users = ref<User[]>();
const currUser = ref<User>();

const isMemberSidebarVisible = ref(false);
const isMenuSidebarVisible = ref(false);
const isActivityDialogVisible = ref(false);

const isUnlockDialogVisible = ref(false);
const qrcode = ref("");
const qrcodeTolgeeKey = ref("");
const isQRCodeVisible = ref(false);

const uploadResult = ref();
const upload = ref();
const calendar = ref();
const clearCalendar = ref(false);
const template = ref();

onMounted(() => {
    if (route.query.username) {
        isMemberSidebarVisible.value = true;
    }

    calculateDays(journeyData.value.from, journeyData.value.to);

    subscribeToChannel();
});

function subscribeToChannel() {
    const name = "App.Models.Journey." + journeyId;

    echo.private(name)
        .listen(".JourneyUpdated", (e: object) => journeyEdited(e.model))
        .listen(".ActivityUpdated", (e: object) => activityUpdated(e))
        .listen(".ActivityCreated", (e: object) => activityCreated(e))
        .error((e: object) => {
            console.error("Private channel error", e);
        });
}

function activityUpdated(e: object) {
    console.log(e);
}

function activityCreated(e: object) {
    console.log(e);
}

const { data, error } = await useAsyncData("journey", () =>
    client(`/api/journey/${journeyId}`),
);

if (error.value?.statusCode === 404) {
    if (
        (!isAuthenticated.value &&
            localStorage.getItem("JP_guest_journey_id")) ||
        localStorage.getItem("JP_invite_journey_id")
    ) {
        localStorage.removeItem("JP_guest_journey_id");
        localStorage.removeItem("JP_invite_journey_id");
    }
    throw createError({
        statusCode: 404,
        data: "isTolgeeKey",
        statusMessage: "error.journey.notfound",
        fatal: true,
    });
} else if (error.value?.statusCode === 403) {
    throw createError({
        statusCode: 403,
        data: "isTolgeeKey",
        statusMessage: "error.journey.access",
        fatal: true,
    });
}

if (isAuthenticated.value) {
    localStorage.removeItem("JP_guest_journey_id");
    localStorage.removeItem("JP_invite_journey_id");
}

await client(`/api/journey/${journeyId}/activity`, {
    async onResponse({ response }) {
        if (response.ok) {
            activityStore.setActivities(response._data.activities);
            activityDataLoaded.value = true;
        }
    },
});

await client(`/api/me/template?journey_id=${journeyId}`, {
    async onResponse({ response }) {
        if (response.ok) {
            if (response._data.data) {
                template.value = response._data.data[0];
            }
        }
    },
});

const journeyData = data as Ref<Journey>;
journeyStore.setJourney(journeyData);

const title = journeyData.value.name;
journeyData.value.invite =
    window.location.origin + "/invite/" + journeyData.value.invite;
useHead({
    title: `${title} | JourneyPlanner`,
});

if (isAuthenticated.value) {
    const { data } = await useAsyncData<User[]>("users", () =>
        client(`/api/journey/${journeyId}/user`),
    );

    if (data.value !== null) {
        users.value = data.value;
    }

    const { data: curr } = await useAsyncData<User>("userRole", async () => {
        const response = await client(`/api/journey/${journeyId}/user/me`);
        const { user_id, ...rest } = response;
        return { id: user_id, ...rest };
    });

    if (curr.value !== null) {
        currUser.value = curr.value;
    }
}

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
QRCode.toDataURL(journeyStore.getInvite(), opts, function (error, url) {
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
            "text-mahagony-500 dark:text-mahagony-400 hover:underline font-bold",
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

async function journeyEdited(journey: Journey) {
    clearCalendar.value = false;
    journeyStore.updateJourney(journey);
    useHead({
        title: `${journey.name} | JourneyPlanner`,
    });
    calculateDays(journey.from, journey.to);

    await client(`/api/journey/${journeyId}/activity`, {
        async onResponse({ response }) {
            if (response.ok) {
                activityStore.setActivities(response._data.activities);
                activityDataLoaded.value = true;
                clearCalendar.value = true;
            }
        },
    });
}

const handleUpload = (result: string) => {
    uploadResult.value = result;
};

/**
 * API call to leave the journey
 * success: toast message and redirect to dashboard
 * error: toast message
 */
async function leaveJourney() {
    await client(`/api/journey/${journeyStore.getID()}/leave`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("leave.journey.toast.success.heading"),
                    detail: t.value("leave.journey.toast.success.detail"),
                    life: 3000,
                });

                if (!isAuthenticated.value) {
                    localStorage.removeItem("JP_guest_journey_id");
                    await navigateTo("/journey/new");
                } else {
                    await navigateTo("/dashboard");
                }
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

function openQRCode(tolgeeKey: string) {
    isQRCodeVisible.value = true;
    qrcodeTolgeeKey.value = tolgeeKey;
}

function scrollToTarget(target: string) {
    if (target === "uploadRef") {
        scroll(upload.value);
    } else if (target === "calendarRef") {
        scroll(calendar.value);
    }
}
</script>

<template>
    <div
        class="flex flex-col overflow-x-hidden font-nunito text-text dark:text-natural-50"
    >
        <JourneyIdMemberSidebar
            :journey-i-d="String(journeyId)"
            :is-member-sidebar-visible="isMemberSidebarVisible"
            :invite="String(journeyStore.getInvite())"
            :users="users! || []"
            :curr-user="currUser! || {}"
            @leave-journey="confirmLeave"
            @close="isMemberSidebarVisible = false"
            @open-qrcode="openQRCode"
            @open-unlock-dialog="isUnlockDialogVisible = true"
        />
        <JourneyIdMenuSidebar
            :is-menu-sidebar-visible="isMenuSidebarVisible"
            :curr-user="currUser! || {}"
            :journey-id="String(journeyId)"
            :template="template"
            @leave-journey="confirmLeave"
            @journey-edited="journeyEdited"
            @close="isMenuSidebarVisible = false"
            @open-unlock-dialog="isUnlockDialogVisible = true"
        />
        <div
            id="header"
            class="mt-5 flex w-full items-center justify-between px-4 font-semibold"
        >
            <button
                to="/dashboard"
                class="flex items-center sm:ml-1 md:ml-2"
                :class="!isAuthenticated ? 'blur-[2px]' : 'group'"
                @click="
                    !isAuthenticated
                        ? (isUnlockDialogVisible = true)
                        : router.push('/dashboard')
                "
            >
                <SvgDashboardIcon
                    class="h-7 w-7 fill-text dark:fill-natural-50 md:h-6 md:w-6"
                />
                <p class="hidden text-2xl group-hover:underline sm:block">
                    Dashboard
                </p>
            </button>
            <div class="flex flex-row items-center">
                <span
                    class="pi pi-users ml-10 mr-5 text-3xl hover:cursor-pointer"
                    @click="isMemberSidebarVisible = true"
                />
                <span
                    class="pi pi-bars mr-2 text-3xl hover:cursor-pointer sm:mr-5 md:mr-8"
                    @click="isMenuSidebarVisible = true"
                />
            </div>
        </div>
        <JourneyIdTicketSection
            :daysto-end="daystoEnd"
            :day="day"
            :tens-days="tensDays"
            :hundreds-days="hundredsDays"
            :during-journey="duringJourney"
            :journey-ended="journeyEnded"
            :curr-user="currUser! || {}"
            @scroll-to-target="scrollToTarget"
            @open-activity-dialog="isActivityDialogVisible = true"
        />
        <div id="divider" class="flex justify-center md:justify-start">
            <div
                class="flex w-[90%] items-end sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <Divider
                    type="solid"
                    class="border text-natural-100 md:hidden md:w-0"
                />
            </div>
        </div>
        <JourneyIdActivitySection
            :curr-user="currUser! || {}"
            :is-activity-dialog-visible="isActivityDialogVisible"
            :journey-start="fromDate"
            :journey-end="toDate"
            @close="isActivityDialogVisible = false"
        />
        <div ref="calendar">
            <JourneyIdJourneyCalendar
                :id="journeyId.toString()"
                :current-user-role="currUser?.role ?? 0"
                :journey-ended="journeyEnded"
                :during-journey="duringJourney"
                :journey-startdate="journeyData.from"
                :journey-enddate="journeyData.to"
                :clear="clearCalendar"
            />
        </div>
        <JourneyIdActivityMap v-if="activityDataLoaded" />
        <div
            ref="upload"
            class="relative flex items-center justify-center md:justify-start"
            :class="!isAuthenticated ? 'cursor-not-allowed' : ''"
            @click="!isAuthenticated ? (isUnlockDialogVisible = true) : null"
        >
            <JourneyIdUpload
                :class="!isAuthenticated ? 'blur-[1.75px]' : ''"
                @uploaded="handleUpload"
            />
            <div
                v-if="!isAuthenticated"
                class="absolute bottom-0 flex h-40 w-[90%] items-center sm:h-[13rem] sm:w-5/6 md:ml-[10%] md:h-[17rem] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <div class="flex w-full items-center justify-center">
                    <button
                        class="w-32 rounded-md border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 text-center font-medium hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="isUnlockDialogVisible = true"
                    >
                        <T key-name="journey.unlock.button" />
                    </button>
                </div>
            </div>
        </div>
        <div
            class="relative flex items-center justify-center md:justify-start"
            :class="!isAuthenticated ? 'cursor-not-allowed' : ''"
            @click="!isAuthenticated ? (isUnlockDialogVisible = true) : null"
        >
            <JourneyIdMediaGallery
                :upload-data="uploadResult"
                :class="!isAuthenticated ? 'blur-[1.75px]' : ''"
            />
            <div
                v-if="!isAuthenticated"
                class="absolute bottom-0 flex h-40 w-[90%] items-center sm:h-[13rem] sm:w-5/6 md:ml-[10%] md:h-[17rem] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
            >
                <div class="flex w-full items-center justify-center">
                    <button
                        class="w-32 rounded-md border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 text-center font-medium hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="isUnlockDialogVisible = true"
                    >
                        <T key-name="journey.unlock.button" />
                    </button>
                </div>
            </div>
        </div>

        <div id="extra-dialogs">
            <JourneyIdDialogsUnlockDialog
                :is-unlock-dialog-visible="isUnlockDialogVisible"
                @close-unlock-dialog="isUnlockDialogVisible = false"
                @open-qrcode="openQRCode"
            />

            <JourneyIdDialogsQRCodeDialog
                :qrcode="qrcode"
                :visible="isQRCodeVisible"
                :tolgee-key="qrcodeTolgeeKey"
                @close="isQRCodeVisible = false"
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
                        class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito gap-x-5',
                    },
                    closeButton: {
                        class: 'bg-natural-50 dark:bg-natural-900 text-natural-500 hover:text-text dark:text-natural-400 hover:dark:text-natural-50 font-nunito',
                    },
                    closeButtonIcon: {
                        class: 'h-5 w-5',
                    },
                }"
            />
        </div>
    </div>
</template>
