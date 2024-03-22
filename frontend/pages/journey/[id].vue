<script setup lang="ts">
import {
  compareAsc,
  differenceInDays,
  format,
  intervalToDuration,
} from "date-fns";
import QRCode from "qrcode";
import JSConfetti from "js-confetti";
const route = useRoute();
const journeyId = route.params.id;
const qrcode = ref("");
const duringJourney = ref(false);
const journeyEnded = ref(false);
const day = ref(0);
const tensDays = ref(0);
const hundredsDays = ref(0);
const jsConfetti = new JSConfetti();

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

const journeyData = ref({} as Journey);

console.log(route.params.id);

const client = useSanctumClient();
await client(`/api/journey/${journeyId}`, {
  method: "Get",
  async onResponse({ response }) {
    journeyData.value = response._data;
  },
});

const title = journeyData.value.name;
useHead({
  title: `${title} | JourneyPlanner`,
});

QRCode.toDataURL(journeyData.value.invite, function (err, url) {
  qrcode.value = url;
});

console.log(journeyData.value);
const fromDate = new Date(journeyData.value.from);
const toDate = new Date(journeyData.value.to);
const currentDate = new Date();
const days = ref(differenceInDays(fromDate, currentDate));
const daystoEnd = ref(differenceInDays(toDate, currentDate));
console.log(daystoEnd.value);
console.log(days);
if (days.value > 0) {
  day.value = Math.floor(days.value % 10);
  days.value = days.value / 10;
  tensDays.value = Math.floor(days.value % 10);
  days.value = days.value / 10;
  hundredsDays.value = Math.floor(days.value % 10);
  console.log(day.value);
  console.log(tensDays.value);
  console.log(hundredsDays.value);
} else if (days.value <= 0 && daystoEnd.value > 0) {
  duringJourney.value = true;
  const journeyEnds = ref(differenceInDays(toDate, currentDate));
  console.log(journeyEnds);
  day.value = Math.floor(journeyEnds.value % 10);
  console.log(day.value);
  journeyEnds.value = journeyEnds.value / 10;
  tensDays.value = Math.floor(journeyEnds.value % 10);
  journeyEnds.value = journeyEnds.value / 10;
  hundredsDays.value = Math.floor(journeyEnds.value % 10);
  console.log(journeyEnds);
} else {
  journeyEnded.value = true;
}

const isFlipped = ref(false);
const flip = () => {
  isFlipped.value = !isFlipped.value;
};
</script>

