<script setup lang="ts">
const route = useRoute();
const router = useRouter();

const activityStore = useActivityStore();
const journeyStore = useJourneyStore();
const client = useSanctumClient();

const templateID = route.params.id;
const activityDataLoaded = ref(false);

const backTolgeeKey = ref("template.back.to.templates");
const backRoute = ref("");

const isCreateTemplateVisible = ref(false);
const updateTemplate = ref(false);

const lastRoute = router.options.history.state.back as string;
backTolgeeKey.value = "common.back";
backRoute.value = lastRoute;

const { data, error } = await useAsyncData("journey", () =>
    client(`/api/template/${templateID}`),
);

if (error.value?.statusCode === 404) {
    throw createError({
        statusCode: 404,
        data: "isTolgeeKey",
        statusMessage: "error.template.notfound",
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

function closeTemplateDialog() {
    isCreateTemplateVisible.value = false;
    updateTemplate.value = false;
}

function changeTemplateDetails(template: Template) {
    journeyStore.setJourney(template);
    useHead({
        title: `${template.name} | JourneyPlanner`,
    });
}
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
            <button
                class="ml-5 mr-4 text-nowrap rounded-xl border-2 border-dandelion-300 bg-background px-2 py-1 text-sm hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 md:text-base"
                @click="isCreateTemplateVisible = true"
            >
                <T key-name="template.change.details" />
            </button>
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
                id: 'TEMPLATE_EDIT',
                display_name: 'TEMPLATE_EDIT',
                username: 'TEMPLATE_EDIT',
                role: 1,
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
                id: 'TEMPLATE_EDIT',
                display_name: 'TEMPLATE_EDIT',
                username: 'TEMPLATE_EDIT',
                role: 1,
            }"
            :is-activity-dialog-visible="false"
        />
        <div ref="calendar">
            <JourneyIdJourneyCalendar
                :id="templateID.toString()"
                :current-user-role="1"
                :journey-ended="false"
                :during-journey="false"
                :journey-startdate="journeyStore.getFromDate()"
                :journey-enddate="journeyStore.getToDate()"
            />
        </div>
        <JourneyIdActivityMap v-if="activityDataLoaded" />
        <div class="my-5 flex justify-center">
            <button
                class="ml-5 mr-4 text-nowrap rounded-xl border-2 border-dandelion-300 bg-background px-2 py-1 text-sm hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 md:text-base"
                @click="isCreateTemplateVisible = true"
            >
                <T key-name="template.change.details" />
            </button>
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
        <JourneyIdDialogsCreateTemplateDialog
            :is-create-template-visible="isCreateTemplateVisible"
            :update-template="true"
            :template-i-d="templateID.toString()"
            :template-name="data?.name"
            :template-descripton="data?.description"
            @updated-template="changeTemplateDetails"
            @close-template-dialog="closeTemplateDialog()"
        />
    </div>
</template>
