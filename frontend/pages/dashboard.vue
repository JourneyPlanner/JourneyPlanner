<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { useDashboardStore } from "@/stores/dashboard";
import { compareAsc, compareDesc } from "date-fns";

const title = "Dashboard";
useHead({
  title: `${title} | JourneyPlanner`,
});

definePageMeta({
  middleware: ["sanctum:auth"],
});

interface Journey {
  id: String;
  name: string;
  destination: string;
  from: Date;
  to: Date;
  role: Number;
}

const { t } = useTranslate();

const store = useDashboardStore();
const journeys = ref<Journey[]>([]);
const searchInput = ref();
const searchInputMobile = ref();
let searchValue = ref<String>("");
let currentJourneys = ref<Journey[]>([]);

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

/**
 * Searches for journeys based on the searchValue
 * sets the currentJourneys to the results
 */
async function searchJourneys() {
  const data: Journey[] = journeys.value;
  const results: Journey[] = data.filter((obj: Journey) => {
    return (
      obj.name.toLowerCase().includes(searchValue.value.toLowerCase()) ||
      obj.destination.toLowerCase().includes(searchValue.value.toLowerCase())
    );
  });
  currentJourneys.value = results;
}

/**
 * Sorts the journeys based on the sortKey
 * @param sortKey the key to sort the journeys by
 */
