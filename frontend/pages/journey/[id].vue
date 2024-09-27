<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { differenceInDays, differenceInHours } from "date-fns";

const route = useRoute();
const confirm = useConfirm();
const toast = useToast();
const journeyStore = useJourneyStore();
const activityStore = useActivityStore();
const client = useSanctumClient();
const { t } = useTranslate();

const journeyId = route.params.id;
const activityDataLoaded = ref(false);

const duringJourney = ref(false);
const journeyEnded = ref(false);
const day = ref(0);
const tensDays = ref(0);
const hundredsDays = ref(0);

const isMemberSidebarVisible = ref(false);
const isMenuSidebarVisible = ref(false);
const isActivityDialogVisible = ref(false);

const uploadResult = ref();
const upload = ref();
const calendar = ref();

definePageMeta({
    middleware: ["sanctum:auth"],
});

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

const { data: users } = await useAsyncData<User[]>("users", () =>
    client(`/api/journey/${journeyId}/user`),
);

const { data: currUser } = await useAsyncData<User>("userRole", async () => {
    const response = await client(`/api/journey/${journeyId}/user/me`);
    const { user_id, ...rest } = response;
    return { id: user_id, ...rest };
});

const journeyData = data as Ref<Journey>;
journeyStore.setJourney(journeyData);

const title = journeyData.value.name;
journeyData.value.invite =
    window.location.origin + "/invite/" + journeyData.value.invite;
useHead({
    title: `${title} | JourneyPlanner`,
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
    journeyStore.setJourney(journey);
    useHead({
        title: `${journey.name} | JourneyPlanner`,
    });
    calculateDays(journey.from, journey.to);
}

const handleUpload = (result: string) => {
    uploadResult.value = result;
};

const confirmLeave = (event: Event) => {
    isMemberSidebarVisible.value = false;
    isMenuSidebarVisible.value = false;
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
</script>

<template>
    <div class="flex flex-col font-nunito text-text dark:text-natural-50">
        <IdMemberSidebar
            :journey-i-d="String(journeyId)"
            :is-member-sidebar-visible="isMemberSidebarVisible"
            :invite="String(journeyStore.getInvite())"
            :users="users!"
            :curr-user="currUser!"
            @leave-journey="confirmLeave"
            @close="isMemberSidebarVisible = false"
        />
        <IdMenuSidebar
            :is-menu-sidebar-visible="isMenuSidebarVisible"
            :curr-user="currUser!"
            @leave-journey="confirmLeave"
            @journey-edited="journeyEdited"
            @close="isMenuSidebarVisible = false"
        />
        <div
            id="header"
            class="mt-5 flex w-full items-center justify-between px-4 font-semibold"
        >
            <NuxtLink
                to="/dashboard"
                class="group flex items-center sm:ml-1 md:ml-2"
            >
                <SvgDashboardIcon class="h-7 w-7 md:h-6 md:w-6" />
                <p class="hidden text-2xl group-hover:underline sm:block">
                    Dashboard
                </p>
            </NuxtLink>
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
        <IdTicketSection
            :daysto-end="daystoEnd"
            :day="day"
            :tens-days="tensDays"
            :hundreds-days="hundredsDays"
            :during-journey="duringJourney"
            :journey-ended="journeyEnded"
            :curr-user="currUser!"
            :calendar-ref="calendar"
            :upload-ref="upload"
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
        <IdActivitySection
            :curr-user="currUser!"
            :is-activity-dialog-visible="isActivityDialogVisible"
            @close="isActivityDialogVisible = false"
        />
        <div ref="calendar">
            <IdJourneyCalendar
                :id="journeyId.toString()"
                :current-user-role="currUser?.role ?? 0"
                :journey-ended="journeyEnded"
                :during-journey="duringJourney"
                :journey-startdate="journeyData.from"
                :journey-enddate="journeyData.to"
            />
        </div>
        <IdActivityMap v-if="activityDataLoaded" />
        <div
            ref="upload"
            class="flex items-center justify-center md:justify-start"
        >
            <IdUpload @uploaded="handleUpload" />
        </div>
        <div class="flex items-center justify-center md:justify-start">
            <IdMediaGallery :upload-data="uploadResult" />
        </div>

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
