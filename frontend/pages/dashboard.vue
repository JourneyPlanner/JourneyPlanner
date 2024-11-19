<script setup lang="ts">
import { useDashboardStore } from "@/stores/dashboard";
import { useTranslate } from "@tolgee/vue";
import { compareAsc, compareDesc } from "date-fns";
import debounce from "~/utils/debounce";

definePageMeta({
    middleware: ["sanctum:auth"],
});

const { t } = useTranslate();
const router = useRouter();
const route = useRoute();
const store = useDashboardStore();
const client = useSanctumClient();

const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");

let borderColor = "#BDBDBD";
let borderColorFocus = "#50A1C0";
if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    borderColor = "#464646";
    borderColorFocus = "#50A1C0";
}
const addressCss = `.SearchIcon {visibility: hidden;} .Input {border: solid 2px ${borderColor}; padding-left: 0.625rem; padding-top: 0rem; padding-bottom: 0rem;} .Input:focus {box-shadow: none; border: solid 2px ${borderColorFocus};}`;

const searchInput = ref();
const searchInputMobile = ref();
const searchValue = ref<string>("");
const tabIndex = ref(0);
const menu = ref();
const items = ref();

//journeys
const journeys = ref<Journey[]>([]);
const currentJourneys = ref<Journey[]>([]);
currentJourneys.value = store.journeys;

//user settings
const user = ref();
const isUserSettingsVisible = ref(false);

//templates
const openedTemplate = ref<Template>();
const isTemplatePopupVisible = ref(false);
const isFilterVisible = ref<boolean>(route.query.filter_open === "true");
const filterDialog = ref<HTMLElement>();
const usernames = ref<string[]>([]);
const templates = ref<Template[]>([]);
const moreTemplatesAvailable = ref<boolean>(true);
const templateJourneyLengthMinMax = ref<Array<number>>([1, 31]);
const sortby = ref("");
const sortorder = ref("");
const templateDestination = ref("");
const templateCreator = ref("");
const cursor = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const observer = ref<IntersectionObserver>();
const loader = ref();

const toggle = (event: Event) => {
    menu.value.toggle(event);
};

//redirect to invite if an invite is stored in local storage
if (localStorage.getItem("JP_invite_journey_id")) {
    await navigateTo(localStorage.getItem("JP_invite_journey_id"));
}

if (route.query.tab === "templates") {
    tabIndex.value = 1;
}

onMounted(() => {
    watch(
        tabIndex,
        () => {
            if (tabIndex.value === 1) {
                router.replace({
                    path: "/dashboard",
                    query: {
                        tab: "templates",
                    },
                });

                useHead({
                    title: "Templates | JourneyPlanner",
                });

                if (observer.value) {
                    observer.value.disconnect();
                }

                observer.value = new IntersectionObserver((entries) => {
                    const target = entries[0];
                    if (target.isIntersecting) {
                        if (
                            moreTemplatesAvailable.value &&
                            !isFilterVisible.value
                        ) {
                            cursor.value = nextCursor.value;
                            refresh();
                        }
                    }
                });

                if (loader.value) {
                    observer.value.observe(loader.value);
                }

                items.value = [
                    {
                        label: t.value("dashboard.sort.length"),
                        icon: "pi pi-calendar-clock",
                        items: [
                            {
                                label: t.value(
                                    "dashboard.sort.ascending.numeric",
                                ),
                                icon: "pi pi-sort-amount-up",
                                command: () => {
                                    sortby.value = "length";
                                    sortorder.value = "asc";
                                    refreshTemplates();
                                },
                            },
                            {
                                label: t.value(
                                    "dashboard.sort.descending.numeric",
                                ),
                                icon: "pi pi-sort-amount-down",
                                command: () => {
                                    sortby.value = "length";
                                    sortorder.value = "desc";
                                    refreshTemplates();
                                },
                            },
                        ],
                    },
                    {
                        label: t.value("dashboard.sort.name"),
                        icon: "pi pi-book",
                        items: [
                            {
                                label: t.value("dashboard.sort.ascending"),
                                icon: "pi pi-sort-alpha-up",
                                command: () => {
                                    sortby.value = "name";
                                    sortorder.value = "asc";
                                    refreshTemplates();
                                },
                            },
                            {
                                label: t.value("dashboard.sort.descending"),
                                icon: "pi pi-sort-alpha-down",
                                command: () => {
                                    sortby.value = "name";
                                    sortorder.value = "desc";
                                    refreshTemplates();
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
                                    sortby.value = "destination";
                                    sortorder.value = "asc";
                                    refreshTemplates();
                                },
                            },
                            {
                                label: t.value("dashboard.sort.descending"),
                                icon: "pi pi-sort-alpha-down",
                                command: () => {
                                    sortby.value = "destination";
                                    sortorder.value = "desc";
                                    refreshTemplates();
                                },
                            },
                        ],
                    },
                ];
            } else {
                router.replace({ path: "/dashboard" });
                useHead({
                    title: "Dashboard | JourneyPlanner",
                });
                items.value = [
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
                ];
            }
        },
        { immediate: true },
    );
});

