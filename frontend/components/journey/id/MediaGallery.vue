<script setup>
import { T, useTranslate } from "@tolgee/vue";
import pkg from "file-saver";
import JSZip from "jszip";
import "lightgallery/css/lg-autoplay.css";
import "lightgallery/css/lg-fullscreen.css";
import "lightgallery/css/lg-rotate.css";
import "lightgallery/css/lg-video.css";
import "lightgallery/css/lg-zoom.css";
import "lightgallery/css/lightgallery.css";
import lgAutoplay from "lightgallery/plugins/autoplay";
import lgFullscreen from "lightgallery/plugins/fullscreen";
import lgRotate from "lightgallery/plugins/rotate";
import lgVideo from "lightgallery/plugins/video";
import lgZoom from "lightgallery/plugins/zoom";
import LightGallery from "lightgallery/vue/LightGalleryVue.umd.js";

const plugins = [lgVideo, lgZoom, lgAutoplay, lgRotate, lgFullscreen];
const journey = useJourneyStore();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();
const config = useRuntimeConfig();
const { t } = useTranslate();
const multimedia = ref([]);
const docs = ref([]);
const downloading = ref(false);
const toast = useToast();
const isDocDialogOpen = ref(false);
const { saveAs } = pkg;

let lightGallery = null;

const props = defineProps({
    uploadData: { type: String, default: "" },
});

/**
 * Watcher for the uploadData prop
 * refetches and refreshes the gallery after a new upload
 */
watch(
    () => props.uploadData,
    async () => {
        await fetchMedia();
        lightGallery.refresh();
    },
);

onMounted(() => {
    fetchMedia();
});

const onInit = (detail) => {
    lightGallery = detail.instance;
};

/**
 * Fetches all media for the current journey
 * and sorts them into multimedia and docs
 */
async function fetchMedia() {
    if (isAuthenticated.value) {
        let data = null;

        await client(`/api/journey/${journey.getID()}/media`, {
            method: "GET",
            async onResponse({ response }) {
                if (response.ok) {
                    data = response._data;
                }
            },
        });

        if (data && data !== null) {
            data.forEach((media) => {
                if (
                    media.type.startsWith("image") ||
                    media.type.startsWith("video")
                ) {
                    if (
                        !multimedia.value.some((item) => item.id === media.id)
                    ) {
                        multimedia.value.push(media);
                    }
                } else {
                    if (!docs.value.some((item) => item.id === media.id)) {
                        docs.value.push(media);
                    }
                }
            });
        }

        journey.setMedia(multimedia.value);
        journey.setDocs(docs.value);
    }
}

/**
 * Downloads all media as a zip file
 */
const downloadMedia = async () => {
    if (isAuthenticated.value) {
        downloading.value = true;
        toast.add({
            severity: "info",
            summary: t.value("journey.download.start"),
            detail: t.value("journey.download.detail"),
            life: 6000,
        });

        const zip = new JSZip();
        const fetchFile = async (url, name) => {
            const response = await client(url, { method: "GET" });

            if (response) {
                zip.file(name, response);
            }
        };

        const multimediaPromises = multimedia.value.map((media) =>
            fetchFile(media.link, media.name),
        );

        const docsPromises = docs.value.map((doc) =>
            fetchFile(doc.link, doc.name),
        );

        await Promise.all([...multimediaPromises, ...docsPromises]);

        zip.generateAsync({ type: "blob" }).then((content) => {
            saveAs(content, `JourneyPlanner_${journey.getName()}.zip`);
        });

        downloading.value = false;
    }
};

/**
 * Sets the description (name of the person who uploaded) of the media
 * @param {Object} media - The media object
 * @param {Boolean} asHtml - Whether to return the name as HTML
 * @param {Boolean} withText - Whether to include the text "Uploaded by"
 * @returns {String} The name of the media
 */
