<script setup lang="ts">
const props = defineProps({
    template: {
        type: Object as PropType<Template>,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
});

console.log(props);
defineEmits(["openTemplate"]);

const isProfileDialogVisible = ref(false);
</script>

<template>
    <div
        class="cursor-pointer border-b border-natural-100 py-1 hover:border-calypso-400 dark:border-natural-400"
        :class="index === 0 ? 'border-t' : ''"
        @click="$emit('openTemplate', template.id)"
    >
        <div
            class="ml-1.5 flex border-l-2 border-natural-400 pl-1 dark:border-natural-400"
        >
            <div class="ml-1.5 w-1/4">
                <h3
                    v-tooltip.top="{
                        value: template.name,
                        pt: { root: 'font-nunito' },
                    }"
                    class="truncate text-lg font-medium text-text dark:text-natural-50"
                >
                    {{ template.name }}
                </h3>
                <h4
                    v-tooltip.top="{
                        value: template.users[0].username,
                        pt: { root: 'font-nunito' },
                    }"
                    class="-mt-1 truncate text-base text-natural-600 dark:text-natural-300"
                >
                    <T key-name="template.by" /><span
                        class="cursor-pointer hover:text-calypso-600 hover:underline"
                        @click.stop="isProfileDialogVisible = true"
                        >{{ template.users[0].username }}</span
                    >
                </h4>
            </div>
            <div
                class="ml-1 flex gap-x-2 text-text dark:text-natural-50 sm:ml-4 sm:gap-x-4"
            >
                <div
                    v-tooltip.top="{
                        value: template.destination,
                        pt: { root: 'font-nunito' },
                    }"
                    class="flex min-w-28 max-w-28 flex-row items-center gap-x-0.5 sm:gap-x-2"
                >
                    <i
                        class="pi pi-map-marker text-base text-calypso-600 dark:text-calypso-400"
                    />
                    <h5 class="truncate text-base">
                        {{ template.destination }}
                    </h5>
                </div>
                <div class="flex flex-row items-center gap-x-0.5 sm:gap-x-2">
                    <i
                        class="pi pi-calendar text-base text-calypso-600 dark:text-calypso-400"
                    />
                    <h5 class="truncate text-base">
                        {{ template.length }}
                        <T
                            :key-name="
                                template.length === 1
                                    ? 'template.day'
                                    : 'template.days'
                            "
                        />
                    </h5>
                </div>
            </div>
            <button class="ml-auto mr-2 hidden items-center xs:flex sm:mr-4">
                <i
                    class="pi pi-arrow-up-right-and-arrow-down-left-from-center text-lg text-natural-500 hover:text-calypso-950 dark:text-natural-400 dark:hover:text-natural-50"
                />
            </button>
        </div>
        <div class="dialogs">
            <JourneyIdDialogsProfileDialog
                :visible="isProfileDialogVisible"
                :username="template.users[0].username"
                :displayname="template.users[0].display_name"
                @close="isProfileDialogVisible = false"
            />
        </div>
    </div>
</template>