<template>
  <div class="w-screen h-screen font-nunito text-text">
    <div
      class="absolute right-0 lg:w-1/3 w-full h-10 flex justify-end items-center font-semibold mt-5"
    >
      <NuxtLink to="/dashboard" class="flex items-center">
        <SvgHome class="w-10 h-10" />
        <p class="text-xl">Dashboard</p>
      </NuxtLink>
      <SvgMenu class="w-10 h-10 mx-10" />
    </div>
    <div class="flex flex-wrap h-fit mt-[12vh]">
      <div class="flex w-full items-center justify-center md:hidden">
        <div class="group w-5/6 [perspective:1000px]" @click="flip">
          <div
            :class="isFlipped ? '[transform:rotateX(180deg)]' : ''"
            class="relative h-full w-full rounded-xl shadow-xl transition-all duration-500 [transform-style:preserve-3d]"
          >
            <div class="lg:w-1/3 md:w-2/5">
              <div
                class="h-1/6 bg-border border-x-2 border-t-2 border-border-darker rounded-t-2xl flex items-center relative"
              >
                <div
                  class="absolute ml-10 rounded-full w-7 h-7 bg-border-gray inline-block self-center"
                ></div>
                <p class="ml-20 text-white font-bold text-xl">JourneyPlanner</p>
                <div class="w-full flex justify-end">
                  <SvgAirplaneIcon class="w-8 mr-5 pb-1" />
                </div>
              </div>
              <div class="flex h-5/6">
                <div
                  class="h-fit w-full rounded-b-2xl bg-background border-border-gray border-l-2 border-b-2 text-sm"
                >
                  <div class="w-full grid grid-cols-5">
                    <div
                      class="w-full col-span-3 pl-5 flex flex-col h-full justify-center font-medium"
                    >
                      <T keyName="form.input.journey.name" />
                      <input
                        class="w-full rounded-md px-2.5 pb-1 mb-2 pt-1 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-dark"
                        disabled
                        :value="journeyData.name"
                      />
                      <T keyName="form.input.journey.destination" />
                      <input
                        class="w-full rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-dark"
                        disabled
                        :value="journeyData.destination"
                      />
                      <T keyName="form.input.journey.date" />
                      <input
                        class="w-4/5 rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-dark"
                        disabled
                        :value="
                          format(fromDate, 'dd/MM/yyyy') +
                          ' - ' +
                          format(toDate, 'dd/MM/yyyy')
                        "
                      />
                    </div>
                    <div class="w-full h-[1/3] col-span-2 relative">
                      <div
                        class="absolute ml-10 rounded-full border-input-placeholder text-input-placeholder w-16 h-16 self-center border-dashed border-2 right-2 bottom-2 flex text-center justify-center text-xs pl-1.5 pr-1.5 pt-1"
                      >
                        <T keyName="journey.turn" />
                      </div>
                      <SvgStripes class="h-fit" />
                    </div>
                  </div>
                </div>
                <div
                  class="h-[90%] w-0 rounded-b-r-3xl bg-background border-border-gray border-r-2 border-dashed"
                ></div>
              </div>
            </div>
            <div
              class="text-text absolute inset-0 h-full w-full rounded-xl bg-white text-center [transform:rotateX(180deg)] [backface-visibility:hidden]"
            >
              <div
                class="bg-border border-x-2 border-t-2 border-border-darker rounded-t-2xl flex items-center relative"
              >
                <div
                  class="absolute ml-10 rounded-full w-7 h-7 bg-border-gray inline-block self-center"
                ></div>
                <p class="ml-20 text-white font-bold text-xl">JourneyPlanner</p>
                <div class="w-full flex justify-end">
                  <SvgAirplaneIcon class="w-8 mr-5 pb-1" />
                </div>
              </div>
              <div class="flex h-[90%]">
                <div
                  class="h-full w-full rounded-b-2xl bg-background border-border-gray border-r-2 border-b-2 flex justify-center"
                >
                  <div class="h-full w-full flex flex-col items-end relative">
                    <img
                      class="absolute right-[50%] top-[25%] z-20 translate-x-[50%] -translate-y-[25%] w-2/5"
                      :src="qrcode"
                      alt="QR Code"
                    />
                    <div
                      class="absolute ml-10 rounded-full border-input-placeholder text-input-placeholder w-16 h-16 self-center border-dashed border-2 right-2 bottom-2 flex text-center justify-center text-xs pl-1.5 pr-1.5 pt-1 z-40"
                    >
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
      <div
        class="ml-[10%] lg:w-1/3 md:w-2/5 md:visible invisible w-0 max-md:h-0"
      >
        <div
          class="h-1/6 bg-border border-x-2 border-t-2 border-border-darker rounded-t-2xl flex items-center relative"
        >
          <div
            class="absolute ml-10 rounded-full w-7 h-7 bg-border-gray inline-block self-center"
          ></div>
          <p class="ml-20 text-white font-bold text-xl">JourneyPlanner</p>
          <div class="w-full flex justify-end">
            <SvgAirplaneIcon class="w-8 mr-5 pb-1" />
          </div>
        </div>
        <div class="flex h-5/6">
          <div
            class="w-full rounded-b-2xl bg-background border-border-gray border-l-2 border-b-2"
          >
            <div class="w-full grid grid-cols-4 relative">
              <div
                class="w-full col-span-3 pl-10 flex flex-col h-full justify-center font-medium"
              >
                <T keyName="form.input.journey.name" />
                <input
                  class="w-full rounded-md px-2.5 pb-1 mb-2 pt-1 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-dark"
                  disabled
                  :value="journeyData.name"
                />
                <T keyName="form.input.journey.destination" />
                <input
                  class="w-full rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-dark"
                  disabled
                  :value="journeyData.destination"
                />
                <T keyName="form.input.journey.date" />
                <input
                  class="w-2/3 rounded-md px-2.5 pb-1 pt-1 mb-2 text-md text-text dark:text-white font-bold bg-input-gray focus:outline-none focus:ring-1 dark:bg-input-dark"
                  disabled
                  :value="
                    format(fromDate, 'dd/MM/yyyy') +
                    ' - ' +
                    format(toDate, 'dd/MM/yyyy')
                  "
                />
              </div>
              <div
                class="w-full xl:col-span-1 2xl:col-span-1 lg:col-span-2 md:col-span-2 absolute"
              >
                <SvgStripes class="absolute w-[9.8rem] right-0" />
              </div>
            </div>
          </div>
          <div
            class="h-[90%] w-0 rounded-b-r-3xl bg-background border-border-gray border-r-2 border-dashed"
          ></div>
        </div>
      </div>
      <div
        class="lg:w-1/6 md:w-1/4 h-72 rounded-2xl bg-background border-solid md:visible invisible w-0 max-md:h-0"
      >
        <div
          class="h-1/6 bg-border border-x-2 border-t-2 border-border-darker rounded-t-2xl"
        >
          <div class="w-full flex justify-end">
            <SvgAirplaneIcon class="w-8 mr-5" />
          </div>
        </div>
        <div class="flex h-5/6">
          <div
            class="h-[90%] w-0 rounded-b-l-3xl bg-background border-border-gray border-l-2 border-dashed"
          ></div>
          <div
            class="h-full w-full rounded-b-2xl bg-background border-border-gray border-r-2 border-b-2 flex justify-center"
          >
            <div class="h-full w-full flex flex-col items-end relative">
              <img
                class="absolute right-[50%] top-[25%] z-20 translate-x-[50%] -translate-y-[25%] lg:w-[10rem] md:w-[8rem]"
                :src="qrcode"
                alt="QR Code"
              />
              <SvgStripes class="absolute w-[9.8rem] right-0" />
              <button
                class="absolute right-[50%] top-[80%] translate-x-[50%] -translate-y-[25%] font-bold border-2 border-cta-border h-1/6 w-2/5 rounded-xl hover:bg-cta-bg z-30 bg-background"
              >
                <T keyName="journey.button.invite" />
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="lg:basis-0 md:basis-full basis-0"></div>
      <div
        class="lg:w-1/6 w-4/5 border-4 rounded-2xl bg-countdown-bg border-solid border-border md:ml-20 max-lg:mt-5"
      >
        <div
          class="flex flex-wrap lg:flex-col h-full lg:justify-center md:justify-start justify-center items-center bg-gradient-to-br from-indigo-500 to-indigo-800"
        >
          <!-- flip clock container -->
          <div
            v-if="hundredsDays <= 0"
            class="relative font-bold lg:text-6xl text-3xl text-text grid grid-cols-2 gap-x-2 my-2 mx-3"
          >
            <div class="relative bg-black p-2 py-3 rounded-xl">
              <!-- background grid of black squares -->
              <div class="absolute inset-0 grid grid-rows-2">
                <div
                  class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md"
                ></div>
                <div
                  class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md"
                ></div>
              </div>

              <!-- time numbers -->
              <span class="absolute top-50">{{ tensDays }}</span>

              <!-- line across the middle -->
              <div class="absolute inset-0 flex items-center">
                <div class="h-px w-full bg-black"></div>
              </div>
            </div>
            <div class="relative bg-black p-2 py-3 rounded-xl">
              <!-- background grid of black squares -->
              <div class="absolute inset-0 grid grid-rows-2">
                <div
                  class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md"
                ></div>
                <div
                  class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md"
                ></div>
              </div>

              <!-- time numbers -->
              <span class="relative">{{ day }}</span>

              <!-- line across the middle -->
              <div class="absolute inset-0 flex items-center">
                <div class="h-px w-full bg-black"></div>
              </div>
            </div>
          </div>

          <div
            v-else
            class="relative font-bold lg:text-6xl text-3xl text-text grid grid-cols-3 gap-x-2 my-2 mx-3"
          >
            <!-- left side -->
            <div class="relative bg-black p-2 py-3 rounded-xl">
              <!-- background grid of black squares -->
              <div class="absolute inset-0 grid grid-rows-2">
                <div
                  class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md"
                ></div>
                <div
                  class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md"
                ></div>
              </div>

              <!-- time numbers -->
              <span class="absolute top-50">{{ hundredsDays }}</span>

              <!-- line across the middle -->
              <div class="absolute inset-0 flex items-center">
                <div class="h-px w-full bg-black"></div>
              </div>
            </div>

            <div class="relative bg-black p-2 py-3 rounded-xl">
              <!-- background grid of black squares -->
              <div class="absolute inset-0 grid grid-rows-2">
                <div
                  class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md"
                ></div>
                <div
                  class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md"
                ></div>
              </div>

              <!-- time numbers -->
              <span class="absolute top-50">{{ tensDays }}</span>

              <!-- line across the middle -->
              <div class="absolute inset-0 flex items-center">
                <div class="h-px w-full bg-black"></div>
              </div>
            </div>
            <div class="relative bg-black p-2 py-3 rounded-xl">
              <!-- background grid of black squares -->
              <div class="absolute inset-0 grid grid-rows-2">
                <div
                  class="bg-gradient-to-br from-gradient-start to-gradient-end rounded-t-md"
                ></div>
                <div
                  class="bg-gradient-to-br from-gradient-start-light to-gradient-end rounded-b-md"
                ></div>
              </div>

              <!-- time numbers -->
              <span class="relative">{{ day }}</span>

              <!-- line across the middle -->
              <div class="absolute inset-0 flex items-center">
                <div class="h-px w-full bg-black"></div>
              </div>
            </div>
          </div>
          <div class="text-center justify-start items-center lg:flex-col flex">
            <p class="font-semibold text-xs lg:text-base">
              <T keyName="journey.countdown.days" />
            </p>
            <p
              v-if="duringJourney"
              class="font-semibold w-full pl-1 lg:text-lg text-xs"
            >
              <T keyName="journey.countdown.ends" />
            </p>
            <p
              v-else-if="journeyEnded"
              class="font-semibold w-full lg:text-lg pl-1 text-xs"
            >
              <T keyName="journey.countdown.finished" />
            </p>
            <p v-else class="font-semibold w-full lg:text-lg pl-1 text-xs">
              <T keyName="journey.countdown.until" />
            </p>
            <button
              v-if="duringJourney"
              class="font-bold border-2 border-cta-border lg:h-3/6 xl:w-[120%] lg:w-[100%] bg-background w-0 h-0 max-lg:invisible max-lg:w-0 rounded-xl hover:bg-cta-bg py-1"
            >
              <T keyName="journey.button.countdown.calendar" />
            </button>
            <button
              v-else-if="journeyEnded"
              class="font-bold border-2 border-cta-border lg:h-3/6 xl:w-[120%] lg:w-[100%] bg-background w-0 h-0 max-lg:invisible max-lg:w-0 rounded-xl hover:bg-cta-bg py-1"
              @click="jsConfetti.addConfetti()"
            >
              <T keyName="journey.button.countdown.celebrate" />
            </button>
            <button
              v-else
              class="font-bold border-2 border-cta-border lg:h-3/6 xl:w-[120%] lg:w-[100%] bg-background w-0 h-0 max-lg:invisible max-lg:w-0 rounded-xl hover:bg-cta-bg py-1"
            >
              <T keyName="journey.button.countdown.planning" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
