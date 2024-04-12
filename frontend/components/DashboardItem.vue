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

const emit = defineEmits(["journeyDeleted", "journeyEdited"]);

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
        group: "delete",
        target: event.currentTarget as HTMLElement,
        header: t.value("dashboard.delete.header"),
        message: t.value("dashboard.delete.confirm"),
        icon: "pi pi-exclamation-triangle",
        rejectClass: "hover:underline",
        acceptClass:
            "text-error dark:text-error-dark hover:underline font-bold",
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

const itemsJourneyGuide = ref([
    {
        label: t.value("dashboard.options.header"),
        items: [
            {
                label: t.value("dashboard.options.edit"),
                icon: "pi pi-pencil",
                className: "text-cta-border",
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
            /* leave for JP-34
            {
                label: t.value('dashboard.options.leave'),
                icon: 'pi pi-sign-out',
                command: ($event: MenuItemCommandEvent) => {
                    confirmLeave($event.originalEvent);
                }
            },
            */
        ],
    },
]);

const itemsJourneyMember = ref([
    {
        label: t.value("dashboard.options.header"),

        items: [
            { label: "No options available yet" },
            /* leave for JP-34
            {
                label: t.value('dashboard.options.leave'),
                icon: 'pi pi-sign-out'
            },*/
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
            .required(t.value("form.error.journey.name"))
            .label(t.value("form.input.journey.name")),
        destination: yup
            .string()
            .required(t.value("form.error.journey.destination"))
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
    loadingEdit.value = false;
});
</script>

<template>
    <div>
        <Toast class="w-3/4 sm:w-auto" />
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
                        class="mt-1.5 w-56 border-b-2 border-dashed border-border-grey text-sm dark:border-input-placeholder"
                    >
                        <span class="mr-1 text-border">
                            <T key-name="dashboard.date" />
                        </span>
                        <span class="-mb-1 text-text dark:text-white"
                            >{{ format(from, "dd/MM/yyyy") }}-{{
                                format(to, "dd/MM/yyyy")
                            }}</span
                        >
                    </div>
                    <h3
                        class="mt-1 w-56 border-b-2 border-dashed border-border-grey text-sm dark:border-input-placeholder"
                    >
                        <span class="mr-1 text-border">
                            <T key-name="dashboard.role" />
                        </span>
                        <span class="text-text dark:text-white">
                            <T :key-name="roleType" />
                        </span>
                    </h3>
                </NuxtLink>
            </div>
        </div>
        <div
            id="journey-mobile"
            class="h-32 min-w-36 rounded-md border border-border bg-card p-1 dark:bg-card-dark md:p-2 lg:hidden"
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
                    class="mt-1.5 border-b-2 border-dashed border-border-grey text-xs dark:border-input-placeholder md:text-sm"
                >
                    <span class="mr-0.5 text-border">
                        <T key-name="dashboard.date" />
                    </span>
                    <br class="sm:hidden" />
                    <span class="whitespace-nowrap text-text dark:text-white"
                        >{{ format(from, "dd/MM/yyyy") }} -
                        {{ format(to, "dd/MM/yyyy") }}</span
                    >
                </h3>
                <h3
                    class="mt-1 border-b-2 border-dashed border-border-grey text-xs dark:border-input-placeholder md:text-sm"
                >
                    <span class="mr-0.5 text-border">
                        <T key-name="dashboard.role" />
                    </span>
                    <span class="text-text dark:text-white">
                        <T :key-name="roleType" />
                    </span>
                </h3>
            </NuxtLink>
        </div>
        <Menu
            id="overlay_menu"
            ref="menu"
            :model="props.role === 1 ? itemsJourneyGuide : itemsJourneyMember"
            class="bg-input dark:bg-input-dark"
            :popup="true"
            :pt="{
                root: { class: 'font-nunito bg-input dark:bg-input-dark' },
                menuitem: {
                    class: 'bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white',
                },
                content: {
                    class: 'bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white',
                },
                submenuHeader: {
                    class: 'text-input-placeholder dark:text-text-light-dark bg-input dark:bg-input-dark',
                },
                label: { class: 'text-text dark:text-white' },
                icon: { class: 'text-text dark:text-white' },
            }"
        />

        <Dialog
            v-model:visible="isEditMenuVisible"
            modal
            :header="t('dashboard.edit.header')"
            :style="{ width: '30rem' }"
            class="bg-input dark:bg-input-dark"
            :pt="{
                root: {
                    class: 'font-nunito text-text bg-input dark:bg-input-dark',
                },
                header: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-white',
                },
                title: { class: 'text-2xl' },
                content: {
                    class: 'bg-input dark:bg-input-dark text-text dark:text-white',
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
                        <FormInput
                            id="journey-destination"
                            name="destination"
                            translation-key="form.input.journey.destination"
                            class="my-0 mb-1 w-2/3"
                            :prefill="props.destination"
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
                        class="h-9 w-40 rounded-xl border-2 border-cancel-border bg-input px-2 font-bold text-text hover:bg-cancel-bg dark:bg-input-dark dark:text-white dark:hover:bg-cancel-bg-dark"
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
                        class="flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-border-green-save bg-input text-center font-bold text-text hover:bg-fill-green-save dark:border-border-green-save-dark dark:bg-input-dark dark:text-white dark:hover:bg-fill-green-save-dark"
                    />
                </div>
            </form>
        </Dialog>
    </div>
</template>
