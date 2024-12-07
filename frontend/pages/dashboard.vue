<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { compareAsc, compareDesc } from "date-fns";
import debounce from "~/utils/debounce";

definePageMeta({
    middleware: ["sanctum:auth"],
});

const { t } = useTranslate();
const router = useRouter();
const route = useRoute();
const journeysStore = useJourneysStore();
const templateFilterstore = useTemplateFilterStore();
const client = useSanctumClient();
const toast = useToast();
const nuxtApp = useNuxtApp();
const colorMode = useColorMode();

const searchInput = ref();
const searchInputMobile = ref();
const searchValue = ref<string>("");
const tabIndex = ref<number>(0);
const menu = ref();
const items = ref();

//journeys
const journeys = ref<Journey[]>([]);
const currentJourneys = ref<Journey[]>([]);
currentJourneys.value = journeysStore.journeys;

//user settings
const user = ref();
const isUserSettingsVisible = ref<boolean>(false);

//templates
const openedTemplate = ref<Template>();
const isTemplatePopupVisible = ref(false);
const isFilterVisible = ref<boolean>(route.query.filter_open === "true");
const filterDialog = ref<HTMLElement>();
const usernames = ref<string[]>([]);
const templates = ref<Template[]>([]);
const observer = ref<IntersectionObserver>();
const loader = ref<HTMLElement | undefined>();

const filters = reactive({
    TEMPLATE_MAX_LENGTH: templateFilterstore.getFilter("TEMPLATE_MAX_LENGTH"),
    PER_PAGE: templateFilterstore.getFilter("PER_PAGE"),
    moreTemplatesAvailable: templateFilterstore.getFilter(
        "moreTemplatesAvailable",
    ),
    templateJourneyLengthMinMax: templateFilterstore.getFilter(
        "templateJourneyLengthMinMax",
    ),
    sortby: templateFilterstore.getFilter("sortby"),
    sortorder: templateFilterstore.getFilter("sortorder"),
    templateDestinationInput: templateFilterstore.getFilter(
        "templateDestinationInput",
    ),
    templateDestinationName: templateFilterstore.getFilter(
        "templateDestinationName",
    ),
    templateCreator: templateFilterstore.getFilter("templateCreator"),
    cursor: templateFilterstore.getFilter("cursor"),
    nextCursor: templateFilterstore.getFilter("nextCursor"),
});

const borderColor = ref();
const borderColorFocus = ref();
const backgroundColor = ref();

const toggle = (event: Event) => {
    menu.value.toggle(event);
};

//redirect to invite if an invite is stored in local storage
if (localStorage.getItem("JP_invite_journey_id")) {
    navigateTo(localStorage.getItem("JP_invite_journey_id"));
}

if (route.query.tab === "templates") {
    tabIndex.value = 1;
} else {
    tabIndex.value = 0;
}

