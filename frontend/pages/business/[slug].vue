<script setup lang="ts">
import { useTolgee, useTranslate } from "@tolgee/vue";

const route = useRoute();
const { t } = useTranslate();
const router = useRouter();
const client = useSanctumClient();
const toast = useToast();
const tolgee = useTolgee(["language"]);

const slug = ref(route.params.slug);
const screenWidth = ref(window.innerWidth);

const images = reactive({
    banner: {
        alt_text: "",
        link: "",
    },
    image: {
        alt_text: "",
        link: "",
    },
});

const texts = reactive({
    button: "",
    button_link: "",
    company_name: "",
    text: "",
});

const { data, error } = await useAsyncData("business", () =>
    client(
        `/api/business/${slug.value}?language=${tolgee.value.getLanguage()}`,
    ),
);

if (error.value) {
    if (error.value.statusCode === 404) {
        throw createError({
            statusCode: 404,
            data: "isTolgeeKey",
            message: "error.business.notfound",
            fatal: true,
        });
    } else {
        throw createError({
            statusCode: 500,
            message: "An error occurred while fetching the data",
            fatal: true,
        });
    }
} else {
    Object.assign(texts, data.value.texts);
    Object.assign(images, data.value.images);

    useHead({
        title: `${texts.company_name} | JourneyPlanner`,
    });
}

const backRoute = ref<string>("/dashboard?tab=templates");
const ALLOWED_ROUTES = ["/journey", "/dashboard?tab=templates", "/user"];

const showMoreTemplates = ref(false);
const openedTemplate = ref<Template | undefined>();
const isTemplatePopupVisible = ref<boolean>(false);
const templatesLoader = ref<HTMLElement | undefined>();

const maxDisplayedTemplates = computed(() => {
    if (screenWidth.value >= 1024) return 8;
    if (screenWidth.value >= 640) return 6;
    return 4;
});

const maxDisplayedActivities = computed(() => {
    if (screenWidth.value >= 1280) return 21;
    if (screenWidth.value >= 1024) return 18;
    if (screenWidth.value >= 768) return 15;
    if (screenWidth.value >= 640) return 8;
    return 6;
});
const activityLoader = ref<HTMLElement | undefined>();
const showMoreActivities = ref<boolean>(false);

const isActivityInfoVisible = ref<boolean>(false);
const activityId = ref<string | null>(null);
const journeyId = ref<string | null>(null);
const address = ref<string | null>(null);
const cost = ref<string | null>(null);
const description = ref<string | null>(null);
const email = ref<string | null>(null);
const estimated_duration = ref<string | null>(null);
const link = ref<string | null>(null);
const mapbox_id = ref<string | null>(null);
const name = ref<string | null>(null);
const opening_hours = ref<string | null>(null);
const phone = ref<string | null>(null);

