<script setup lang="ts">
const props = defineProps({
    isSidebarVisible: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const isVisible = ref(props.isSidebarVisible);

watch(
    () => props.isSidebarVisible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};
</script>
<template>
    <div>
        <Sidebar
            id="member-sidebar"
            v-model:visible="isVisible"
            position="right"
            :block-scroll="true"
            :pt="{
                closeButton: {
                    class: 'w-9 h-9 col-span-2 flex w-full justify-end pr-1',
                },
                closeIcon: {
                    class: 'w-7 h-7 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50',
                },
                header: { class: 'p-2 pl-3 grid grid-rows-1 grid-cols-12' },
                content: {
                    class: 'pl-3 pr-2 py-2 flex h-full flex-col',
                },
                root: {
                    class: 'dark:bg-background-dark font-nunito relative',
                },
            }"
            @hide="close"
        ></Sidebar>
    </div>
</template>
