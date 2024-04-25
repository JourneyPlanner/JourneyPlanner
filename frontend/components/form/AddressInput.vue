<script setup lang="ts">
//import * as Mapbox from "@mapbox/search-js-web";
import { useTolgee } from "@tolgee/vue";
import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "~/tailwind.config.js";

const props = defineProps({
    name: { type: String, required: true },
});

const { value } = useField<Feature>(() => props.name);
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
let bg = "";
let hoverCancel = "";

if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    input = fullConfig.theme.accentColor["input-dark"] as string;
    text = fullConfig.theme.accentColor["input"] as string;
    bg = fullConfig.theme.accentColor["background-dark"] as string;
    hoverCancel = fullConfig.theme.accentColor["cancel-bg-dark"] as string;
} else {
    input = fullConfig.theme.accentColor["input"] as string;
    text = fullConfig.theme.accentColor["text"] as string;
    bg = fullConfig.theme.accentColor["background"] as string;
    hoverCancel = fullConfig.theme.accentColor["cancel-bg"] as string;
}

const css = `.Input {background-color: ${input}; color: ${text};} .Input:focus {background-color: ${input}; color: ${text}; box-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);} .Results {background-color: ${input}; color: ${text};} .SearchBox {background-color: ${bg};} .Suggestion:hover {background-color: ${bg};}  .ClearBtn:hover {color: ${hoverCancel}}`;

function handleRetrieve(event: MapBoxRetrieveEvent) {
    value.value = event.detail.features[0];
}
</script>
<template>
    <form class="mb-0 font-nunito" @submit.prevent>
        <ClientOnly v-if="isLoaded">
            <mapbox-search-box
                class="font-nunito"
                :name="name"
                :access-token="config.public.NUXT_MAPBOX_API_KEY"
                proximity="0,0"
                placeholder=" "
                :options="{ language: tolgee.getLanguage() }"
                :theme="{
                    cssText: `.Input {border-radius: 0.5rem; font-family: Nunito; font-size: 1rem; line-height: 1.5rem; border: solid 2px #69aecd;} .Input:focus {border-radius: 0.5rem; border: solid 2px #69aecd;} .SearchBox {box-shadow: none} .Results {font-family: Nunito;, z-index:500} .ResultsAttribution {color: #7b7b7b} .SearchIcon {fill: #69aecd;} .ActionIcon {color: #7b7b7b}  ${css}`,
                }"
                @retrieve="
                    (event: MapBoxRetrieveEvent) => handleRetrieve(event)
                "
            />
        </ClientOnly>
    </form>
</template>
