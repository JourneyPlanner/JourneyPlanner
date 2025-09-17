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
                    class="h-0.5 w-5 rounded-l-sm bg-calypso-400 dark:bg-calypso-600"
                />
                <h3
                    class="text-nowrap text-3xl font-medium text-text dark:text-natural-50"
                >
                    <T key-name="profile.gravatar.info.header" />
                </h3>
                <span
                    class="h-0.5 w-full rounded-r-sm bg-calypso-400 dark:bg-calypso-600 md:mr-2"
                />
            </template>
            <div
                class="flex flex-col gap-y-4 font-normal text-natural-950 dark:text-natural-50"
            >
                <p><T key-name="profile.gravatar.info.description" /></p>
                <div class="flex justify-center">
                    <NuxtLink
                        to="https://gravatar.com/emails/"
                        target="_blank"
                        class="mt-auto flex h-9 w-44 flex-row items-center justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:cursor-pointer hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    >
                        <T key-name="profile.gravatar.info.button" />
                    </NuxtLink>
                </div>
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="bottom"
            :auto-z-index="true"
            :draggable="false"
            :show-close-icon="false"
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
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-down text-2xl" />
                </button>
                <div class="font-nunito text-2xl font-semibold">
                    <T key-name="profile.gravatar.info.header" />
                </div>
            </template>
            <div class="flex items-center justify-center">
                <div
                    class="ml-4 w-11/12 flex-col gap-y-5 pb-5 text-natural-950 dark:text-natural-50"
                >
                    <p><T key-name="profile.gravatar.info.description" /></p>
                    <div class="mt-2 flex justify-center">
                        <NuxtLink
                            to="https://gravatar.com/emails/"
                            target="_blank"
                            class="mt-auto flex h-9 w-44 flex-row items-center justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:cursor-pointer hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        >
                            <T key-name="profile.gravatar.info.button" />
                        </NuxtLink>
                    </div>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