onUnmounted(() => {
    if (observer.value && loader.value) {
        observer.value.unobserve(loader.value);
    }
});

/**
 * Fetches templates from the backend
 * stores response in templates ref
 */
const {
    data: templateData,
    refresh,
    status,
} = await useAsyncData("templates", () =>
    client(
        `/api/template?sort_by=${sortby.value}&order=${sortorder.value}&per_page=40&template_name=${searchValue.value}&template_journey_length_min=${templateJourneyLengthMinMax.value[0]}&template_journey_length_max=${templateJourneyLengthMinMax.value[1]}&template_destination=${templateDestination.value}&template_creator=${templateCreator.value}&cursor=${cursor.value}`,
    ),
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

const refreshTemplates = () => {
    templates.value = [];
    cursor.value = null;
    nextCursor.value = null;
    moreTemplatesAvailable.value = true;
    refresh();
};

/**
 * close the template filter dialog when there is a click outside of it
 * @param event MouseEvent
 */
const closeFilterWhenOutsideClick = (event: MouseEvent) => {
    const filterElement = filterDialog.value as HTMLElement;
    if (filterElement && !filterElement.contains(event.target as Node)) {
        isFilterVisible.value = false;
    }
};

/**
 * debounce search input to prevent too many requests
 */
const searchTemplate = debounce(() => {
    refreshTemplates();
});

const isFiltered = computed(() => {
    return (
        templateJourneyLengthMinMax.value[0] !== 1 ||
        templateJourneyLengthMinMax.value[1] !== 31 ||
        templateDestination.value !== "" ||
        templateCreator.value !== ""
    );
});

/**
 * clear template filters
 */
function clearFilters() {
    templateJourneyLengthMinMax.value = [1, 31];
    templateDestination.value = "";
    templateCreator.value = "";
    searchValue.value = "";
    refreshTemplates();
}

/*
 * debounce getUsers() to prevent too many request on input
 */
const searchUser = debounce(() => {
    getUsers();
});

/**
 * get username(s) for AutoComplete for created by template filter
 */
function getUsers() {
    client(`/api/user?search=${templateCreator.value}&per_page=25`).then(
        (res) => {
            usernames.value = res.data.map((user: User) => user.username);
        },
    );
}

/*
Fetches all journeys from the backend
stores response in journeys and currentJourneys
also sets journeys in the store
*/
const { data } = await useAsyncData("journeys", () => client("/api/journey"));
journeys.value = data.value;
currentJourneys.value = data.value;
store.setJourneys(data.value);

const { data: currUser } = await useAsyncData("currUser", () =>
    client(`/api/me`),
);

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

function changeAddress(newAddress: string) {
    templateDestination.value = newAddress;
    refreshTemplates();
}

//TODO z index scuffed user settings bzw edit/delete popup ist unter den sachen vom header
//TODO is create journey responsive?
//TODO adress inpout sidebar
</script>

<template>
    <div @click="closeFilterWhenOutsideClick">
        <div
            class="absolute right-0 top-3 z-10 pr-2 sm:top-4 md:top-6 md:pr-8 lg:pr-20"
        >
            <div id="right-header" class="flex flex-row items-center">
                <div
                    id="search-and-filter"
                    class="mr-4 hidden flex-row border-r-2 border-natural-200 lg:flex"
                >
                    <div
                        id="search"
                        v-tooltip.top="{
                            value:
                                tabIndex === 0
                                    ? t('dashboard.search.journey.tooltip')
                                    : t('dashboard.search.template.tooltip'),
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
                            @input="
                                tabIndex === 0
                                    ? searchJourneys()
                                    : searchTemplate()
                            "
                        />
                        <button @click="searchInput.focus()">
                            <SvgSearchIcon
                                class="absolute right-1 top-1 h-7 w-7"
                            />
                        </button>
                    </div>
                    <div id="sort" class="mr-4">
                        <SvgDashboardSort
                            aria-haspopup="true"
                            aria-controls="overlay_tmenu"
                            class="h-9 w-9 hover:cursor-pointer"
                            @click="toggle"
                        />
                    </div>
                    <div v-if="tabIndex === 1" id="filter" class="mr-4">
                        <SvgDashboardFilter
                            class="h-9 w-9 hover:cursor-pointer"
                            :is-active="!!isFilterVisible || isFiltered"
                            @click.stop="isFilterVisible = !isFilterVisible"
                        />
                    </div>
                </div>
                <NuxtLink
                    v-if="tabIndex === 0"
                    to="/journey/new"
                    class="group mr-2.5 hidden flex-row items-center rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 font-semibold text-text hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-pesto-600 dark:text-natural-50 dark:hover:bg-ronchi-300 dark:hover:text-text lg:flex"
                >
                    <SvgCreateNewJourneyIcon
                        class="mr-1 h-5 w-5 fill-text dark:fill-natural-50 dark:group-hover:fill-text"
                    />
                    <T key-name="dashboard.new" />
                </NuxtLink>
                <NuxtLink :to="'/user/' + user.username" class="mr-2.5">
                    <SvgUserIcon class="mt-1 h-9 w-9" />
                </NuxtLink>
                <button @click="isUserSettingsVisible = !isUserSettingsVisible">
                    <SvgDashboardSettings class="mt-1 h-9 w-9" />
                </button>
            </div>
        </div>
        <TabView
            v-model:active-index="tabIndex"
            class="mt-5 bg-background px-2 dark:bg-background-dark md:px-8 lg:px-20"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark text-text dark:text-natural-50',
                },
                panelContainer: {
                    class: 'text-text dark:text-natural-50 font-nunito bg-background dark:bg-background-dark p-0',
                },
                nav: {
                    class: 'font-nunito bg-background dark:bg-background-dark sm:flex sm:gap-x-5',
                },
                navContainer: {
                    class: 'border-b-2 border-calypso-300 dark:border-calypso-700 rounded ',
                },
                inkbar: { class: 'pt-0.5 bg-calypso-600 dark:bg-calypso-400' },
            }"
        >
            <TabPanel
                :pt="{
                    headerAction: () => ({
                        class: [
                            'font-nunito bg-background dark:bg-background-dark p-1 mr-2 sm:pb-2',
                            {
                                'text-text dark:text-natural-50':
                                    tabIndex === 0,
                                'text-natural-500 dark:text-natural-300':
                                    tabIndex !== 0,
                            },
                        ],
                    }),
                }"
            >
                <template #header>
                    <div class="flex flex-row items-center">
                        <SvgDashboardIcon
                            class="mr-1 mt-0.5 md:h-7 md:w-7"
                            :class="
                                tabIndex !== 0
                                    ? 'fill-natural-500 dark:fill-natural-300'
                                    : 'fill-text dark:fill-natural-50'
                            "
                        />
                        <h1
                            class="mt-1 text-xl font-medium sm:text-2xl md:text-3xl"
                            :class="tabIndex === 0 ? 'max-xs:hidden' : ''"
                        >
                            <T key-name="common.dashboard" />
                        </h1>
                    </div>
                </template>
                <div
                    id="header-mobile-second-row"
                    class="mt-3 flex justify-between lg:hidden"
                >
                    <div id="search-and-filter" class="flex flex-row">
                        <div id="filter" class="mr-2">
                            <SvgDashboardSort
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
                                class="w-40 rounded-3xl border border-natural-200 bg-natural-50 px-3 py-1.5 placeholder-natural-400 focus:border-dandelion-300 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:placeholder-natural-200 xs:w-44 sm:w-72 lg:w-72"
                                :placeholder="t('dashboard.search')"
                                @input="searchJourneys()"
                            />
                            <button @click="searchInputMobile.focus()">
                                <SvgSearchIcon
                                    class="absolute right-1 top-1 h-7 w-7"
                                />
                            </button>
                        </div>
                    </div>
                    <NuxtLink
                        to="/journey/new"
                        class="flex flex-row items-center justify-center lg:hidden"
                    >
                        <button
                            class="flex flex-row items-center justify-center text-nowrap rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 font-semibold text-text dark:border-dandelion-300 dark:bg-ronchi-300 md:px-4"
                        >
                            <SvgCreateNewJourneyIcon
                                class="mr-1 h-5 w-5 fill-text"
                            />
                            <span class="xs:hidden">
                                <T key-name="dashboard.new.short" />
                            </span>
                            <span class="max-xs:hidden">
                                <T key-name="dashboard.new" />
                            </span>
                        </button>
                    </NuxtLink>
                </div>
                <div class="flex justify-center">
                    <div
                        id="journeys"
                        class="mt-2 grid gap-5 md:gap-4 lg:gap-6"
                        :class="
                            currentJourneys.length === 0
                                ? 'grid-cols-1'
                                : 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4'
                        "
                    >
                        <DashboardJourneyCard
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
                            <SvgCreateNewJourneyCardDark
                                class="hidden dark:lg:block"
                            />
                            <div
                                class="flex h-[7.5rem] min-w-36 flex-grow items-center justify-center rounded-md border border-dandelion-300 bg-dandelion-100 dark:bg-pesto-600 lg:hidden"
                            >
                                <SvgCreateNewJourneyIcon
                                    class="h-14 w-14 fill-text dark:fill-natural-50"
                                />
                            </div>
                        </NuxtLink>
                    </div>
                </div>
            </TabPanel>
            <TabPanel
                :pt="{
                    headerAction: () => ({
                        class: [
                            'font-nunito bg-background dark:bg-background-dark p-1 sm:pb-2',
                            {
                                'text-text dark:text-natural-50':
                                    tabIndex === 1,
                                'text-natural-500 dark:text-natural-300':
                                    tabIndex !== 1,
                            },
                        ],
                    }),
                }"
            >
                <template #header>
                    <div class="flex flex-row items-center">
                        <i
                            class="pi pi-objects-column mr-2 text-xl sm:text-2xl"
                            :class="tabIndex === 1 ? 'max-xs:mt-1' : ''"
                        />
                        <h1
                            class="mt-1 text-xl font-medium sm:text-2xl md:text-3xl"
                            :class="tabIndex === 1 ? 'max-xs:hidden' : ''"
                        >
                            <T key-name="common.templates" />
                        </h1>
                    </div>
                </template>
                <div
                    id="header-mobile-second-row"
                    class="mt-3 flex justify-between lg:hidden"
                >
                    <div id="search-and-filter" class="flex flex-row">
                        <div id="filter" class="mr-2">
                            <SvgDashboardSort
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
                                class="w-40 rounded-3xl border border-natural-200 bg-natural-50 px-3 py-1.5 placeholder-natural-400 focus:border-dandelion-300 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:placeholder-natural-200 xs:w-[50vw] sm:w-72 lg:w-72"
                                :placeholder="t('dashboard.search')"
                                @input="searchTemplate()"
                            />
                            <button @click="searchInputMobile.focus()">
                                <SvgSearchIcon
                                    class="absolute right-1 top-1 h-7 w-7"
                                />
                            </button>
                        </div>
                    </div>
                    <SvgDashboardFilter
                        class="h-9 w-9 hover:cursor-pointer"
                        :is-active="!!isFilterVisible || isFiltered"
                        @click.stop="isFilterVisible = !isFilterVisible"
                    />
                </div>
                <div
                    id="templates"
                    class="mt-3 grid w-full grid-cols-2 gap-5 sm:grid-cols-3 xl:grid-cols-4"
                >
                    <TemplateCard
                        v-for="template in templates"
                        :key="template.id"
                        class="hidden sm:block"
                        :class="
                            status === 'pending' && isFilterVisible
                                ? 'hidden'
                                : ''
                        "
                        :template="template"
                        @open-template="
                            openedTemplate = template;
                            isTemplatePopupVisible = true;
                        "
                    />
                    <TemplateCardSmall
                        v-for="template in templates"
                        :key="template.id"
                        class="sm:hidden"
                        :class="
                            status === 'pending' && isFilterVisible
                                ? 'hidden'
                                : ''
                        "
                        :template="template"
                        @open-template="
                            openedTemplate = template;
                            isTemplatePopupVisible = true;
                        "
                    />
                    <h4
                        v-if="
                            status === 'success' &&
                            templateData.data.length === 0
                        "
                        class="col-span-full font-bold"
                    >
                        <T key-name="dashboard.templates.filter.none" />
                        <button
                            class="text-mahagony-400 underline"
                            @click="clearFilters"
                        >
                            <T key-name="dashboard.template.filter.clear" />
                        </button>
                    </h4>
                    <div
                        v-if="isFilterVisible"
                        id="filter-dialog"
                        ref="filterDialog"
                        class="absolute right-0 mr-20 hidden w-64 flex-col rounded-lg bg-natural-50 px-3 pt-2 shadow-lg dark:bg-background-dark sm:flex"
                    >
                        <div id="length">
                            <div class="flex items-center">
                                <h3 class="text-xl">
                                    <T key-name="dashboard.sort.length" />
                                </h3>
                                <span
                                    class="ml-1 h-0.5 w-full bg-calypso-400"
                                />
                            </div>
                            <div class="mt-3 px-1">
                                <Slider
                                    v-model="templateJourneyLengthMinMax"
                                    range
                                    :min="1"
                                    :max="31"
                                    class="w-full"
                                    :pt="{
                                        root: 'bg-natural-200 dark:bg-natural-300',
                                        range: 'bg-calypso-400 dark:bg-calypso-400',
                                        startHandler: 'bg-calypso-600',
                                        endHandler: 'bg-calypso-600',
                                    }"
                                    @slideend="refreshTemplates()"
                                />
                                <div
                                    class="-px-1 mt-2.5 flex justify-between text-natural-500 dark:text-natural-300"
                                >
                                    <span>1</span>
                                    <span>31+</span>
                                </div>
                                <div class="mt-1 flex flex-row gap-x-3">
                                    <T
                                        key-name="dashboard.template.filter.length.from"
                                    />
                                    <InputNumber
                                        v-model="templateJourneyLengthMinMax[0]"
                                        input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400"
                                        input-id="min"
                                        :min="1"
                                        :max="31"
                                        :allow-empty="false"
                                        @input="refreshTemplates()"
                                    />
                                    <T
                                        key-name="dashboard.template.filter.length.to"
                                    />
                                    <InputNumber
                                        v-model="templateJourneyLengthMinMax[1]"
                                        input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400"
                                        input-id="max"
                                        :min="1"
                                        :max="31"
                                        :allow-empty="false"
                                        @input="refreshTemplates()"
                                    />
                                    <T key-name="template.days" />
                                </div>
                            </div>
                        </div>
                        <div id="destination" class="mt-3">
                            <div class="flex items-center">
                                <h3 class="whitespace-nowrap text-xl">
                                    <T
                                        key-name="dashboard.template.filter.destination"
                                    />
                                </h3>
                                <span
                                    class="ml-1 h-0.5 w-full bg-calypso-400"
                                />
                            </div>
                            <p
                                class="mb-1 text-natural-700 dark:text-natural-200"
                            >
                                <T
                                    key-name="dashboard.template.filter.destination.description"
                                />
                            </p>
                            <FormAddressInput
                                id="template-destination"
                                name="destination"
                                :value="templateDestination"
                                :custom-class="addressCss"
                                @change-address="changeAddress"
                            />
                        </div>
                        <div id="creator" class="mt-5">
                            <div class="flex items-center">
                                <h3 class="whitespace-nowrap text-xl">
                                    <T
                                        key-name="dashboard.template.filter.creator"
                                    />
                                </h3>
                                <span
                                    class="ml-1 h-0.5 w-full bg-calypso-400"
                                />
                            </div>
                            <p
                                class="mb-1 text-natural-700 dark:text-natural-200"
                            >
                                <T
                                    key-name="dashboard.template.filter.creator.description"
                                />
                            </p>
                            <AutoComplete
                                v-model="templateCreator"
                                input-class="bg-natural-50 dark:bg-natural-700 border-2 border-natural-300 dark:border-natural-800 rounded-lg pl-1.5 text-base focus:border-calypso-400 py-[0.275rem]"
                                :pt="{
                                    panel: 'w-20 bg-natural-50 dark:bg-natural-900',
                                    item: 'text-text dark:text-natural-50 hover:text-text hover:bg-natural-100 dark:hover:bg-natural-700 focus:bg-natural-100 dark:focus:bg-natural-700',
                                }"
                                :suggestions="usernames"
                                :force-selection="true"
                                :complete-on-focus="true"
                                :empty-search-message="
                                    t('dashboard.template.filter.creator.empty')
                                "
                                @before-show="getUsers()"
                                @complete="searchUser()"
                                @item-select="refreshTemplates()"
                                @clear="refreshTemplates(), searchUser()"
                            />
                        </div>
                        <div class="flex justify-end pb-1 pt-20">
                            <button
                                class="dark:text-mahagony-200 text-mahagony-400 hover:underline"
                                @click="clearFilters"
                            >
                                <T key-name="dashboard.template.filter.clear" />
                            </button>
                        </div>
                    </div>
                </div>

                <div ref="loader">
                    <div v-if="moreTemplatesAvailable">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="dashboard.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="!moreTemplatesAvailable"
                    class="mt-5 flex w-full flex-col justify-center gap-x-2 text-center sm:flex sm:flex-row sm:text-left"
                >
                    <T key-name="dashboard.templates.nomoretemplates" />
                    <NuxtLink
                        to="/journey/new"
                        class="group flex items-center justify-center hover:text-calypso-400 sm:justify-start"
                    >
                        <span class="group-hover:underline">
                            <T
                                key-name="dashboard.templates.nomoretemplates.link"
                            />
                        </span>
                        <i class="pi pi-angle-right" />
                    </NuxtLink>
                </div>
            </TabPanel>
        </TabView>
        <div class="dialogs z-50">
            <Sidebar
                v-model:visible="isFilterVisible"
                modal
                position="bottom"
                :auto-z-index="true"
                :draggable="false"
                :block-scroll="true"
                :show-close-icon="false"
                class="z-50 mt-auto flex h-auto flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden lg:-z-10"
                :pt="{
                    root: {
                        class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden ',
                    },
                    header: {
                        class: 'flex w-full justify-between pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50 rounded-3xl',
                    },
                    title: {
                        class: 'font-nunito text-4xl font-semibold',
                    },
                    content: {
                        class: 'font-nunito bg-background dark:bg-background-dark px-0 -ml-2 sm:pr-12 h-full',
                    },
                    footer: { class: 'h-0' },
                    mask: {
                        class: 'sm:collapse bg-natural-50',
                    },
                }"
                @hide="isFilterVisible = false"
            >
                <template #header>
                    <div class="flex items-center">
                        <button
                            class="-ml-6 flex justify-center pr-4"
                            @click="isFilterVisible = false"
                        >
                            <span class="pi pi-angle-down text-2xl" />
                        </button>
                        <div
                            class="text-nowrap font-nunito text-3xl font-semibold"
                        >
                            <T key-name="dashboard.template.filter" />
                        </div>
                    </div>
                    <button
                        class="dark:text-mahagony-200 text-mahagony-400 hover:underline"
                        @click="clearFilters"
                    >
                        <T key-name="dashboard.template.filter.clear" />
                    </button>
                </template>
                <div
                    class="flex h-full flex-col gap-y-5 pl-6 pr-2 text-text dark:text-natural-50"
                >
                    <div id="length">
                        <div class="flex items-center">
                            <h3 class="text-2xl">
                                <T key-name="dashboard.sort.length" />
                            </h3>
                        </div>
                        <div class="mt-3 px-1">
                            <Slider
                                v-model="templateJourneyLengthMinMax"
                                range
                                :min="1"
                                :max="31"
                                class="w-full"
                                :pt="{
                                    root: 'bg-natural-200 dark:bg-natural-300',
                                    range: 'bg-calypso-400 dark:bg-calypso-400',
                                    startHandler: 'bg-calypso-600',
                                    endHandler: 'bg-calypso-600',
                                }"
                                @slideend="refreshTemplates()"
                            />
                            <div
                                class="-px-1 mt-2.5 flex justify-between text-natural-500 dark:text-natural-300"
                            >
                                <span>1</span>
                                <span>31+</span>
                            </div>
                            <div class="mt-1 flex flex-row gap-x-3">
                                <T
                                    key-name="dashboard.template.filter.length.from"
                                />
                                <InputNumber
                                    v-model="templateJourneyLengthMinMax[0]"
                                    input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400"
                                    input-id="min"
                                    :min="1"
                                    :max="31"
                                    :allow-empty="false"
                                    @input="refreshTemplates()"
                                />
                                <T
                                    key-name="dashboard.template.filter.length.to"
                                />
                                <InputNumber
                                    v-model="templateJourneyLengthMinMax[1]"
                                    input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400"
                                    input-id="max"
                                    :min="1"
                                    :max="31"
                                    :allow-empty="false"
                                    @input="refreshTemplates()"
                                />
                                <T key-name="template.days" />
                            </div>
                        </div>
                    </div>
                    <div id="destination" class="mt-3">
                        <div class="flex items-center">
                            <h3 class="whitespace-nowrap text-2xl">
                                <T
                                    key-name="dashboard.template.filter.destination"
                                />
                            </h3>
                        </div>
                        <p class="mb-1 text-natural-700 dark:text-natural-200">
                            <T
                                key-name="dashboard.template.filter.destination.description"
                            />
                        </p>
                        <FormAddressInput
                            id="template-destination"
                            name="destination"
                            value=""
                            :custom-class="addressCss"
                            @change-address="changeAddress"
                        />
                    </div>
                    <div id="creator" class="mt-5 w-full">
                        <div class="flex items-center">
                            <h3 class="whitespace-nowrap text-2xl">
                                <T
                                    key-name="dashboard.template.filter.creator"
                                />
                            </h3>
                        </div>
                        <p class="mb-1 text-natural-700 dark:text-natural-200">
                            <T
                                key-name="dashboard.template.filter.creator.description"
                            />
                        </p>
                        <!-- TODO w-full -->
                        <AutoComplete
                            v-model="templateCreator"
                            input-class="bg-natural-50  dark:bg-natural-700 border-2 border-natural-300 dark:border-natural-800 rounded-lg pl-1.5 text-base focus:border-calypso-400 py-[0.275rem]"
                            :pt="{
                                panel: 'bg-natural-50 dark:bg-natural-900',
                                item: 'text-text dark:text-natural-50 hover:text-natural-100 hover:bg-natural-100 dark:hover:bg-natural-700 focus:bg-natural-100 dark:focus:bg-natural-700',
                            }"
                            :suggestions="usernames"
                            :force-selection="true"
                            :complete-on-focus="true"
                            :empty-search-message="
                                t('dashboard.template.filter.creator.empty')
                            "
                            @before-show="getUsers()"
                            @complete="searchUser()"
                            @item-select="refreshTemplates()"
                            @clear="refreshTemplates(), searchUser()"
                        />
                    </div>
                    <div
                        class="mt-5 flex w-full justify-center text-text dark:text-natural-50"
                    >
                        <button
                            class="w-full rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-center text-xl font-semibold text-natural-900 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-200 dark:hover:bg-pesto-600"
                            @click="isFilterVisible = false"
                        >
                            <T key-name="dashboard.template.filter.button" />
                        </button>
                    </div>
                </div>
            </Sidebar>
            <TemplatePopup
                v-if="openedTemplate"
                :template="openedTemplate!"
                :is-template-dialog-visible="isTemplatePopupVisible"
                @close="isTemplatePopupVisible = false"
            />
            <TieredMenu
                id="overlay_tmenu"
                ref="menu"
                :model="items"
                popup
                class="z-30 rounded-xl border-2 border-natural-200 bg-natural-50 dark:border-natural-900 dark:bg-natural-800"
                :pt="{
                    menuitem: {
                        class: 'bg-natural-50 dark:bg-natural-800 hover:bg-dandelion-300 rounded-md',
                    },
                    content: { class: 'hover:bg-dandelion-300 rounded-md' },
                    submenu: { class: 'bg-natural-50 dark:bg-natural-800' },
                }"
            >
                <template #start>
                    <h1
                        class="ml-2 text-sm text-natural-400 dark:text-natural-200"
                    >
                        <T
                            v-if="tabIndex === 0"
                            key-name="dashboard.sort.journeys.header"
                        />
                        <T v-else key-name="dashboard.sort.templates.header" />
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
                        <i
                            v-if="hasSubmenu"
                            class="pi pi-angle-right ml-auto"
                        />
                    </a>
                </template>
            </TieredMenu>
            <DashboardUserSettings
                :visible="isUserSettingsVisible"
                :prop-username="user.username"
                :prop-displayname="user.display_name"
                :prop-email="user.email"
                @close="isUserSettingsVisible = false"
            />
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
        </div>
        <!--
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
                    class="group mr-2.5 hidden flex-row items-center rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 font-semibold text-text hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-pesto-600 dark:text-natural-50 dark:hover:bg-ronchi-300 dark:hover:text-text lg:flex"
                >
                    <SvgCreateNewJourneyIcon
                        class="mr-1 h-5 w-5 fill-text dark:fill-natural-50 dark:group-hover:fill-text"
                    />
                    <T key-name="dashboard.new" />
                </NuxtLink>
                <NuxtLink :to="'/user/' + user.username" class="mr-2.5">
                    <SvgUserIcon class="mt-1 h-9 w-9" />
                </NuxtLink>
                <button @click="isUserSettingsVisible = !isUserSettingsVisible">
                    <SvgSettingsIcon class="mt-1 h-9 w-9" />
                </button>
                <UserSettings
                    :visible="isUserSettingsVisible"
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
                        class="flex h-[7.5rem] min-w-36 flex-grow items-center justify-center rounded-md border border-dandelion-300 bg-dandelion-100 dark:bg-pesto-600 lg:hidden"
                    >
                        <SvgCreateNewJourneyIcon
                            class="h-14 w-14 fill-text dark:fill-natural-50"
                        />
                    </div>
                </NuxtLink>
            </div>
        </div>

    </div>
    --></div>
</template>
