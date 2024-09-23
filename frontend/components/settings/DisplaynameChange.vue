<script setup lang="ts">
import { T } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
    displayname: { type: String, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "changeDisplayname"]);
const newDisplayname = ref(props.displayname);

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

watch(
    () => props.displayname,
    (value) => {
        newDisplayname.value = value;
    },
);

const close = () => {
    newDisplayname.value = props.displayname;
    emit("close");
};

/**
 * emits the function so it can be handled in userSettings
 */
function changeDisplayname() {
    emit("changeDisplayname", newDisplayname.value);
}
</script>

<template>
    <Sidebar
        v-model:visible="isVisible"
        modal
        position="right"
        :auto-z-index="true"
        :draggable="false"
        class="z-50 mt-auto flex h-[95%] w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
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
                <span class="pi pi-angle-left text-2xl" />
            </button>
            <div class="font-nunito text-3xl font-semibold">
                <T key-name="dashboard.user.settings.display.name" />
            </div>
        </template>
        <div class="flex h-full flex-col pl-8">
            <div
                class="-pt-4 overflow-hidden overflow-ellipsis text-[0.95rem] text-natural-700 dark:text-natural-200"
            >
                <T
                    key-name="dashboard.user.settings.change.displayname.description"
                />
                <br />
                <T key-name="dashboard.user.displayname" />
                <b> {{ displayname }} </b>
            </div>
            <div class="flex items-center pl-6 pt-4">
                <div class="flex w-full flex-col items-center">
                    <div
                        class="mb-2 mr-10 flex w-full items-start text-base text-text dark:text-natural-50"
                    />
                    <input
                        id="displayname"
                        v-model="newDisplayname"
                        class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-300 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        @keyup.enter="changeDisplayname"
                    />
                </div>
            </div>
            <div class="mt-auto flex w-full justify-center">
                <button
                    class="ml-1 mr-6 mt-auto w-full rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-xl font-semibold hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    @click="changeDisplayname"
                >
                    <T key-name="common.save" />
                </button>
            </div>
        </div>
    </Sidebar>
</template>
