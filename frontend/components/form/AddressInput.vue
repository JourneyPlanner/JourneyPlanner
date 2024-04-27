<script setup lang="ts">
import { useTolgee } from "@tolgee/vue";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const props = defineProps({
    name: { type: String, required: true },
    placeholder: { type: String, default: " " },
    customClass: { type: String, default: "" },
    withLabel: { type: Boolean, default: false },
    id: { type: String, default: "" },
    translationKey: { type: String, default: "" },
    bgLightKey: { type: String, default: "background" },
    bgDarkKey: { type: String, default: "background-dark" },
});

const { value: mapbox } = useField<Feature>(() => "mapbox");
const { value: inputValue } = useField<string>(() => props.name);

const config = useRuntimeConfig();

let search = null;
let Mapbox = null;
const isLoaded = ref(false);

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
let bg = "";
let hoverCancel = "";
const border = fullConfig.theme.accentColor["border"] as string;

if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    input = fullConfig.theme.accentColor["input-dark"] as string;
    text = fullConfig.theme.accentColor["input"] as string;
    placeholderColor = fullConfig.theme.accentColor[
        "input-placeholder"
    ] as string;
    bg = fullConfig.theme.accentColor[props.bgDarkKey] as string;
    hoverCancel = fullConfig.theme.accentColor["cancel-bg-dark"] as string;
} else {
    input = fullConfig.theme.accentColor["input"] as string;
    text = fullConfig.theme.accentColor["text"] as string;
    placeholderColor = fullConfig.theme.accentColor[
        "input-placeholder"
    ] as string;
    bg = fullConfig.theme.accentColor[props.bgLightKey] as string;
    hoverCancel = fullConfig.theme.accentColor["cancel-bg"] as string;
}

const css = `.Input {background-color: ${input}; color: ${text};} .Input:focus {background-color: ${input}; color: ${text}; box-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);} .Input::placeholder {font-family: Nunito; font-size: 0.75rem; line-height: 1rem; color: ${placeholderColor}} .Results {background-color: ${input}; color: ${text};} .SearchBox {background-color: ${bg};} .Suggestion:hover {background-color: ${bg};}  .ClearBtn:hover {color: ${hoverCancel}}`;

function changeInput(event: InputEvent) {
    inputValue.value = (event.target as HTMLInputElement).value;
}

function handleRetrieve(event: MapBoxRetrieveEvent) {
    mapbox.value = event.detail.features[0];
    inputValue.value = event.detail.features[0].properties.full_address;
}
</script>
<template>
    <form class="mb-0 font-nunito" @submit.prevent>
        <ClientOnly v-if="isLoaded" class="relative">
            <mapbox-search-box
                class="font-nunito"
                :name="name"
                :access-token="config.public.NUXT_MAPBOX_API_KEY"
                :placeholder="placeholder"
                :options="{ language: tolgee.getLanguage() }"
                :theme="{
                    cssText: `.Input {border-radius: 0.5rem; font-family: Nunito; font-size: 1rem; line-height: 1.5rem; border: solid 2px ${border};} .Input:focus {border-radius: 0.5rem; border: solid 2px ${border};} .SearchBox {box-shadow: none;} .Results {font-family: Nunito;} .ResultsAttribution {color: ${placeholderColor}} .SearchIcon {fill: ${border};} .ActionIcon {color: ${placeholderColor}}  ${css} ${customClass}`,
                }"
                @input="changeInput"
                @retrieve="
                    (event: MapBoxRetrieveEvent) => handleRetrieve(event)
                "
            />
        </ClientOnly>
    </form>
</template>
