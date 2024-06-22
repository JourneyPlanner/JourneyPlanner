<script setup>
import { T, useTranslate } from "@tolgee/vue";
import { saveAs } from "file-saver";
import JSZip from "jszip";
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
const downloading = ref(false);
const toast = useToast();
const isDocDialogOpen = ref(false);

const { data } = await useAsyncData("media", () =>
    client(`/api/journey/${journey.getID()}/media`),
);

if (data && data.value !== null) {
    data.value.forEach((media) => {
        if (media.type.startsWith("image") || media.type.startsWith("video")) {
            multimedia.value.push(media);
        } else {
            docs.value.push(media);
        }
    });
}

const downloadMedia = async () => {
    downloading.value = true;
    toast.add({
        severity: "info",
        summary: t.value("journey.download.start"),
        detail: t.value("journey.download.detail"),
        life: 6000,
    });

    const zip = new JSZip();
    const fetchImage = async (url, name) => {
        const response = await fetch(url);
        const blob = await response.blob();
        zip.file(name, blob);
    };

    const promises = multimedia.value.map((media) =>
        fetchImage(media.link, media.name),
    );

    await Promise.all(promises);

    zip.generateAsync({ type: "blob" }).then((content) => {
        saveAs(content, `JourneyPlanner_${journey.getName()}.zip`);
    });
    downloading.value = false;
};

const setName = (media, asHtml, withText = true) => {
    let name = media.user_first_name;

    if (media.user_first_name && media.user_last_name) {
        name = media.user_first_name + " " + media.user_last_name;
    }

    if (!withText) {
        return name;
    } else {
        return asHtml
            ? `<p>${t.value("journey.media.uploadedby")}<strong>${name}</strong></p>`
            : `${t.value("journey.media.uploadedby")}${name}`;
    }
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

console.log(docs.value);

//TODO responsive, dark mode; wenn upload dann gallery refresh
</script>

<template>
    <div class="w-full flex-col">
        <div class="mb-3 flex flex-row justify-between">
            <h1 class="text-2xl font-semibold">
                <T key-name="journey.media.overview" />
            </h1>
            <div class="flex items-center gap-5">
                <span
                    class="pi pi-file-pdf text-xl hover:cursor-pointer"
                    @click="isDocDialogOpen = true"
                />
                <Button
                    :label="t('journey.media.download')"
                    icon="pi pi-download"
                    :loading="downloading"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    class="ml-auto flex rounded-xl border-2 border-cta-border bg-input px-2 py-1 text-base font-bold hover:bg-cta-bg dark:bg-input-dark dark:text-input dark:hover:bg-cta-bg-dark"
                    @click="downloadMedia"
                />
            </div>
        </div>
        <div
            class="h-[9.7rem] w-full rounded-2xl border-[3px] border-calypso-400 p-1 sm:h-[12.7rem] md:h-[16.7rem] lg:rounded-3xl"
        >
            <ScrollPanel
                class="h-full w-full overflow-x-hidden"
                :pt="{
                    barY: 'w-1.5 bg-natural-200 hover:bg-natural-300 dark:bg-[#888] dark:hover:bg-[#555]',
                }"
            >
                <lightgallery
                    v-if="multimedia.length > 0"
                    :settings="{
                        speed: 300,
                        controls: true,
                        plugins: plugins,
                        closeOnTap: true,
                    }"
                    class="grid grid-cols-5 gap-2 p-3"
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
                        <img
                            loading="lazy"
                            class="img-responsive h-32 w-64 rounded-lg object-cover hover:cursor-zoom-in"
                            :src="setImage(media)"
                            :alt="setName(media, false)"
                        />
                    </a>
                </lightgallery>
                <div
                    v-else
                    class="flex h-full items-center justify-center font-nunito text-text dark:text-natural-50"
                >
                    <h6>No media uploaded yet.</h6>
                </div>
            </ScrollPanel>
        </div>

        <!-- TODO farben -->
        <Dialog
            v-model:visible="isDocDialogOpen"
            modal
            :header="t('journey.media.docs')"
            :draggable="false"
            :style="{ width: '35rem' }"
            class="bg-input dark:bg-input-dark"
            :pt="{
                root: {
                    class: 'font-nunito text-text bg-input dark:bg-input-dark',
                },
                header: {
                    class: 'pb-2 h-15 bg-input dark:bg-input-dark text-text dark:text-input',
                },
                title: { class: 'text-xl mt-0.5' },
                content: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-input',
                },
                closeButtonIcon: {
                    class: 'text-input-placeholder hover:text-text dark:text-input-placeholder dark:hover:text-input h-5 w-5',
                },
            }"
        >
            <ScrollPanel
                class="relative h-[15rem]"
                :pt="{
                    barY: 'w-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                    barX: 'h-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                }"
            >
                <div class="font-nunito text-text dark:text-natural-50">
                    <table
                        class="w-full table-fixed text-left text-sm md:text-base"
                    >
                        <thead
                            class="border-b border-text text-xs uppercase dark:border-input"
                        >
                            <tr>
                                <th scope="col" class="">
                                    <T key-name="journey.media.name" />
                                </th>
                                <th scrop="col" class="text-center">
                                    <T key-name="journey.media.uploadedby" />
                                </th>
                                <th scope="col" class="">
                                    <T key-name="journey.media.link" />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="doc in docs" :key="doc.id">
                                <th scope="row" class="truncate">
                                    {{ doc.name.replace(/^\d+_/, "") }}
                                </th>
                                <td class="text-center">
                                    {{ setName(doc, false, false) }}
                                </td>
                                <td class="">
                                    <a
                                        :href="doc.link"
                                        target="_blank"
                                        class="truncate hover:underline"
                                    >
                                        {{ doc.link }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </ScrollPanel>
        </Dialog>
    </div>
</template>
