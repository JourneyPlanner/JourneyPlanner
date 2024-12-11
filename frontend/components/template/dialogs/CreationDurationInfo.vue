<script setup lang="ts">
const props = defineProps({
    isVisible: { type: Boolean, required: true },
});

const emit = defineEmits(["close"]);
const visible = ref();

watch(
    () => props.isVisible,
    (value) => {
        visible.value = value;
    },
);

const close = () => {
    emit("close");
};
</script>
<template>
    <Dialog
        v-model:visible="visible"
        modal
        :block-scroll="true"
        :auto-z-index="true"
        :draggable="false"
        close-on-escape
        dismissable-mask
        class="z-50 mx-5 -mt-80 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-1/3 xl:w-1/4"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex gap-x-3 pb-2 font-nunito bg-background dark:bg-background-dark px-4 sm:px-7',
            },
            title: {
                class: 'font-nunito text-2xl font-semibold text-text dark:text-natural-50 -pt-1',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-4 sm:px-7 h-full',
            },
            footer: { class: 'h-0' },
            closeButtonIcon: {
                class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10 ',
            },
        }"
        @hide="close"
    >
        <template #header>
            <div class="flex w-[90%] items-center">
                <div class="h-0.5 w-5 bg-calypso-400" />
                <div
                    class="flex-grow-5 mx-3 text-2xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="common.toast.info.heading" />
                </div>
                <div class="h-0.5 flex-grow bg-calypso-400" />
            </div>
        </template>
        <div class="font-semibold text-text dark:text-natural-50">
            <T key-name="template.creation.duration.info" />
        </div>
    </Dialog>
</template>
