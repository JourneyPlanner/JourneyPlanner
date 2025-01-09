<script setup lang="ts">
import { T } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
    noSingle: { type: Boolean, default: false },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "post"]);
const editOption = ref("single");
const singleVisible = ref(false);

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

watch(
    () => props.noSingle,
    (value) => {
        if (value) {
            editOption.value = "following";
        } else {
            editOption.value = "single";
        }
        singleVisible.value = value;
    },
);

const close = () => {
    emit("close");
    editOption.value = "single";
};

const post = () => {
    emit("close");
    emit("post", editOption.value);
    editOption.value = "single";
};
</script>
<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        class="z-50 flex w-1/2 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/4 2xl:w-1/5"
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
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-5 h-full',
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
                <div class="h-0.5 w-5 bg-calypso-400" />
                <div
                    class="flex-grow-5 mx-3 text-3xl text-text dark:text-natural-50"
                >
                    <T key-name="activity.repeat.edit" />
                </div>
                <div class="h-0.5 flex-grow bg-calypso-400" />
            </div>
        </template>
        <div class="text-text dark:text-natural-50">
            <T key-name="activity.recurring.event" />
            <br />
            <T key-name="activity.repeat.edit.choose" />
            <div>
                <input
                    v-if="!singleVisible"
                    id="thisEvent"
                    v-model="editOption"
                    name="thisEvent"
                    type="radio"
                    checked
                    value="single"
                    class="form-radio text-blue-600"
                />
                <label
                    v-if="!singleVisible"
                    for="thisEvent"
                    class="pl-2"
                    :class="{
                        'text-natural-400 dark:text-natural-300': true,
                        'text-natural-900 dark:text-natural-50':
                            editOption == 'single',
                    }"
                >
                    <T key-name="activity.recurring.event.single"
                /></label>
                <br />
                <input
                    id="followingEvents"
                    v-model="editOption"
                    name="followingEvents"
                    type="radio"
                    value="following"
                    class="form-radio text-blue-600"
                    :checked="singleVisible"
                />
                <label
                    for="followingEvents"
                    class="pl-2"
                    :class="{
                        'text-natural-400 dark:text-natural-300': true,
                        'text-natural-900 dark:text-natural-50':
                            editOption == 'following',
                    }"
                >
                    <T key-name="activity.recurring.event.following"
                /></label>
                <br />
                <input
                    id="allEvents"
                    v-model="editOption"
                    name="allEvents"
                    type="radio"
                    value="all"
                    class="form-radio text-blue-600"
                />
                <label
                    for="allEvents"
                    class="pl-2"
                    :class="{
                        'text-natural-400 dark:text-natural-300': true,
                        'text-natural-900 dark:text-natural-50':
                            editOption == 'all',
                    }"
                >
                    <T key-name="activity.recurring.event.all"
                /></label>
            </div>
            <div class="mt-auto flex w-full justify-center">
                <div class="ml-auto flex justify-center">
                    <button
                        class="flex w-20 items-center justify-center rounded-md bg-natural-50 px-2 text-base font-semibold text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                        @click="close"
                    >
                        <T key-name="common.button.cancel" />
                    </button>
                </div>
                <button
                    class="mt-auto flex items-center justify-center rounded-md border-[3px] border-dandelion-300 bg-natural-50 px-8 py-0.5 text-center text-base font-semibold hover:bg-dandelion-200 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-pesto-600"
                    @click="post"
                >
                    <T key-name="common.edit" />
                </button>
            </div>
        </div>
    </Dialog>
</template>
