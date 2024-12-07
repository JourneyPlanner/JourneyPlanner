<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const route = useRoute();
const { t } = useTranslate();
const router = useRouter();
const client = useSanctumClient();
const toast = useToast();

const username = ref(route.params.username);
const displayname = ref("");

const MAX_FIRST_TEMPLATES = {
    MOBILE: 4,
    DESKTOP: 8,
};

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
}

definePageMeta({
    middleware: ["sanctum:auth"],
});

const showMore = ref(false);
const toggleText = ref(t.value("profile.showMore") + username.value);
const toggleTextShort = ref(t.value("profile.showMore.short"));
const allowedRoutes = ["/journey", "/dashboard?tab=templates"];
const isCloseIcon = ref(false);
const templates = ref<Template[]>([]);
const openedTemplate = ref<Template | undefined>();
const isTemplatePopupVisible = ref<boolean>(false);
const moreTemplatesAvailable = ref<boolean>(true);
const cursor = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const observer = ref<IntersectionObserver>();
const loader = ref<HTMLElement | undefined>();

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
                    refresh();
                }
            }
        });

        if (loader.value) {
            observer.value.observe(loader.value);
        }
    }
});

const firstEightTemplates = computed(() =>
    templates.value.slice(0, MAX_FIRST_TEMPLATES.DESKTOP),
);
const firstFourTemplates = computed(() =>
    templates.value.slice(0, MAX_FIRST_TEMPLATES.MOBILE),
);
const remainingTemplatesDesktop = computed(() =>
    templates.value.slice(MAX_FIRST_TEMPLATES.DESKTOP),
);
const remainingTemplatesMobile = computed(() =>
    templates.value.slice(MAX_FIRST_TEMPLATES.MOBILE),
);

const toggle = () => {
    showMore.value = !showMore.value;
    toggleText.value = showMore.value
        ? t.value("profile.showLess") + username.value
        : t.value("profile.showMore") + username.value;
    toggleTextShort.value = showMore.value
        ? t.value("profile.showLess.short")
        : t.value("profile.showMore.short");
};

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
};

const openTemplateDialog = (template: Template) => {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
};
</script>

<template>
    <div>
        <div class="px-1 font-nunito md:px-3">
            <div id="header" class="mt-5 flex flex-col">
                <div class="flex flex-row items-center">
                    <div class="flex flex-row items-center">
                        <span
                            class="mr-1 h-0.5 w-2 bg-calypso-400 xs:mr-2 xs:w-4 sm:mr-3.5 sm:w-8 md:mr-5 md:w-10"
                        />
                        <h1
                            class="max-w-48 truncate text-nowrap text-xl font-medium text-text dark:text-natural-50 xs:text-2xl sm:max-w-sm md:max-w-screen-md md:text-3xl"
                        >
                            {{ displayname }}
                        </h1>
                    </div>
                    <span
                        class="ml-1 h-0.5 w-full bg-calypso-400 xs:ml-2 sm:ml-3.5 md:ml-5 md:mr-5"
                    />
                    <span
                        v-if="isCloseIcon"
                        class="pi pi-times cursor-pointer pl-2 pr-2 text-2xl text-natural-600 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 xs:text-3xl md:pr-3 md:text-3xl lg:pr-5"
                        @click="navigateBack"
                    />
                    <NuxtLink
                        v-else
                        to="/dashboard"
                        class="group mx-2 flex items-center sm:ml-1 md:ml-2 lg:mr-5"
                    >
                        <SvgDashboardIcon
                            class="h-7 w-7 fill-text dark:fill-natural-50 md:h-6 md:w-6"
                        />
                        <p
                            class="hidden text-xl text-text group-hover:underline dark:text-natural-50 sm:block lg:text-2xl"
                        >
                            Dashboard
                        </p>
                    </NuxtLink>
                </div>
                <div class="mt-0.5 md:mt-2">
                    <h2
                        class="ml-3 max-w-48 truncate text-lg text-natural-500 dark:text-natural-300 xs:ml-6 xs:text-xl sm:ml-[2.875rem] md:ml-[3.75rem] md:max-w-screen-md md:text-2xl"
                    >
                        @{{ username }}
                    </h2>
                </div>
            </div>
            <div
                class="ml-3 mt-6 pr-2 xs:ml-6 xs:pr-10 sm:ml-[2.875rem] md:ml-[3.75rem] md:mt-16 md:pr-20 lg:mt-10"
            >
                <h1
                    class="text-lg font-medium text-text dark:text-natural-50 xs:text-xl md:text-2xl"
                >
                    <T key-name="profile.templates" />
                    {{ username }}
                </h1>
                <div
                    id="templates"
                    class="relative mt-2 grid grid-cols-2 gap-5 sm:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-6"
                >
                    <TemplateCard
                        v-for="template in firstEightTemplates"
                        :key="template.id"
                        class="hidden md:block"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="openTemplateDialog(template)"
                    />
                    <TemplateCardSmall
                        v-for="template in firstFourTemplates"
                        :key="template.id"
                        class="md:hidden"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="openTemplateDialog(template)"
                    />
                    <div
                        v-if="firstEightTemplates.length === 0"
                        class="col-span-full hidden md:block"
                    >
                        <T key-name="template.none" />
                    </div>
                    <div
                        v-if="firstFourTemplates.length === 0"
                        class="col-span-full md:hidden"
                    >
                        <T key-name="template.none" />
                    </div>
                </div>

                <div
                    v-if="showMore"
                    id="more-templates"
                    class="mt-5 grid grid-cols-2 gap-5 sm:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-6"
                >
                    <TemplateCard
                        v-for="template in remainingTemplatesDesktop"
                        :key="template.id"
                        class="hidden md:block"
                        :template="template"
                        @open-template="openTemplateDialog(template)"
                    />
                    <TemplateCardSmall
                        v-for="template in remainingTemplatesMobile"
                        :key="template.id"
                        class="md:hidden"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="openTemplateDialog(template)"
                    />
                </div>
                <div ref="loader" class="col-span-full">
                    <div v-if="moreTemplatesAvailable && showMore">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="dashboard.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="remainingTemplatesDesktop.length > 0"
                    class="mt-4 flex justify-center max-md:hidden"
                >
                    <button
                        class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                        @click="toggle"
                    >
                        <span>{{ toggleText }}</span>
                        <span
                            class="pi mt-1"
                            :class="
                                showMore
                                    ? 'pi-chevron-up order-first mb-1'
                                    : 'pi-chevron-down'
                            "
                        />
                    </button>
                </div>
                <div
                    v-if="remainingTemplatesMobile.length > 0"
                    class="mt-4 flex justify-center md:hidden"
                >
                    <button
                        class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                        @click="toggle"
                    >
                        <span>{{ toggleTextShort }}</span>
                        <span
                            class="pi mt-1"
                            :class="
                                showMore
                                    ? 'pi-chevron-up order-first mb-1'
                                    : 'pi-chevron-down'
                            "
                        />
                    </button>
                </div>
            </div>
        </div>
        <div id="dialogs">
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
