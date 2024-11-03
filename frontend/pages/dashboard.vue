<script setup lang="ts">
import { useDashboardStore } from "@/stores/dashboard";
import { useTranslate } from "@tolgee/vue";
import { compareAsc, compareDesc } from "date-fns";

const title = "Dashboard";
useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:auth"],
});

const { t } = useTranslate();
const store = useDashboardStore();
const toast = useToast();

const journeys = ref<Journey[]>([]);
const searchInput = ref();
const searchInputMobile = ref();
const searchValue = ref<string>("");
const currentJourneys = ref<Journey[]>([]);
const user = ref();
const isUserSettingsVisible = ref(false);

currentJourneys.value = store.journeys;

const menu = ref();
const items = ref([
    {
        label: t.value("dashboard.sort.name"),
        icon: "pi pi-book",
        items: [
            {
                label: t.value("dashboard.sort.ascending"),
                icon: "pi pi-sort-alpha-up",
                command: () => {
                    sortJourneys("name-asc");
                },
            },
            {
                label: t.value("dashboard.sort.descending"),
                icon: "pi pi-sort-alpha-down",
                command: () => {
                    sortJourneys("name-desc");
                },
            },
        ],
    },
    {
        label: t.value("dashboard.sort.startdate"),
        icon: "pi pi-calendar",
        items: [
            {
                label: t.value("dashboard.sort.oldestToNewest"),
                icon: "pi pi-sort-amount-up",
                command: () => {
                    sortJourneys("startdate-asc");
                },
            },
            {
                label: t.value("dashboard.sort.newestToOldest"),
                icon: "pi pi-sort-amount-down",
                command: () => {
                    sortJourneys("startdate-desc");
                },
            },
        ],
    },
    {
        label: t.value("dashboard.sort.destination"),
        icon: "pi pi-map-marker",
        items: [
            {
                label: t.value("dashboard.sort.ascending"),
                icon: "pi pi-sort-alpha-up",
                command: () => {
                    sortJourneys("destination-asc");
                },
            },
            {
                label: t.value("dashboard.sort.descending"),
                icon: "pi pi-sort-alpha-down",
                command: () => {
                    sortJourneys("destination-desc");
                },
            },
        ],
    },
]);

const toggle = (event: Event) => {
    menu.value.toggle(event);
};

onMounted(async () => {
    if (localStorage.getItem("JP_invite_journey_id")) {
        toast.add({
            severity: "info",
            summary: t.value("common.toast.info.heading"),
            detail: t.value("invite.joining"),
            life: 6000,
        });
        await navigateTo(localStorage.getItem("JP_invite_journey_id"));
    }
});

/*
Fetches all journeys from the backend
stores response in journeys and currentJourneys
also sets journeys in the store
*/
const client = useSanctumClient();
const { data } = await useAsyncData("journeys", () => client("/api/journey"));
journeys.value = data.value;
currentJourneys.value = data.value;
store.setJourneys(data.value);

const { data: currUser } = await useAsyncData("currUser", () =>
    client(`/api/user`),
);

console.log(currUser.value);

user.value = currUser.value;

/**
 * Searches for journeys based on the searchValue
 * sets the currentJourneys to the results
 */
async function searchJourneys() {
    const data: Journey[] = journeys.value;
    const results: Journey[] = data.filter((obj: Journey) => {
        return (
            obj.name.toLowerCase().includes(searchValue.value.toLowerCase()) ||
            obj.destination
                .toLowerCase()
                .includes(searchValue.value.toLowerCase())
        );
    });
    currentJourneys.value = results;
}

/**
 * Sorts the journeys based on the sortKey
 * @param sortKey the key to sort the journeys by
 */
function sortJourneys(sortKey: string) {
    currentJourneys.value.sort((a: Journey, b: Journey) => {
        switch (sortKey) {
            case "name-asc":
                return b.name.localeCompare(a.name);
            case "name-desc":
                return a.name.localeCompare(b.name);
            case "startdate-asc":
                return compareAsc(new Date(a.from), new Date(b.from));
            case "startdate-desc":
                return compareDesc(new Date(a.from), new Date(b.from));
            case "destination-asc":
                return b.destination.localeCompare(a.destination);
            case "destination-desc":
                return a.destination.localeCompare(b.destination);
            default:
                return 0;
        }
    });
}

