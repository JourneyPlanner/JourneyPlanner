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
});

const { value: mapbox } = useField<Feature>(() => "mapbox");
const { value: inputValue, errorMessage } = useField<string>(() => props.name);

const journey = useJourneyStore();
const config = useRuntimeConfig();

let longlat = [0, 0];
const search = ref();
let Mapbox = null;
const isLoaded = ref(false);

if (journey.getLat() && journey.getLong()) {
    longlat = [journey.getLong(), journey.getLat()];
}

onMounted(async () => {
    if (props.value) {
        inputValue.value = props.value;
    }
    Mapbox = await import("@mapbox/search-js-web");
    search.value = new Mapbox.MapboxSearchBox();
    isLoaded.value = true;
    await nextTick();
    if (props.value) {
        search.value.input.value = props.value;
    }

    if (props.disabled) {
        search.value.input.disabled = true;
        search.value.input.style.cursor = "not-allowed";
        search.value.input.style.backgroundColor = disabledBg;
    } else {
        search.value.input.disabled = false;
        search.value.input.style.cursor = "text";
        search.value.input.style.backgroundColor = input;
    }

    watch(props, () => {
        if (props.disabled) {
            search.value.input.disabled = true;
            search.value.input.style.cursor = "not-allowed";
            search.value.input.style.backgroundColor = disabledBg;
        } else {
            search.value.input.disabled = false;
            search.value.input.style.cursor = "text";
            search.value.input.style.backgroundColor = input;
        }
    });
});

onUnmounted(() => {
    Mapbox = null;
    search.value = null;
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
let disabledBg = "";
const bg = "";
let border = "";
let hover = "";

if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    input = "#3D3D3D";
    input = "#3D3D3D";
    text = "#FCFCFC";
    placeholderColor = "#989898";
    hoverCancel = "#E58484";
    disabledBg = "#464646";
    border = "#50A1C0";
    hover = "#525252";
} else {
    input = "#FCFCFC";
    text = fullConfig.theme.accentColor["text"] as string;
    placeholderColor = "#989898";
    hoverCancel = "#E58484";
    disabledBg = "#EFEFEF";
    border = "#88C4D8";
    hover = "#EFEFEF";
}

const css = `.Input {background-color: ${input}; color: ${text};} .Input:focus {background-color: ${input}; color: ${text}; box-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);} .Input::placeholder {font-family: Nunito; font-size: 0.75rem; line-height: 1rem; color: ${placeholderColor}} .Results {background-color: ${input}; color: ${text};} .Suggestion:hover {background-color: ${hover};} .SearchBox {background-color: ${bg}; border-radius: 0.5rem;} .ClearBtn:hover {color: ${hoverCancel}}`;

function changeInput(event: InputEvent) {
    inputValue.value = (event.target as HTMLInputElement).value;
}

function handleRetrieve(event: MapBoxRetrieveEvent) {
    mapbox.value = event.detail.features[0];
    inputValue.value = event.detail.features[0].properties.full_address;
}
</script>
<template>
    <form
        class="order-4 col-span-full mb-0 flex flex-col font-nunito sm:order-3 sm:col-span-3"
        @submit.prevent
    >
        <ClientOnly v-if="isLoaded" class="relative">
            <mapbox-search-box
                ref="search"
                class="font-nunito"
                :name="name"
                :placeholder="placeholder"
                :access-token="config.public.NUXT_MAPBOX_API_KEY"
                :options="{
                    language: tolgee.getLanguage(),
                    proximity: longlat,
                }"
                :theme="{
                    cssText: `.Input {border-radius: 0.5rem; font-family: Nunito; font-size: 1rem; line-height: 1.5rem; border-style: solid;  border-width:2px; border-color: ${border};} .Input:focus {border-radius: 0.5rem; border: solid 2px ${border};} .SearchBox {box-shadow: none;} .Results {font-family: Nunito;} .ResultsAttribution {color: ${placeholderColor}} .SearchIcon {fill: #50A1C0;} .ActionIcon {color: ${placeholderColor}}  ${css} ${customClass}`,
                }"
                @input="changeInput"
                @clear="inputValue = ''"
                @retrieve="
                    (event: MapBoxRetrieveEvent) => handleRetrieve(event)
                "
            />
            <div
                v-if="errorMessage"
                class="mt-1 h-1.5 w-full text-left leading-3"
            >
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
