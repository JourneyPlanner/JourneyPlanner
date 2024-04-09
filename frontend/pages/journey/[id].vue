<script setup lang="ts">
import { differenceInDays, format } from "date-fns";
import QRCode from "qrcode";
import { useTranslate, T } from "@tolgee/vue";
import JSConfetti from "js-confetti";
import Toast from "primevue/toast";

const route = useRoute();
const journeyId = route.params.id;
const qrcode = ref("");
const duringJourney = ref(false);
const journeyEnded = ref(false);
const day = ref(0);
const tensDays = ref(0);
const hundredsDays = ref(0);
const jsConfetti = new JSConfetti();
const visibleSidebar = ref(false);
const toast = useToast();
const { t } = useTranslate();
const op = ref();
const toggle = (event: Event) => {
  op.value.toggle(event);
};
const editEnabled = ref(false);

definePageMeta({
  middleware: ["sanctum:auth"],
});

interface Journey {
  name: string;
  invite: string;
  destination: string;
  from: string;
  to: string;
}

interface User {
  id: string;
  firstName: string;
  lastName: string;
  role: number;
}

const client = useSanctumClient();
const { data, pending, error, refresh } = await useAsyncData("journey", () =>
  client(`/api/journey/${journeyId}`)
);

if (error.value) {
  throw showError({
    statusCode: 404,
    statusMessage: "Page Not Found",
    fatal: true,
  });
}

const { data: users, pending: usersPending, error: usersError, refresh: usersRefresh } = await useAsyncData("users", () =>
  client(`/api/journey/${journeyId}/user`)
);

const { data: currUser, pending: currUserPending, error: currUserError, refresh: currUserRefresh } = await useAsyncData("userRole", () =>
  client(`/api/journey/${journeyId}/user/me`)
);

const journeyData = data as Ref<Journey>;

const title = journeyData.value.name;
journeyData.value.invite =
  window.location.origin + "/invite/" + journeyData.value.invite;
useHead({
  title: `${title} | JourneyPlanner`,
});

const colorMode = useColorMode();
let darkColor = "#333333";
let lightColor = "#fcfcfc";

if (colorMode.preference === "dark") {
  darkColor = "#ffffff";
  lightColor = "#353f44";
}

var opts = {
  margin: 0,
  color: {
    dark: darkColor,
    light: lightColor,
  },
};
QRCode.toDataURL(journeyData.value.invite, opts, function (error, url) {
  qrcode.value = url;
});

const fromDate = new Date(journeyData.value.from);
const toDate = new Date(journeyData.value.to);
const currentDate = new Date();
const days = ref(differenceInDays(fromDate, currentDate));
const daystoEnd = ref(differenceInDays(toDate, currentDate));

if (days.value > 0) {
  day.value = Math.floor(days.value % 10);
  days.value = days.value / 10;
  tensDays.value = Math.floor(days.value % 10);
  days.value = days.value / 10;
  hundredsDays.value = Math.floor(days.value % 10);
} else if (days.value <= 0 && daystoEnd.value > 0) {
  duringJourney.value = true;
  const journeyEnds = ref(differenceInDays(toDate, currentDate));
  day.value = Math.floor(journeyEnds.value % 10);
  journeyEnds.value = journeyEnds.value / 10;
  tensDays.value = Math.floor(journeyEnds.value % 10);
  journeyEnds.value = journeyEnds.value / 10;
  hundredsDays.value = Math.floor(journeyEnds.value % 10);
} else {
  journeyEnded.value = true;
}

const isFlipped = ref(false);
const flip = () => {
  isFlipped.value = !isFlipped.value;
};

function copyToClipboard() {
  navigator.clipboard.writeText(journeyData.value.invite);
  toast.add({
    severity: "info",
    summary: t.value("common.toast.info.heading"),
    detail: t.value("common.invite.toast.info"),
    life: 2000,
  });
}

async function changeRole(userid: String, selectedRole: Number) {
  await client(`/api/journey/${journeyId}/user/${userid}`, {
    method: "PATCH",
    body: {
      role: selectedRole,
      random: 1
    },
    async onResponseError() {
      toast.add({
        severity: "error",
        summary: t.value("common.toast.error.heading"),
        detail: t.value("common.error.unknown"),
        life: 6000,
      });
    },
  });

}
</script>

