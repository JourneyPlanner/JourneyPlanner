<script setup lang="ts">
const route = useRoute();

const activityStore = useActivityStore();
const journeyStore = useJourneyStore();
const client = useSanctumClient();

const templateID = route.params.id;
const activityDataLoaded = ref(false);

const { data, error } = await useAsyncData("journey", () =>
    client(`/api/template/${templateID}`),
);

if (error.value?.statusCode === 404) {
    throw createError({
        statusCode: 404,
        statusMessage: "Template not found",
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
                statusMessage: response.statusText,
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
            class="mt-5 flex w-full items-center justify-between font-semibold"
        >
            <NuxtLink
                to="/dashboard?tab=templates"
                class="group flex items-center sm:ml-1 md:ml-2"
            >
                <i class="pi pi-angle-left text-2xl" />
                <p class="hidden text-2xl group-hover:underline sm:block">
                    <T key-name="template.back" />
                </p>
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
        <div id="extra-dialogs" />
    </div>
</template>
