<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
    editBanner: {
        type: Boolean,
        required: true,
    },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "remove"]);
const { t } = useTranslate();

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};

const remove = () => {
    emit("remove");
};
</script>
<template>
    <div class="text-text dark:text-natural-50">
        <Dialog
            v-model:visible="isVisible"
            modal
            :auto-z-index="true"
            :draggable="false"
            class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/2 2xl:w-2/6"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex justify-start pb-2 font-nunito bg-background dark:bg-background-dark',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-12 h-full',
                },
                footer: { class: 'h-0' },
                icons: {
                    class: 'justify-end items-center w-fit pl-5',
                },
                closeButtonIcon: {
                    class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 focus:outline-none focus-ring-1 h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex w-[90%] items-center">
                    <div class="h-0.5 w-3 bg-mahagony-500" />
                    <div
                        class="flex-grow-5 mx-2 text-3xl text-text dark:text-natural-50"
                    >
                        <T
                            key-name="business.remove.image"
                            :params="{
                                image: props.editBanner
                                    ? t('business.banner')
                                    : t('business.image'),
                            }"
                        />
                    </div>
                    <div class="h-0.5 flex-grow bg-mahagony-500" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-ml-8 w-[110%] overflow-hidden overflow-ellipsis text-natural-700 dark:text-natural-300"
                >
                    <T
                        key-name="business.remove.image.detail"
                        :params="{
                            image: props.editBanner
                                ? t('business.banner')
                                : t('business.image'),
                        }"
                    />
                </div>

                <div class="flex w-full justify-center pb-1 pt-7">
                    <button
                        class="ml-2 w-40 rounded-md bg-natural-50 px-2 pr-5 text-xl text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                        @click="close"
                    >
                        <T key-name="common.button.cancel" />
                    </button>
                    <button
                        class="ml-1 mr-6 mt-auto w-[70%] text-nowrap rounded-lg border-[2.5px] border-mahagony-500 bg-natural-50 px-2 py-1 pl-2 text-base font-semibold text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="remove"
                    >
                        <T
                            key-name="business.remove.image"
                            :params="{
                                image: props.editBanner
                                    ? t('business.banner')
                                    : t('business.image'),
                            }"
                        />
                    </button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
