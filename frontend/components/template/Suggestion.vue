<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

defineProps({
    template: {
        type: Object as PropType<Template>,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
});

defineEmits(["openTemplate"]);

const { t } = useTranslate();
const isProfileDialogVisible = ref(false);
</script>

<template>
    <div
        class="w-full cursor-pointer border-b border-y-natural-100 first:border-t first:border-t-natural-100 hover:border-y-background dark:border-y-natural-400 first:dark:border-t-natural-400 dark:hover:border-y-background-dark"
        role="button"
        tabindex="0"
        @click="$emit('openTemplate', template.id)"
        @keyup.enter="$emit('openTemplate', template.id)"
    >
        <div class="py-1 hover:border-y-2 hover:border-calypso-400">
            <div
                class="ml-1.5 flex items-center border-l-2 border-natural-400 pl-1 hover:border-calypso-400 dark:border-natural-400 dark:hover:border-calypso-400"
            >
                <div class="ml-1.5 w-2/5 sm:w-1/3 lg:w-2/6 xl:w-1/3">
                    <h3
                        v-tooltip.top="{
                            value: template.name,
                            pt: { root: 'font-nunito' },
                        }"
                        class="truncate text-base font-medium text-text dark:text-natural-50"
                    >
                        {{ template.name }}
                    </h3>
                    <h4
                        v-tooltip.top="{
                            value: template.creator.username,
                            pt: { root: 'font-nunito' },
                        }"
                        class="-mt-1 truncate text-sm text-natural-600 dark:text-natural-300"
                    >
                        <T key-name="template.by" /><span
                            class="cursor-pointer hover:text-calypso-600 hover:underline"
                            @click.stop="isProfileDialogVisible = true"
                            >{{ template.creator.username }}</span
                        >
                    </h4>
                </div>
                <div
                    class="ml-3 flex flex-col gap-x-0.5 text-text dark:text-natural-50 xs:ml-[3vw] lg:ml-3 lg:flex-row lg:gap-x-4 xl:ml-5"
                >
                    <div
                        v-tooltip.top="{
                            value: template.destination,
                            pt: { root: 'font-nunito' },
                        }"
                        class="flex min-w-28 max-w-28 flex-row items-center gap-x-1 xs:min-w-36 xs:max-w-36 sm:min-w-32 sm:max-w-32 sm:gap-x-2 lg:min-w-24 lg:max-w-24 xl:min-w-32 xl:max-w-32 2xl:min-w-44 2xl:max-w-44"
                    >
                        <i
                            class="pi pi-map-marker text-sm text-calypso-400 dark:text-calypso-400 xl:text-base"
                        />
                        <h5 class="truncate text-sm xl:text-base">
                            {{ template.destination }}
                        </h5>
                    </div>
                    <div
                        v-tooltip.top="{
                            value:
                                template.length +
                                ' ' +
                                t(
                                    template.length === 1
                                        ? 'template.day'
                                        : 'template.days',
                                ),
                            pt: { root: 'font-nunito' },
                        }"
                        class="flex min-w-20 max-w-20 flex-row items-center gap-x-1 xs:min-w-36 xs:max-w-32 sm:min-w-32 sm:max-w-24 sm:gap-x-2 lg:min-w-24 lg:max-w-24 xl:min-w-32 xl:max-w-32 2xl:min-w-32 2xl:max-w-32"
                    >
                        <i
                            class="pi pi-calendar text-sm text-calypso-400 dark:text-calypso-400 xl:text-base"
                        />
                        <h5 class="truncate text-sm xl:text-base">
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
                <button class="ml-auto mr-2 flex items-center xs:mr-3 sm:mr-4">
                    <i
                        class="pi pi-arrow-up-right-and-arrow-down-left-from-center text-base text-natural-500 hover:text-calypso-950 dark:text-natural-400 dark:hover:text-natural-50"
                    />
                </button>
            </div>
        </div>
        <div class="dialogs">
            <JourneyIdDialogsProfileDialog
                :visible="isProfileDialogVisible"
                :username="template.creator.username"
                :displayname="template.creator.display_name"
                @close="isProfileDialogVisible = false"
            />
        </div>
    </div>
</template>