const setName = (media, asHtml, withText = true) => {
    const displayname = media.user_display_name;
    const username = media.user_username;

    if (!withText) {
        return displayname;
    } else {
        return asHtml
            ? `<a href="/user/${username}?journey=${journey.getID()}"><p>${t.value("journey.media.uploadedby")}<strong>${displayname}</strong></p></a>`
            : `${t.value("journey.media.uploadedby")}${name}`;
    }
};

/**
 * Sets the source of the media
 * @param {Object} media - The media object
 * @returns {String} The source of the media
 */
const setSrc = (media) => {
    if (media.type.startsWith("image")) {
        return media.link;
    }
    return "";
};

/**
 * Sets the video of the media
 * @param {Object} media - The media object
 * @returns {String} The video of the media
 */
const setVideo = (media) => {
    if (media.type.startsWith("video")) {
        return `{ "source": [{ "src": "${media.link}", "type": "video/mp4" }], "attributes": { "preload": false, "controls": true } }`;
    }
    return "";
};

/**
 * Sets the thumbnail of the media
 * @param {Object} media - The media object
 * @returns {String} The thumbnail of the media
 */
const setThumbnail = (media) => {
    if (media.type.startsWith("video")) {
        return media.link + "?thumbnail";
    }
    return "";
};

/**
 * Sets the image of the media
 * @param {Object} media - The media object
 * @returns {String} The image of the media
 */
const setImage = (media) => {
    if (media.type.startsWith("image")) {
        return media.link;
    } else if (media.type.startsWith("video")) {
        return media.link + "?thumbnail";
    }
    return "";
};
</script>

