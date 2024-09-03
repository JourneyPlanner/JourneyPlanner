<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import type { MenuItemCommandEvent } from "primevue/menuitem";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { useForm } from "vee-validate";
import { ref } from "vue";
import * as yup from "yup";

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
const loadingEdit = ref(false);

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
            deleteJourney();
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
async function deleteJourney() {
    await client(`/api/journey/${props.id}`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("delete.journey.toast.success.heading"),
                    detail: t.value("delete.journey.toast.success"),
                    life: 6000,
                });
                emit("journeyDeleted", props.id);
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

/**
 * form validation
 * when submitting form, fields are checked for validation
 */
const { handleSubmit } = useForm({
    validationSchema: yup.object({
        name: yup
            .string()
            .min(1, t.value("form.error.journey.name"))
            .required(t.value("form.error.journey.name"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.name"))
            .label(t.value("form.input.journey.name")),
        destination: yup
            .string()
            .min(1, t.value("form.error.journey.destination"))
            .required(t.value("form.error.journey.destination"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.destination"))
            .label(t.value("form.input.journey.destination")),
        range: yup
            .array()
            .of(
                yup
                    .date()
                    .required(t.value("form.error.journey.dates"))
                    .label(t.value("form.input.journey.dates")),
            )
            .required(t.value("form.error.journey.dates"))
            .label(t.value("form.input.journey.dates")),
    }),
});

/**
 * form save
 * when saving the edit form, values are checked for validation with handleSubmit
 * and then a journey object is created and sent to the backend
 */
const onSave = handleSubmit(async (values) => {
    const name = values.name;
    const destination = values.destination;
    const from = format(values.range[0], "yyyy-MM-dd");
    const to = format(values.range[1], "yyyy-MM-dd");

    loadingEdit.value = true;
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.toast.info.save"),
        life: 6000,
    });

    const journey = {
        name,
        destination,
        mapbox_full_address: values.mapbox?.properties?.full_address,
        mapbox_id: values.mapbox?.properties?.mapbox_id,
        from,
        to,
    };

    await client(`/api/journey/${props.id}`, {
        method: "PUT",
        body: journey,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("edit.journey.toast.success.heading"),
                    detail: t.value("edit.journey.toast.success"),
                    life: 6000,
                });
                emit("journeyEdited", journey, props.id);
                isEditMenuVisible.value = false;
            }
            loadingEdit.value = false;
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
            loadingEdit.value = false;
        },
    });
});
</script>

