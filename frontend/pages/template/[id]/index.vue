<script setup lang="ts">
const route = useRoute();
const router = useRouter();

const activityStore = useActivityStore();
const journeyStore = useJourneyStore();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();

const templateID = route.params.id;
const activityDataLoaded = ref(false);

const backTolgeeKey = ref("template.back.to.templates");
const backRoute = ref("/dashboard?tab=templates");
const createJourneyFromTemplate = `/journey/new/template/${templateID}`;

onMounted(() => {
    const lastRoute = router.options.history.state.back as string;

    if (lastRoute) {
        if (isAuthenticated.value) {
            if (lastRoute.startsWith("/dashboard?tab=templates")) {
                backTolgeeKey.value = "template.back.to.templates";
                backRoute.value = lastRoute;
            } else if (lastRoute.startsWith("/journey/new")) {
                backTolgeeKey.value = "template.back.to.new.journey";
                backRoute.value = lastRoute;
            }
        } else {
            backTolgeeKey.value = "common.back";
            backRoute.value = lastRoute;
        }
    } else {
        if (isAuthenticated.value) {
            backTolgeeKey.value = "template.back.to.templates";
            backRoute.value = "/dashboard?tab=templates";
        } else {
            backTolgeeKey.value = "template.back.to.new.journey";
            backRoute.value = "/journey/new";
        }
    }
});

const { data, error } = await useAsyncData("journey", () =>
    client(`/api/template/${templateID}`),
);

if (error.value?.statusCode === 404) {
    throw createError({
        statusCode: 404,
        data: "isTolgeeKey",
        message: "template.notfound.summary",
        fatal: true,
    });
}

await client(`/api/template/${templateID}/activity`, {
    async onResponse({ response }) {
        if (response.ok) {
            activityStore.setActivities(response._data.activities);
            activityDataLoaded.value = true;
        } else {
            throw createError({
                statusCode: response.status,
                message: response.statusText,
                fatal: true,
            });
        }
    },
});

const templateData = data as Ref<Journey>;
journeyStore.setJourney(templateData.value);

const title = templateData.value.name;
useHead({
    title: `${title} | Template | JourneyPlanner`,
});
</script>

<template>
    <div class="flex flex-col font-nunito text-text dark:text-natural-50">
        <div
            id="header"
            class="mt-5 flex w-full items-center justify-between font-semibold md:mt-3"
        >
            <NuxtLink
                :to="backRoute"
                class="group flex items-center sm:ml-1 md:ml-2"
            >
                <i class="pi pi-angle-left text-2xl" />
                <span
                    class="ml-1.5 mt-0.5 text-xl group-hover:underline md:text-2xl"
                >
                    <T :key-name="backTolgeeKey" />
                </span>
            </NuxtLink>
            <NuxtLink
                :to="createJourneyFromTemplate"
                class="ml-5 mr-4 text-nowrap rounded-xl border-2 border-dandelion-300 bg-background px-2 py-1 text-sm hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 md:text-base"
            >
                <T key-name="template.use" />
            </NuxtLink>
        </div>
        <JourneyIdTicketSection
            :daysto-end="-1"
            :day="-1"
            :tens-days="-1"
            :hundreds-days="-1"
            :during-journey="false"
            :journey-ended="false"
            :is-template="true"
            :template-id="templateID.toString()"
            :curr-user="{
                id: 'TEMPLATE',
                display_name: 'TEMPLATE',
                username: 'TEMPLATE',
                email: 'TEMPLATE',
                role: 0,
            }"
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
            :curr-user="{
                id: 'TEMPLATE',
                display_name: 'TEMPLATE',
                username: 'TEMPLATE',
                email: 'TEMPLATE',
                role: 0,
            }"
            :is-activity-dialog-visible="false"
        />
        <div ref="calendar">
            <JourneyIdJourneyCalendar
                :id="templateID.toString()"
                :current-user-role="0"
                :journey-ended="false"
                :during-journey="false"
                :journey-startdate="journeyStore.getFromDate()"
                :journey-enddate="journeyStore.getToDate()"
            />
        </div>
        <JourneyIdActivityMap v-if="activityDataLoaded" />
        <div class="my-5 flex justify-center">
            <NuxtLink
                :to="createJourneyFromTemplate"
                class="ml-5 mr-4 text-nowrap rounded-xl border-2 border-dandelion-300 bg-background px-2 py-1 text-sm hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 md:text-base"
            >
                <T key-name="template.use" />
            </NuxtLink>
        </div>
    </div>
</template>
