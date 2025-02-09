<script setup lang="ts">
import { useTolgee, useTranslate } from "@tolgee/vue";

const route = useRoute();
const { t } = useTranslate();
const router = useRouter();
const client = useSanctumClient();
const toast = useToast();
const tolgee = useTolgee(["language"]);

const slug = ref(route.params.slug);

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

const activities = ref<Activity[]>([]);

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
            message: "An error occurred while fetching user data",
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

//const allowedRoutes = ["/journey", "/dashboard?tab=templates"];
//const isCloseIcon = ref(false);

const showMoreTemplates = ref(false);
const toggleText = ref(t.value("profile.showMore") + texts.company_name);
const toggleTextShort = ref(t.value("profile.showMore.short"));

const templates = ref<Template[]>([]);
const openedTemplate = ref<Template | undefined>();
const isTemplatePopupVisible = ref<boolean>(false);
const moreTemplatesAvailable = ref<boolean>(true);
const templatesCursor = ref<string | null>(null);
const nextTemplatesCursor = ref<string | null>(null);
const templatesObserver = ref<IntersectionObserver>();
const templatesLoader = ref<HTMLElement | undefined>();

const activityLoader = ref<HTMLElement | undefined>();
const showMoreActivities = ref<boolean>(false);
const moreActivitiesAvailable = ref<boolean>(true);
const activitiesCursor = ref<string | null>(null);
const nextActivitiesCursor = ref<string | null>(null);
const activityObserver = ref<IntersectionObserver>();

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
    /*
    const lastRoute = router.options.history.state.back as string;
    if (
        lastRoute &&
        allowedRoutes.some((route) => lastRoute.startsWith(route))
    ) {
        isCloseIcon.value = true;
    } else if (route?.query?.journey) {
        isCloseIcon.value = true;
    } else {
        isCloseIcon.value = false;
    }
    */

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

onUnmounted(() => {
    if (templatesObserver.value && templatesLoader.value) {
        templatesObserver.value.unobserve(templatesLoader.value);
    }
});
/*
const { data: templateData, refresh: refreshBusinessTemplates } = await useAsyncData(
    "business-templates",
    () => client(`/api/business/${slug.value}/templates?cursor=${templatesCursor.value}`),
);*/
const templateData = ref();

watch(
    templateData,
    () => {
        if (templateData.value) {
            templates.value.push(...templateData.value.data);
            if (templateData.value.next_cursor === null) {
                moreTemplatesAvailable.value = false;
            } else {
                nextTemplatesCursor.value = templateData.value.next_cursor;
                moreTemplatesAvailable.value = true;
            }
        }
    },
    { immediate: true },
);

watch(showMoreTemplates, () => {
    if (showMoreTemplates.value) {
        if (templatesObserver.value) {
            templatesObserver.value.disconnect();
        }

        templatesObserver.value = new IntersectionObserver((entries) => {
            if (entries.length === 0) {
                return;
            }
            const target = entries[0];
            if (target.isIntersecting) {
                if (moreTemplatesAvailable.value && showMoreTemplates.value) {
                    templatesCursor.value = nextTemplatesCursor.value;
                    //refreshBusinessTemplates();
                }
            }
        });

        if (templatesLoader.value) {
            templatesObserver.value.observe(templatesLoader.value);
        }
    }
});

const { data: activityData, refresh: refreshBusinessActivities } =
    await useAsyncData("business-templates", () =>
        client(
            `/api/business/${slug.value}/activities?cursor=${templatesCursor.value}`,
        ),
    );

watch(showMoreActivities, () => {
    if (showMoreActivities.value) {
        if (activityObserver.value) {
            activityObserver.value.disconnect();
        }

        activityObserver.value = new IntersectionObserver((entries) => {
            if (entries.length === 0) {
                return;
            }
            const target = entries[0];
            if (target.isIntersecting) {
                if (moreActivitiesAvailable.value && showMoreActivities.value) {
                    activitiesCursor.value = nextActivitiesCursor.value;
                    refreshBusinessActivities();
                }
            }
        });

        if (activityLoader.value) {
            activityObserver.value.observe(activityLoader.value);
        }
    }
});

