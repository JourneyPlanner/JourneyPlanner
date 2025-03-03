<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

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

const router = useRouter();

defineEmits(["openTemplate"]);

const { t } = useTranslate();
const isProfileDialogVisible = ref(false);

function handleUserClick() {
    if (!props.template?.creator?.business) {
        isProfileDialogVisible.value = true;
    } else if (props.template?.creator?.business) {
        router.push(`/business/${props.template?.creator?.username}`);
    }
}
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
                <div class="ml-1.5 w-1/2 lg:w-1/3">
                    <div class="flex items-center">
                        <h3
                            v-tooltip.top="{
                                value: template.name,
                                pt: { root: 'font-nunito' },
                            }"
                            class="truncate pr-2 text-base font-medium text-text dark:text-natural-50"
                        >
                            {{ template.name }}
                        </h3>
                        <span
                            v-if="template?.creator?.business"
                            v-tooltip.top="{
                                value: t('template.created.business'),
                                pt: { root: 'font-nunito' },
                            }"
                            class="pi pi-verified justify-end text-lg text-calypso-600 dark:text-calypso-400"
                        />
                    </div>
                    <h4
                        v-tooltip.top="{
                            value: template.creator.username,
                            pt: { root: 'font-nunito' },
                        }"
                        class="-mt-1 truncate text-sm text-natural-600 dark:text-natural-300"
                    >
                        <T key-name="template.by" /><span
                            class="cursor-pointer hover:text-calypso-600 hover:underline"
                            @click.stop="handleUserClick"
                            >{{ template.creator.username }}</span
                        >
                    </h4>
                </div>
                <div
                    class="ml-3 w-1/2 gap-x-0.5 text-text dark:text-natural-50 max-lg:flex max-lg:flex-col xs:ml-[3vw] lg:ml-3 lg:grid lg:w-2/3 lg:grid-cols-9 lg:gap-4"
                >
                    <div
                        v-tooltip.top="{
                            value: template.destination,
                            pt: { root: 'font-nunito' },
                        }"
                        class="col-span-3 flex flex-row items-center gap-x-1"
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
                        class="col-span-3 flex flex-row items-center gap-x-1"
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
                    <div
                        class="col-span-2 flex min-w-20 max-w-20 flex-row items-center gap-x-1"
                    >
                        <i
                            class="pi pi-star text-sm text-calypso-400 dark:text-calypso-400 xl:text-base"
                        />
                        <h5 class="truncate text-sm xl:text-base">
                            {{ template.average_rating }}
                            ({{ template.total_ratings }})
                        </h5>
                    </div>

                    <button
                        class="col-span-1 ml-auto mr-2 hidden items-center xs:mr-3 sm:mr-4 lg:flex"
                    >
                        <i
                            class="pi pi-arrow-up-right-and-arrow-down-left-from-center text-base text-natural-500 hover:text-calypso-950 dark:text-natural-400 dark:hover:text-natural-50"
                        />
                    </button>
                </div>
                <button
                    class="col-span-1 ml-auto mr-2 items-center max-lg:flex xs:mr-3 sm:mr-4 lg:hidden"
                >
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
