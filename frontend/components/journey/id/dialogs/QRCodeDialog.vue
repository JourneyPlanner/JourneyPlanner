<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    qrcode: {
        type: String,
        required: true,
    },
    tolgeeKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const { t } = useTranslate();
const isVisible = ref(false);

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        dismissable-mask
        close-on-esc
        :header="t(tolgeeKey)"
        :pt="{
            root: { class: 'bg-background dark:bg-background-dark' },
            content: { class: 'bg-background dark:bg-background-dark' },
            header: {
                class: 'bg-background dark:bg-background-dark text-text dark:text-natural-50 flex gap-x-5 font-nunito items-center',
            },
            closeButtonIcon: {
                class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10',
            },
        }"
        @hide="close"
    >
        <div class="bg-background dark:bg-background-dark">
            <img class="w-full" :src="qrcode" alt="QR Code" />
        </div>
    </Dialog>
</template>
