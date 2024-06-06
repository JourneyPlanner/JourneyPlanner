<script setup>
import lgVideo from "lightgallery/plugins/video/lg-video.umd.js";
import lgZoom from "lightgallery/plugins/zoom/lg-zoom.umd.js";
import Lightgallery from "lightgallery/vue/LightGalleryVue.umd.js";

const plugins = [lgZoom, lgVideo];

const client = useSanctumClient();
const { data } = await useAsyncData("media", () => client("/api/journey"));
console.log(data);
</script>

<template>
    <div class="w-full flex-col">
        <div class="mb-3 flex flex-row justify-between">
            <h1 class="text-2xl font-semibold">
                <T key-name="journey.media.overview" />
            </h1>
            <!-- download button
            <button
                class=" ml-auto flex rounded-xl border-2 border-cta-border bg-input px-2 py-1 text-base font-bold hover:bg-cta-bg dark:bg-input-dark dark:text-input dark:hover:bg-cta-bg-dark"
                @click="">
                <SvgDownload class="h-5 w-5 mr-1" />
                <T key-name="journey.media.download" />
            </button>
            -->
        </div>
        <div
            class="w-full rounded-2xl border-[3px] border-border lg:rounded-3xl"
        >
            <ScrollPanel
                class="h-[9.7rem] w-full p-2 sm:h-[12.7rem] md:h-[16.7rem]"
                :pt="{
                    barY: 'w-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                    barX: 'h-1.5 bg-border-gray hover:bg-border-light dark:bg-[#888] dark:hover:bg-[#555]',
                }"
            >
                <lightgallery
                    :settings="{ speed: 300, controls: true, plugins: plugins }"
                >
                    <a
                        data-lg-size="1406-1390"
                        class="gallery-item"
                        data-src="https://images.unsplash.com/photo-1581894158358-5ecd2c518883?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1406&q=80"
                        data-sub-html="<h4>Photo by - <a href='https://unsplash.com/@entrycube' >Diego Guzmán </a></h4> <p> Location - <a href='https://unsplash.com/s/photos/fushimi-inari-taisha-shrine-senbontorii%2C-68%E7%95%AA%E5%9C%B0-fukakusa-yabunouchicho%2C-fushimi-ward%2C-kyoto%2C-japan'>Fushimi Ward, Kyoto, Japan</a></p>"
                    >
                        <img
                            class="img-responsive"
                            src="https://images.unsplash.com/photo-1581894158358-5ecd2c518883?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=240&q=80"
                        />
                    </a>
                    <a
                        data-lg-size="1280-720"
                        data-src="https://www.youtube.com/watch?v=EIUJfXk3_3w"
                        class="gallery-item"
                        data-poster="https://img.youtube.com/vi/EIUJfXk3_3w/maxresdefault.jpg"
                        data-sub-html="<h4>Puffin Hunts Fish To Feed Puffling | Blue Planet II | BBC Earth</h4><p>On the heels of Planet Earth II's record-breaking Emmy nominations, BBC America presents stunning visual soundscapes from the series' amazing habitats.</p>"
                    >
                        <img
                            width="300"
                            height="100"
                            class="img-responsive"
                            src="https://img.youtube.com/vi/EIUJfXk3_3w/maxresdefault.jpg"
                        />
                    </a>
                    <a
                        data-lg-size="1400-1400"
                        class="gallery-item"
                        data-src="https://images.unsplash.com/photo-1544550285-f813152fb2fd?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1400&q=80"
                        data-sub-html="<h4>Photo by - <a href='https://unsplash.com/@asoshiation' >Shah </a></h4><p> Location - <a href='https://unsplash.com/s/photos/shinimamiya%2C-osaka%2C-japan'>Shinimamiya, Osaka, Japan</a></p>"
                    >
                        <img
                            class="img-responsive"
                            src="https://images.unsplash.com/photo-1544550285-f813152fb2fd?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=240&q=80"
                        />
                    </a>
                    <a
                        data-lg-size="1400-1400"
                        class="gallery-item"
                        data-src="https://images.unsplash.com/photo-1584592740039-cddf0671f3d4?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1400&q=80"
                        data-sub-html="<h4>Photo by - <a href='https://unsplash.com/@katherine_xx11' >Katherine Gu </a></h4><p> For all those years we were alone and helpless.</p>"
                    >
                        <img
                            style="width: 200px"
                            class="img-responsive"
                            src="https://images.unsplash.com/photo-1584592740039-cddf0671f3d4?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=240&q=80"
                        />
                    </a>
                </lightgallery>
            </ScrollPanel>
        </div>
    </div>
</template>

<style lang="css" scoped>
@import url("https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.4/css/lightgallery.css");
@import url("https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.4/css/lg-zoom.css");
@import url("https://cdn.jsdelivr.net/npm/lightgallery@2.0.0-beta.4/css/lg-video.css");
</style>
