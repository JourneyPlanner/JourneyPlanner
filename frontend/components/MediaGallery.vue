<script setup>
import { T, useTranslate } from "@tolgee/vue";
import "lightgallery/css/lg-video.css";
import "lightgallery/css/lightgallery.css";
import lgVideo from "lightgallery/plugins/video/lg-video.umd.js";
import Lightgallery from "lightgallery/vue/LightGalleryVue.umd.js";

const plugins = [lgVideo];
const journey = useJourneyStore();
const client = useSanctumClient();
const { t } = useTranslate();
const multimedia = ref([]);
const docs = ref([]);

const { data } = await useAsyncData("media", () =>
    client(`/api/journey/${journey.getID()}/media`),
);

if (data && data.value !== null) {
    data.value.forEach((media) => {
        if (media.type.startsWith("image") || media.type.startsWith("video")) {
            multimedia.value.push({ ...media, isLoaded: false });
        } else {
            docs.value.push(media);
        }
    });
}

const downloadMedia = async () => {
    await client(`/api/journey/${journey.getID()}/media/download`);
};

const setHtml = (media, asHtml) => {
    let name = media.user_first_name;

    if (media.user_first_name && media.user_last_name) {
        name = media.user_first_name + " " + media.user_last_name;
    }

    return asHtml
        ? `<p>${t.value("journey.media.uploadedby")}<strong>${name}</strong></p>`
        : `${t.value("journey.media.uploadedby")}${name}`;
};

const setSrc = (media) => {
    if (media.type.startsWith("image")) {
        return media.link;
    }
    return "";
};

const setVideo = (media) => {
    if (media.type.startsWith("video")) {
        return `{ "source": [{ "src": "${media.link}", "type": "video/mp4" }], "attributes": { "preload": false, "controls": true } }`;
    }
    return "";
};

const setThumbnail = (media) => {
    if (media.type.startsWith("video")) {
        return media.link + "?thumbnail";
    }
    return "";
};

const setImage = (media) => {
    if (media.type.startsWith("image")) {
        return media.link;
    } else if (media.type.startsWith("video")) {
        return media.link + "?thumbnail";
    }
    return "";
};

//TODO scroll bar, responsive, dokumente, no files yet, download, download name, loading images..., dark mode
</script>

<template>
    <div class="w-full flex-col">
        <div class="mb-3 flex flex-row justify-between">
            <h1 class="text-2xl font-semibold">
                <T key-name="journey.media.overview" />
            </h1>
            <button
                class="ml-auto flex rounded-xl border-2 border-cta-border bg-input px-2 py-1 text-base font-bold hover:bg-cta-bg dark:bg-input-dark dark:text-input dark:hover:bg-cta-bg-dark"
                @click="downloadMedia"
            >
                <SvgDownload class="mr-1 h-5 w-5" />
                <T key-name="journey.media.download" />
            </button>
        </div>
        <div
            class="w-full rounded-2xl border-[3px] border-calypso-400 lg:rounded-3xl"
        >
            <ScrollPanel
                class="h-[9.7rem] w-full sm:h-[12.7rem] md:h-[16.7rem]"
                :pt="{
                    barY: 'w-1.5 bg-natural-200 hover:bg-natural-300 dark:bg-[#888] dark:hover:bg-[#555]',
                }"
            >
                <lightgallery
                    :settings="{
                        speed: 300,
                        controls: true,
                        plugins: plugins,
                        closeOnTap: true,
                    }"
                    class="grid grid-cols-7 gap-2 p-3"
                >
                    <a
                        v-for="media in multimedia"
                        :key="media.link"
                        class="gallery-item"
                        :data-sub-html="setHtml(media, true)"
                        :data-src="setSrc(media)"
                        :data-video="setVideo(media)"
                        :data-poster="setThumbnail(media)"
                        :data-download="'downloadname'"
                    >
                        <DeferredContent>
                            <img
                                loading="lazy"
                                class="img-responsive h-32 w-64 rounded-lg object-cover hover:cursor-zoom-in"
                                :src="setImage(media)"
                                :alt="setHtml(media, false)"
                                @load="media.isLoaded = true"
                            />
                        </DeferredContent>
                    </a>
                </lightgallery>
            </ScrollPanel>
        </div>
    </div>
</template>
