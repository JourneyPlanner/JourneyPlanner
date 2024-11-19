<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import type { MenuItemCommandEvent } from "primevue/menuitem";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    destination: { type: String, required: true },
    from: { type: Date, required: true },
    to: { type: Date, required: true },
    role: { type: Number, required: true },
});

const emit = defineEmits(["journeyDeleted", "journeyEdited", "journeyLeave"]);

const { t } = useTranslate();
const confirm = useConfirm();
const toast = useToast();
const client = useSanctumClient();

const roleType = computed(() => {
    return props.role === 1 ? "dashboard.role.guide" : "dashboard.role.member";
});
const link = computed(() => {
    return "/journey/" + props.id;
});

const isEditMenuVisible = ref(false);
const menu = ref();

const toggle = (event: Event) => {
    menu.value.toggle(event);
};

const confirmDelete = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        group: "dashboard",
        header: t.value("dashboard.delete.header"),
        message: t.value("dashboard.delete.confirm"),
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline dark:text-natural-200",
        acceptClass:
            "text-mahagony-500 dark:text-mahagony-400 hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("common.delete"),
        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("common.toast.info.heading"),
                detail: t.value("delete.journey.toast.message"),
                life: 3000,
            });
            isEditMenuVisible.value = false;
            deleteJourney(props.id);
        },
    });
};

const confirmLeave = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        group: "dashboard",
        header: t.value("journey.leave.header"),
        message: t.value("journey.leave.message"),
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline",
        acceptClass:
            "text-mahagony-500 dark:text-mahagony-400 hover:underline font-bold",
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("journey.leave"),
        accept: () => {
            toast.add({
                severity: "info",
                summary: t.value("common.toast.info.heading"),
                detail: t.value("leave.journey.toast.message"),
                life: 3000,
            });
            leaveJourney();
        },
    });
};

/**
 * delete the journey
 */
async function deleteJourney(id: string) {
    await client(`/api/journey/${id}`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("delete.journey.toast.success.heading"),
                    detail: t.value("delete.journey.toast.success"),
                    life: 6000,
                });
                emit("journeyDeleted", id);
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

/**
 * leave the journey
 */
async function leaveJourney() {
    await client(`/api/journey/${props.id}/leave`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("leave.journey.toast.success.heading"),
                    detail: t.value("leave.journey.toast.success.detail"),
                    life: 3000,
                });
                emit("journeyLeave", props.id);
            }
        },
        async onResponseError({ response }) {
            if (response.status === 403) {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("leave.journey.toast.error"),
                    life: 6000,
                });
            } else {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            }
        },
    });
}

