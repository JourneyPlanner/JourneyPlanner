<script setup lang="ts">
const props = defineProps({
    visible: { type: Boolean, required: true },
});

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const emit = defineEmits(["close"]);

const close = () => {
    emit("close");
};
const isVisible = ref(props.visible);
</script>
<template>
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            :block-scroll="true"
            :auto-z-index="true"
            :draggable="false"
            close-on-escape
            dismissable-mask
            class="mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-1/3"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex gap-x-3 pb-2 font-nunito bg-background dark:bg-background-dark px-4 sm:px-7',
                },
                title: {
                    class: 'font-nunito text-2xl font-semibold text-text dark:text-natural-50',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-4 sm:px-7 h-full',
                },
                footer: { class: 'h-0' },
                closeButtonIcon: {
                    class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse bg-natural-50',
                },
            }"
            @hide="close"
        >
            <template #header>
                <span
                    class="h-1 w-5 rounded-l-sm bg-calypso-400 dark:bg-calypso-600"
                />
                <h3
                    class="text-nowrap text-3xl font-medium text-text dark:text-natural-50"
                >
                    <T key-name="journey.weather.license.info" />
                </h3>
                <span
                    class="h-1 w-full rounded-r-sm bg-calypso-400 dark:bg-calypso-600 md:mr-2"
                />
            </template>
            <div
                class="flex flex-col gap-y-5 font-semibold text-natural-950 dark:text-natural-50"
            >
                <div class="">
                    <T key-name="journey.weather.license.part1" />
                    <a
                        href="https://open-meteo.com/"
                        class="underline hover:text-calypso-600 dark:hover:text-calypso-300"
                        ><T key-name="journey.weather.license.open.meteo"
                    /></a>
                    <T key-name="journey.weather.license.part2" />
                    <a
                        href="https://creativecommons.org/licenses/by/4.0/"
                        class="underline hover:text-calypso-600 dark:hover:text-calypso-300"
                        ><T key-name="journey.weather.license.cc.4" />
                    </a>
                </div>
                <T key-name="journey.weather.license.part3" />
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="bottom"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-fit w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden ',
                },
                header: {
                    class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50 rounded-3xl',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 -ml-2 sm:pr-12 h-full',
                },
                footer: { class: 'h-0' },
                closeButton: {
                    class: 'justify-start w-full h-full items-center collapse',
                },
                mask: {
                    class: 'sm:collapse bg-natural-50',
                },
            }"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-down text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="journey.weather.license.info" />
                </div>
            </template>
            <div class="flex items-center justify-center">
                <div
                    class="ml-2 w-11/12 flex-col gap-y-5 pb-5 font-semibold text-natural-950 dark:text-natural-50"
                >
                    <div class="">
                        <T key-name="journey.weather.license.part1" />
                        <a
                            href="https://open-meteo.com/"
                            class="underline hover:text-calypso-600 dark:hover:text-calypso-300"
                            ><T key-name="journey.weather.license.open.meteo"
                        /></a>
                        <T key-name="journey.weather.license.part2" />
                        <a
                            href="https://creativecommons.org/licenses/by/4.0/"
                            class="underline hover:text-calypso-600 dark:hover:text-calypso-300"
                            ><T key-name="journey.weather.license.cc.4" />
                        </a>
                    </div>
                    <T key-name="journey.weather.license.part3" />
                </div>
            </div>
        </Sidebar>
    </div>
</template>
