<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { useToast } from "primevue/usetoast";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    isJourneyDialogVisible: { type: Boolean, required: true },
    id: { type: String, required: true },
    name: { type: String, required: true },
    destination: { type: String, required: true },
    from: { type: Date, required: true },
    to: { type: Date, required: true },
});

const emit = defineEmits([
    "deleteJourney",
    "journeyEdited",
    "closeEditJourneyDialog",
]);

watch(
    () => props.isJourneyDialogVisible,
    (value) => {
        isDialogVisible.value = value;
    },
);

const isDialogVisible = ref(false);

const { t } = useTranslate();
const toast = useToast();
const client = useSanctumClient();
const confirm = useConfirm();

const loadingEdit = ref(false);

const close = () => {
    emit("closeEditJourneyDialog");
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
            close();
            emit("deleteJourney", props.id);
        },
    });
};

/**
 * form validation
 * when submitting form, fields are checked for validation
 */
const { handleSubmit } = useForm({
    validationSchema: yup.object({
        name: yup
            .string()
            .required(t.value("form.error.journey.name"))
            .matches(/^(?!\s+$).*/, t.value("form.error.journey.name"))
            .label(t.value("form.input.journey.name")),
        destination: yup
            .string()
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
                isDialogVisible.value = false;
                emit("journeyEdited", response._data.journey, props.id);
                close();
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
    <Dialog
        v-model:visible="isDialogVisible"
        modal
        :block-scroll="true"
        :auto-z-index="true"
        :draggable="false"
        close-on-escape
        dismissable-mask
        class="z-50 mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-8/12 md:w-6/12 md:rounded-xl lg:w-5/12 xl:w-4/12"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex gap-x-3 pb-2 font-nunito bg-background dark:bg-background-dark px-4 sm:px-7',
            },
            title: {
                class: 'font-nunito text-2xl font-semibold text-text dark:text-natural-50',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-4 sm:px-7 h-full',
            },
            footer: { class: 'h-0' },
            closeButtonIcon: {
                class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10 ',
            },
        }"
        @hide="close"
    >
        <template #header>
            <h3
                class="text-nowrap text-2xl font-medium text-text dark:text-natural-50"
            >
                <T key-name="dashboard.edit.header" />
            </h3>
            <span class="h-0.5 w-full bg-calypso-400 md:mr-2" />
        </template>
        <form @submit.prevent="onSave()">
            <div class="flex flex-col">
                <div
                    class="flex flex-row items-center justify-between lg:-mt-1"
                >
                    <label
                        for="journey-name"
                        class="text-lg font-bold text-text dark:text-natural-50 sm:text-xl"
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
                <div
                    class="mt-3.5 flex flex-row items-center justify-between lg:mt-3"
                >
                    <label
                        for="journey-destination"
                        class="text-lg font-bold text-text dark:text-natural-50 sm:text-xl"
                    >
                        <T key-name="form.input.journey.destination" />
                    </label>
                    <FormAddressInput
                        id="journey-destination"
                        name="destination"
                        translation-key="form.input.journey.destination"
                        class="my-0 mb-1 mt-1 w-2/3"
                        custom-class=".SearchIcon {visibility: hidden;} .Input {height: fit-content; font-weight: 700; padding-right: 0.625rem; padding-top: 0.625rem; padding-bottom: 0.625rem; padding-left: 0.625rem;} .Input::placeholder {font-family: Nunito; font-weight: 400; font-size: 1rem; line-height: 1.5rem;}"
                        :value="props.destination"
                    />
                </div>
                <div
                    class="mt-3.5 flex flex-row items-center justify-between lg:mt-3"
                >
                    <label
                        for="journey-range-calendar"
                        class="text-lg font-bold text-text dark:text-natural-50 sm:text-xl"
                    >
                        <T key-name="dashboard.edit.dates" />
                    </label>
                    <FormCalendar
                        id="journey-range-calendar"
                        name="range"
                        class="my-0 mr-0 mt-1 w-2/3"
                        translation-key="form.input.journey.dates"
                        :prefill="[new Date(props.from), new Date(props.to)]"
                    />
                </div>
            </div>
            <div class="mt-10 flex justify-between gap-2 lg:mt-8">
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
                    icon="pi pi-save"
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
</template>