const itemsJourneyGuide = ref([
    {
        label: t.value("dashboard.options.header"),
        items: [
            {
                label: t.value("dashboard.options.edit"),
                icon: "pi pi-pencil",
                className: "text-natural-50",
                command: () => {
                    isEditMenuVisible.value = true;
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
                label: t.value("dashboard.options.leave"),
                icon: "pi pi-sign-out",
                command: ($event: MenuItemCommandEvent) => {
                    confirmLeave($event.originalEvent);
                },
            },
        ],
    },
]);

const itemsJourneyMember = ref([
    {
        label: t.value("dashboard.options.header"),

        items: [
            {
                label: t.value("dashboard.options.leave"),
                icon: "pi pi-sign-out",
                command: ($event: MenuItemCommandEvent) => {
                    confirmLeave($event.originalEvent);
                },
            },
        ],
    },
]);
</script>

<template>
    <div>
        <div
            id="journey-desktop"
            class="relative hidden hover:cursor-pointer lg:block"
        >
            <SvgDashboardJourneyCard :link="link" class="dark:hidden" />
            <SvgDashboardJourneyCardDark
                :link="link"
                class="hidden dark:block"
            />
            <div class="absolute left-10 top-6">
                <div class="flex w-56 justify-between">
                    <NuxtLink
                        :to="link"
                        class="w-full overflow-hidden overflow-ellipsis whitespace-nowrap"
                    >
                        <h1
                            v-tooltip.top="{
                                value: name,
                                pt: { root: 'font-nunito' },
                            }"
                            class="overflow-hidden overflow-ellipsis whitespace-nowrap text-2xl font-semibold"
                        >
                            {{ name }}
                        </h1>
                    </NuxtLink>
                    <button
                        class="pi pi-ellipsis-v justify-end"
                        aria-haspopup="true"
                        aria-controls="overlay_menu"
                        @click="toggle"
                    />
                </div>
                <NuxtLink :to="link">
                    <h2
                        v-tooltip.bottom="{
                            value: destination,
                            pt: { root: 'font-nunito' },
                        }"
                        class="-mt-1.5 w-56 overflow-hidden overflow-ellipsis whitespace-nowrap text-xl font-medium"
                    >
                        {{ destination }}
                    </h2>
                    <div
                        class="mt-1.5 flex w-56 items-center gap-x-2 border-b-2 border-dashed border-natural-200 text-sm dark:border-natural-400"
                    >
                        <span class="text-calypso-400">
                            <i class="pi pi-calendar" />
                        </span>
                        <span class="text-text dark:text-natural-50"
                            >{{ format(from, "dd/MM/yyyy") }}-{{
                                format(to, "dd/MM/yyyy")
                            }}</span
                        >
                    </div>
                    <div
                        class="mt-1 flex w-56 items-center gap-x-2 border-b-2 border-dashed border-natural-200 text-sm dark:border-natural-400"
                    >
                        <span class="text-calypso-400">
                            <i class="pi pi-user" />
                        </span>
                        <span class="text-text dark:text-natural-50">
                            <T :key-name="roleType" />
                        </span>
                    </div>
                </NuxtLink>
            </div>
        </div>
        <div
            id="journey-mobile"
            class="h-[7.5rem] min-w-36 rounded-md border border-calypso-300 bg-calypso-50 bg-opacity-20 p-1 dark:bg-gothic-800 md:p-2 lg:hidden"
        >
            <div class="flex justify-between">
                <NuxtLink
                    :to="link"
                    class="overflow-hidden overflow-ellipsis whitespace-nowrap"
                >
                    <h1
                        v-tooltip.top="{
                            value: name,
                            pt: { root: 'font-nunito' },
                        }"
                        class="overflow-hidden overflow-ellipsis whitespace-nowrap text-xl font-semibold"
                    >
                        {{ name }}
                    </h1>
                </NuxtLink>
                <Button
                    type="button"
                    icon="pi pi-ellipsis-v"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                    class="justify-end"
                    @click="toggle"
                />
            </div>
            <NuxtLink :to="link">
                <h2
                    v-tooltip.bottom="{
                        value: destination,
                        pt: { root: 'font-nunito' },
                    }"
                    class="-mt-1.5 overflow-hidden overflow-ellipsis whitespace-nowrap text-lg font-medium"
                >
                    {{ destination }}
                </h2>
                <h3
                    class="mt-1.5 flex items-center gap-x-2 border-b-2 border-dashed border-natural-200 text-sm dark:border-natural-400"
                >
                    <span class="text-calypso-400">
                        <i class="pi pi-calendar" />
                    </span>
                    <span
                        class="truncate whitespace-nowrap text-text dark:text-natural-50"
                        >{{ format(from, "dd/MM/yy") }} -
                        {{ format(to, "dd/MM/yy") }}</span
                    >
                </h3>
                <h3
                    class="mt-1 flex items-center gap-x-2 border-b-2 border-dashed border-natural-200 text-sm dark:border-natural-400"
                >
                    <span class="text-calypso-400">
                        <i class="pi pi-user" />
                    </span>
                    <span class="text-text dark:text-natural-50">
                        <T :key-name="roleType" />
                    </span>
                </h3>
            </NuxtLink>
        </div>
        <Menu
            id="overlay_menu"
            ref="menu"
            :model="props.role === 1 ? itemsJourneyGuide : itemsJourneyMember"
            class="bg-natural-50 dark:bg-natural-800"
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
        <JourneyEditDialog
            :id="props.id"
            :is-journey-dialog-visible="isEditMenuVisible"
            :name="props.name"
            :destination="props.destination"
            :from="props.from"
            :to="props.to"
            @close-edit-journey-dialog="isEditMenuVisible = false"
            @delete-journey="deleteJourney"
            @journey-edited="
                (journey, id) => emit('journeyEdited', journey, id)
            "
        />
    </div>
</template>
