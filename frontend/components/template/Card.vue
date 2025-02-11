<script setup lang="ts">
defineProps({
    template: {
        type: Object as PropType<Template>,
        required: true,
    },
    displayedInProfile: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["openTemplate"]);

const isProfileDialogVisible = ref(false);
</script>

<template>
    <div
        class="rounded-xl border-2 border-natural-200 bg-natural-50 text-text hover:cursor-pointer hover:border-calypso-400 dark:border-natural-800 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
        role="button"
        tabindex="0"
        @click="$emit('openTemplate', template.id)"
        @keyup.enter="$emit('openTemplate', template.id)"
    >
        <div
            class="flex h-6 w-full items-center rounded-t-[0.7rem] bg-calypso-300 px-2 dark:bg-calypso-700"
        >
            <div
                class="w-full border border-dashed border-natural-50 dark:border-calypso-300"
            />
            <SvgAirplaneIcon
                class="h-5 pl-1 text-natural-50 dark:border-calypso-300"
            />
        </div>
        <div class="px-2.5 pb-2 pt-2">
            <h3
                v-tooltip.top="{
                    value: template.name,
                    pt: { root: 'font-nunito' },
                }"
                class="truncate text-xl font-medium"
            >
                {{ template.name }}
            </h3>
            <h4
                v-tooltip.top="{
                    value: template.users[0].username,
                    pt: { root: 'font-nunito' },
                }"
                class="-mt-1 truncate text-xl text-natural-600 dark:text-natural-300"
            >
                <T key-name="template.by" /><span
                    :class="
                        !displayedInProfile
                            ? 'cursor-pointer hover:text-calypso-600 hover:underline'
                            : ''
                    "
                    @click.stop="
                        !displayedInProfile
                            ? (isProfileDialogVisible = true)
                            : ''
                    "
                    >{{ template.users[0].username }}</span
                >
            </h4>
            <div id="template-details" class="mt-2">
                <div
                    v-tooltip.top="{
                        value: template.destination,
                        pt: { root: 'font-nunito' },
                    }"
                    class="flex flex-row items-center gap-x-1"
                >
                    <i class="pi pi-map-marker text-lg text-calypso-600" />
                    <h5 class="truncate text-lg">{{ template.destination }}</h5>
                </div>
                <div class="flex flex-row items-center gap-x-1">
                    <i class="pi pi-calendar text-lg text-calypso-600" />
                    <h5 class="truncate text-lg">
                        {{ template.length }}
                        <T
                            :key-name="
                                template.length === 1
                                    ? 'template.day'
                                    : 'template.days'
                            "
                        />
                    </h5>
                    <div class="ml-auto flex items-center gap-x-1">
                        <i class="pi pi-star text-lg text-calypso-600" />
                        <h5 class="truncate text-lg">
                            {{ template.average_rating }}
                            ({{ template.total_ratings }})
                        </h5>
                    </div>
                </div>
            </div>
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
