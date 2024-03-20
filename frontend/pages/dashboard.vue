<script setup lang="ts">
import { useTranslate } from '@tolgee/vue';

const title = "Dashboard";
useHead({
  title: `${title} | JourneyPlanner`,
})


definePageMeta({
  middleware: ["sanctum:auth"],
});

interface Journey {
  id: number;
  name: string;
  destination: string;
  from: string;
  to: string;
  role: Number;
}

const { t } = useTranslate();

const journeys = ref();
const searchInput = ref();
let search = ref('');
let currentJourneys = ref();

const menu = ref();
const items = ref([
  {
    label: t.value('dashboard.sort.name'),
    icon: 'pi pi-book',
    items: [
      {
        label: t.value('dashboard.sort.ascending'),
        icon: 'pi pi-sort-alpha-up',
        command: () => {
          sortJourneys('name-asc');
        }
      },
      {
        label: t.value('dashboard.sort.descending'),
        icon: 'pi pi-sort-alpha-down',
        command: () => {
          sortJourneys('name-desc');
        }
      },
    ]
  },
  {
    label: t.value('dashboard.sort.startdate'),
    icon: 'pi pi-calendar',
    items: [
      {
        label: t.value('dashboard.sort.oldestToNewest'),
        icon: 'pi pi-sort-amount-up',
        command: () => {
          sortJourneys('startdate-asc');
        }
      },
      {
        label: t.value('dashboard.sort.newestToOldest'),
        icon: 'pi pi-sort-amount-down',
        command: () => {
          sortJourneys('startdate-desc');
        }
      },
    ]
  },
  {
    label: t.value('dashboard.sort.destination'),
    icon: 'pi pi-map-marker',
    items: [
      {
        label: t.value('dashboard.sort.ascending'),
        icon: 'pi pi-sort-alpha-up',
        command: () => {
          sortJourneys('destination-asc');
        }
      },
      {
        label: t.value('dashboard.sort.descending'),
        icon: 'pi pi-sort-alpha-down',
        command: () => {
          sortJourneys('destination-desc');
        }
      },
    ]
  },

]);

const toggle = (event) => {
  menu.value.toggle(event);
};

async function fetchJourneys() {
  const client = useSanctumClient();
  await client("/api/journey", {
    method: "GET",
    async onResponse({ response }) {
      console.log(response._data);
      journeys.value = response._data;
      currentJourneys.value = response._data;
    }
  });

}

async function searchJourneys() {
  const data = journeys.value;
  //TODO typescript
  const results = data.filter(obj => {
    return Object.values(obj).some(value =>
      String(value).toLowerCase().includes(search.value.toLowerCase())
    );
  });

  currentJourneys.value = results;
}

function sortJourneys(sortKey: String) {
  currentJourneys.value.sort((a: Journey, b: Journey) => {
    if (sortKey === 'name-asc') return b.name.localeCompare(a.name);
    if (sortKey === 'name-desc') return a.name.localeCompare(b.name);
    if (sortKey === 'startdate-asc') return a.from.localeCompare(b.from);
    if (sortKey === 'startdate-desc') return b.from.localeCompare(a.from);
    if (sortKey === 'destination-asc') return b.destination.localeCompare(a.destination);
    if (sortKey === 'destination-desc') return a.destination.localeCompare(b.destination);
  });
}

fetchJourneys();


</script>

<template>
  <div class="font-nunito px-20 text-text">
    <div id="header" class="border-b-2 border-border mt-10 pb-5 flex justify-between items-center">
      <div class="flex flex-row items-center">
        <SvgDashboardIcon class="w-9 h-9" />
        <h1 class="text-5xl font-medium">
          <T keyName="common.dashboard" />
        </h1>
      </div>
      <div id="right-header" class="flex flex-row items-center">
        <div id="search-and-filter" class="flex flex-row border-r-2  border-border-grey">
          <div id="search" class="relative mr-2.5">
            <input type="text" ref="searchInput" @input="searchJourneys" v-model="search"
              class="rounded-3xl bg-input border px-3 py-1.5 border-border-grey focus:outline-none focus:ring-1 focus:ring-cta-border "
              :placeholder="t('dashboard.search')">
            <button @click="searchInput.focus()">
              <SvgSearchIcon class="absolute top-1 right-1 w-7 h-7" />
            </button>
          </div>
          <div id="filter" class="mr-4">
            <SvgFilterIcon @click="toggle" aria-haspopup="true" aria-controls="overlay_tmenu"
              class="w-9 h-9 hover:cursor-pointer" />
            <div class="">
              <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup class="rounded-xl">
                <template #start>
                  <h1 class="text-sm ml-2 text-input-placeholder">
                    <T keyName="dashboard.sort.header" />
                  </h1>
                  <Divider type="solid" class="text-[#CCCCCC] border-b mt-1 mb-1" />
                </template>
                <template #item="{ item, props, hasSubmenu }">
                  <a v-ripple class="flex align-items-center hover:bg-cta-bg-light rounded-md text-text text-sm"
                    v-bind="props.action">
                    <span :class="item.icon"></span>
                    <span class="ml-2">{{ item.label }}</span>
                    <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto"></i>
                  </a>
                </template>
              </TieredMenu>
            </div>
          </div>
        </div>
        <div id="create-and-settings" class="flex flex-row ml-4 items-center">
          <NuxtLink to="/journey/new" class="mr-2.5 flex flex-row items-center">
            <button
              class="bg-cta-bg border-2 border-cta-border text-text py-1 px-4 rounded-xl font-semibold flex flex-row">
              <SvgCreateNewJourneyIcon class="w-5 h-5 mr-2" />
              <T keyName="dashboard.new" />
            </button>
          </NuxtLink>
          <NuxtLink to="/settings">
            <SvgSettingsIcon class="w-9 h-9" />
          </NuxtLink>
        </div>
      </div>
    </div>
    <div id="journeys" class="grid grid-cols-4  gap-y-10 mt-5">
      <div v-for="journey in currentJourneys">
        <DashboardItem class="" :name="journey.name" :destination="journey.destination" :from="journey.from"
          :to="journey.to" :role="journey.pivot.role" />
      </div>

      <NuxtLink to="/journey/new">
        <SvgCreateNewJourneyCard class="" />
      </NuxtLink>

    </div>
  </div>
</template>