function sortJourneys(sortKey: String) {
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

function deleteJourney(id: String) {
  journeys.value.splice(
    journeys.value.findIndex((journey) => journey.id === id),
    1,
  );
  currentJourneys.value = journeys.value;
}

function editJourney(journey: Journey, id: String) {
  const index = journeys.value.findIndex((j) => j.id === id);
  journeys.value[index].destination = journey.destination;
  journeys.value[index].from = journey.from;
  journeys.value[index].to = journey.to;
  journeys.value[index].name = journey.name;
}
</script>

<template>
  <div class="font-nunito px-2 md:px-8 lg:px-20 text-text dark:text-white">
    <div
      id="header"
      class="border-b-2 border-border mt-5 pb-3 md:pb-5 flex justify-between items-center"
    >
      <div class="flex flex-row items-center">
        <SvgDashboardIcon class="mt-0.5 md:w-9 md:h-9 mr-1" />
        <h1 class="text-3xl md:text-5xl font-medium mt-1">
          <T keyName="common.dashboard" />
        </h1>
      </div>
      <div id="right-header" class="flex flex-row items-center">
        <div
          id="search-and-filter"
          class="hidden lg:flex flex-row border-r-2 mr-4 border-border-grey"
        >
          <div
            id="search"
            class="relative mr-2.5"
            v-tooltip.top="{
              value: t('dashboard.tooltip.search'),
              pt: { root: 'font-nunito' },
            }"
          >
            <input
              type="text"
              ref="searchInput"
              @input="searchJourneys"
              v-model="searchValue"
              class="rounded-3xl bg-input dark:bg-input-dark placeholder-input-placeholder dark:placeholder-text-light-dark border px-3 py-1.5 border-border-grey dark:border-input-dark focus:outline-none focus:ring-1 focus:ring-cta-border"
              :placeholder="t('dashboard.search')"
            />
            <button @click="searchInput.focus()">
              <SvgSearchIcon class="absolute top-1 right-1 w-7 h-7" />
            </button>
          </div>
          <div id="filter" class="mr-4">
            <SvgFilterIcon
              @click="toggle"
              aria-haspopup="true"
              aria-controls="overlay_tmenu"
              class="w-9 h-9 hover:cursor-pointer"
            />
          </div>
        </div>
        <NuxtLink
          to="/journey/new"
          class="mr-2.5 hidden lg:flex flex-row items-center"
        >
          <button
            class="bg-cta-bg dark:bg-cta-bg-fill border-2 border-cta-border dark:border-cta-bg-fill text-text py-1 px-4 rounded-xl font-semibold flex flex-row"
          >
            <SvgCreateNewJourneyIcon class="w-5 h-5 mr-1 fill-text" />
            <T keyName="dashboard.new" />
          </button>
        </NuxtLink>
        <NuxtLink to="/settings">
          <SvgSettingsIcon class="w-9 h-9 -mt-1" />
        </NuxtLink>
      </div>
    </div>
    <div
      id="header-mobile-second-row"
      class="flex lg:hidden mt-3 justify-between"
    >
      <div id="search-and-filter" class="flex flex-row">
        <div id="filter" class="mr-2">
          <SvgFilterIcon
            @click="toggle"
            aria-haspopup="true"
            aria-controls="overlay_tmenu"
            class="w-9 h-9 hover:cursor-pointer"
          />
        </div>
        <div id="search" class="relative">
          <input
            type="text"
            ref="searchInputMobile"
            @input="searchJourneys"
            v-model="searchValue"
            class="rounded-3xl bg-input dark:bg-input-dark placeholder-input-placeholder dark:placeholder-text-light-dark border px-3 py-1.5 border-border-grey dark:border-input-dark focus:outline-none focus:ring-1 focus:ring-cta-border w-40 md:w-52"
            :placeholder="t('dashboard.search')"
          />
          <button @click="searchInputMobile.focus()">
            <SvgSearchIcon class="absolute top-1 right-1 w-7 h-7" />
          </button>
        </div>
      </div>
      <NuxtLink
        to="/journey/new"
        class="flex flex-row items-center justify-center"
      >
        <button
          class="bg-cta-bg border-2 border-cta-border dark:bg-cta-bg-fill dark:border-cta-bg-fill text-text py-1 px-2 md:px-4 rounded-xl font-semibold flex flex-row justify-center items-center"
        >
          <SvgCreateNewJourneyIcon class="w-5 h-5 mr-1 fill-text" />
          <T keyName="dashboard.new" />
        </button>
      </NuxtLink>
    </div>
    <div class="flex justify-center">
      <div
        id="journeys"
        class="grid gap-y-5 md:gap-y-4 lg:gap-y-6 gap-x-5 md:gap-x-4 lg:gap-x-6 mt-5"
        :class="
          currentJourneys.length === 0
            ? 'grid-cols-1'
            : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4'
        "
      >
        <DashboardItem
          v-for="journey in currentJourneys"
          :id="new String(journey.id).valueOf()"
          :name="journey.name"
          :destination="journey.destination"
          :from="new Date(journey.from)"
          :to="new Date(journey.to)"
          :role="new Number(journey.role).valueOf()"
          @journey-deleted="deleteJourney"
          @journey-edited="editJourney"
        />
        <NuxtLink to="/journey/new">
          <SvgCreateNewJourneyCard class="hidden lg:block dark:hidden" />
          <SvgCreateNewJourneyCardDark class="hidden dark:lg:block" />
          <div
            class="lg:hidden flex flex-grow justify-center items-center min-w-36 h-32 bg-cta-bg-light dark:bg-cta-bg-dark rounded-md border border-cta-border"
          >
            <SvgCreateNewJourneyIcon
              class="w-14 h-14 fill-text dark:fill-white"
            />
          </div>
        </NuxtLink>
      </div>
    </div>
    <TieredMenu
      ref="menu"
      id="overlay_tmenu"
      :model="items"
      popup
      class="rounded-xl bg-input dark:bg-input-dark"
      :pt="{
        menuitem: {
          class: 'bg-input dark:bg-input-dark hover:bg-cta-bg rounded-md',
        },
        content: { class: 'hover:bg-cta-bg rounded-md' },
        submenu: { class: 'bg-input dark:bg-input-dark' },
      }"
    >
      <template #start>
        <h1
          class="text-sm ml-2 text-input-placeholder dark:text-text-light-dark"
        >
          <T keyName="dashboard.sort.header" />
        </h1>
        <Divider type="solid" class="text-[#CCCCCC] border-b mt-1 mb-1" />
      </template>
      <template #item="{ item, props, hasSubmenu }">
        <a
          v-ripple
          class="flex align-items-center bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white text-sm"
          v-bind="props.action"
        >
          <span :class="item.icon"></span>
          <span class="ml-2">{{ item.label }}</span>
          <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto"></i>
        </a>
      </template>
    </TieredMenu>
    <ConfirmDialog
      :draggable="false"
      group="delete"
      :pt="{
        header: {
          class:
            'bg-input dark:bg-input-dark text-text dark:text-white font-nunito',
        },
        content: {
          class:
            'bg-input dark:bg-input-dark text-text dark:text-white font-nunito',
        },
        footer: {
          class:
            'bg-input dark:bg-input-dark text-text dark:text-white font-nunito',
        },
      }"
    >
    </ConfirmDialog>
  </div>
</template>