<template>
  <div class="flex flex-col font-nunito text-text dark:text-white">
    <Toast class="w-3/4 sm:w-auto" />
    <Sidebar v-model:visible="visibleSidebar" position="right" :pt="{
      closeButton: { class: 'w-9 h-9 dark:fill-white' },
      closeIcon: { class: 'w-7 h-7 text-text-disabled dark:text-white' },
      header: { class: 'p-2' },
      content: { class: 'pl-3 pr-2 py-2' },
      root: { class: ' dark:bg-background-dark font-nunito' },
    }">
      <div class="text-xl text-text font-medium dark:text-white">
        <T keyName="sidebar.invite.link" />
      </div>
      <div class="flex items-center border-b-2 border-border-grey dark:border-text-disabled pb-4">
        <input
          class="w-4/5 rounded-md px-1 pb-1 pt-1 text-base text-text dark:text-white bg-input-disabled focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
          disabled :value="journeyData.invite" />
        <div class="w-1/5 flex justify-end">
          <button
            class="w-9 h-9 border-2 ml-3 border-cta-border rounded-full hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark flex items-center justify-center"
            @click="copyToClipboard">
            <SvgCopy class="w-4" />
          </button>
        </div>
      </div>
      <div
        class="flex flex-row items-center justify-center border-b border-border-grey dark:border-input-placeholder pt-1 pb-1gi">
        <h1 class="text-xl text-footer dark:text-border-grey w-4/5">
          <T keyName="journey.sidebar.list.header" />
        </h1>
        <div class="w-1/5 flex justify-end items-center mb-1 mt-1">
          <button @click="editEnabled = !editEnabled" v-if="currUser.role === 1"
            class="w-9 h-9 border-2 ml-3 border-cta-border rounded-full hover:bg-cta-bg dark:bg-input-dark dark:hover:bg-cta-bg-dark flex items-center justify-center">
            <SvgEdit class="w-4" v-if="!editEnabled" />
            <SvgEditOff class="w-4" v-if="editEnabled" />
          </button>
        </div>
      </div>
      <div id="list" class="mt-3 flex flex-col gap-3">
        <MemberItem v-for="user in users.sort(function (a: User, b: User) { return b.role - a.role })" :key="user.id"
          :id="user.id" :firstName="user.firstName" :lastName="user.lastName" :role="user.role" :edit="editEnabled"
          :currentID="currUser.user_id" @changeRole="changeRole" />
      </div>
    </Sidebar>
    <div class="absolute right-0 lg:w-1/3 w-full h-10 flex justify-end items-center font-semibold mt-5">
      <NuxtLink to="/dashboard" class="flex items-center">
        <SvgDashboardIcon class="w-6 h-6" />
        <p class="text-2xl hover:underline">Dashboard</p>
      </NuxtLink>
      <SvgMenu class="md:w-10 md:h-10 md:mx-10 mx-5 w-8 h-8 hover:cursor-pointer" @click="visibleSidebar = true" />
    </div>
    <div class="flex flex-wrap h-fit mt-[12vh]">
      <div class="flex w-full items-center justify-center md:hidden">
        <div class="group sm:w-5/6 w-[90%] [perspective:1000px]" @click="flip">
          <div :class="isFlipped ? '[transform:rotateX(180deg)]' : ''"
            class="relative h-full w-full rounded-2xl transition-all duration-500 [transform-style:preserve-3d]">
            <div class="lg:w-1/3 md:w-2/5 bg-none">
              <div
                class="h-10 bg-border border-x-2 border-t-2 border-border-darker rounded-t-2xl flex items-center relative dark:border-border-blue-dark dark:bg-ticket-top-dark-bg">
                <div class="absolute ml-5 rounded-full w-7 h-7 bg-border-gray inline-block self-center"></div>
                <p class="ml-16 text-white font-bold text-xl">JourneyPlanner</p>
                <div class="w-full flex justify-end h-full items-center">
                  <SvgAirplaneIcon class="w-7 mr-5" />
                </div>
              </div>
              <div class="flex h-5/6">
                <div
                  class="h-fit w-full rounded-b-2xl bg-background border-border-gray border-x-2 border-b-2 text-sm dark:bg-border-dark dark:border-form-input-dark -mr-1">
                  <div class="w-full grid grid-cols-4 mt-1 mb-2">
                    <div class="w-full col-span-3 pl-5 flex flex-col h-full justify-center font-semibold">
                      <T keyName="form.input.journey.name" />
                      <input
                        class="w-full rounded-md px-2.5 pb-1 mb-2 pt-1 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
                        disabled :value="journeyData.name" />
                      <T keyName="form.input.journey.destination" />
                      <input
                        class="w-full rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
                        disabled :value="journeyData.destination" />
                      <T keyName="form.input.journey.date" />
                      <input
                        class="md:w-4/5 w-5/6 rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
                        disabled :value="format(fromDate, 'dd/MM/yyyy') +
                          ' - ' +
                          format(toDate, 'dd/MM/yyyy')
                          " />
                    </div>
                    <div class="w-full -mt-1 relative">
                      <SvgStripes class="absolute w-[7.4rem] right-0 z-0" />
                      <div
                        class="absolute ml-10 rounded-full border-input-placeholder text-input-placeholder w-16 h-16 self-center border-dashed border-2 right-2 bottom-2 flex text-center justify-center items-center text-xs pl-1.5 pr-1.5 dark:border-white dark:text-white">
                        <T keyName="journey.turn" />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="h-[90%] w-0 rounded-b-r-3xl border-border-gray border-r-2 border-dashed"></div>
              </div>
            </div>
            <div
              class="text-text absolute inset-0 h-full w-full rounded-xl bg-white text-center [transform:rotateX(180deg)] [backface-visibility:hidden] dark:bg-background-dark">
              <div
                class="h-10 bg-border border-x-2 border-t-2 border-border-darker rounded-t-2xl flex items-center relative dark:border-border-blue-dark dark:bg-ticket-top-dark-bg">
                <div class="absolute ml-5 rounded-full w-7 h-7 bg-border-gray inline-block self-center"></div>
                <p class="ml-16 text-white font-bold text-xl">JourneyPlanner</p>
                <div class="w-full flex justify-end h-full items-center">
                  <SvgAirplaneIcon class="w-7 mr-5" />
                </div>
              </div>
              <div class="flex h-5/6">
                <div
                  class="h-full w-full rounded-b-2xl bg-background border-border-gray border-x-2 border-b-2 flex justify-center dark:bg-border-dark dark:border-form-input-dark">
                  <div class="h-full w-full flex flex-col items-end relative">
                    <img
                      class="absolute right-[50%] top-[25%] z-20 translate-x-[50%] -translate-y-[25%] w-40 max-sm:mt-1"
                      :src="qrcode" alt="QR Code" />
                    <div
                      class="absolute items-center justify-center flex ml-10 rounded-full border-input-placeholder text-input-placeholder w-16 h-16 self-center border-dashed border-2 right-2 bottom-4 text-xs pl-1.5 pr-1.5 z-40 dark:border-white dark:text-white">
                      <T keyName="journey.turn" />
                    </div>
                    <SvgStripes class="z-0 lg:w-1/2 md:w-2/3" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="xl:ml-[10%] lg:ml-10 ml-[10%] lg:w-1/3 md:w-[50%] md:visible invisible w-0 max-md:h-0">
        <div
          class="h-10 bg-border border-x-2 border-t-2 border-border-darker rounded-t-3xl flex items-center relative dark:bg-ticket-top-dark-bg dark:border-border-blue-dark">
          <div class="ml-5 absolute rounded-full w-7 h-7 bg-border-gray inline-block self-center"></div>
          <p class="ml-14 text-white font-bold text-xl">JourneyPlanner</p>
          <div class="w-full flex justify-end h-full items-center">
            <SvgAirplaneIcon class="w-7 mr-5" />
          </div>
        </div>
        <div class="flex lg:h-[15.5rem] h-[13.5rem]">
          <div
            class="w-full rounded-b-3xl bg-background border-border-gray border-l-2 border-b-2 dark:bg-border-dark dark:border-form-input-dark">
            <div class="w-full grid grid-cols-4 relative">
              <div class="w-full col-span-3 pl-10 flex flex-col h-[120%] justify-center font-semibold">
                <T keyName="form.input.journey.name" />
                <input
                  class="w-full rounded-md px-2.5 pb-1 mb-2 pt-1 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
                  disabled :value="journeyData.name" />
                <T keyName="form.input.journey.destination" />
                <input
                  class="w-full rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
                  disabled :value="journeyData.destination" />
                <T keyName="form.input.journey.date" />
                <input
                  class="lg:w-2/3 md:w-5/6 rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-disabled-dark"
                  disabled :value="format(fromDate, 'dd/MM/yyyy') +
                    ' - ' +
                    format(toDate, 'dd/MM/yyyy')
                    " />
              </div>
              <div class="w-full xl:col-span-1 2xl:col-span-1 lg:col-span-2 md:col-span-2 absolute">
                <SvgStripes class="absolute lg:w-[10.15rem] md:w-[8.8rem] right-0" />
              </div>
            </div>
          </div>
          <div class="h-[90%] w-0 rounded-b-r-3xl border-border-gray border-r-2 border-dashed"></div>
        </div>
      </div>
      <div
        class="lg:w-72 md:w-64 lg:h-72 md:h-64 rounded-3xl bg-background border-solid md:visible invisible w-0 max-md:h-0 dark:bg-border-dark">
        <div
          class="h-10 bg-border border-x-2 border-t-2 border-border-darker rounded-t-3xl dark:bg-ticket-top-dark-bg dark:border-border-blue-dark">
          <div class="w-full flex justify-end items-center h-full">
            <SvgAirplaneIcon class="w-7 mr-5" />
          </div>
        </div>
        <div class="flex lg:h-[15.5rem] h-[13.5rem]">
          <div class="h-[90%] w-0 rounded-b-l-3xl border-border-gray border-l-2 border-dashed"></div>
          <div
            class="h-full w-full rounded-b-3xl border-border-gray border-r-2 border-b-2 flex justify-center dark:border-form-input-dark">
            <div class="h-full w-full flex flex-col items-end relative">
              <img
                class="absolute right-[50%] top-[25%] z-20 translate-x-[50%] -translate-y-[25%] lg:w-[10rem] md:w-[8rem]"
                :src="qrcode" alt="QR Code" />
              <SvgStripes class="absolute lg:w-[10.15rem] md:w-[8.8rem] right-0" />
              <button
                class="absolute items-center justify-center flex right-[50%] top-[80%] translate-x-[50%] lg:-translate-y-[2%] md:-translate-y-[30%] font-bold border-2 border-cta-border h-1/6 w-2/5 rounded-xl hover:bg-cta-bg z-30 bg-background dark:bg-input-dark dark:hover:bg-cta-bg-dark"
                @click="toggle">
                <T keyName="journey.button.invite" />
                <SvgShare class="w-3 ml-2" />
              </button>
              <OverlayPanel ref="op"
                class="bg-input dark:bg-input-dark text-text dark:text-white font-nunito rounded-lg">
                <div class="flex flex-column gap-3 w-25rem">
                  <div>
                    <span class="font-medium text-lg block mb-1">
                      <T keyName="sidebar.invite.link" />
                    </span>
                    <div class="flex">
                      <input
                        class="w-full shadow-sm rounded-l-md pl-2.5 pb-1 pt-1 text-base text-text dark:text-white font-medium border-2 border-border-gray dark:border-input-disabled-dark-grey focus:outline-none focus:ring-1 bg-input-disabled dark:bg-color-gray-200"
                        disabled :value="journeyData.invite" />
                      <button
                        class="w-9 h-9 shadow-sm rounded-r-md border-y-2 border-r-2 bg-input-disabled dark:bg-input-dark hover:bg-cta-bg dark:hover:bg-cta-bg-dark flex items-center justify-center border-2 border-cta-border"
                        @click="copyToClipboard">
                        <SvgCopy class="w-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </OverlayPanel>
            </div>
          </div>
        </div>
      </div>
      <div class="lg:basis-0 md:basis-full basis-0"></div>
      <div class="lg:w-72 w-full flex md:justify-start justify-center xl:ml-32 lg:ml-10">
        <div
          class="lg:ml-0 md:ml-[10%] lg:w-full md:w-[calc(50%+16rem)] sm:w-5/6 w-[90%] border-2 lg:rounded-3xl rounded-2xl bg-countdown-bg border-solid border-border max-lg:mt-5 dark:bg-surface-dark">
          <div
            class="flex flex-wrap lg:flex-col h-full lg:justify-center xs:justify-start justify-center items-center bg-gradient-to-br from-indigo-500 to-indigo-800">
            <!-- flip clock container -->
            <div v-if="hundredsDays <= 0"
              class="relative font-bold lg:text-6xl text-4xl text-text grid grid-cols-2 gap-x-1 my-2 mx-3 dark:text-white">
              <div class="relative bg-black p-1 py-2 rounded-xl">
                <!-- background grid of black squares -->
                <div class="absolute inset-0 grid grid-rows-2">
                  <div
                    class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                  <div
                    class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                </div>

                <!-- time numbers -->
                <span class="absolute top-50">{{ tensDays }}</span>

                <!-- line across the middle -->
                <div class="absolute inset-0 flex items-center">
                  <div class="h-px w-full bg-border dark:bg-countdown-stroke-dark"></div>
                </div>
              </div>
              <div class="relative bg-black p-1 py-2 rounded-xl">
                <!-- background grid of black squares -->
                <div class="absolute inset-0 grid grid-rows-2">
                  <div
                    class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                  <div
                    class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                </div>

                <!-- time numbers -->
                <span class="relative">{{ day }}</span>

                <!-- line across the middle -->
                <div class="absolute inset-0 flex items-center">
                  <div class="h-px w-full bg-border dark:bg-countdown-stroke-dark"></div>
                </div>
              </div>
            </div>

            <div v-else
              class="relative font-bold lg:text-6xl text-4xl text-text grid grid-cols-3 lg:gap-x-2 gap-x-1 my-2 mx-3 dark:text-white">
              <!-- left side -->
              <div class="relative bg-black lg:p-2 lg:py-3 p-1 py-2 rounded-xl">
                <!-- background grid of black squares -->
                <div class="absolute inset-0 grid grid-rows-2">
                  <div
                    class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                  <div
                    class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                </div>

                <!-- time numbers -->
                <span class="absolute top-50">{{ hundredsDays }}</span>

                <!-- line across the middle -->
                <div class="absolute inset-0 flex items-center">
                  <div class="h-px w-full bg-border dark:bg-countdown-stroke-dark"></div>
                </div>
              </div>

              <div class="relative bg-black lg:p-2 lg:py-3 p-1 py-2 rounded-xl">
                <!-- background grid of black squares -->
                <div class="absolute inset-0 grid grid-rows-2">
                  <div
                    class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                  <div
                    class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                </div>

                <!-- time numbers -->
                <span class="absolute top-50">{{ tensDays }}</span>

                <!-- line across the middle -->
                <div class="absolute inset-0 flex items-center">
                  <div class="h-px w-full bg-border dark:bg-countdown-stroke-dark"></div>
                </div>
              </div>
              <div class="relative bg-black lg:p-2 lg:py-3 p-1 py-2 rounded-xl">
                <!-- background grid of black squares -->
                <div class="absolute inset-0 grid grid-rows-2">
                  <div
                    class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                  <div
                    class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md dark:from-gradient-start-dark dark:to-gradient-end-dark">
                  </div>
                </div>

                <!-- time numbers -->
                <span class="relative">{{ day }}</span>

                <!-- line across the middle -->
                <div class="absolute inset-0 flex items-center">
                  <div class="h-px w-full bg-border dark:bg-countdown-stroke-dark"></div>
                </div>
              </div>
            </div>
            <div class="text-center justify-start items-center lg:flex-col flex">
              <p class="font-bold text-base">
                <T keyName="journey.countdown.days" />
              </p>
              <p v-if="duringJourney" class="font-bold w-full pl-1 lg:text-lg text-base">
                <T keyName="journey.countdown.ends" />
              </p>
              <p v-else-if="journeyEnded" class="font-bold w-full lg:text-lg pl-1 text-base">
                <T keyName="journey.countdown.finished" />
              </p>
              <p v-else class="font-bold w-full lg:text-lg pl-1 text-base">
                <T keyName="journey.countdown.until" />
              </p>
              <button v-if="duringJourney"
                class="font-bold border-2 border-cta-border lg:h-3/6 xl:w-[110%] lg:w-[80%] bg-background w-0 h-0 max-lg:invisible max-lg:w-0 rounded-xl hover:bg-cta-bg py-2 dark:bg-input-dark mt-6 dark:hover:bg-cta-bg-dark">
                <T keyName="journey.button.countdown.calendar" />
              </button>
              <button v-else-if="journeyEnded"
                class="font-bold border-2 border-cta-border lg:h-3/6 xl:w-[120%] lg:w-[100%] bg-background w-0 h-0 max-lg:invisible max-lg:w-0 rounded-xl hover:bg-cta-bg py-2 dark:bg-input-dark mt-6 dark:hover:bg-cta-bg-dark"
                @click="jsConfetti.addConfetti()">
                <T keyName="journey.button.countdown.celebrate" />
              </button>
              <button v-else
                class="font-bold border-2 border-cta-border lg:h-3/6 xl:w-[120%] lg:w-[100%] bg-background w-0 h-0 max-lg:invisible max-lg:w-0 rounded-xl hover:bg-cta-bg py-2 dark:bg-input-dark mt-6 dark:hover:bg-cta-bg-dark">
                <T keyName="journey.button.countdown.planning" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
