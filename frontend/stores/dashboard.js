import { ref } from "vue";
import { defineStore } from "pinia";

export const useDashboardStore = defineStore("dashboard", () => {
  const journeys = ref([]);

  function setJourneys(journeys) {
    this.journeys = journeys;
  }

  return { journeys, setJourneys };
});
