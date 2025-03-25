<script setup lang="ts">
const props = defineProps({
    visible: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "remove"]);

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
    emit("close");
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
                    <div class="h-0.5 w-3 bg-calypso-400" />
                    <div
                        class="flex-grow-5 mx-2 text-3xl text-text dark:text-natural-50"
                    >
                        <T key-name="business.save.data" />
                    </div>
                    <div class="h-0.5 flex-grow bg-calypso-400" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-ml-8 w-[110%] overflow-hidden overflow-ellipsis text-natural-700 dark:text-natural-300"
                >
                    <T key-name="business.save.data.detail" />
                </div>

                <div class="flex w-full justify-center pb-1 pt-7">
                    <button
                        class="ml-1 mr-auto mt-auto w-[30%] text-nowrap rounded-xl border-[2.5px] border-atlantis-400 bg-natural-50 py-1 text-center text-text hover:bg-atlantis-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-atlantis-30040"
                        @click="close"
                    >
                        <T key-name="common.no" />
                    </button>
                    <button
                        class="ml-1 mr-6 mt-auto w-[30%] text-nowrap rounded-xl border-[2.5px] border-mahagony-500 bg-natural-50 px-2 py-1 pl-2 text-base font-semibold text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="remove"
                    >
                        <T key-name="common.yes" />
                    </button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
