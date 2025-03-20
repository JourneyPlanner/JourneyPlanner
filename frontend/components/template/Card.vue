<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import type { MenuItemCommandEvent } from "primevue/menuitem";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    template: {
        type: Object as PropType<Template>,
        required: true,
    },
    displayedInProfile: {
        type: Boolean,
        default: false,
    },
    isCurrentUser: {
        type: Boolean,
        default: false,
    },
    openedFromBusiness: {
        type: Boolean,
        default: false,
    },
});

const { t } = useTranslate();
const toast = useToast();
const client = useSanctumClient();

const isProfileDialogVisible = ref(false);
const router = useRouter();

const confirm = useConfirm();
const isCreateTemplateVisible = ref(false);
const menu = ref();
const toggle = (event: Event) => {
    menu.value.toggle(event);
};
const emit = defineEmits(["templateDeleted", "templateEdited", "openTemplate"]);

const templateItems = ref([
    {
        label: t.value("dashboard.options.header"),
        items: [
            {
                label: t.value("dashboard.options.edit"),
                icon: "pi pi-pencil",
                className: "text-natural-50",
                command: () => {
                    router.push({
                        path: `/template/${props.template.id}/edit`,
                    });
                },
            },
            {
                label: t.value("dashboard.options.delete"),
                icon: "pi pi-trash",
                command: ($event: MenuItemCommandEvent) => {
                    confirmDelete($event.originalEvent);
                },
            },
        ],
    },
]);

const businessTemplateItems = ref([
    {
        label: t.value("dashboard.options.header"),
        items: [
            {
                label: t.value("dashboard.options.edit"),
                icon: "pi pi-pencil",
                className: "text-natural-50",
                command: () => {
                    router.push({
                        path: `/template/${props.template.id}/edit`,
                    });
                },
            },
            {
                label: t.value("dashboard.options.delete"),
                icon: "pi pi-trash",
                command: ($event: MenuItemCommandEvent) => {
                    confirmDelete($event.originalEvent);
                },
            },
            {
                label: t.value("business.template.show"),
                icon: "pi pi-eye",
                className: "text-natural-50",
                command: () => {
                    emit("openTemplate", props.template.id);
                },
            },
        ],
    },
]);

const confirmDelete = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        group: "username",
        header: t.value("dashboard.delete.header"),
        message: t.value("template.delete.confirm"),
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline dark:text-natural-200",
        acceptClass:
            "text-mahagony-500 dark:text-mahagony-400 hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("template.delete"),
        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("common.toast.info.heading"),
                detail: t.value("delete.template.toast.message"),
                life: 3000,
            });
            deleteTemplate(props?.template?.id);
        },
    });
};

async function deleteTemplate(id: string) {
    await client(`/api/journey/${id}`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("delete.template.toast.success.heading"),
                    detail: t.value("delete.template.toast.success"),
                    life: 6000,
                });
                emit("templateDeleted", id);
            }
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

function handleUserClick() {
    if (!props.displayedInProfile && !props.template?.creator?.business) {
        isProfileDialogVisible.value = true;
    } else if (props.template?.creator?.business) {
        router.push(`/business/${props.template?.creator?.username}`);
    }
}
</script>

<template>
    <div
        class="min-w-72 rounded-xl border-2 border-natural-200 bg-natural-50 text-text hover:cursor-pointer hover:border-calypso-400 dark:border-natural-800 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
        role="button"
        tabindex="0"
        @click="$emit('openTemplate', template?.id)"
        @keyup.enter="$emit('openTemplate', template?.id)"
    >
        <Menu
            id="overlay_menu"
            ref="menu"
            :model="openedFromBusiness ? businessTemplateItems : templateItems"
            class="z-[502] bg-natural-50 dark:bg-natural-800"
            :popup="true"
            :pt="{
                root: {
                    class: 'font-nunito bg-natural-50 dark:bg-natural-800',
                },
                menuitem: {
                    class: 'bg-natural-50 dark:bg-natural-800 hover:bg-dandelion-100 dark:hover:bg-pesto-600 rounded-md text-text dark:text-natural-50',
                },
                content: {
                    class: 'bg-natural-50 dark:bg-natural-800 hover:bg-dandelion-100 dark:hover:bg-pesto-600 rounded-md text-text dark:text-natural-50',
                },
                submenuHeader: {
                    class: 'text-natural-500 dark:text-natural-100 bg-natural-50 dark:bg-natural-800',
                },
                label: { class: 'text-text dark:text-natural-50' },
                icon: { class: 'text-text dark:text-natural-50' },
            }"
        />
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
            <div class="flex">
                <h3
                    v-tooltip.top="{
                        value: template?.name,
                        pt: { root: 'font-nunito' },
                    }"
                    class="w-full truncate text-xl font-medium"
                >
                    <div
                        class="block overflow-hidden overflow-ellipsis text-nowrap"
                        :class="!isCurrentUser ? 'w-full' : 'w-11/12'"
                    >
                        {{ template?.name }}
                    </div>
                </h3>
                <div class="flex items-center pl-2">
                    <span
                        v-if="template?.creator?.business"
                        v-tooltip.top="{
                            value: t('template.created.business'),
                            pt: { root: 'font-nunito text-center' },
                        }"
                        class="pi pi-verified ml-auto justify-end text-xl text-calypso-600 dark:text-calypso-400"
                    />
                </div>
                <Button
                    v-if="isCurrentUser || openedFromBusiness"
                    type="button"
                    icon="pi pi-ellipsis-v"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                    class="ml-auto justify-end"
                    @click.stop="toggle"
                />
            </div>
            <h4
                v-tooltip.top="{
                    value: template?.creator?.username,
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
                    @click.stop="handleUserClick"
                    >{{ template?.creator?.username }}</span
                >
            </h4>
            <div id="template-details" class="mt-2">
                <div
                    v-tooltip.top="{
                        value: template?.destination,
                        pt: { root: 'font-nunito' },
                    }"
                    class="flex flex-row items-center gap-x-1"
                >
                    <i class="pi pi-map-marker text-lg text-calypso-600" />
                    <h5 class="truncate text-lg">
                        {{ template?.destination }}
                    </h5>
                </div>
                <div class="flex flex-row items-center gap-x-1">
                    <i class="pi pi-calendar text-lg text-calypso-600" />
                    <h5 class="truncate text-lg">
                        {{ template?.length }}
                        <T
                            :key-name="
                                template?.length === 1
                                    ? 'template.day'
                                    : 'template.days'
                            "
                        />
                    </h5>
                    <div class="ml-auto flex items-center gap-x-1">
                        <i class="pi pi-star text-lg text-calypso-600" />
                        <h5 class="truncate text-lg">
                            {{
                                Math.round(template?.average_rating * 100) / 100
                            }}
                            ({{ template?.total_ratings }})
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="dialogs">
            <JourneyIdDialogsProfileDialog
                :visible="isProfileDialogVisible"
                :username="template?.creator?.username"
                :displayname="template?.creator?.display_name"
                @close="isProfileDialogVisible = false"
            />
            <JourneyIdDialogsCreateTemplateDialog
                :is-create-template-visible="isCreateTemplateVisible"
                :update-template="true"
                @close-template-dialog="isCreateTemplateVisible = false"
            />
        </div>
    </div>
</template>
