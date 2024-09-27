<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import JSConfetti from "js-confetti";

import scroll from "../../utils/scroll";

defineProps({
    hundredsDays: {
        type: Number,
        required: true,
    },
    tensDays: {
        type: Number,
        required: true,
    },
    day: {
        type: Number,
        required: true,
    },
    duringJourney: {
        type: Boolean,
        required: true,
    },
    journeyEnded: {
        type: Boolean,
        required: true,
    },
    currUser: {
        type: Object as PropType<User>,
        required: true,
    },
    calendarRef: {
        type: Object as PropType<Ref>,
        required: true,
    },
    uploadRef: {
        type: Object as PropType<Ref>,
        required: true,
    },
});

const emits = defineEmits(["open-activity-dialog"]);

const client = useSanctumClient();
const jsConfetti = new JSConfetti();
const journeyStore = useJourneyStore();
const { t } = useTranslate();

const isFlipped = ref(false);
const celsius = ref(true);
const fahrenheit = ref(false);
const currTemperature = ref();
const weatherType = ref("");
const highestTemp = ref(24);
const lowestTemp = ref(9);

const flip = () => {
    isFlipped.value = !isFlipped.value;
};

const { data: weather } = await useAsyncData("weather", () =>
    client(`/api/journey/${journeyStore.getID()}/weather`),
);

currTemperature.value = Math.round(weather.value.current.temperature);
highestTemp.value = Math.round(weather.value.forecast[0].temperature_max);
lowestTemp.value = Math.round(weather.value.forecast[0].temperature_min);
weatherType.value = t.value(
    `weather.code.${weather.value.current.weather_code}`,
);
const weatherTypeTomorrow = t.value(
    `weather.code.${weather.value.forecast[1].weather_code}`,
);
const weatherTypeInTwoDays = t.value(
    `weather.code.${weather.value.forecast[2].weather_code}`,
);
const weatherTypeInThreeDays = t.value(
    `weather.code.${weather.value.forecast[3].weather_code}`,
);
const weatherCodeToday = weather.value.current.weather_code;
const weatherCodeTomorrow = weather.value.forecast[1].weather_code;
const weatherCodeInTwoDays = weather.value.forecast[2].weather_code;
const weatherCodeInThreeDays = weather.value.forecast[3].weather_code;

function changeToCelsius() {
    if (fahrenheit.value == true) {
        currTemperature.value = Math.round(weather.value.current.temperature);
        highestTemp.value = Math.round(
            weather.value.forecast[0].temperature_max,
        );
        lowestTemp.value = Math.round(
            weather.value.forecast[0].temperature_min,
        );
    }

    celsius.value = true;
    fahrenheit.value = false;
}

function changeToFahrenheit() {
    if (celsius.value == true) {
        currTemperature.value = Math.round(
            (weather.value.current.temperature * 9) / 5 + 32,
        );
        highestTemp.value = Math.round(
            (weather.value.forecast[0].temperature_max * 9) / 5 + 32,
        );
        lowestTemp.value = Math.round(
            (weather.value.forecast[0].temperature_min * 9) / 5 + 32,
        );
    }
    celsius.value = false;
    fahrenheit.value = true;
}

function toggleActivityDialog() {
    emits("open-activity-dialog");
}
</script>