onMounted(async () => {
    const lastRoute = router.options.history.state.back as string;

    if (
        (lastRoute &&
            lastRoute !== "" &&
            ALLOWED_ROUTES.some((route) => lastRoute.startsWith(route))) ||
        lastRoute === "/"
    ) {
        backRoute.value = lastRoute;
    } else {
        backRoute.value = "/dashboard?tab=templates";
    }

    window.addEventListener("resize", updateScreenWidth);

    if (
        route.query.id &&
        route.query.id !== "undefined" &&
        route.query.id !== "null" &&
        route.query.id !== ""
    ) {
        client(`/api/template/${route.query.id}`, {
            async onResponse({ response }) {
                if (response.ok) {
                    openedTemplate.value = response._data;
                    isTemplatePopupVisible.value = true;
                }
            },
            async onResponseError({ response }) {
                if (response.status === 404) {
                    toast.add({
                        severity: "error",
                        summary: t.value("template.notfound.summary"),
                        detail: t.value("template.notfound.summary.detail"),
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
                router.push({
                    query: {},
                });
            },
        });
    }

    watch(isTemplatePopupVisible, (value) => {
        if (value) {
            router.push({
                query: {
                    id: openedTemplate.value ? openedTemplate.value.id : null,
                },
            });
        } else {
            router.push({
                query: {},
            });
        }
    });
});

onUnmounted(async () => {
    window.removeEventListener("resize", updateScreenWidth);
});

const {
    data: activities,
    moreDataAvailable: moreActivitiesAvailable,
    toggle: toggleActivities,
    toggleText: toggleTextActivities,
} = await useInfiniteScroll<Activity>({
    loader: activityLoader,
    showMoreData: showMoreActivities,
    showMoreDataText: t.value("subdomain.activities.showMore"),
    showLessDataText: t.value("subdomain.activities.showLess"),
    identifier: "business-activities",
    apiEndpoint: `/api/business/${slug.value}/activities`,
    params: {
        per_page: 21,
    },
});

const {
    data: templates,
    moreDataAvailable: moreTemplatesAvailable,
    toggle: toggleTemplates,
    toggleText: toggleTextTemplates,
} = await useInfiniteScroll<Template>({
    loader: templatesLoader,
    showMoreData: showMoreTemplates,
    showMoreDataText: t.value("subdomain.templates.showMore"),
    showLessDataText: t.value("subdomain.templates.showLess"),
    identifier: "business-templates",
    apiEndpoint: `/api/business/${slug.value}/templates`,
    params: {
        per_page: 8,
    },
});

const updateScreenWidth = () => {
    screenWidth.value = window.innerWidth;
};

function openTemplateDialog(template: Template) {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
}

function openActivityDialog(activity: Activity) {
    activityId.value = activity.id;
    journeyId.value = activity.journey_id;
    address.value = activity.address;
    cost.value = activity.cost;
    description.value = activity.description;
    email.value = activity.email;
    estimated_duration.value = activity.estimated_duration;
    link.value = activity.link;
    mapbox_id.value = activity.mapbox_id;
    name.value = activity.name;
    opening_hours.value = activity.opening_hours;
    phone.value = activity.phone;

    isActivityInfoVisible.value = true;
}
</script>

<template>
    <div class="text-text dark:text-natural-50">
        <div
            class="relative h-44 bg-cover bg-center lg:h-96"
            :style="{ backgroundImage: `url(${images.banner.link})` }"
        >
            <div
                class="absolute inset-0 top-20 bg-gradient-to-b from-natural-50/0 to-background dark:from-background-dark/0 dark:to-background-dark lg:top-48"
            ></div>
            <div
                class="absolute left-2.5 top-2.5 rounded-xl border-2 border-natural-400 bg-natural-50 text-text drop-shadow-lg backdrop-blur-xl hover:border-natural-400 hover:bg-natural-200 dark:border-natural-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-natural-600 dark:hover:bg-natural-950 lg:left-5 lg:top-5"
            >
                <NuxtLink
                    :to="backRoute"
                    class="ml-1 mr-2 flex items-center md:ml-2 md:mr-3"
                >
                    <i class="pi pi-angle-left text-2xl" />
                    <span class="ml-1.5 mt-0.5 text-xl md:text-2xl">
                        <T key-name="common.back" />
                    </span>
                </NuxtLink>
            </div>
            <img
                :src="images.banner.link"
                :alt="images.banner.alt_text"
                class="sr-only"
            />
        </div>
        <div class="flex flex-col gap-y-8 px-5 lg:px-16">
            <div
                id="details"
                class="flex w-full flex-col justify-between gap-x-5 overflow-hidden lg:flex-row lg:pt-5"
            >
                <div id="text" class="flex-1 lg:w-3/5">
                    <h1 class="mb-4 text-2xl font-semibold">
                        {{ texts.company_name }}
                    </h1>
                    <p class="text-lg">
                        {{ texts.text }}
                    </p>
                    <div class="mb-5 max-md:hidden">
                        <button
                            class="mt-6 w-44 rounded-xl border-2 border-dandelion-300 bg-natural-50 py-1 text-center text-lg hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600 lg:w-48"
                        >
                            <a :href="texts.button_link" target="_blank">{{
                                texts.button
                            }}</a>
                        </button>
                    </div>
                </div>
                <div id="image" class="mt-2 flex justify-center lg:w-2/5">
                    <NuxtImg
                        :src="images.image.link"
                        :alt="images.image.alt_text"
                        class="max-h-[320px] rounded-xl object-contain"
                    />
                </div>
                <div class="mb-5 flex justify-center md:hidden">
                    <button
                        class="mt-3.5 w-44 rounded-lg border-2 border-dandelion-300 bg-natural-50 py-0.5 text-center text-lg hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600 lg:w-48"
                    >
                        <a :href="texts.button_link" target="_blank">{{
                            texts.button
                        }}</a>
                    </button>
                </div>
            </div>
            <div id="activity-section">
                <h2 class="text-xl font-medium">
                    <T key-name="subdomain.heading.activities" />
                </h2>
                <TransitionGroup
                    id="activities"
                    name="fade"
                    tag="div"
                    class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7"
                >
                    <BusinessActivityCard
                        v-for="(activity, index) in activities"
                        v-show="
                            showMoreActivities || index < maxDisplayedActivities
                        "
                        :id="activity.id"
                        :key="activity.id"
                        :activity="activity"
                        @open-activity-dialog="openActivityDialog"
                    />
                </TransitionGroup>
                <div ref="activityLoader" class="col-span-full">
                    <div v-if="moreActivitiesAvailable && showMoreActivities">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="subdomain.activities.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="
                        activities.length > 0 &&
                        (moreActivitiesAvailable ||
                            activities.length > maxDisplayedActivities)
                    "
                    class="mt-4 flex justify-center"
                >
                    <button
                        class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                        @click="toggleActivities"
                    >
                        <span>{{ toggleTextActivities }}</span>
                        <span
                            class="pi mt-1"
                            :class="
                                showMoreActivities
                                    ? 'pi-chevron-up order-first mb-1'
                                    : 'pi-chevron-down'
                            "
                        />
                    </button>
                </div>
            </div>
            <div id="template-section">
                <h2 class="text-xl font-medium">
                    <T key-name="subdomain.heading.templates" />
                </h2>
                <TransitionGroup
                    name="fade"
                    tag="div"
                    class="relative mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-4"
                >
                    <TemplateCard
                        v-for="(template, index) in templates"
                        v-show="
                            showMoreTemplates || index < maxDisplayedTemplates
                        "
                        :key="'template-card' + template.id"
                        class="hidden md:block"
                        :template="template"
                        :displayed-in-profile="false"
                        @open-template="openTemplateDialog(template)"
                    />
                    <TemplateCardSmall
                        v-for="(template, index) in templates"
                        v-show="
                            showMoreTemplates || index < maxDisplayedTemplates
                        "
                        :key="'template-card-small' + template.id"
                        class="md:hidden"
                        :template="template"
                        :displayed-in-profile="false"
                        @open-template="openTemplateDialog(template)"
                    />
                </TransitionGroup>
                <div v-if="templates.length === 0" class="col-span-full">
                    <T key-name="subdomain.template.none" />
                </div>
                <div ref="templatesLoader" class="col-span-full">
                    <div v-if="moreTemplatesAvailable && showMoreTemplates">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="subdomain.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="
                        templates.length > 0 &&
                        (moreTemplatesAvailable || templates.length)
                    "
                    class="mt-4 flex justify-center"
                >
                    <button
                        class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                        @click="toggleTemplates"
                    >
                        <span>{{ toggleTextTemplates }}</span>
                        <span
                            class="pi mt-1"
                            :class="
                                showMoreTemplates
                                    ? 'pi-chevron-up order-first mb-1'
                                    : 'pi-chevron-down'
                            "
                        />
                    </button>
                </div>
            </div>
        </div>
        <div id="dialogs">
            <JourneyIdDialogsActivityDialog
                v-if="isActivityInfoVisible"
                :id="journeyId ?? ''"
                :visible="isActivityInfoVisible"
                :activity-id="activityId ?? ''"
                :only-show="true"
                :address="address ?? ''"
                :cost="cost ?? ''"
                :description="description ?? ''"
                :email="email ?? ''"
                :estimated-duration="estimated_duration ?? ''"
                :link="link ?? ''"
                :mapbox-id="mapbox_id ?? ''"
                :name="name ?? ''"
                :opening-hours="opening_hours ?? ''"
                :phone="phone ?? ''"
                :update="false"
                @close="isActivityInfoVisible = false"
            />
            <TemplatePopup
                v-if="openedTemplate"
                :template="openedTemplate"
                :is-template-dialog-visible="isTemplatePopupVisible"
                @close="
                    isTemplatePopupVisible = false;
                    openedTemplate = undefined;
                "
            />
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.5s ease,
        transform 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(30px);
}
</style>
