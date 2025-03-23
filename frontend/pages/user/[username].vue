<script setup lang="ts">
import { GravatarQuickEditorCore } from "@gravatar-com/quick-editor";
import { useTolgee, useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { de } from "date-fns/locale/de";
import { enUS } from "date-fns/locale/en-US";

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
const joinDate = ref<Date | null>(null);

const showMoreTemplates = ref<boolean>(false);
const openedTemplate = ref<Template | undefined>();
const isTemplatePopupVisible = ref<boolean>(false);
const templatesLoader = ref<HTMLElement | undefined>();
const reloadData = ref(false);

const maxDisplayedTemplates = computed(() => {
    if (screenWidth.value >= 1280) return 6;
    if (screenWidth.value >= 1024) return 4;
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
}

isCurrentUser.value = data.value?.username === user.value?.username;
displayname.value = data.value?.display_name;
username.value = data.value?.username;
joinDate.value = new Date(data.value?.created_at);

const { avatarUrl, refreshAvatar } = useGravatar(
    data.value?.email_hash,
    data.value?.display_name,
);

useHead({
    title: `${displayname.value} (@${username.value}) | JourneyPlanner`,
});

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

    window.addEventListener("resize", updateScreenWidth);

    gravatarEditor.value = new GravatarQuickEditorCore({
        email: user?.value?.email,
        scope: ["avatars"],
        locale: tolgee.value?.getLanguage(),
        onProfileUpdated: () => {
            refreshAvatar();
        },
    });
});

onUnmounted(async () => {
    window.removeEventListener("resize", updateScreenWidth);
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
    reloadData: reloadData,
    showMoreDataText: t.value("profile.showMore", {
        username: isCurrentUser.value
            ? t.value("profile.templates.created.by.you")
            : "@" + username.value,
    }),
    showLessDataText: t.value("profile.showLess", {
        username: isCurrentUser.value
            ? t.value("profile.templates.created.by.you")
            : "@" + username.value,
    }),
    identifier: "user-templates",
    apiEndpoint: `/api/user/${username.value}/template`,
    params: {
        per_page: 6,
    },
});

const whoseTemplates = computed(() => {
    if (isCurrentUser.value) {
        return t.value("profile.yourTemplates");
    } else {
        return t.value("profile.templates", { username: username.value });
    }
});

const updateScreenWidth = () => {
    screenWidth.value = window.innerWidth;
};

function openTemplateDialog(template: Template) {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
}

const locale = computed(() => {
    if (tolgee.value?.getLanguage() === "de") {
        return de;
    } else {
        return enUS;
    }
});
</script>