<template>
    <div>
        <div
            id="journey-desktop"
            class="relative hidden hover:cursor-pointer lg:block"
        >
            <SvgDashboardJourney :link="link" class="dark:hidden" />
            <SvgDashboardJourneyDark :link="link" class="hidden dark:block" />
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
                        class="mt-1.5 w-56 border-b-2 border-dashed border-natural-200 text-sm dark:border-natural-400"
                    >
                        <span class="mr-1 text-calypso-400">
                            <T key-name="dashboard.date" />
                        </span>
                        <span class="-mb-1 text-text dark:text-natural-50"
                            >{{ format(from, "dd/MM/yyyy") }}-{{
                                format(to, "dd/MM/yyyy")
                            }}</span
                        >
                    </div>
                    <h3
                        class="mt-1 w-56 border-b-2 border-dashed border-natural-200 text-sm dark:border-natural-400"
                    >
                        <span class="mr-1 text-calypso-400">
                            <T key-name="dashboard.role" />
                        </span>
                        <span class="text-text dark:text-natural-50">
                            <T :key-name="roleType" />
                        </span>
                    </h3>
                </NuxtLink>
            </div>
        </div>
        <div
            id="journey-mobile"
            class="h-32 min-w-36 rounded-md border border-calypso-300 bg-calypso-50 bg-opacity-20 p-1 dark:bg-gothic-800 md:p-2 lg:hidden"
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
                    class="mt-1.5 border-b-2 border-dashed border-natural-200 text-xs dark:border-natural-400 md:text-sm"
                >
                    <span class="mr-0.5 text-calypso-400">
                        <T key-name="dashboard.date" />
                    </span>
                    <br class="sm:hidden" />
                    <span
                        class="whitespace-nowrap text-text dark:text-natural-50"
                        >{{ format(from, "dd/MM/yyyy") }} -
                        {{ format(to, "dd/MM/yyyy") }}</span
                    >
                </h3>
                <h3
                    class="mt-1 border-b-2 border-dashed border-natural-200 text-xs dark:border-natural-400 md:text-sm"
                >
                    <span class="mr-0.5 text-calypso-400">
                        <T key-name="dashboard.role" />
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

        <Dialog
            v-model:visible="isEditMenuVisible"
            close-on-escape
            modal
            :header="t('dashboard.edit.header')"
            :style="{ width: '30rem' }"
            class="bg-natural-50 dark:bg-natural-800"
            :pt="{
                root: {
                    class: 'font-nunito text-text bg-natural-50 dark:bg-natural-800',
                },
                header: {
                    class: 'bg-natural-50 dark:bg-natural-800 text-text dark:text-natural-50',
                },
                title: { class: 'text-2xl' },
                content: {
                    class: 'bg-natural-50 dark:bg-natural-800 text-text dark:text-natural-50',
                },
            }"
        >
            <form @submit.prevent="onSave()">
                <div class="flex flex-col">
                    <div class="flex flex-row items-center justify-between">
                        <label
                            for="journey-name"
                            class="text-base font-bold sm:text-xl"
                        >
                            <T key-name="form.input.journey.name" />
                        </label>
                        <FormInput
                            id="journey-name"
                            name="name"
                            translation-key="form.input.journey.name"
                            :prefill="props.name"
                            class="my-0 mb-1 w-2/3"
                        />
                    </div>
                    <div class="flex flex-row items-center justify-between">
                        <label
                            for="journey-destination"
                            class="text-base font-bold sm:text-xl"
                        >
                            <T key-name="form.input.journey.destination" />
                        </label>
                        <FormAddressInput
                            id="journey-destination"
                            name="destination"
                            translation-key="form.input.journey.destination"
                            class="my-0 mb-1 mt-5 w-2/3"
                            custom-class=".SearchIcon {visibility: hidden;} .Input {height: fit-content; font-weight: 700; padding-right: 0.625rem; padding-top: 0.625rem; padding-bottom: 0.625rem; padding-left: 0.625rem;} .Input::placeholder {font-family: Nunito; font-weight: 400; font-size: 1rem; line-height: 1.5rem;}"
                            :value="props.destination"
                            only
                        />
                    </div>
                    <div class="flex flex-row items-center justify-between">
                        <label
                            for="journey-range-calendar"
                            class="text-base font-bold sm:text-xl"
                        >
                            <T key-name="dashboard.edit.dates" />
                        </label>
                        <FormCalendar
                            id="journey-range-calendar"
                            name="range"
                            class="my-0 mr-0 mt-5 w-2/3"
                            translation-key="form.input.journey.dates"
                            :prefill="[
                                new Date(props.from),
                                new Date(props.to),
                            ]"
                        />
                    </div>
                </div>
                <div class="mt-10 flex justify-between gap-2">
                    <Button
                        type="button"
                        :label="t('common.delete')"
                        icon="pi pi-trash"
                        class="h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: { class: 'display-block flex-none' },
                        }"
                        @click="confirmDelete($event)"
                    />
                    <Button
                        type="submit"
                        :label="t('common.save')"
                        icon="pi pi-check"
                        :loading="loadingEdit"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: { class: 'display-block flex-none' },
                        }"
                        class="flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-atlantis-400 bg-natural-50 text-center font-bold text-text hover:bg-atlantis-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-atlantis-30040"
                    />
                </div>
            </form>
        </Dialog>
    </div>
</template>
