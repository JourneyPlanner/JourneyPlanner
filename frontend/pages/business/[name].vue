<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const route = useRoute();
const { t } = useTranslate();
const router = useRouter();
const client = useSanctumClient();
const toast = useToast();

//const username = ref(route.params.name);
//const displayname = ref("");

const activities = ref<Activity[]>([]);

/*
const { data, error } = await useAsyncData("user", () =>
    client(`/api/user/${username.value}`),
);

if (error.value) {
    if (error.value.statusCode === 404) {
        throw createError({
            statusCode: 404,
            message: "No user was found with that username",
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
    displayname.value = data.value.display_name;
    username.value = data.value.username;
    useHead({
        title: `${displayname.value} (@${username.value}) | JourneyPlanner`,
    });
}*/

const showMore = ref(false);
//const toggleText = ref(t.value("profile.showMore") + username.value);
//const toggleTextShort = ref(t.value("profile.showMore.short"));
const allowedRoutes = ["/journey", "/dashboard?tab=templates"];
const isCloseIcon = ref(false);
//const templates = ref<Template[]>([]);
const openedTemplate = ref<Template | undefined>();
const isTemplatePopupVisible = ref<boolean>(false);
const moreTemplatesAvailable = ref<boolean>(true);
const cursor = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const observer = ref<IntersectionObserver>();
const loader = ref<HTMLElement | undefined>();

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
        lastRoute &&
        allowedRoutes.some((route) => lastRoute.startsWith(route))
    ) {
        isCloseIcon.value = true;
    } else if (route?.query?.journey) {
        isCloseIcon.value = true;
    } else {
        isCloseIcon.value = false;
    }

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
    if (observer.value && loader.value) {
        observer.value.unobserve(loader.value);
    }
});
/*
const { data: templateData, refresh } = await useAsyncData(
    "userTemplates",
    () => client(`/api/user/${username.value}/template?cursor=${cursor.value}`),
);

watch(
    templateData,
    () => {
        if (templateData.value) {
            templates.value.push(...templateData.value.data);
            if (templateData.value.next_cursor === null) {
                moreTemplatesAvailable.value = false;
            } else {
                nextCursor.value = templateData.value.next_cursor;
                moreTemplatesAvailable.value = true;
            }
        }
    },
    { immediate: true },
);
*/
watch(showMore, () => {
    if (showMore.value) {
        if (observer.value) {
            observer.value.disconnect();
        }

        observer.value = new IntersectionObserver((entries) => {
            if (entries.length === 0) {
                return;
            }
            const target = entries[0];
            if (target.isIntersecting) {
                if (moreTemplatesAvailable.value && showMore.value) {
                    cursor.value = nextCursor.value;
                    //refresh();
                }
            }
        });

        if (loader.value) {
            observer.value.observe(loader.value);
        }
    }
});

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
/*
const openTemplateDialog = (template: Template) => {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
};
*/

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
        <!--
        <div
            class="h-72 bg-[linear-gradient(to_top,rgba(0,0,0,0.7),rgba(0,0,0,0.2)),url('https://placehold.co/1920x353')] bg-cover bg-center">
            <div class="flex h-full items-center justify-center text-white">
                <h1 class="text-4xl font-bold">Gradient direkt im Hintergrund</h1>
            </div>
        </div>
        -->
        <div
            class="relative h-72 bg-[url('https://placehold.co/1920x353')] bg-cover bg-center"
        >
            <div
                class="absolute inset-0 top-36 bg-gradient-to-b from-natural-50/0 to-natural-50"
            ></div>
            <div
                class="text-white relative z-10 flex h-full items-center justify-center"
            >
                <h1 class="text-4xl font-bold">Dein Text hier</h1>
            </div>
        </div>
        <div class="flex flex-col gap-y-5 px-16">
            <div id="details" class="flex w-full justify-between gap-x-5 pt-5">
                <div id="text" class="w-3/5">
                    <h1 class="mb-2 text-2xl font-medium">Company Name</h1>
                    <p class="text-lg">
                        Lorem ipsum sapientem ne neque dolor erat,eros solet
                        invidunt duo Quisque aliquid leo. Pretium patrioque
                        sociis eu nihil Cum enim ad, ipsum alii vidisse justo
                        id. Option porttitor diam voluptua. Cu Eam augue dolor
                        dolores quis, Nam aliquando elitr Etiam consetetur.
                        Fringilla lucilius mel adipiscing rebum. Sit nulla
                        Integer ad volumus, dicta scriptorem viderer lobortis
                        est Utinam, enim commune corrumpit Aenean erat tellus.
                        Metus sed amet dolore justo, gubergren sed. Lorem ipsum
                        sapientem ne neque dolor erat,eros solet invidunt duo
                        Quisque aliquid leo. Pretium patrioque sociis eu nihil
                        Cum enim ad, ipsum alii vidisse justo id. Option
                        porttitor diam voluptua.
                    </p>
                </div>
                <div id="image">
                    <NuxtImg
                        src="https://placehold.co/400x300"
                        alt="Company Logo"
                        class="rounded-xl"
                    />
                </div>
            </div>
            <div id="activity-section">
                <h2 class="text-xl font-medium">
                    <T key-name="subdomain.heading.activities" />
                </h2>
                <div id="activities">
                    <BusinessActivityCard
                        v-for="activity in activities"
                        :key="activity.id"
                        :activity="activity"
                        @open-activity-dialog="openActivityDialog"
                    />
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
        </div>
    </div>
</template>