function removeJourney(id: string) {
    journeys.value.splice(
        journeys.value.findIndex((journey) => journey.id === id),
        1,
    );
    currentJourneys.value = journeys.value;
}

function editJourney(journey: Journey, id: string) {
    const index = journeys.value.findIndex((j) => j.id === id);
    journeys.value[index].destination = journey.destination;
    journeys.value[index].from = journey.from;
    journeys.value[index].to = journey.to;
    journeys.value[index].name = journey.name;
}
</script>

<template>
    <div
        class="px-2 font-nunito text-text dark:text-natural-50 md:px-8 lg:px-20"
    >
        <div
            id="header"
            class="mt-5 flex items-center justify-between border-b-2 border-calypso-300 pb-3 dark:border-calypso-400 md:pb-4"
        >
            <div class="flex flex-row items-center">
                <SvgDashboardIcon class="mr-1 mt-0.5 md:h-9 md:w-9" />
                <h1 class="mt-1 text-3xl font-medium md:text-5xl">
                    <T key-name="common.dashboard" />
                </h1>
            </div>
            <div id="right-header" class="flex flex-row items-center">
                <div
                    id="search-and-filter"
                    class="mr-4 hidden flex-row border-r-2 border-natural-200 lg:flex"
                >
                    <div
                        id="search"
                        v-tooltip.top="{
                            value: t('dashboard.tooltip.search'),
                            pt: { root: 'font-nunito' },
                        }"
                        class="relative mr-2.5"
                    >
                        <input
                            ref="searchInput"
                            v-model="searchValue"
                            type="text"
                            class="rounded-3xl border border-natural-200 bg-natural-50 px-3 py-1.5 placeholder-natural-400 focus:border-dandelion-300 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:placeholder-natural-200"
                            :placeholder="t('dashboard.search')"
                            @input="searchJourneys"
                        />
                        <button @click="searchInput.focus()">
                            <SvgSearchIcon
                                class="absolute right-1 top-1 h-7 w-7"
                            />
                        </button>
                    </div>
                    <div id="filter" class="mr-4">
                        <SvgFilterIcon
                            aria-haspopup="true"
                            aria-controls="overlay_tmenu"
                            class="h-9 w-9 hover:cursor-pointer"
                            @click="toggle"
                        />
                    </div>
                </div>
                <NuxtLink
                    to="/journey/new"
                    class="mr-2.5 hidden flex-row items-center lg:flex"
                >
                    <button
                        class="flex flex-row rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 font-semibold text-text dark:border-ronchi-300 dark:bg-ronchi-300"
                    >
                        <SvgCreateNewJourneyIcon
                            class="mr-1 h-5 w-5 fill-text"
                        />
                        <T key-name="dashboard.new" />
                    </button>
                </NuxtLink>
                <NuxtLink :to="'/user/' + user.username" class="mr-2.5">
                    <SvgUserIcon class="mt-1 h-9 w-9" />
                </NuxtLink>
                <button @click="isUserSettingsVisible = !isUserSettingsVisible">
                    <SvgSettingsIcon class="mt-1 h-9 w-9" />
                </button>
                <UserSettings
                    :visible="isUserSettingsVisible"
                    :is-oauth="user.microsoft_id || user.google_id"
                    :prop-username="user.username"
                    :prop-displayname="user.display_name"
                    :prop-email="user.email"
                    @close="isUserSettingsVisible = false"
                />
            </div>
        </div>
        <div
            id="header-mobile-second-row"
            class="mt-3 flex justify-between lg:hidden"
        >
            <div id="search-and-filter" class="flex flex-row">
                <div id="filter" class="mr-2">
                    <SvgFilterIcon
                        aria-haspopup="true"
                        aria-controls="overlay_tmenu"
                        class="h-9 w-9 hover:cursor-pointer"
                        @click="toggle"
                    />
                </div>
                <div id="search" class="relative">
                    <input
                        ref="searchInputMobile"
                        v-model="searchValue"
                        type="text"
                        class="w-40 rounded-3xl border border-natural-200 bg-natural-50 px-3 py-1.5 placeholder-natural-400 focus:border-dandelion-300 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:placeholder-natural-200 md:w-52"
                        :placeholder="t('dashboard.search')"
                        @input="searchJourneys"
                    />
                    <button @click="searchInputMobile.focus()">
                        <SvgSearchIcon class="absolute right-1 top-1 h-7 w-7" />
                    </button>
                </div>
            </div>
            <NuxtLink
                to="/journey/new"
                class="flex flex-row items-center justify-center"
            >
                <button
                    class="flex flex-row items-center justify-center rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 font-semibold text-text dark:border-dandelion-300 dark:bg-ronchi-300 md:px-4"
                >
                    <SvgCreateNewJourneyIcon class="mr-1 h-5 w-5 fill-text" />
                    <T key-name="dashboard.new" />
                </button>
            </NuxtLink>
        </div>
        <div class="flex justify-center">
            <div
                id="journeys"
                class="mt-5 grid gap-x-5 gap-y-5 md:gap-x-4 md:gap-y-4 lg:gap-x-6 lg:gap-y-6"
                :class="
                    currentJourneys.length === 0
                        ? 'grid-cols-1'
                        : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4'
                "
            >
                <DashboardItem
                    v-for="journey in currentJourneys"
                    :id="String(journey.id)"
                    :key="journey.id"
                    :name="journey.name"
                    :destination="journey.destination"
                    :from="new Date(journey.from)"
                    :to="new Date(journey.to)"
                    :role="Number(journey.role)"
                    @journey-deleted="removeJourney"
                    @journey-leave="removeJourney"
                    @journey-edited="editJourney"
                />
                <NuxtLink to="/journey/new">
                    <SvgCreateNewJourneyCard
                        class="hidden dark:hidden lg:block"
                    />
                    <SvgCreateNewJourneyCardDark class="hidden dark:lg:block" />
                    <div
                        class="flex h-32 min-w-36 flex-grow items-center justify-center rounded-md border border-dandelion-300 bg-dandelion-100 dark:bg-pesto-600 lg:hidden"
                    >
                        <SvgCreateNewJourneyIcon
                            class="h-14 w-14 fill-text dark:fill-natural-50"
                        />
                    </div>
                </NuxtLink>
            </div>
        </div>
        <TieredMenu
            id="overlay_tmenu"
            ref="menu"
            :model="items"
            popup
            class="rounded-xl border-2 border-natural-200 bg-natural-50 dark:border-natural-900 dark:bg-natural-800"
            :pt="{
                menuitem: {
                    class: 'bg-natural-50 dark:bg-natural-800 hover:bg-dandelion-300 rounded-md',
                },
                content: { class: 'hover:bg-dandelion-300 rounded-md' },
                submenu: { class: 'bg-natural-50 dark:bg-natural-800' },
            }"
        >
            <template #start>
                <h1 class="ml-2 text-sm text-natural-400 dark:text-natural-200">
                    <T key-name="dashboard.sort.header" />
                </h1>
                <Divider
                    type="solid"
                    class="mb-1 mt-1 border-b text-[#E3E3E3]"
                />
            </template>
            <template #item="{ item, props, hasSubmenu }">
                <a
                    v-ripple
                    class="align-items-center flex rounded-md bg-natural-50 text-sm text-text hover:bg-dandelion-100 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                    v-bind="props.action"
                >
                    <span :class="item.icon" />
                    <span class="ml-2">{{ item.label }}</span>
                    <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto" />
                </a>
            </template>
        </TieredMenu>
        <ConfirmDialog
            :draggable="false"
            group="dashboard"
            :pt="{
                header: {
                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito',
                },
                content: {
                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito',
                },
                footer: {
                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50 font-nunito',
                },
            }"
        />
    </div>
</template>