onMounted(() => {
    watch(
        tabIndex,
        async () => {
            if (tabIndex.value === 1) {
                if (
                    route.query.id &&
                    route.query.id !== "undefined" &&
                    route.query.id !== "null" &&
                    route.query.id !== "" &&
                    route.query.tab === "templates"
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
                                    summary: t.value(
                                        "template.notfound.summary",
                                    ),
                                    detail: t.value(
                                        "template.notfound.summary.detail",
                                    ),
                                    life: 6000,
                                });
                            } else {
                                toast.add({
                                    severity: "error",
                                    summary: t.value(
                                        "common.toast.error.heading",
                                    ),
                                    detail: t.value("common.error.unknown"),
                                    life: 6000,
                                });
                            }
                            router.push({
                                path: "/dashboard",
                                query: {
                                    tab: "templates",
                                },
                            });
                        },
                    });
                } else {
                    router.push({
                        path: "/dashboard",
                        query: {
                            tab: "templates",
                        },
                    });
                }

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
                            filters.moreTemplatesAvailable &&
                            !isFilterVisible.value
                        ) {
                            filters.cursor = filters.nextCursor;
                            refresh();
                        }
                    }
                });

                if (loader.value) {
                    observer.value.observe(loader.value);
                }

                setAddressColor();

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
                                    filters.sortby.value = "length";
                                    filters.sortorder.value = "asc";
                                    refreshTemplates();
                                },
                            },
                            {
                                label: t.value(
                                    "dashboard.sort.descending.numeric",
                                ),
                                icon: "pi pi-sort-amount-down",
                                command: () => {
                                    filters.sortby.value = "length";
                                    filters.sortorder.value = "desc";
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
                                    filters.sortby.value = "name";
                                    filters.sortorder.value = "asc";
                                    refreshTemplates();
                                },
                            },
                            {
                                label: t.value("dashboard.sort.descending"),
                                icon: "pi pi-sort-alpha-down",
                                command: () => {
                                    filters.sortby.value = "name";
                                    filters.sortorder.value = "desc";
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
                                    filters.sortby.value = "destination";
                                    filters.sortorder.value = "asc";
                                    refreshTemplates();
                                },
                            },
                            {
                                label: t.value("dashboard.sort.descending"),
                                icon: "pi pi-sort-alpha-down",
                                command: () => {
                                    filters.sortby.value = "destination";
                                    filters.sortorder.value = "desc";
                                    refreshTemplates();
                                },
                            },
                        ],
                    },
                ];
            } else {
                router.push({ path: "/dashboard" });
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

    watch(filters.templateJourneyLengthMinMax, () => {
        let temp;
        if (
            filters.templateJourneyLengthMinMax.value[0] >
            filters.templateJourneyLengthMinMax.value[1]
        ) {
            temp = filters.templateJourneyLengthMinMax.value[0];
            filters.templateJourneyLengthMinMax.value[0] =
                filters.templateJourneyLengthMinMax.value[1];
            filters.templateJourneyLengthMinMax.value[1] = temp;
        }
    });

    watch(
        filters,
        () => {
            templateFilterstore.setFilters(filters);
        },
        { deep: true },
    );

    watch(
        () => colorMode.preference,
        () => {
            setAddressColor();
        },
    );
});

onUnmounted(() => {
    if (observer.value && loader.value) {
        observer.value.unobserve(loader.value);
    }
});

/**
 * Fetches templates from the backend
 */
const {
    data: templateDataDashboard,
    refresh,
    status,
} = await useAsyncData(
    "templates",
    () =>
        client(`/api/template`, {
            params: {
                sort_by: filters.sortby,
                order: filters.sortorder,
                template_name: searchValue.value,
                template_journey_length_min:
                    filters.templateJourneyLengthMinMax[0],
                template_journey_length_max:
                    filters.templateJourneyLengthMinMax[1],
                template_journey_length_max_const: filters.TEMPLATE_MAX_LENGTH,
                template_destination_input: filters.templateDestinationInput,
                template_destination_name: filters.templateDestinationName,
                template_creator: filters.templateCreator,
                cursor: filters.cursor,
                per_page: filters.PER_PAGE,
            },
        }),
    {
        getCachedData(key) {
            return nuxtApp.payload.data[key] || nuxtApp.static.data[key];
        },
    },
);

watch(
    templateDataDashboard,
    () => {
        if (templateDataDashboard.value) {
            templates.value.push(...templateDataDashboard.value.data);

            if (templateDataDashboard.value.next_cursor === null) {
                filters.moreTemplatesAvailable = false;
            } else {
                filters.nextCursor = templateDataDashboard.value.next_cursor;
                filters.moreTemplatesAvailable = true;
            }
        }
    },
    { immediate: true },
);

const refreshTemplates = () => {
    templates.value = [];
    filters.cursor = null;
    filters.nextCursor = null;
    filters.moreTemplatesAvailable = true;
    refresh();
};

