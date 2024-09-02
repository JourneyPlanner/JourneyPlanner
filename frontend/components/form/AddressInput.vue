<script setup lang="ts">
import { useTolgee } from "@tolgee/vue";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const props = defineProps({
    name: { type: String, required: true },
    placeholder: { type: String, default: " " },
    value: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    customClass: { type: String, default: "" },
    withLabel: { type: Boolean, default: false },
    id: { type: String, default: "" },
    translationKey: { type: String, default: "" },
});

const { value: mapbox } = useField<Feature>(() => "mapbox");
const { value: inputValue, errorMessage } = useField<string>(() => props.name);

const journey = useJourneyStore();
const config = useRuntimeConfig();

let longlat = [0, 0];
let search = null;
let Mapbox = null;
const isLoaded = ref(false);
const onlyShow = ref(true);

if (journey.getLat() && journey.getLong()) {
    longlat = [journey.getLong(), journey.getLat()];
}

onBeforeMount(async () => {
    if (import.meta.client) {
        Mapbox = await import("@mapbox/search-js-web");
        search = new Mapbox.MapboxSearchBox();
        search.accessToken = config.public.NUXT_MAPBOX_API_KEY as string;
        isLoaded.value = true;
    }
});

onUnmounted(() => {
    Mapbox = null;
    search = null;
    isLoaded.value = false;
});

const tolgee = useTolgee(["language"]);
const fullConfig = resolveConfig(tailwindConfig);

const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");

let input = "";
let text = "";
let placeholderColor = "";
let hoverCancel = "";
const border = "#88C4D8";

if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    input = "#464646";
    text = "#FCFCFC";
    placeholderColor = "#989898";
    hoverCancel = fullConfig.theme.accentColor["mahagony-400"] as string;
} else {
    input = "#FCFCFC";
    text = fullConfig.theme.accentColor["text"] as string;
    placeholderColor = "#989898";
    hoverCancel = "#E58484";
}

const css = `.Input {background-color: ${input}; color: ${text};} .Input:focus {background-color: ${input}; color: ${text}; box-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);} .Input::placeholder {font-family: Nunito; font-size: 0.75rem; line-height: 1rem; color: ${placeholderColor}} .Results {background-color: ${input}; color: ${text};}  border-radius: 0.5rem;} .ClearBtn:hover {color: ${hoverCancel}}`;

function changeInput(event: InputEvent) {
    inputValue.value = (event.target as HTMLInputElement).value;
}

function handleRetrieve(event: MapBoxRetrieveEvent) {
    mapbox.value = event.detail.features[0];
    inputValue.value = event.detail.features[0].properties.full_address;
}

function handleInput() {
    onlyShow.value = false;
    inputValue.value = "";
    mapbox.value = {} as Feature;
}
</script>
<template>
    <FormClassicInputIcon
        v-if="(value && onlyShow) || disabled"
        id="address-cover"
        name="address-cover"
        :disabled="disabled"
        :value="value"
        icon="pi-map-marker"
        :icon-pos-is-left="true"
        class="order-4 col-span-full flex flex-col sm:order-3 sm:col-span-3"
        @input="handleInput"
    />
    <form v-else class="mb-0 font-nunito" @submit.prevent>
        <ClientOnly v-if="isLoaded" class="relative">
            <mapbox-search-box
                class="font-nunito"
                :name="name"
                :access-token="config.public.NUXT_MAPBOX_API_KEY"
                :placeholder="placeholder"
                :options="{
                    language: tolgee.getLanguage(),
                    proximity: longlat,
                }"
                :theme="{
                    cssText: `.Input {border-radius: 0.5rem; font-family: Nunito; font-size: 1rem; line-height: 1.5rem; border: solid 2px ${border};} .Input:focus {border-radius: 0.5rem; border: solid 2px ${border};} .SearchBox {box-shadow: none;} .Results {font-family: Nunito;} .ResultsAttribution {color: ${placeholderColor}} .SearchIcon {fill: ${border};} .ActionIcon {color: ${placeholderColor}}  ${css} ${customClass}`,
                }"
                @input="changeInput"
                @retrieve="
                    (event: MapBoxRetrieveEvent) => handleRetrieve(event)
                "
            />
            <div v-if="errorMessage" class="h-1.5 w-full text-left">
                <span
                    class="ml-3 text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300"
                    :class="{
                        invisible: !errorMessage,
                        visible: errorMessage,
                    }"
                    >{{ errorMessage }}</span
                >
            </div>
        </ClientOnly>
    </form>
</template>