<template>
    <div
        class="relative mt-4 flex w-[90%] items-center sm:mt-7 sm:w-5/6 md:ml-[10%] md:w-[calc(50%+16rem)] md:justify-between lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
    >
        <div class="w-full flex-col">
            <div class="mb-1.5 flex flex-row items-center justify-between">
                <div class="flex flex-row items-center gap-2">
                    <h1 class="text-2xl font-semibold">
                        <T key-name="journey.media.overview" />
                    </h1>
                    <Badge
                        v-if="multimedia.length > 0"
                        :value="multimedia.length"
                        severity="secondary"
                    />
                </div>
                <div class="flex items-center gap-5">
                    <i
                        v-badge.info="docs.length"
                        v-tooltip.top="{
                            value: isAuthenticated
                                ? t('journey.media.docs.tooltip')
                                : '',
                            pt: { root: 'font-nunito' },
                        }"
                        class="pi pi-file-pdf text-2xl"
                        :class="
                            isAuthenticated
                                ? 'hover:cursor-pointer'
                                : 'cursor-not-allowed'
                        "
                        @click="
                            isAuthenticated ? (isDocDialogOpen = true) : null
                        "
                    />

                    <Button
                        v-tooltip.top="{
                            value: isAuthenticated
                                ? t('journey.media.download.tooltip')
                                : '',
                            pt: { root: 'font-nunito text-nowrap' },
                        }"
                        :label="t('journey.media.download')"
                        icon="pi pi-download"
                        :loading="downloading"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-bold font-nunito',
                            },
                        }"
                        class="ml-auto flex items-center rounded-xl border-2 border-dandelion-300 bg-natural-50 px-2 py-1 text-sm font-bold dark:bg-background-dark dark:text-natural-50 sm:text-base lg:mb-1"
                        :class="
                            isAuthenticated
                                ? 'hover:bg-dandelion-200 dark:hover:bg-pesto-600'
                                : 'cursor-not-allowed'
                        "
                        @click="downloadMedia"
                    />
                </div>
            </div>
            <div
                class="h-[9.7rem] w-full rounded-2xl border-[3px] border-calypso-400 dark:border-calypso-600 sm:h-[12.7rem] md:h-[16.7rem] lg:rounded-3xl lg:p-1"
            >
                <ScrollPanel
                    class="h-full w-full overflow-x-hidden"
                    :pt="{
                        barY: 'w-1.5 bg-natural-200 hover:bg-natural-300 dark:bg-[#888] dark:hover:bg-[#555]',
                    }"
                >
                    <LightGallery
                        v-if="multimedia.length > 0"
                        :on-init="onInit"
                        :settings="{
                            speed: 300,
                            controls: true,
                            plugins: plugins,
                            closeOnTap: true,
                            licenseKey: config.public.NUXT_LIGHTGALLERY_KEY,
                        }"
                        class="grid grid-cols-2 gap-2 p-3 xs:grid-cols-3 sm:grid-cols-4 lg:grid-cols-5"
                    >
                        <a
                            v-for="media in multimedia"
                            :key="media.link"
                            class="gallery-item"
                            :data-sub-html="setName(media, true)"
                            :data-src="setSrc(media)"
                            :data-video="setVideo(media)"
                            :data-poster="setThumbnail(media)"
                            :data-download="media.name"
                        >
                            <NuxtImg
                                loading="lazy"
                                class="img-responsive h-16 w-32 rounded-lg object-cover hover:cursor-zoom-in sm:h-24 sm:w-52 lg:h-32 lg:w-64"
                                :src="setImage(media)"
                                :alt="setName(media, false)"
                            />
                        </a>
                    </LightGallery>
                    <div
                        v-else
                        class="flex h-full items-center justify-center font-nunito text-text dark:text-natural-50"
                    >
                        <h6>
                            <T key-name="journey.media.multimedia.empty" />
                        </h6>
                    </div>
                </ScrollPanel>
            </div>
            <Dialog
                v-model:visible="isDocDialogOpen"
                modal
                :header="t('journey.media.docs')"
                :draggable="false"
                :style="{ width: '35rem' }"
                class="bg-natural-50 dark:bg-background-dark"
                :pt="{
                    root: {
                        class: 'font-nunito text-text bg-natural-50 dark:bg-background-dark',
                    },
                    header: {
                        class: 'pb-2 h-15 bg-natural-50 dark:bg-background-dark text-text dark:text-input',
                    },
                    title: { class: 'text-xl mt-0.5' },
                    content: {
                        class: 'bg-natural-50 dark:bg-background-dark text-text dark:text-input',
                    },
                    closeButtonIcon: {
                        class: 'text-natural-500 hover:text-text dark:text-input-placeholder dark:hover:text-input h-5 w-5',
                    },
                }"
            >
                <p
                    class="dark:border-input-placeholder mb-2 border-b-2 border-natural-300 pb-0.5"
                >
                    <T key-name="journey.media.docs.info" />
                </p>
                <ScrollPanel
                    class="relative h-[15rem]"
                    :pt="{
                        barY: 'w-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                        barX: 'h-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                    }"
                >
                    <div class="font-nunito text-text dark:text-natural-50">
                        <table
                            v-if="docs.length > 0"
                            class="w-full table-fixed text-left text-sm md:text-base"
                        >
                            <thead
                                class="dark:border-input border-b border-text text-xs uppercase"
                            >
                                <tr>
                                    <th scope="col" class="w-2/3">
                                        <T key-name="journey.media.name" />
                                    </th>
                                    <th scrop="col" class="text-center">
                                        <T
                                            key-name="journey.media.uploadedby"
                                        />
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="doc in docs" :key="doc.id">
                                    <th scope="row" class="truncate">
                                        <a
                                            :href="doc.link"
                                            target="_blank"
                                            class="hover:underline"
                                        >
                                            {{ doc.name.replace(/^\d+_/, "") }}
                                        </a>
                                    </th>
                                    <td class="truncate text-center">
                                        {{ setName(doc, false, false) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else>
                            <h6 class="text-center">
                                <T key-name="journey.media.docs.empty" />
                            </h6>
                        </div>
                    </div>
                </ScrollPanel>
            </Dialog>
        </div>
    </div>
</template>