function setAddressColor() {
    const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");

    if (
        colorMode.preference === "dark" ||
        (darkTheme.matches && colorMode.preference === "system")
    ) {
        borderColor.value = "#464646";
        borderColorFocus.value = "#50A1C0";
        backgroundColor.value = "#525252";
    } else {
        borderColor.value = "#BDBDBD";
        borderColorFocus.value = "#50A1C0";
        backgroundColor.value = "#FCFCFC";
    }
}

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

const openTemplateDialog = (template: Template) => {
    openedTemplate.value = template;
    isTemplatePopupVisible.value = true;
    router.push({
        query: {
            tab: "templates",
            id: template ? template.id : null,
        },
    });
};

const closeTemplateDialog = () => {
    isTemplatePopupVisible.value = false;
    openedTemplate.value = undefined;
    router.push({
        query: {
            tab: "templates",
        },
    });
};

/**
 * debounce search input to prevent too many requests
 */
const searchTemplate = debounce(() => {
    refreshTemplates();
});

const isFiltered = computed(() => {
    return (
        filters.templateJourneyLengthMinMax[0] !== 1 ||
        filters.templateJourneyLengthMinMax[1] !==
            filters.TEMPLATE_MAX_LENGTH ||
        filters.templateDestinationInput !== "" ||
        filters.templateDestinationName !== "" ||
        filters.templateCreator !== ""
    );
});

/**
 * clear template filters
 */
function clearFilters() {
    Object.assign(filters, templateFilterstore.resetFilters());
    searchValue.value = "";
    refreshTemplates();
}

/**
 * get username(s) for AutoComplete for created by template filter
 */
function getUser() {
    client(`/api/user?search=${filters.templateCreator}&per_page=25`).then(
        (res) => {
            usernames.value = res.data.map((user: User) => user.username);
        },
    );
}

/**
 * debounce creator search input to prevent too many requests
 */
const filterTemplateCreator = debounce(() => {
    refreshTemplates();
});

const changeAddress = debounce((inputValue: unknown) => {
    filters.templateDestinationInput = inputValue as string;
    refreshTemplates();
});

function retrievedAddress(inputValue: string, name: string) {
    filters.templateDestinationInput = inputValue;
    filters.templateDestinationName = name;
    refreshTemplates();
}

/*
Fetches all journeys from the backend
stores response in journeys and currentJourneys
also sets journeys in the store
*/
const { data } = await useAsyncData("journeys", () => client("/api/journey"), {
    getCachedData(key) {
        return nuxtApp.payload.data[key] || nuxtApp.static.data[key];
    },
});
journeys.value = data.value;
currentJourneys.value = data.value;
journeysStore.setJourneys(data.value);

