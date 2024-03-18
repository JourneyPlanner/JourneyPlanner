<script setup lang="ts">
import axios from 'axios';

const title = "Dashboard";
useHead({
  title: `${title} | JourneyPlanner`,
})

/*
TODO
definePageMeta({
  middleware: ["sanctum:auth"],
});
*/



const journeys = ref();
let search = ref('');
let searchedJourneys = ref();

async function fetchJourneys() {
  const response = await axios.get('testdata.json');
  journeys.value = response.data;
  searchedJourneys.value = response.data;
}

async function searchJourneys() {
  const data = journeys.value;
  const results = data.filter(obj => {
    return Object.values(obj).some(value =>
      String(value).toLowerCase().includes(search.value.toLowerCase())
    );
  });
  searchedJourneys.value = results;
}

fetchJourneys();

</script>

<template>
  <div>
    <div class="font-nunito bg-surface flex justify-between">
      <h1 class="mt-10 ml-10 h-20 font-bold text-4xl">
        <T keyName="common.dashboard" />
      </h1>
      <NuxtLink to="/journey/new" class="mt-10">
        <button class="bg-cta-bg border-2 border-cta-border text-text min-w-44 py-2 rounded-lg font-bold mr-10">
          <T keyName="form.header.journey.create" />
        </button>
      </NuxtLink>
    </div>
    <div>
      <input type="text" class="w-1/3 h-10 mt-10 ml-10 rounded-lg border-2 border-border"
        placeholder="Search for a journey" @input="searchJourneys" v-model="search">
    </div>
    <div class="flex flex-wrap">
      <div v-for="journey in searchedJourneys" :key="journey.id">
        <DashboardItem :name="journey.name" :destination="journey.destination" :from="journey.from" :to="journey.to" />
      </div>
    </div>
  </div>
</template>
