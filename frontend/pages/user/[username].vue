<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const route = useRoute();
const router = useRouter();
const { t } = useTranslate();
const user = useSanctumUser<User>();
const { isAuthenticated } = useSanctumAuth<boolean>();
const client = useSanctumClient();

const ALLOWED_ROUTES = ["/journey", "/dashboard?tab=templates"];

const isCurrentUser = ref<boolean>(false);
const username = ref<string>(route.params.username as string);
const displayname = ref<string>("");
const profilePicture = ref("https://placehold.co/44");
const joinDate = ref<Date | null>(null);

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
});

const whoseProfile = computed(() => {
    if (isCurrentUser.value) {
        return t.value("profile.your");
    } else {
        return `${displayname.value}`;
    }
});
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
        <div id="content" class="mt-5 flex w-full flex-row pl-16">
            <div
                id="profile"
                class="flex h-[65vh] w-[48vh] flex-col items-center rounded-lg border-[3px] border-calypso-400 pt-5"
            >
                <NuxtImg
                    :src="profilePicture"
                    class="h-40 w-40 rounded-full object-contain"
                    :alt="t('profile.picture')"
                />
                <h2 class="mt-6 text-2xl">{{ displayname }}</h2>
                <h3 class="mt-1 text-xl text-natural-800">@{{ username }}</h3>
                <span class="mb-1 mt-auto"
                    ><T
                        key-name="profile.created_at"
                        :params="{
                            year: joinDate ? joinDate.getFullYear() : '',
                        }"
                /></span>
            </div>
            <div id="templates"></div>
        </div>
    </div>
</template>
