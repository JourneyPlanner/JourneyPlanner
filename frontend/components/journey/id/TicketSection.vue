<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import JSConfetti from "js-confetti";

const props = defineProps({
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
    isTemplate: {
        type: Boolean,
        default: false,
    },
    templateId: {
        type: String,
        default: "",
    },
});

const emits = defineEmits(["open-activity-dialog", "scrollToTarget"]);

const client = useSanctumClient();
const jsConfetti = new JSConfetti();
const journeyStore = useJourneyStore();
const { t } = useTranslate();
const { isAuthenticated } = useSanctumAuth();

const isFlipped = ref(false);
const celsius = ref(true);
const fahrenheit = ref(false);
const currTemperature = ref();
const weatherType = ref("");
const highestTemp = ref(24);
const lowestTemp = ref(9);
const isLicenseVisible = ref(false);

const weatherTypeTomorrow = ref();
const weatherTypeInTwoDays = ref();
const weatherTypeInThreeDays = ref();
const weatherCodeToday = ref();
const weatherCodeTomorrow = ref();
const weatherCodeInTwoDays = ref();
const weatherCodeInThreeDays = ref();
const locationFound = ref(true);
const createJourneyFromTemplate = `/journey/new/template/${props.templateId}`;

const flip = () => {
    isFlipped.value = !isFlipped.value;
};

const { data: weather, refresh } = await useAsyncData("weather", () =>
    client(
        `/api/journey/${props.isTemplate ? props.templateId : journeyStore.getID()}/weather`,
    ),
);

if (weather.value && weather.value.error != "Weather data not available") {
    currTemperature.value = Math.round(weather.value.current.temperature);
    highestTemp.value = Math.round(weather.value.forecast[0].temperature_max);
    lowestTemp.value = Math.round(weather.value.forecast[0].temperature_min);

    weatherType.value = t.value(
        `weather.code.${weather.value.current.weather_code}`,
    );
    weatherTypeTomorrow.value = t.value(
        `weather.code.${weather.value.forecast[1].weather_code}`,
    );
    weatherTypeInTwoDays.value = t.value(
        `weather.code.${weather.value.forecast[2].weather_code}`,
    );
    weatherTypeInThreeDays.value = t.value(
        `weather.code.${weather.value.forecast[3].weather_code}`,
    );

    weatherCodeToday.value = weather.value.current.weather_code;
    weatherCodeTomorrow.value = weather.value.forecast[1].weather_code;
    weatherCodeInTwoDays.value = weather.value.forecast[2].weather_code;
    weatherCodeInThreeDays.value = weather.value.forecast[3].weather_code;
} else {
    locationFound.value = false;
}

watch(journeyStore, async () => {
    await refresh();
    if (weather.value && weather.value.error != "Weather data not available") {
        updateWeatherData();
    } else {
        locationFound.value = false;
    }
});

function updateWeatherData() {
    locationFound.value = true;
    currTemperature.value = Math.round(weather.value.current.temperature);
    highestTemp.value = Math.round(weather.value.forecast[0].temperature_max);
    lowestTemp.value = Math.round(weather.value.forecast[0].temperature_min);

    weatherType.value = t.value(
        `weather.code.${weather.value.current.weather_code}`,
    );
    weatherTypeTomorrow.value = t.value(
        `weather.code.${weather.value.forecast[1].weather_code}`,
    );
    weatherTypeInTwoDays.value = t.value(
        `weather.code.${weather.value.forecast[2].weather_code}`,
    );
    weatherTypeInThreeDays.value = t.value(
        `weather.code.${weather.value.forecast[3].weather_code}`,
    );

    weatherCodeToday.value = weather.value.current.weather_code;
    weatherCodeTomorrow.value = weather.value.forecast[1].weather_code;
    weatherCodeInTwoDays.value = weather.value.forecast[2].weather_code;
    weatherCodeInThreeDays.value = weather.value.forecast[3].weather_code;
}

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

function emitScroll(target: string) {
    emits("scrollToTarget", target);
}
</script>