<template>
    <div class="text-text dark:text-natural-50">
        <div id="header" class="mt-2 flex lg:mt-5 lg:flex-col">
            <div class="flex w-full flex-row items-center justify-between">
                <NuxtLink
                    :to="backRoute"
                    class="flex cursor-pointer items-center gap-x-1 pl-2 text-natural-600 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 lg:gap-x-2 lg:pl-8"
                >
                    <i class="pi pi-angle-left text-2xl sm:text-3xl" />
                    <span
                        class="mt-0.5 text-xl font-semibold sm:text-2xl lg:text-2xl"
                    >
                        <T key-name="common.back" />
                    </span>
                </NuxtLink>
                <button
                    v-if="isCurrentUser"
                    class="mr-5 flex h-8 w-8 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 sm:h-9 sm:w-9 lg:hidden"
                    @click="gravatarEditor?.open()"
                >
                    <i class="pi pi-pencil" />
                </button>
            </div>
        </div>
        <div
            id="content"
            class="mt-3 flex w-full cursor-default flex-col pl-2 pr-2 lg:mt-5 lg:flex-row lg:pl-16 lg:pr-5 xl:pr-28"
        >
            <div
                id="profile"
                class="relative flex flex-row px-2 max-lg:pb-4 md:ml-10 lg:ml-0 lg:h-[65vh] lg:min-w-[48vh] lg:max-w-[48vh] lg:flex-col lg:items-center lg:rounded-xl lg:border-[3px] lg:border-calypso-400 lg:pt-5"
            >
                <button
                    v-if="isCurrentUser"
                    class="absolute right-2 top-2 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:hidden"
                    @click="gravatarEditor?.open()"
                >
                    <i class="pi pi-pencil" />
                </button>
                <div
                    id="picture"
                    class="group relative"
                    @click="isCurrentUser && gravatarEditor?.open()"
                >
                    <NuxtImg
                        :src="avatarUrl"
                        class="h-28 w-28 rounded-full border object-contain sm:h-36 sm:w-36 lg:h-40 lg:w-40"
                        :class="
                            isCurrentUser
                                ? 'cursor-pointer group-hover:opacity-80 group-hover:blur-sm'
                                : ''
                        "
                        :alt="t('profile.picture', { username: username })"
                        placeholder
                    />
                    <i
                        v-show="isCurrentUser"
                        class="pi pi-pencil invisible absolute left-1/2 top-1/2 cursor-pointer group-hover:visible"
                    />
                </div>
                <div
                    id="info"
                    class="ml-2 flex max-w-44 flex-col xs:ml-4 xs:max-w-56 sm:max-w-96 md:ml-8 lg:ml-0 lg:mt-6 lg:h-full lg:w-full lg:max-w-full lg:items-center lg:justify-center lg:px-10"
                >
                    <h1
                        v-tooltip.top="{
                            value: displayname,
                            pt: { root: 'font-nunito' },
                        }"
                        class="max-w-full truncate text-xl sm:text-2xl lg:text-2xl"
                    >
                        {{ displayname }}
                    </h1>
                    <h2
                        v-tooltip.top="{
                            value: '@' + username,
                            pt: { root: 'font-nunito' },
                        }"
                        class="max-w-full truncate text-lg text-natural-800 dark:text-natural-200 sm:text-xl lg:mt-1 lg:text-xl"
                    >
                        @{{ username }}
                    </h2>
                    <span
                        v-tooltip.top="{
                            value: $t('profile.created_at.tooltip', {
                                date: joinDate
                                    ? format(
                                          new Date(joinDate),
                                          'dd. MMMM yyyy',
                                          {
                                              locale: locale,
                                          },
                                      )
                                    : '',
                            }),
                            pt: { root: 'font-nunito text-center' },
                        }"
                        class="mb-1 mt-auto text-natural-900 dark:text-natural-200"
                    >
                        <T
                            key-name="profile.created_at"
                            :params="{
                                year: joinDate ? joinDate.getFullYear() : '',
                            }"
                        />
                    </span>
                </div>
            </div>
            <div class="mx-2 h-0.5 bg-calypso-600 md:mx-10 lg:hidden"></div>
            <div
                id="template-section"
                class="w-full px-2 max-lg:mt-4 md:px-10 lg:ml-5 xl:ml-10"
            >
                <h2 class="text-lg sm:text-xl lg:text-2xl lg:font-semibold">
                    {{ whoseTemplates }}
                </h2>
                <TransitionGroup
                    name="fade"
                    tag="div"
                    class="relative mt-2 grid w-full grid-cols-2 gap-2 sm:grid-cols-3 md:gap-4 lg:grid-cols-2 lg:gap-4 xl:grid-cols-3"
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
                            showMoreTemplates || index < maxDisplayedTemplates
                        "
                        :key="'template-card' + template.id"
                        class="hidden lg:block"
                        :template="template"
                        :displayed-in-profile="true"
                        :is-current-user="isCurrentUser"
                        @open-template="openTemplateDialog(template)"
                    />
                    <TemplateCardSmall
                        v-for="(template, index) in templates"
                        v-show="
                            showMoreTemplates || index < maxDisplayedTemplates
                        "
                        :key="'template-card-small' + template.id"
                        class="lg:hidden"
                        :template="template"
                        :displayed-in-profile="true"
                        :is-current-user="isCurrentUser"
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
                    v-if="
                        templates.length > 0 &&
                        (moreTemplatesAvailable ||
                            templates.length > maxDisplayedTemplates)
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
