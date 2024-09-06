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

const savingTemplate = ref(false);
const templateName = ref();
const isVisible = ref();

watch(
    () => props.isCreateTemplateVisible,
    (value) => {
        isVisible.value = value;
        templateName.value = journey.getName();
    },
);

const close = () => {
    console.log("close");
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
    console.log(values);
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
                //activityStore.addActivity(response._data);
                //activityStore.setNewActivity(response._data);
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
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        close-on-escape
        dismissable-mask
        class="z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-5/12 md:rounded-xl"
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
                class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10',
            },
        }"
        @hide="close"
    >
        <form
            class="flex h-full flex-col justify-between bg-background font-nunito text-text dark:bg-background-dark dark:text-natural-50"
            @submit="onSubmitCreateTemplate"
        >
            <h1 class="text-2xl font-semibold text-text dark:text-natural-50">
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
                        class="col-span-2 col-start-2 block w-full rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text focus:outline-none focus:ring-1 dark:bg-natural-900 dark:text-natural-50"
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
                        class="col-span-full row-span-4 block h-full w-full resize-none rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text focus:outline-none focus:ring-1 dark:bg-natural-900 dark:text-natural-50"
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
                    class="mt-auto h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-pesto-600"
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
                    :label="t('common.save')"
                    :loading="savingTemplate"
                    icon="pi pi-check"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-bold font-nunito',
                        },
                    }"
                    class="mt-auto flex h-9 w-40 flex-row justify-center rounded-xl border-2 border-atlantis-400 bg-natural-50 text-center text-text hover:bg-atlantis-200 dark:bg-background-dark dark:text-natural-50 dark:hover:bg-atlantis-30040"
                />
            </div>
        </form>
    </Dialog>
</template>
