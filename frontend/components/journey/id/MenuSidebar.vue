<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const props = defineProps({
    isMenuSidebarVisible: {
        type: Boolean,
        required: true,
    },
    currUser: {
        type: Object as PropType<User>,
        required: true,
    },
});

const emit = defineEmits([
    "leave-journey",
    "journey-edited",
    "close",
    "open-unlock-dialog",
]);

const journeyStore = useJourneyStore();
const { t } = useTranslate();
const { isAuthenticated } = useSanctumAuth();

const isVisible = ref(props.isMenuSidebarVisible);
const isJourneyEditMenuVisible = ref(false);
const isCreateTemplateVisible = ref(false);

watch(
    () => props.isMenuSidebarVisible,
    (value) => {
        isVisible.value = value;
    },
);

function close() {
    emit("close");
}

function leave(event: Event) {
    emit("leave-journey", event);
}

function journeyEdited(journey: Journey) {
    emit("journey-edited", journey);
}

function openUnlockDialog() {
    emit("open-unlock-dialog");
}
</script>

<template>
    <div>
        <Sidebar
            id="menu-sidebar"
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
                header: { class: 'p-2 pl-3 grid grid-cols-12' },
                content: {
                    class: 'pl-3 pr-2 text-text dark:text-natural-50',
                },
                root: {
                    class: 'bg-background dark:bg-background-dark font-nunito',
                },
            }"
            @hide="close"
        >
            <template #header>
                <span class="h-0.5 w-full bg-calypso-400" />
                <div
                    class="col-span-3 flex w-full flex-row justify-center text-2xl font-medium text-text dark:text-natural-50"
                >
                    <h3>
                        <T key-name="journey.sidebar.menu" />
                    </h3>
                </div>
                <span
                    class="col-span-6 col-start-5 h-0.5 w-full bg-calypso-400"
                />
            </template>
            <div>
                <Accordion class="font-nunito text-xl text-text">
                    <AccordionTab
                        v-if="currUser?.role === 1 || !isAuthenticated"
                        :header="t('dashboard.edit.header')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: 'pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                        }"
                    >
                        <div>
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="dashboard.edit.detail" />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-dandelion-300 bg-natural-50 py-1 text-base font-semibold text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="
                                    isJourneyEditMenuVisible =
                                        !isJourneyEditMenuVisible
                                "
                            >
                                <T key-name="dashboard.edit.short" />
                            </button>
                        </div>
                    </AccordionTab>
                    <AccordionTab
                        v-if="currUser?.role === 1 || !isAuthenticated"
                        :header="t('journey.template.create')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: `pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50 ${!isAuthenticated ? 'blur-[1.75px]' : ''}`,
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50 relative',
                            },
                        }"
                    >
                        <div
                            class="relative"
                            :class="!isAuthenticated ? 'blur-[1.75px]' : ''"
                        >
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="journey.template.create.detail" />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-dandelion-300 bg-natural-50 py-1 text-base font-semibold text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="
                                    isAuthenticated
                                        ? (isCreateTemplateVisible = true)
                                        : openUnlockDialog()
                                "
                            >
                                <T key-name="journey.template.create" />
                            </button>
                        </div>

                        <div
                            v-if="!isAuthenticated"
                            class="absolute top-0 -mt-10 flex h-full w-full items-center justify-center"
                        >
                            <button
                                class="flex w-32 justify-center rounded-md border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 text-base font-medium text-text hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="openUnlockDialog"
                            >
                                <T key-name="journey.unlock.button" />
                            </button>
                        </div>
                    </AccordionTab>
                    <AccordionTab
                        :header="t('dashboard.options.leave')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: 'pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                        }"
                    >
                        <div>
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="journey.leave.detail" />
                                <T
                                    v-if="
                                        currUser?.role === 1 && isAuthenticated
                                    "
                                    key-name="journey.leave.detail.journeyguide"
                                />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-mahagony-500 bg-natural-50 py-1 text-base font-semibold text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                                @click="leave($event)"
                            >
                                <T key-name="journey.leave.short" />
                            </button>
                        </div>
                    </AccordionTab>
                    <AccordionTab
                        v-if="!isAuthenticated"
                        :header="t('dashboard.options.unlock')"
                        :pt="{
                            root: {
                                class: 'border-b-2 border-natural-300 dark:border-natural-700',
                            },
                            headerAction: {
                                class: 'pl-0 pr-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                            content: {
                                class: 'pl-0 bg-background dark:bg-background-dark text-text dark:text-natural-50',
                            },
                        }"
                    >
                        <div>
                            <p
                                class="text-base font-medium text-natural-600 dark:text-natural-300"
                            >
                                <T key-name="journey.unlock.detail" />
                            </p>
                            <button
                                class="mt-4 w-full rounded-lg border-2 border-dandelion-300 bg-natural-50 py-1 text-base font-semibold text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                                @click="openUnlockDialog"
                            >
                                <T key-name="journey.unlock.short" />
                            </button>
                        </div>
                    </AccordionTab>
                </Accordion>
            </div>
        </Sidebar>
        <JourneyEditDialog
            :id="journeyStore.getID()"
            :is-journey-dialog-visible="isJourneyEditMenuVisible"
            :name="journeyStore.getName()"
            :destination="journeyStore.getDestination()"
            :from="new Date(journeyStore.getFromDate())"
            :to="new Date(journeyStore.getToDate())"
            @close-edit-journey-dialog="isJourneyEditMenuVisible = false"
            @journey-edited="journeyEdited"
        />
        <JourneyIdDialogsTemplateDialog
            v-if="currUser?.role === 1"
            :is-create-template-visible="isCreateTemplateVisible"
            @close-template-dialog="isCreateTemplateVisible = false"
        />
    </div>
</template>
