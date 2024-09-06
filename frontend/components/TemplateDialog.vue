<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { ErrorMessage, Field } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    isCreateTemplateVisible: { type: Boolean, required: true },
});

const emit = defineEmits(["closeTemplateDialog"]);

const journey = useJourneyStore();
const { t } = useTranslate();
const toast = useToast();
const client = useSanctumClient();
const confirm = useConfirm();

const savingTemplate = ref(false);
const templateName = ref();
const isVisible = ref();

watch(
    () => props.isCreateTemplateVisible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    savingTemplate.value = false;
    templateName.value = "";
    emit("closeTemplateDialog");
};

const validationSchema = yup.object({
    templateName: yup
        .string()
        .required(t.value("form.input.required"))
        .matches(/^(?!\s+$).*/, t.value("form.input.required")),
    templateDescription: yup.string().nullable(),
});

const { handleSubmit: createTemplate } = useForm<Template>({
    validationSchema: validationSchema,
});

const onSubmitCreateTemplate = createTemplate(async (values) => {
    savingTemplate.value = true;

    const template = {
        name: values.name,
        description: values.description,
    };

    //TODO api call to create template
    await client(`/api/journey/${journey.getID()}/`, {
        method: "POST",
        body: template,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value(
                        "form.template.create.toast.success.heading",
                    ),
                    detail: t.value(
                        "form.template.create.toast.success.detail",
                    ),
                    life: 6000,
                });
                close();
                savingTemplate.value = false;
            }
        },
        async onRequestError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
            savingTemplate.value = false;
        },
        async onResponseError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
            savingTemplate.value = false;
        },
    });
});

const confirmJourneyNameAsTemplateName = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        group: "template",
        message: t.value("form.template.create.confirm.journeyname.message"),
        rejectLabel: t.value("common.button.cancel"),
        acceptLabel: t.value("form.template.create.confirm.journeyname.accept"),
        acceptClass:
            "px-2 py-1 text-nowrap rounded-xl border-2 border-dandelion-300 bg-natural-50 text-sm text-center text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600",
        rejectClass: "hover:underline",
        accept: () => {
            templateName.value = journey.getName();
        },
    });
};
</script>

<template>
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            :block-scroll="true"
            :auto-z-index="true"
            :draggable="false"
            close-on-escape
            dismissable-mask
            class="z-50 flex flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-5/12 md:rounded-xl"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex justify-end h-1 pb-2 font-nunito bg-background dark:bg-background-dark',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:px-7 h-full',
                },
                footer: { class: 'h-0' },
                closeButtonIcon: {
                    class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10',
                },
            }"
            @hide="close"
        >
            <form
                class="flex h-full flex-col justify-between bg-background font-nunito text-text dark:bg-background-dark dark:text-natural-50"
                @submit="onSubmitCreateTemplate"
            >
                <h1
                    class="text-2xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="journey.template.create" />
                </h1>
                <div class="mb-5 mt-5 flex flex-col">
                    <div class="grid grid-cols-3 grid-rows-2 items-center">
                        <label
                            for="template-name"
                            class="text-md col-start-1 row-start-1 font-medium md:text-lg"
                        >
                            <T key-name="form.input.template.name" />
                        </label>
                        <Field
                            id="template-name"
                            v-model="templateName"
                            name="templateName"
                            :validate-on-blur="false"
                            class="col-span-2 col-start-2 block w-full rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text placeholder:text-natural-400 focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50"
                            @click="
                                confirmJourneyNameAsTemplateName($event.target)
                            "
                        />
                        <div class="col-span-3 flex justify-end">
                            <ErrorMessage
                                name="templateName"
                                class="text-nowrap text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300 sm:text-sm"
                            />
                        </div>
                    </div>
                    <div class="-mt-4 grid grid-cols-2 grid-rows-5">
                        <label
                            for="template-description"
                            class="text-md font-medium md:text-lg"
                        >
                            <T key-name="form.input.template.description" />
                        </label>
                        <Field
                            id="template-description"
                            type="text"
                            as="textarea"
                            name="templateDescription"
                            :placeholder="
                                t('form.input.template.description.placeholder')
                            "
                            class="col-span-full row-span-4 block h-full w-full resize-none rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text placeholder:text-natural-400 focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50"
                        />
                    </div>
                </div>
                <div
                    class="flex h-full flex-row justify-between gap-2 bg-background align-bottom font-nunito dark:bg-background-dark"
                >
                    <Button
                        type="button"
                        :label="t('common.button.cancel')"
                        icon="pi pi-times"
                        class="mt-auto h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-bold font-nunito',
                            },
                        }"
                        @click="close"
                    />
                    <Button
                        type="submit"
                        :label="t('common.button.create')"
                        :loading="savingTemplate"
                        icon="pi pi-check"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-bold font-nunito',
                            },
                        }"
                        class="mt-auto flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-dandelion-300 bg-natural-50 text-center text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    />
                </div>
            </form>
        </Dialog>

        <ConfirmPopup
            group="template"
            :pt="{
                root: {
                    class: 'font-nunito text-text dark:text-natural-50 bg-natural-50 dark:bg-background-dark ml-0',
                },
                message: { class: 'ml-0' },
                footer: { class: 'flex justify-between' },
            }"
        />
    </div>
</template>
