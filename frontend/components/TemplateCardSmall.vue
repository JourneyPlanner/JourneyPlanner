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
        class="rounded-md border-2 border-natural-200 hover:cursor-pointer hover:border-calypso-400"
        @click="$emit('openTemplate', template.id)"
    >
        <div class="my-1 ml-1 border-l-2 border-calypso-600 pl-1">
            <h3
                v-tooltip.top="{
                    value: template.name,
                    pt: { root: 'font-nunito' },
                }"
                class="truncate text-base font-medium"
            >
                {{ template.name }}
            </h3>
            <h4
                v-tooltip.top="{
                    value: template.users[0].username,
                    pt: { root: 'font-nunito' },
                }"
                class="-mt-1 truncate text-base text-natural-600"
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
                    <i class="pi pi-map-marker text-sm text-calypso-600" />
                    <h5 class="truncate text-sm">{{ template.destination }}</h5>
                </div>
                <div class="flex flex-row items-center gap-x-1">
                    <i class="pi pi-calendar text-sm text-calypso-600" />
                    <h5 class="truncate text-sm">
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