watch(
    activityData,
    () => {
        if (activityData.value) {
            activities.value.push(...activityData.value.data);
            if (activityData.value.next_cursor === null) {
                moreActivitiesAvailable.value = false;
            } else {
                nextActivitiesCursor.value = activityData.value.next_cursor;
                moreActivitiesAvailable.value = true;
            }
        }
    },
    { immediate: true },
);

/*
const toggle = () => {
    showMore.value = !showMore.value;
    toggleText.value = showMore.value
        ? t.value("profile.showLess") + username.value
        : t.value("profile.showMore") + username.value;
    toggleTextShort.value = showMore.value
        ? t.value("profile.showLess.short")
        : t.value("profile.showMore.short");
};
*/

/*
const navigateBack = () => {
    const lastRoute = router.options.history.state.back as string;
    if (
        lastRoute &&
        allowedRoutes.some((route) => lastRoute.startsWith(route))
    ) {
        router.back();
    } else if (route?.query?.journey) {
        router.push("/journey/" + route.query.journey);
    } else {
        router.push("/dashboard");
    }
};*/

function toggleActivities() {
    showMoreActivities.value = !showMoreActivities.value;
    toggleText.value = showMoreActivities.value
        ? t.value("profile.showLess")
        : t.value("profile.showMore");
    toggleTextShort.value = showMoreActivities.value
        ? t.value("profile.showLess.short")
        : t.value("profile.showMore.short");
}

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
    <div>
        <div
            class="relative h-96 bg-cover bg-center"
            :style="{ backgroundImage: `url(${images.banner.link})` }"
        >
            <div
                class="absolute inset-0 top-48 bg-gradient-to-b from-natural-50/0 to-natural-50"
            ></div>
            <img
                :src="images.banner.link"
                :alt="images.banner.alt_text"
                class="sr-only"
            />
        </div>
        <div class="flex flex-col gap-y-8 px-16">
            <div
                id="details"
                class="flex w-full justify-between gap-x-5 overflow-hidden pt-5"
            >
                <div id="text" class="w-3/5 flex-1">
                    <h1 class="mb-2 text-2xl font-medium">
                        JourneyPlanner for Businesses
                    </h1>
                    <p class="text-lg">
                        {{ texts.text }}
                    </p>
                    <button
                        class="mt-3.5 w-48 rounded-lg border-2 border-dandelion-300 bg-natural-50 py-0.5 text-center text-lg hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                    >
                        <a :href="texts.button_link" target="_blank">{{
                            texts.button
                        }}</a>
                    </button>
                </div>
                <div id="image" class="w-2/5">
                    <NuxtImg
                        :src="images.image.link"
                        :alt="images.image.alt_text"
                        class="max-h-[320px] rounded-xl object-contain"
                    />
                </div>
            </div>
            <div id="activity-section">
                <h2 class="text-xl font-medium">
                    <T key-name="subdomain.heading.activities" />
                </h2>
                <div id="activities" class="grid grid-cols-7 gap-x-2 gap-y-4">
                    <BusinessActivityCard
                        v-for="activity in activities"
                        :key="activity.id"
                        :activity="activity"
                        @open-activity-dialog="openActivityDialog"
                    />
                </div>
                <div ref="activityLoader" class="col-span-full">
                    <div v-if="moreActivitiesAvailable && showMoreActivities">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="dashboard.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="moreActivitiesAvailable"
                    class="mt-4 flex justify-center max-md:hidden"
                >
                    <button
                        class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                        @click="toggleActivities"
                    >
                        <span>{{ toggleText }}</span>
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
                <div
                    id="templates"
                    class="relative mt-2 grid grid-cols-2 gap-5 sm:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-6"
                >
                    <TemplateCard
                        v-for="template in templates"
                        :key="template.id"
                        class="hidden md:block"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="openTemplateDialog(template)"
                    />
                    <TemplateCardSmall
                        v-for="template in templates"
                        :key="template.id"
                        class="md:hidden"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="openTemplateDialog(template)"
                    />
                    <div
                        v-if="templates.length === 0"
                        class="col-span-full hidden md:block"
                    >
                        <T key-name="subdomain.template.none" />
                    </div>
                    <div
                        v-if="templates.length === 0"
                        class="col-span-full md:hidden"
                    >
                        <T key-name="subdomain.template.none" />
                    </div>
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
