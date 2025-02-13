<script setup lang="ts">
import { GravatarQuickEditorCore } from "@gravatar-com/quick-editor";
import { useTolgee, useTranslate } from "@tolgee/vue";

const route = useRoute();
const router = useRouter();
const { t } = useTranslate();
const tolgee = useTolgee(["language"]);
const user = useSanctumUser<User>();
const { isAuthenticated } = useSanctumAuth<boolean>();
const client = useSanctumClient();
const gravatarEditor = ref<GravatarQuickEditorCore | undefined>();

const ALLOWED_ROUTES = ["/journey", "/dashboard?tab=templates"];
const screenWidth = ref(window.innerWidth);

const isCurrentUser = ref<boolean>(false);
const username = ref<string>(
    (route.params.username as string).replace("@", ""),
);
const displayname = ref<string>("");
const profilePicture = ref("https://placehold.co/44");
const joinDate = ref<Date | null>(null);

const showMoreTemplates = ref<boolean>(false);
const openedTemplate = ref<Template | undefined>();
const isTemplatePopupVisible = ref<boolean>(false);
const templatesLoader = ref<HTMLElement | undefined>();

const maxDisplayedTemplates = computed(() => {
    if (screenWidth.value >= 1024) return 6;
    if (screenWidth.value >= 640) return 6;
    return 4;
});

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
    isCurrentUser.value = data.value?.username === user.value?.username;
    displayname.value = data.value?.display_name;
    username.value = data.value?.username;
    //profilePicture.value = data.value?.profile_picture;
    joinDate.value = new Date(data.value?.created_at);
    useHead({
        title: `${displayname.value} (@${username.value}) | JourneyPlanner`,
    });
}

const backRoute = ref("");

onMounted(() => {
    const lastRoute = router.options.history.state.back as string;
    if (
        lastRoute &&
        (ALLOWED_ROUTES.some((route) => lastRoute.startsWith(route)) ||
            !isAuthenticated.value)
    ) {
        backRoute.value = lastRoute;
    } else if (!isAuthenticated.value) {
        backRoute.value = "/journey/new";
    } else if (route?.query?.journey) {
        backRoute.value = `/journey/${route.query.journey}`;
    } else {
        backRoute.value = "/dashboard";
    }

    gravatarEditor.value = new GravatarQuickEditorCore({
        email: user?.value?.email,
        scope: ["avatars"],
        locale: tolgee.value?.getLanguage(),
        onProfileUpdated: (profile) => {
            console.log("Profile updated", profile);
        },
        onOpened: () => {
            console.log("Editor opened");
        },
    });
});

const {
    data: templates,
    moreDataAvailable: moreTemplatesAvailable,
    status: templatesStatus,
    toggle: toggleTemplates,
    toggleText: toggleTextTemplates,
} = await useInfiniteScroll<Template>({
    loader: templatesLoader,
    showMoreData: showMoreTemplates,
    showMoreDataText: t.value("profile.showMore", {
        username: isCurrentUser.value
            ? t.value("profile.templates.created.by.you")
            : username.value,
    }),
    showLessDataText: t.value("profile.showLess", {
        username: isCurrentUser.value
            ? t.value("profile.templates.created.by.you")
            : username.value,
    }),
    identifier: "user-templates",
    apiEndpoint: `/api/user/${username.value}/template`,
    params: {
        per_page: 6,
    },
});

const whoseProfile = computed(() => {
    if (isCurrentUser.value) {
        return t.value("profile.your");
    } else {
        return `${displayname.value}`;
    }
});

const whoseTemplates = computed(() => {
    if (isCurrentUser.value) {
        return t.value("profile.yourTemplates");
    } else {
        return t.value("profile.templates", { username: username.value });
    }
});

function openTemplateDialog(template: Template) {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
}

//TODO: darkmode
//TODO: responsive
//TODO: max width username/displayname
//TODO: wieso werden nicht alle templates geladen, zweiter request ist []
</script>

<template>
    <div>
        <div id="header" class="mt-5 flex flex-col">
            <div class="flex flex-row items-center">
                <NuxtLink
                    :to="backRoute"
                    class="pi pi-angle-left cursor-pointer pl-2 pr-2 text-2xl text-natural-600 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 xs:text-3xl md:pr-3 md:text-3xl lg:pl-2 lg:pr-6"
                />
                <div class="flex w-full flex-row items-center justify-between">
                    <h1
                        class="max-w-48 truncate text-nowrap text-xl font-medium text-text dark:text-natural-50 xs:text-2xl sm:max-w-sm md:max-w-screen-md md:text-3xl"
                    >
                        {{ whoseProfile }}
                    </h1>
                    <NuxtLink
                        :to="backRoute"
                        class="pi pi-times cursor-pointer pl-2 pr-2 text-2xl text-natural-600 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 xs:text-3xl md:pr-3 md:text-3xl lg:pr-10"
                    />
                </div>
            </div>
        </div>
        <div id="content" class="mt-5 flex w-full flex-row pl-16 pr-28">
            <div
                id="profile"
                class="relative flex h-[65vh] min-w-[48vh] flex-col items-center rounded-lg border-[3px] border-calypso-400 pt-5"
            >
                <button
                    v-if="isCurrentUser"
                    class="absolute right-2 top-2 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                    @click="gravatarEditor?.open()"
                >
                    <i class="pi pi-pencil" />
                </button>
                <NuxtImg
                    :src="profilePicture"
                    class="h-40 w-40 rounded-full object-contain"
                    :alt="t('profile.picture')"
                />
                <h2 class="mt-6 text-2xl">{{ displayname }}</h2>
                <h3 class="mt-1 text-xl text-natural-800">@{{ username }}</h3>
                <span class="mb-1 mt-auto">
                    <T
                        key-name="profile.created_at"
                        :params="{
                            year: joinDate ? joinDate.getFullYear() : '',
                        }"
                    />
                </span>
            </div>
            <div id="template-section" class="ml-10 w-full">
                <h2 class="text-2xl font-semibold">
                    {{ whoseTemplates }}
                </h2>
                <TransitionGroup
                    name="fade"
                    tag="div"
                    class="relative mt-2 grid w-full grid-cols-2 gap-2 sm:grid-cols-3 md:gap-4 lg:grid-cols-3 lg:gap-4"
                >
                    <Skeleton
                        v-for="index in maxDisplayedTemplates"
                        v-show="
                            templatesStatus === 'pending' && !showMoreTemplates
                        "
                        :key="index"
                        class="w-16"
                        height="10rem"
                    />
                    <TemplateCard
                        v-for="(template, index) in templates"
                        v-show="
                            templatesStatus === 'success' &&
                            (showMoreTemplates || index < maxDisplayedTemplates)
                        "
                        :key="template.id"
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
                        :key="template.id"
                        class="md:hidden"
                        :template="template"
                        :displayed-in-profile="false"
                        @open-template="openTemplateDialog(template)"
                    />
                </TransitionGroup>
                <div
                    v-if="
                        templates.length === 0 &&
                        templatesStatus !== 'idle' &&
                        templatesStatus !== 'pending'
                    "
                    class="col-span-full"
                >
                    <T key-name="template.none" />
                </div>
                <div ref="templatesLoader" class="col-span-full">
                    <div v-if="moreTemplatesAvailable && showMoreTemplates">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="dashboard.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="templates.length > 0"
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
    </div>
</template>