<template>
    <div class="mt-4 flex h-fit flex-wrap md:mt-8">
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
                            <SvgLogoHorizontalWhite class="ml-5 w-56" />
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
                                                !isTemplate
                                                    ? format(
                                                          journeyStore.getFromDate(),
                                                          'dd/MM/yyyy',
                                                      ) +
                                                      ' - ' +
                                                      format(
                                                          journeyStore.getToDate(),
                                                          'dd/MM/yyyy',
                                                      )
                                                    : '----'
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
                            <SvgLogoHorizontalWhite class="ml-5 w-56" />
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
                                    v-if="locationFound"
                                    class="relative flex h-full w-full flex-col overflow-hidden"
                                >
                                    <div
                                        class="absolute left-2 top-2 z-10 flex h-[1.25rem] w-[1.25rem] cursor-pointer items-center justify-center rounded-full text-center text-sm text-natural-500 hover:text-natural-900 dark:text-natural-400 dark:hover:text-natural-100"
                                        @click.stop="
                                            isLicenseVisible = !isLicenseVisible
                                        "
                                    >
                                        <span class="pi pi-info-circle" />
                                    </div>
                                    <JourneyIdDialogsLicenseDialog
                                        :visible="isLicenseVisible"
                                        @close="isLicenseVisible = false"
                                    />
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
                                                <JourneyIdComponentsWeatherIcon
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
                                            <JourneyIdComponentsSmallWeather
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
                                            <JourneyIdComponentsSmallWeather
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
                                <div v-if="!locationFound">
                                    <div
                                        class="absolute bottom-4 right-1 z-40 ml-10 mt-1 flex h-[3.8rem] w-[3.8rem] cursor-pointer select-none items-center justify-center self-center rounded-full border-2 border-dashed border-natural-400 pl-1.5 pr-1.5 text-xs text-natural-400 dark:border-natural-50 dark:text-natural-50"
                                    >
                                        <T key-name="journey.turn" />
                                    </div>
                                    <div class="flex">
                                        <div
                                            class="flex w-full items-center justify-center"
                                        >
                                            <SvgWeatherNotFound
                                                class="-ml-2 mt-6 block w-28 dark:hidden"
                                            />
                                            <SvgWeatherNotFoundDark
                                                class="-ml-2 mt-6 hidden w-28 dark:block"
                                            />
                                        </div>
                                        <div
                                            class="mt-6 text-natural-800 dark:text-natural-200"
                                        >
                                            <h1
                                                class="-ml-2 mt-2 pr-10 text-left text-xl font-semibold"
                                            >
                                                <T
                                                    key-name="journey.weather.not.found"
                                                />
                                            </h1>
                                            <div
                                                class="-ml-2 text-left text-sm"
                                            >
                                                <T
                                                    key-name="journey.weather.not.found.solution"
                                                />
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
                <SvgLogoHorizontalWhite class="ml-3 w-56" />

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
                                    !isTemplate
                                        ? format(
                                              journeyStore.getFromDate(),
                                              'dd/MM/yyyy',
                                          ) +
                                          ' - ' +
                                          format(
                                              journeyStore.getToDate(),
                                              'dd/MM/yyyy',
                                          )
                                        : '----'
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
                    <JourneyIdDialogsLicenseDialog
                        :visible="isLicenseVisible"
                        @close="isLicenseVisible = false"
                    />
                    <div
                        v-if="locationFound"
                        class="relative flex h-full w-full flex-col items-end"
                    >
                        <div
                            class="absolute left-2 top-2 flex h-[1.25rem] w-[1.25rem] cursor-pointer items-center justify-center rounded-full text-center text-sm text-natural-500 hover:text-natural-900 dark:text-natural-400 dark:hover:text-natural-100"
                            @click="isLicenseVisible = !isLicenseVisible"
                        >
                            <span class="pi pi-info-circle" />
                        </div>
                        <div class="flex w-full pt-2 font-nunito max-lg:h-1/2">
                            <div class="flex w-1/2 items-center justify-center">
                                <JourneyIdComponentsWeatherIcon
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
                                            class="h-6 w-32 overflow-hidden overflow-ellipsis text-nowrap text-center"
                                            >{{ weatherType }}
                                        </span>
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
                            <JourneyIdComponentsSmallWeather
                                :celsius="celsius"
                                :max-temp="weather.forecast[1].temperature_max"
                                :min-temp="weather.forecast[1].temperature_min"
                                :day="1"
                                :weather-code="weatherCodeTomorrow"
                                :weather-type="weatherTypeTomorrow"
                            />
                            <JourneyIdComponentsSmallWeather
                                :celsius="celsius"
                                :max-temp="weather.forecast[2].temperature_max"
                                :min-temp="weather.forecast[2].temperature_min"
                                :day="2"
                                :weather-code="weatherCodeInTwoDays"
                                :weather-type="weatherTypeInTwoDays"
                            />
                            <JourneyIdComponentsSmallWeather
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
                    <div
                        v-if="!locationFound"
                        class="text-natural-800 dark:text-natural-200"
                    >
                        <div class="flex w-full items-center justify-center">
                            <SvgWeatherNotFound
                                class="mt-6 block w-32 dark:hidden"
                            />
                            <SvgWeatherNotFoundDark
                                class="mt-6 hidden w-32 dark:block"
                            />
                        </div>
                        <h1 class="mt-2 text-center text-xl font-semibold">
                            <T key-name="journey.weather.not.found" />
                        </h1>
                        <div class="px-5 text-center text-sm">
                            <T key-name="journey.weather.not.found.solution" />
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
                        v-if="hundredsDays <= 0 && !isTemplate"
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
                        v-else-if="!isTemplate"
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
                    <div v-else class="max-lg:hidden">
                        <i
                            class="pi pi-objects-column mt-6 text-7xl text-calypso-300"
                        />
                    </div>
                    <div
                        class="flex items-center justify-start text-center lg:flex-col"
                    >
                        <p v-if="!isTemplate" class="text-base font-bold">
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
                            v-else-if="!isTemplate"
                            class="w-full pl-1 text-base font-bold lg:text-lg"
                        >
                            <T key-name="journey.countdown.until" />
                        </p>
                        <div v-else>
                            <h5
                                class="mt-4 font-semibold text-text dark:text-natural-50 max-sm:hidden md:text-xl"
                            >
                                <T key-name="template.use" />
                            </h5>
                            <p class="p-2 text-sm max-xs:hidden sm:text-base">
                                <T key-name="template.use.description" />
                            </p>
                        </div>
                        <button
                            v-if="
                                duringJourney ||
                                (currUser?.role !== 1 && !isTemplate) ||
                                (!isAuthenticated && !isTemplate)
                            "
                            class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[80%] xl:w-[110%]"
                            @click="emitScroll('calendarRef')"
                        >
                            <T key-name="journey.button.countdown.calendar" />
                        </button>
                        <button
                            v-else-if="journeyEnded"
                            class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                            @click="
                                emitScroll('uploadRef');
                                jsConfetti.addConfetti();
                            "
                        >
                            <T key-name="journey.button.countdown.celebrate" />
                        </button>
                        <button
                            v-else-if="!isTemplate"
                            class="mt-6 h-0 w-0 rounded-xl border-2 border-dandelion-300 bg-background py-2 font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 max-lg:invisible max-lg:w-0 lg:h-3/6 lg:w-[100%] xl:w-[120%]"
                            @click="toggleActivityDialog"
                        >
                            <T key-name="journey.button.create.activity" />
                        </button>
                        <NuxtLink
                            v-else
                            :to="createJourneyFromTemplate"
                            class="my-4 ml-5 mr-4 text-nowrap rounded-xl border-2 border-dandelion-300 bg-background px-2 py-1 text-sm hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600 md:mt-4 md:text-base"
                        >
                            <T key-name="template.use" />
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
