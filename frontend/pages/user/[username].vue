<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const route = useRoute();
const { t } = useTranslate();
const router = useRouter();
const client = useSanctumClient();

const username = ref(route.params.username);
const displayname = ref("");

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
}

definePageMeta({
    middleware: ["sanctum:auth"],
});

const showMore = ref(false);
const toggleText = ref(t.value("profile.showMore") + username.value);
const toggleTextShort = ref(t.value("profile.showMore.short"));
const allowedRoutes = ["/journey"];
const isCloseIcon = ref(false);

onMounted(() => {
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
});

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
</script>

<template>
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
                    <SvgDashboardIcon class="h-7 w-7 md:h-6 md:w-6" />
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
                    {{ username }}
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
                class="relative mt-2 grid grid-cols-2 grid-rows-2 gap-2 xs:gap-3 sm:grid-cols-3 lg:grid-cols-6"
            >
                <Skeleton
                    v-for="index in 12"
                    :key="index"
                    height="7rem"
                    class="hidden w-full dark:bg-text lg:block"
                />
                <Skeleton
                    v-for="index in 6"
                    :key="index"
                    height="7rem"
                    class="hidden w-full dark:bg-text sm:block lg:hidden"
                />
                <Skeleton
                    v-for="index in 6"
                    :key="index"
                    height="4rem"
                    class="w-full dark:bg-text sm:hidden"
                />
                <div
                    class="pointer-events-none absolute inset-0 flex items-center justify-center"
                >
                    <span
                        class="font-nunito text-xl font-bold text-text dark:text-natural-50 md:text-2xl"
                        >Coming Soon</span
                    >
                </div>
            </div>

            <div
                v-if="showMore"
                id="more-templates"
                class="mt-2 grid grid-cols-2 grid-rows-2 gap-2 xs:gap-3 sm:grid-cols-3 lg:grid-cols-6"
            >
                <Skeleton
                    v-for="index in 8"
                    :key="index"
                    height="7rem"
                    class="hidden w-full dark:bg-text md:block"
                />
                <Skeleton
                    v-for="index in 5"
                    :key="index"
                    height="4rem"
                    class="w-full dark:bg-text md:hidden"
                />
            </div>

            <div class="mt-4 flex justify-center">
                <button
                    class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                    @click="toggle"
                >
                    <span class="md:hidden">{{ toggleTextShort }}</span>
                    <span class="max-md:hidden">{{ toggleText }}</span>
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
</template>
