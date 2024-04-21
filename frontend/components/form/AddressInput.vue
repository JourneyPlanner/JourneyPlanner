<script setup lang="ts">
import * as Mapbox from "@mapbox/search-js-web";
import { useTolgee } from "@tolgee/vue";

const props = defineProps({
    name: { type: String, required: true },
});

const { value } = useField<string>(() => props.name);

//TODO: Add Mapbox access token here
const search = new Mapbox.MapboxSearchBox();
search.accessToken = "";

const tolgee = useTolgee(["language"]);

const colorMode = useColorMode();
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");

//TODO: maybe improve this
let css = "";
if (
    colorMode.preference === "dark" ||
    (darkTheme.matches && colorMode.preference === "system")
) {
    css =
        ".Input {background-color: #454849; color: #f8f8f8;} .Input:focus {background-color: #454849; color: #f8f8f8; box-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);} .Results {background-color: #454849; color: #f8f8f8;} .SearchBox {background-color: #2c2c2c;} .Suggestion:hover {background-color: #2c2c2c;}  .ClearBtn:hover {color: #7c6464}";
} else {
    css =
        ".Input {background-color: #f8f8f8; color: #333333;} .Input:focus {background-color: #f8f8f8; color: #333333; box-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);} .Results {background-color: #f8f8f8; color: #333333;} .SearchBox {background-color: #fcfcfc;} .ClearBtn:hover {color: #d98f8f}";
}
</script>
<template>
    <form class="mb-0 font-nunito" @submit.prevent>
        <mapbox-search-box
            v-model="value"
            class="font-nunito"
            :name="name"
            access-token=""
            proximity="0,0"
            placeholder=" "
            :options="{ language: tolgee.getLanguage() }"
            :theme="{
                cssText: `.Input {border-radius: 0.5rem; font-family: Nunito; font-size: 1rem; line-height: 1.5rem; border: solid 2px #69aecd;} .Input:focus {border-radius: 0.5rem; border: solid 2px #69aecd;} .SearchBox {box-shadow: none} .Results {font-family: Nunito;} .ResultsAttribution {color: #7b7b7b} .SearchIcon {fill: #69aecd;} .ActionIcon {color: #7b7b7b}  ${css}`,
            }"
        />
    </form>
</template>