const { data: currUser } = await useAsyncData(
    "currUserDashboard",
    () => client(`/api/me`),
    {
        getCachedData(key) {
            return nuxtApp.payload.data[key] || nuxtApp.static.data[key];
        },
    },
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
</script>

<template>
    <div
        id="dashboard"
        class="min-h-screen"
        @click="closeFilterWhenOutsideClick"
    >
        <TabView
            v-model:active-index="tabIndex"
            class="mt-5 bg-background px-2 dark:bg-background-dark md:px-8 lg:px-20"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark text-text dark:text-natural-50 z-0',
                },
                panelContainer: {
                    class: 'text-text dark:text-natural-50 font-nunito bg-background dark:bg-background-dark p-0',
                },
                nav: {
                    class: 'font-nunito bg-background dark:bg-background-dark sm:flex sm:gap-x-5',
                },
                navContainer: {
                    class: 'border-b-2 border-calypso-300 dark:border-calypso-700 rounded z-0',
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
                    <div
                        class="flex flex-row items-center"
                        data-test="tab-dashboard"
                    >
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
                                data-test="sort-journeys-mobile"
                                @click="toggle"
                            />
                        </div>
                        <div id="search" class="relative">
                            <input
                                ref="searchInputMobile"
                                v-model="searchValue"
                                type="text"
                                data-test="search-journeys-input-mobile"
                                class="w-40 rounded-3xl border border-natural-200 bg-natural-50 px-3 py-1.5 placeholder-natural-400 focus:border-dandelion-300 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:placeholder-natural-200 xs:w-44 sm:w-72 lg:w-72"
                                :placeholder="t('dashboard.search')"
                                @input="searchJourneys()"
                            />
                            <button
                                data-test="search-journeys-button-mobile"
                                @click="searchInputMobile.focus()"
                            >
                                <SvgSearchIcon
                                    class="absolute right-1 top-1 h-7 w-7"
                                />
                            </button>
                        </div>
                    </div>
                    <NuxtLink
                        to="/journey/new"
                        class="flex flex-row items-center justify-center lg:hidden"
                        data-test="new-journey-button-mobile"
                    >
                        <button
                            class="group flex flex-row items-center justify-center text-nowrap rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-2 py-1 font-semibold text-text hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-pesto-600 dark:text-natural-50 dark:hover:bg-ronchi-300 dark:hover:text-text md:px-4"
                        >
                            <SvgCreateNewJourneyIcon
                                class="mr-1 h-5 w-5 fill-text dark:fill-natural-50 dark:group-hover:fill-text"
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
                <div class="mt-3 flex justify-center">
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
                            :data-test="'journey-card-' + journey.id"
                            :name="journey.name"
                            :destination="journey.destination"
                            :from="new Date(journey.from)"
                            :to="new Date(journey.to)"
                            :role="Number(journey.role)"
                            :mapbox-id="journey.mapbox_id"
                            :mapbox-full-address="journey.mapbox_full_address"
                            @journey-deleted="removeJourney"
                            @journey-leave="removeJourney"
                            @journey-edited="editJourney"
                        />
                        <NuxtLink
                            to="/journey/new"
                            class="group"
                            data-test="new-journey-card"
                        >
                            <SvgCreateNewJourneyCard
                                class="hidden dark:hidden lg:block"
                            />
                            <SvgCreateNewJourneyCardDark
                                class="hidden dark:lg:block"
                            />
                            <div
                                class="flex h-[7.5rem] min-w-36 flex-grow items-center justify-center rounded-md border border-dandelion-300 bg-dandelion-200 hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-pesto-600 dark:text-natural-50 dark:hover:bg-ronchi-300 lg:hidden"
                            >
                                <SvgCreateNewJourneyIcon
                                    class="h-14 w-14 fill-text dark:fill-natural-50 dark:group-hover:fill-text"
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
                    <div
                        class="flex flex-row items-center"
                        data-test="tab-templates"
                    >
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
                                data-test="sort-templates-mobile"
                                class="h-9 w-9 hover:cursor-pointer"
                                @click="toggle"
                            />
                        </div>
                        <div id="search" class="relative">
                            <input
                                ref="searchInputMobile"
                                v-model="searchValue"
                                type="text"
                                data-test="search-templates-input-mobile"
                                class="w-40 rounded-3xl border border-natural-200 bg-natural-50 px-3 py-1.5 placeholder-natural-400 focus:border-dandelion-300 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:placeholder-natural-200 xs:w-[50vw] sm:w-72 lg:w-72"
                                :placeholder="t('dashboard.search')"
                                @input="searchTemplate()"
                            />
                            <button
                                data-test="search-templates-button-mobile"
                                @click="searchInputMobile.focus()"
                            >
                                <SvgSearchIcon
                                    class="absolute right-1 top-1 h-7 w-7"
                                />
                            </button>
                        </div>
                    </div>
                    <SvgDashboardFilter
                        class="h-9 w-9 hover:cursor-pointer"
                        data-test="filter-templates-mobile"
                        :is-active="!!isFilterVisible || isFiltered"
                        @click.stop="isFilterVisible = !isFilterVisible"
                    />
                </div>
                <div
                    id="templates"
                    class="mt-5 grid w-full grid-cols-2 gap-5 sm:grid-cols-3 md:gap-4 lg:gap-6 xl:grid-cols-4 2xl:px-20"
                >
                    <TemplateCard
                        v-for="template in templates"
                        :key="template.id"
                        class="hidden md:block"
                        :class="
                            status === 'pending' && isFilterVisible
                                ? 'hidden'
                                : ''
                        "
                        :template="template"
                        :data-test="'template-' + template.id"
                        @open-template="openTemplateDialog(template)"
                    />
                    <TemplateCardSmall
                        v-for="template in templates"
                        :key="template.id"
                        class="md:hidden"
                        :class="
                            status === 'pending' && isFilterVisible
                                ? 'hidden'
                                : ''
                        "
                        :template="template"
                        :data-test="'small-template-' + template.id"
                        @open-template="openTemplateDialog(template)"
                    />
                    <h4
                        v-if="
                            status === 'success' &&
                            templateDataDashboard.data.length === 0
                        "
                        class="col-span-full font-bold"
                        data-test="templates-none"
                    >
                        <T key-name="dashboard.templates.filter.none" />
                        <button
                            class="text-mahagony-400 hover:underline"
                            data-test="templates-filter-clear-button"
                            @click="clearFilters"
                        >
                            <T key-name="dashboard.template.filter.clear" />
                        </button>
                    </h4>
                    <div
                        v-if="isFilterVisible"
                        id="filter-dialog"
                        ref="filterDialog"
                        data-test="templates-filter-dialog"
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
                                    v-model="
                                        filters.templateJourneyLengthMinMax
                                    "
                                    range
                                    :min="1"
                                    :max="filters.TEMPLATE_MAX_LENGTH"
                                    class="w-full"
                                    :pt="{
                                        root: 'bg-natural-200 dark:bg-natural-300',
                                        range: 'bg-calypso-400 dark:bg-calypso-400',
                                        startHandler: 'bg-calypso-600',
                                        endHandler: 'bg-calypso-600',
                                    }"
                                    data-test="templates-filter-length"
                                    @slideend="refreshTemplates()"
                                />
                                <div
                                    class="-px-1 mt-2.5 flex justify-between text-natural-500 dark:text-natural-300"
                                >
                                    <span>1</span>
                                    <span
                                        >{{
                                            filters.TEMPLATE_MAX_LENGTH
                                        }}+</span
                                    >
                                </div>
                                <div class="mt-1 flex flex-row gap-x-3">
                                    <T
                                        key-name="dashboard.template.filter.length.from"
                                    />
                                    <InputNumber
                                        v-model="
                                            filters
                                                .templateJourneyLengthMinMax[0]
                                        "
                                        data-test="templates-filter-length-min"
                                        input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400 dark:focus:border-calypso-400"
                                        input-id="min"
                                        :min="1"
                                        :max="filters.TEMPLATE_MAX_LENGTH"
                                        :allow-empty="false"
                                        @input="refreshTemplates()"
                                    />
                                    <T
                                        key-name="dashboard.template.filter.length.to"
                                    />
                                    <InputNumber
                                        v-model="
                                            filters
                                                .templateJourneyLengthMinMax[1]
                                        "
                                        data-test="templates-filter-length-max"
                                        input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400 dark:focus:border-calypso-400"
                                        input-id="max"
                                        :min="1"
                                        :max="filters.TEMPLATE_MAX_LENGTH"
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
                                data-test="templates-filter-destination"
                                :value="filters.templateDestinationInput"
                                :custom-class="`.SearchIcon {visibility: hidden;} .Input {border: solid 2px ${borderColor} !important; background-color: ${backgroundColor} !important; padding-left: 0.625rem; padding-top: 0rem; padding-bottom: 0rem;} .Input:focus {box-shadow: none; border: solid 2px ${borderColorFocus} !important;}`"
                                @change-address="changeAddress"
                                @retrieved-address="retrievedAddress"
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
                                v-model="filters.templateCreator"
                                data-test="templates-filter-creator"
                                input-class="bg-natural-50 dark:bg-natural-700 border-2 border-natural-300 dark:border-natural-800 rounded-lg pl-1.5 text-base focus:border-calypso-400 dark:focus:border-calypso-400 py-[0.275rem]"
                                :pt="{
                                    panel: 'w-20 bg-natural-50 dark:bg-natural-900',
                                    emptyMessage:
                                        'text-text dark:text-natural-50 font-nunito p-1',
                                    item: 'text-text dark:text-natural-50 hover:text-text hover:bg-natural-100 dark:hover:bg-natural-700 focus:bg-natural-100 dark:focus:bg-natural-700',
                                }"
                                :suggestions="usernames"
                                :complete-on-focus="true"
                                :auto-option-focus="true"
                                :delay="300"
                                :empty-search-message="
                                    t('dashboard.template.filter.creator.empty')
                                "
                                @update:model-value="filterTemplateCreator()"
                                @complete="getUser()"
                                @item-select="refreshTemplates()"
                                @clear="(refreshTemplates(), getUser())"
                            />
                        </div>
                        <div class="flex justify-end pb-1 pt-20">
                            <button
                                class="dark:text-mahagony-200 text-mahagony-400 hover:underline"
                                data-test="templates-filter-clear"
                                @click="clearFilters"
                            >
                                <T key-name="dashboard.template.filter.clear" />
                            </button>
                        </div>
                    </div>
                </div>
                <div ref="loader">
                    <div
                        v-if="filters.moreTemplatesAvailable"
                        data-test="templates-loading"
                    >
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="dashboard.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="!filters.moreTemplatesAvailable"
                    class="mt-5 flex w-full flex-col justify-center gap-x-2 text-center sm:flex sm:flex-row sm:text-left"
                >
                    <T key-name="dashboard.templates.nomoretemplates" />
                    <NuxtLink
                        to="/journey/new"
                        data-test="new-template-link-no-templates-found"
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
        <div
            class="absolute right-0 top-3 pr-2 sm:top-4 md:top-6 md:pr-8 lg:pr-20"
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
                            data-test="search-input"
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
                            data-test="sort-button"
                            class="h-9 w-9 hover:cursor-pointer"
                            @click="toggle"
                        />
                    </div>
                    <div v-if="tabIndex === 1" id="filter" class="mr-4">
                        <SvgDashboardFilter
                            class="h-9 w-9 hover:cursor-pointer"
                            data-test="filter-button"
                            :is-active="!!isFilterVisible || isFiltered"
                            @click.stop="isFilterVisible = !isFilterVisible"
                        />
                    </div>
                </div>
                <NuxtLink
                    v-if="tabIndex === 0"
                    to="/journey/new"
                    data-test="new-journey-button"
                    class="group mr-2.5 hidden flex-row items-center rounded-xl border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 font-semibold text-text hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-pesto-600 dark:text-natural-50 dark:hover:bg-ronchi-300 dark:hover:text-text lg:flex"
                >
                    <SvgCreateNewJourneyIcon
                        class="mr-1 h-5 w-5 fill-text dark:fill-natural-50 dark:group-hover:fill-text"
                    />
                    <T key-name="dashboard.new" />
                </NuxtLink>
                <NuxtLink
                    :to="'/user/' + user.username"
                    class="mr-2.5"
                    data-test="user-profile-button"
                >
                    <SvgUserIcon class="mt-1 h-9 w-9" />
                </NuxtLink>
                <button
                    data-test="user-settings-button"
                    @click="isUserSettingsVisible = !isUserSettingsVisible"
                >
                    <SvgDashboardSettings class="mt-1 h-9 w-9" />
                </button>
            </div>
        </div>
        <div id="dialogs" class="z-50">
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
                            data-test="close-template-filter-drawer"
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
                        data-test="clear-template-filter-drawer"
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
                                v-model="filters.templateJourneyLengthMinMax"
                                range
                                :min="1"
                                :max="filters.TEMPLATE_MAX_LENGTH"
                                class="w-full"
                                :pt="{
                                    root: 'bg-natural-200 dark:bg-natural-300',
                                    range: 'bg-calypso-400 dark:bg-calypso-400',
                                    startHandler: 'bg-calypso-600',
                                    endHandler: 'bg-calypso-600',
                                }"
                                data-test="templates-filter-length-drawer"
                                @slideend="refreshTemplates()"
                            />
                            <div
                                class="-px-1 mt-2.5 flex justify-between text-natural-500 dark:text-natural-300"
                            >
                                <span>1</span>
                                <span>{{ filters.TEMPLATE_MAX_LENGTH }}+</span>
                            </div>
                            <div class="mt-1 flex flex-row gap-x-3">
                                <T
                                    key-name="dashboard.template.filter.length.from"
                                />
                                <InputNumber
                                    v-model="
                                        filters.templateJourneyLengthMinMax[0]
                                    "
                                    data-test="templates-filter-length-min-drawer"
                                    input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400 dark:focus:border-calypso-400"
                                    input-id="min"
                                    :min="1"
                                    :max="filters.TEMPLATE_MAX_LENGTH"
                                    :allow-empty="false"
                                    @input="refreshTemplates()"
                                />
                                <T
                                    key-name="dashboard.template.filter.length.to"
                                />
                                <InputNumber
                                    v-model="
                                        filters.templateJourneyLengthMinMax[1]
                                    "
                                    data-test="templates-filter-length-max-drawer"
                                    input-class="w-11 rounded border-2 border-natural-300 dark:border-natural-800 dark:bg-natural-700 bg-natural-50 pl-1 font-nunito focus:border-calypso-400 dark:focus:border-calypso-400"
                                    input-id="max"
                                    :min="1"
                                    :max="filters.TEMPLATE_MAX_LENGTH"
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
                            data-test="templates-filter-destination-drawer"
                            :custom-class="`.SearchIcon {visibility: hidden;} .Input {border: solid 2px ${borderColor} !important; background-color: ${backgroundColor} !important; padding-left: 0.625rem; padding-top: 0rem; padding-bottom: 0rem;} .Input:focus {box-shadow: none; border: solid 2px ${borderColorFocus} !important;}`"
                            @change-address="changeAddress"
                            @retrieved-address="retrievedAddress"
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
                        <AutoComplete
                            ref="creator"
                            v-model="filters.templateCreator"
                            data-test="templates-filter-creator-drawer"
                            input-class="bg-natural-50 w-full dark:bg-natural-700 border-2 border-natural-300 dark:border-natural-800 rounded-lg pl-1.5 text-base focus:border-calypso-400 dark:focus:border-calypso-400 py-[0.275rem]"
                            :pt="{
                                root: 'w-full',
                                panel: 'bg-natural-50 dark:bg-natural-900',
                                emptyMessage:
                                    'text-text dark:text-natural-50 font-nunito p-1',
                                item: 'text-text dark:text-natural-50 hover:text-text hover:bg-natural-100 dark:hover:bg-natural-700 focus:bg-natural-100 dark:focus:bg-natural-700',
                            }"
                            :suggestions="usernames"
                            :force-selection="true"
                            :complete-on-focus="true"
                            :delay="300"
                            :empty-search-message="
                                t('dashboard.template.filter.creator.empty')
                            "
                            @complete="getUser()"
                            @item-select="refreshTemplates()"
                            @clear="(refreshTemplates(), getUser())"
                        />
                    </div>
                    <div
                        class="mt-5 flex w-full justify-center text-text dark:text-natural-50"
                    >
                        <button
                            data-test="close-button-template-filter-drawer"
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
                :template="openedTemplate"
                :is-template-dialog-visible="isTemplatePopupVisible"
                @close="closeTemplateDialog()"
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
    </div>
</template>

<style>
.p-autocomplete-panel
    .p-autocomplete-items
    .p-autocomplete-item:not(.p-highlight):not(.p-disabled).p-focus {
    @apply bg-natural-100 dark:bg-natural-700;
}
</style>
