<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    isVisible: { type: Boolean, required: true },
    businessSlug: { type: String, required: true },
});

const emit = defineEmits(["close", "changedTemplates"]);
const toast = useToast();
const route = useRoute();
const { t } = useTranslate();
const client = useSanctumClient();
const visible = ref();
const templatesLoader = ref<HTMLElement | undefined>();
const showMoreTemplates = ref(false);
const reloadData = ref(false);
const maxDisplayedTemplates = 8;
const {
    data: templates,
    moreDataAvailable: moreTemplatesAvailable,
    toggle: toggleTemplates,
    toggleText: toggleTextTemplates,
} = await useInfiniteScroll<Template>({
    loader: templatesLoader,
    showMoreData: showMoreTemplates,
    reloadData: reloadData,
    showMoreDataText: t.value("subdomain.activities.showMore"),
    showLessDataText: t.value("subdomain.activities.showLess"),
    identifier: "business-templates-private",
    apiEndpoint: `/api/business/${props.businessSlug}/templates`,
    params: {
        per_page: 8,
        private: 1,
    },
});

const checkedItems = ref(new Map());

const changedItems = ref(new Map());

watch(templates.value, (newTemplates) => {
    newTemplates.forEach((element) => {
        checkedItems.value.set(element.id, element.visible == 1 ? true : false);
    });
});

watch(
    () => props.isVisible,
    (value) => {
        visible.value = value;
    },
);

const close = () => {
    emit("close");
};

async function changeVisibleTemplates() {
    const visibleTemplates: { templates: object[] } = { templates: [] };
    changedItems.value.forEach((value, key) => {
        visibleTemplates.templates.push({
            template_id: key,
            visible: value,
        });
    });
    await client(`/api/business/${route.params.slug}/templates `, {
        method: "POST",
        body: visibleTemplates,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value(
                        "form.input.activity.edit.toast.success.heading",
                    ),
                    detail: t.value(
                        "form.input.activity.edit.toast.success.detail",
                    ),
                    life: 6000,
                });
                emit("changedTemplates");
                close();
            }
        },
        async onRequestError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
    });
}

function toggleTemplateAvailability(id: string) {
    checkedItems.value.set(id, !checkedItems.value.get(id));
    changedItems.value.set(id, checkedItems.value.get(id));
}
</script>
<template>
    <div>
        <Dialog
            v-model:visible="visible"
            modal
            :block-scroll="true"
            :auto-z-index="true"
            :draggable="false"
            close-on-escape
            dismissable-mask
            class="mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-full"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 max-sm:collapse',
                },
                header: {
                    class: 'flex gap-x-3 pb-2 font-nunito bg-background dark:bg-background-dark px-4 sm:px-7',
                },
                title: {
                    class: 'font-nunito text-2xl font-semibold text-text dark:text-natural-50 -pt-1',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-4 sm:px-7 h-full',
                },
                footer: { class: 'h-0' },
                closeButton: {
                    class: 'hidden collapse ',
                },
                closeButtonIcon: {
                    class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse bg-natural-50',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex w-full items-center">
                    <span
                        class="pi pi-times z-20 cursor-pointer text-2xl text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50"
                        @click="close"
                    />
                    <div
                        class="flex-grow-5 mx-3 text-2xl font-semibold text-text dark:text-natural-50"
                    >
                        <T key-name="business.edit.choose.templates" />
                    </div>
                    <div class="h-0.5 flex-grow bg-calypso-400" />
                </div>
            </template>
            <div id="template-section">
                <TransitionGroup
                    name="fade"
                    tag="div"
                    class="relative mt-2 grid w-full grid-cols-2 gap-2 md:grid-cols-2 md:gap-4 lg:grid-cols-3 lg:gap-1 xl:grid-cols-4"
                >
                    <div
                        v-for="(template, index) in templates"
                        v-show="
                            showMoreTemplates || index < maxDisplayedTemplates
                        "
                        :key="template.id"
                        class="cursor-pointer rounded-xl px-2 py-2"
                        :class="{
                            'bg-natural-100': checkedItems.get(template.id),
                        }"
                        @click="toggleTemplateAvailability(template.id)"
                    >
                        <label
                            class="relative flex w-fit cursor-pointer items-center rounded-md p-1"
                        >
                            <input
                                :id="index.toString()"
                                :checked="checkedItems.get(template.id)"
                                type="checkbox"
                                class="peer relative h-5 w-5 cursor-pointer appearance-none rounded-md border-2 border-calypso-400 bg-natural-50 transition-all checked:border-calypso-400 checked:bg-calypso-400 dark:bg-natural-800 checked:dark:bg-calypso-500"
                            />
                            <div
                                class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-natural-50 opacity-0 transition-opacity peer-checked:opacity-100"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5 fill-natural-50"
                                    viewBox="0 0 20 20"
                                    stroke-width="1"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                        </label>
                        <TemplateCard
                            class="md:block"
                            :template="template"
                            :displayed-in-profile="false"
                            :opened-from-business="true"
                        />
                    </div>
                </TransitionGroup>
                <div v-if="templates.length === 0" class="col-span-full">
                    <T key-name="subdomain.template.none" />
                </div>
                <div ref="templatesLoader" class="col-span-full">
                    <div v-if="moreTemplatesAvailable && showMoreTemplates">
                        <div class="flex justify-center">
                            <ProgressSpinner class="w-10" />
                        </div>
                        <div class="flex justify-center italic">
                            <T key-name="subdomain.templates.loading" />
                        </div>
                    </div>
                </div>
                <div
                    v-if="templates.length > 0"
                    class="mt-4 flex justify-center"
                >
                    <button
                        class="flex flex-col items-center justify-center text-text dark:text-natural-50"
                        @click="toggleTemplates"
                    >
                        <span>{{ toggleTextTemplates }}</span>
                        <span
                            class="pi mt-1"
                            :class="
                                showMoreTemplates
                                    ? 'pi-chevron-up order-first mb-1'
                                    : 'pi-chevron-down'
                            "
                        />
                    </button>
                </div>
                <div
                    class="flex justify-end"
                    :class="showMoreTemplates ? '-mt-7' : '-mt-11'"
                >
                    <Button
                        type="button"
                        :label="t('common.save')"
                        icon="pi pi-save"
                        :pt="{
                            root: {
                                class: 'flex items-center justify-center',
                            },
                            label: {
                                class: 'display-block flex-none font-nunito',
                            },
                        }"
                        class="mt-auto flex h-9 w-48 flex-row justify-center rounded-xl border-2 border-atlantis-400 bg-natural-50 text-center text-text hover:bg-atlantis-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-atlantis-30040"
                        @click="changeVisibleTemplates"
                    />
                </div>
            </div>
        </Dialog>
    </div>
</template>