<template>
    <div class="mt-8 flex h-fit flex-wrap">
        <div class="flex w-full items-center justify-center md:hidden">
            <div
                class="group w-[90%] [perspective:1000px] sm:w-5/6"
                @click="flip"
            >
                <div
                    :class="isFlipped ? '[transform:rotateX(180deg)]' : ''"
                    class="relative h-full w-full rounded-2xl transition-all duration-500 [transform-style:preserve-3d]"
                >
                    <div class="bg-none md:w-2/5 lg:w-1/3">
                        <div
                            class="relative flex h-10 items-center rounded-t-2xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
                        >
                            <div
                                class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-natural-200"
                            />
                            <p class="ml-16 text-xl font-bold text-natural-50">
                                JourneyPlanner
                            </p>
                            <div
                                class="flex h-full w-full items-center justify-end"
                            >
                                <SvgAirplaneIcon class="mr-5 w-7" />
                            </div>
                        </div>
                        <div class="flex h-5/6">
                            <div
                                class="-mr-1 h-fit w-full rounded-b-2xl border-x-2 border-b-2 border-natural-200 bg-natural-50 text-sm dark:border-gothic-600 dark:bg-dark"
                            >
                                <div class="mb-2 mt-1 grid w-full grid-cols-4">
                                    <div
                                        class="col-span-3 flex h-full w-full flex-col justify-center pl-5 font-semibold"
                                    >
                                        <T key-name="form.input.journey.name" />
                                        <input
                                            class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                            disabled
                                            :value="journeyStore.getName()"
                                        />
                                        <T
                                            key-name="form.input.journey.destination"
                                        />
                                        <input
                                            class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                            disabled
                                            :value="
                                                journeyStore.getDestination()
                                            "
                                        />
                                        <T key-name="form.input.journey.date" />
                                        <input
                                            class="text-md mb-2 w-5/6 rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50 md:w-4/5"
                                            disabled
                                            :value="
                                                format(
                                                    journeyStore.getFromDate(),
                                                    'dd/MM/yyyy',
                                                ) +
                                                ' - ' +
                                                format(
                                                    journeyStore.getToDate(),
                                                    'dd/MM/yyyy',
                                                )
                                            "
                                        />
                                    </div>
                                    <div class="relative -mt-1 w-full">
                                        <SvgStripes
                                            class="absolute right-0 z-0 w-[7.4rem]"
                                        />
                                        <div
                                            class="absolute bottom-2 right-1 ml-10 flex h-[3.8rem] w-[3.8rem] cursor-pointer select-none items-center justify-center self-center rounded-full border-2 border-dashed border-natural-400 pl-1.5 pr-1.5 text-center text-xs text-natural-400 dark:border-natural-50 dark:text-natural-50"
                                        >
                                            <T key-name="journey.turn" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-b-r-3xl h-[90%] w-0 cursor-pointer border-r-2 border-dashed border-natural-200"
                            />
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 h-full w-full rounded-xl bg-natural-50 text-center text-text [backface-visibility:hidden] [transform:rotateX(180deg)] dark:bg-background-dark"
                    >
                        <div
                            class="relative flex h-10 items-center rounded-t-2xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
                        >
                            <div
                                class="absolute ml-5 inline-block h-7 w-7 self-center rounded-full bg-natural-200"
                            />
                            <p class="ml-16 text-xl font-bold text-natural-50">
                                JourneyPlanner
                            </p>
                            <div
                                class="flex h-full w-full items-center justify-end"
                            >
                                <SvgAirplaneIcon class="mr-5 w-7" />
                            </div>
                        </div>
                        <div class="flex h-5/6">
                            <div
                                class="flex h-full w-full justify-center rounded-b-2xl border-x-2 border-b-2 border-natural-200 bg-natural-50 text-sm dark:border-gothic-600 dark:bg-dark"
                            >
                                <div
                                    class="relative flex h-full w-full flex-col overflow-hidden"
                                >
                                    <div
                                        class="absolute bottom-4 right-1 z-40 ml-10 mt-1 flex h-[3.8rem] w-[3.8rem] cursor-pointer select-none items-center justify-center self-center rounded-full border-2 border-dashed border-natural-400 pl-1.5 pr-1.5 text-xs text-natural-400 dark:border-natural-50 dark:text-natural-50"
                                    >
                                        <T key-name="journey.turn" />
                                    </div>
                                    <div class="flex h-full">
                                        <div class="z-0 ml-0 h-full w-1/2">
                                            <div
                                                class="-ml-14 mt-3 flex h-1/2 items-center justify-center"
                                            >
                                                <WeatherIcon
                                                    class="-mt-2 ml-8 w-24"
                                                    :weather-code="
                                                        weatherCodeToday
                                                    "
                                                />
                                            </div>
                                            <div
                                                class="mt-3 flex items-center justify-center"
                                            >
                                                <div>
                                                    <div
                                                        class="flex h-full w-full items-center justify-start text-5xl text-text dark:text-natural-50"
                                                    >
                                                        {{ currTemperature }}°
                                                    </div>
                                                </div>
                                                <div>
                                                    <div
                                                        class="ml-4 flex h-full flex-col items-start justify-center pt-1 text-sm text-natural-800 dark:text-natural-200"
                                                    >
                                                        <div>
                                                            H:
                                                            {{ highestTemp }}°
                                                        </div>
                                                        <div class="pt-2">
                                                            T:
                                                            {{ lowestTemp }}°
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="z-0 h-full w-1/3 pt-2">
                                            <SmallWeather
                                                :celsius="celsius"
                                                :max-temp="
                                                    weather.forecast[1]
                                                        .temperature_max
                                                "
                                                :min-temp="
                                                    weather.forecast[1]
                                                        .temperature_min
                                                "
                                                :day="1"
                                                :weather-code="
                                                    weatherCodeTomorrow
                                                "
                                                :weather-type="
                                                    weatherTypeTomorrow
                                                "
                                            />
                                            <SmallWeather
                                                :celsius="celsius"
                                                :max-temp="
                                                    weather.forecast[2]
                                                        .temperature_max
                                                "
                                                :min-temp="
                                                    weather.forecast[2]
                                                        .temperature_min
                                                "
                                                :day="2"
                                                :weather-code="
                                                    weatherCodeInTwoDays
                                                "
                                                :weather-type="
                                                    weatherTypeInTwoDays
                                                "
                                                :right-line="false"
                                            />
                                        </div>
                                        <div class="z-0 h-full w-1/5">
                                            <div class="mr-2 mt-2 flex w-1/3">
                                                <button
                                                    class="h-1/5 pr-2 text-xl"
                                                    :class="
                                                        celsius === true
                                                            ? 'font-bold text-calypso-600 dark:text-natural-50'
                                                            : 'font-normal text-text dark:text-natural-300'
                                                    "
                                                    @click.stop="
                                                        changeToCelsius
                                                    "
                                                >
                                                    °C
                                                </button>
                                                <div
                                                    class="border-l border-natural-300 dark:border-natural-400"
                                                />
                                                <button
                                                    class="font ml-1 h-1/5 text-xl"
                                                    :class="
                                                        fahrenheit === true
                                                            ? 'font-bold text-calypso-600 dark:text-natural-50'
                                                            : 'font-normal text-text dark:text-natural-300'
                                                    "
                                                    @click.stop="
                                                        changeToFahrenheit
                                                    "
                                                >
                                                    °F
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            id="ticket-left-desktop"
            class="invisible ml-[10%] w-0 max-md:h-0 md:visible md:w-[50%] lg:ml-10 lg:w-1/3 xl:ml-[10%]"
        >
            <div
                class="relative flex h-10 items-center rounded-t-3xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
            >
                <div
                    class="bg-calypso-300-gray absolute ml-5 inline-block h-7 w-7 self-center rounded-full"
                />
                <p class="ml-14 text-xl font-bold text-natural-50">
                    JourneyPlanner
                </p>
                <div class="flex h-full w-full items-center justify-end">
                    <SvgAirplaneIcon class="mr-5 w-7" />
                </div>
            </div>
            <div class="flex h-[13.5rem] lg:h-[15.5rem]">
                <div
                    class="dark:bg-calypso-300-dark w-full rounded-b-3xl border-b-2 border-l-2 border-natural-200 bg-natural-50 text-sm dark:border-gothic-600 dark:bg-dark"
                >
                    <div class="relative grid w-full grid-cols-4">
                        <div
                            class="col-span-3 flex h-[120%] w-full flex-col justify-center pl-10 text-base font-semibold"
                        >
                            <T key-name="form.input.journey.name" />
                            <input
                                class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                disabled
                                :value="journeyStore.getName()"
                            />
                            <T key-name="form.input.journey.destination" />
                            <input
                                class="text-md mb-2 w-full rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                                disabled
                                :value="journeyStore.getDestination()"
                            />
                            <T key-name="form.input.journey.date" />
                            <input
                                class="text-md mb-2 rounded-md bg-natural-100 px-2.5 pb-1 pt-1 font-bold text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50 md:w-5/6 lg:w-2/3"
                                disabled
                                :value="
                                    format(
                                        journeyStore.getFromDate(),
                                        'dd/MM/yyyy',
                                    ) +
                                    ' - ' +
                                    format(
                                        journeyStore.getToDate(),
                                        'dd/MM/yyyy',
                                    )
                                "
                            />
                        </div>
                        <div
                            class="absolute w-full md:col-span-2 lg:col-span-2 xl:col-span-1 2xl:col-span-1"
                        >
                            <SvgStripes
                                class="absolute right-0 md:w-[8.8rem] lg:w-[10.15rem]"
                            />
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-b-r-3xl h-[90%] w-0 border-r-2 border-dashed border-natural-200"
                />
            </div>
        </div>
        <div
            id="ticket-right-desktop"
            class="invisible w-0 rounded-3xl border-solid bg-background dark:bg-dark max-md:h-0 md:visible md:h-64 md:w-64 lg:h-72 lg:w-72"
        >
            <div
                class="h-10 rounded-t-3xl border-x-2 border-t-2 border-calypso-400 bg-calypso-300 dark:border-gothic-500 dark:bg-gothic-400"
            >
                <div class="flex h-full w-full items-center justify-end">
                    <SvgAirplaneIcon class="mr-5 w-7" />
                </div>
            </div>
            <div id="weather" class="flex h-[13.5rem] lg:h-[15.5rem]">
                <div
                    class="rounded-b-l-3xl h-[90%] w-0 border-l-2 border-dashed border-natural-200"
                />
                <div
                    class="flex h-full w-full cursor-default justify-center rounded-b-3xl border-b-2 border-r-2 border-natural-200 dark:border-gothic-600 dark:bg-dark"
                >
                    <div class="relative flex h-full w-full flex-col items-end">
                        <div class="flex w-full pt-2 font-nunito max-lg:h-1/2">
                            <div class="flex w-1/2 items-center justify-center">
                                <WeatherIcon
                                    class="w-28"
                                    :weather-code="weatherCodeToday"
                                />
                            </div>
                            <div class="w-1/2">
                                <div class="flex">
                                    <div
                                        class="flex w-2/3 flex-col justify-center"
                                    >
                                        <div
                                            class="flex justify-center text-5xl lg:text-6xl"
                                        >
                                            {{ currTemperature }}
                                        </div>
                                    </div>
                                    <div class="mr-2 flex w-1/3">
                                        <button
                                            class="-ml-1 h-1/5 pr-2 text-xl"
                                            :class="
                                                celsius === true
                                                    ? 'font-bold text-calypso-600 dark:text-natural-50'
                                                    : 'font-normal text-text dark:text-natural-300'
                                            "
                                            @click="changeToCelsius"
                                        >
                                            °C
                                        </button>
                                        <div
                                            class="h-2/5 border-l-2 border-natural-300 dark:border-natural-400"
                                        />
                                        <button
                                            class="font mr-1 h-1/5 pl-1 text-xl"
                                            :class="
                                                fahrenheit === true
                                                    ? 'font-bold text-calypso-600 dark:text-natural-50'
                                                    : 'font-normal text-text dark:text-natural-300'
                                            "
                                            @click="changeToFahrenheit"
                                        >
                                            °F
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="flex h-6 w-28 justify-center overflow-hidden overflow-ellipsis text-natural-800 dark:text-natural-200"
                                    >
                                        <span
                                            v-tooltip.right="{
                                                value: weatherType,
                                                pt: { root: 'font-nunito' },
                                            }"
                                            class="-ml-1 flex h-6 w-32 justify-center overflow-hidden overflow-ellipsis text-nowrap"
                                            >{{ weatherType }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex items-end justify-start pt-1 lg:text-xl"
                                    >
                                        <div>H: {{ highestTemp }}°</div>
                                        <div class="pl-2">
                                            T: {{ lowestTemp }}°
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid h-1/2 w-full grid-cols-3 gap-2 pt-4">
                            <SmallWeather
                                :celsius="celsius"
                                :max-temp="weather.forecast[1].temperature_max"
                                :min-temp="weather.forecast[1].temperature_min"
                                :day="1"
                                :weather-code="weatherCodeTomorrow"
                                :weather-type="weatherTypeTomorrow"
                            />
                            <SmallWeather
                                :celsius="celsius"
                                :max-temp="weather.forecast[2].temperature_max"
                                :min-temp="weather.forecast[2].temperature_min"
                                :day="2"
                                :weather-code="weatherCodeInTwoDays"
                                :weather-type="weatherTypeInTwoDays"
                            />
                            <SmallWeather
                                :celsius="celsius"
                                :max-temp="weather.forecast[3].temperature_max"
                                :min-temp="weather.forecast[3].temperature_min"
                                :day="3"
                                :right-line="false"
                                :weather-code="weatherCodeInThreeDays"
                                :weather-type="weatherTypeInThreeDays"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="basis-0 md:basis-full lg:basis-0" />
        <div
            id="widget"
            class="flex w-full justify-center md:justify-start lg:ml-10 lg:w-72 xl:ml-32"
        >
            <div
                class="w-[90%] rounded-2xl border-2 border-solid border-calypso-300 bg-calypso-50 bg-opacity-20 dark:border-calypso-600 dark:bg-gothic-300 dark:bg-opacity-20 max-lg:mt-5 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] lg:ml-0 lg:w-full lg:rounded-3xl"
            >
                <div
                    class="from-indigo-500 to-indigo-800 flex h-full flex-wrap items-center justify-center bg-gradient-to-br xs:justify-start lg:flex-col lg:justify-center"
                >
                    <!-- flip clock container -->
                    <div
                        v-if="hundredsDays <= 0"
                        class="relative mx-3 my-2 grid grid-cols-2 gap-x-1 text-4xl font-bold text-text dark:text-natural-50 lg:text-6xl"
                    >
                        <div class="bg-black relative rounded-xl p-1 py-2">
                            <!-- background grid of black squares -->
                            <div class="absolute inset-0 grid grid-rows-2">
                                <div
                                    class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                                <div
                                    class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                            </div>

                            <!-- time numbers -->
                            <span class="top-50 absolute">{{ tensDays }}</span>

                            <!-- line across the middle -->
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                />
                            </div>
                        </div>
                        <div class="bg-black relative rounded-xl p-1 py-2">
                            <!-- background grid of black squares -->
                            <div class="absolute inset-0 grid grid-rows-2">
                                <div
                                    class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                                <div
                                    class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                            </div>

                            <!-- time numbers -->
                            <span class="relative">{{ day }}</span>

                            <!-- line across the middle -->
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="relative mx-3 my-2 grid grid-cols-3 gap-x-1 text-4xl font-bold text-text dark:text-natural-50 lg:gap-x-2 lg:text-6xl"
                    >
                        <!-- left side -->
                        <div
                            class="bg-black relative rounded-xl p-1 py-2 lg:p-2 lg:py-3"
                        >
                            <!-- background grid of black squares -->
                            <div class="absolute inset-0 grid grid-rows-2">
                                <div
                                    class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                                <div
                                    class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                            </div>

                            <!-- time numbers -->
                            <span class="top-50 absolute">{{
                                hundredsDays
                            }}</span>

                            <!-- line across the middle -->
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                />
                            </div>
                        </div>

                        <div
                            class="bg-black relative rounded-xl p-1 py-2 lg:p-2 lg:py-3"
                        >
                            <!-- background grid of black squares -->
                            <div class="absolute inset-0 grid grid-rows-2">
                                <div
                                    class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                                <div
                                    class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                            </div>

                            <!-- time numbers -->
                            <span class="top-50 absolute">{{ tensDays }}</span>

                            <!-- line across the middle -->
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                />
                            </div>
                        </div>
                        <div
                            class="bg-black relative rounded-xl p-1 py-2 lg:p-2 lg:py-3"
                        >
                            <!-- background grid of black squares -->
                            <div class="absolute inset-0 grid grid-rows-2">
                                <div
                                    class="rounded-t-md bg-gradient-to-br from-gradient-start to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                                <div
                                    class="rounded-b-md bg-gradient-to-br from-gradient-start-light to-gradient-end dark:from-gradient-start-dark dark:to-gradient-end-dark"
                                />
                            </div>

                            <!-- time numbers -->
                            <span class="relative">{{ day }}</span>

                            <!-- line across the middle -->
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="dark:bg-countdown-stroke-dark h-px w-full bg-calypso-300"
                                />
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-start text-center lg:flex-col"
                    >
                        <p class="text-base font-bold">
                            <T key-name="journey.countdown.days" />
                        </p>
                        <p
                            v-if="duringJourney"
                            class="w-full pl-1 text-base font-bold lg:text-lg"
                        >
                            <T key-name="journey.countdown.ends" />
                        </p>
                        <p
                            v-else-if="journeyEnded"
                            class="w-full pl-1 text-base font-bold lg:text-lg"
                        >
                            <T key-name="journey.countdown.finished" />
                        </p>
                        <p
                            v-else
                            class="w-full pl-1 text-base font-bold lg:text-lg"
                        >
                            <T key-name="journey.countdown.until" />
                        </p>
                        <button
                            v-if="duringJourney || currUser?.role !== 1"
                            class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[80%] xl:w-[110%]"
                            @click="scroll(calendarRef)"
                        >
                            <T key-name="journey.button.countdown.calendar" />
                        </button>
                        <button
                            v-else-if="journeyEnded"
                            class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                            @click="
                                scroll(uploadRef);
                                jsConfetti.addConfetti();
                            "
                        >
                            <T key-name="journey.button.countdown.celebrate" />
                        </button>
                        <button
                            v-else
                            class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                            @click="toggleActivityDialog"
                        >
                            <T key-name="journey.button.create.activity" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
