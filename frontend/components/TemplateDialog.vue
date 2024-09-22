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
const templateName = ref("");
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

/**
 * Validation schema for the template form
 * required fields: templateName
 * optional fields: templateDescription
 */
const validationSchema = yup.object({
    name: yup
        .string()
        .required(t.value("form.template.name.required"))
        .matches(/^(?!\s+$).*/, t.value("form.template.name.required")),
    description: yup.string().nullable(),
});

/**
 * validate template form against the validation schema
 */
const { handleSubmit: createTemplate } = useForm<Template>({
    validationSchema: validationSchema,
});

/**
 * call validation function, submit the template
 * @param values form values
 */
const onSubmitCreateTemplate = createTemplate(async (values) => {
    savingTemplate.value = true;

    const template = {
        journey_id: journey.getID(),
        name: values.name,
        description: values.description,
    };

    await client(`/api/template/`, {
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
                    life: 3000,
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
        async onResponseError({ response }) {
            if (response.status === 409) {
                toast.add({
                    severity: "error",
                    summary: t.value(
                        "form.template.create.toast.error.duplicate.heading",
                    ),
                    detail: t.value(
                        "form.template.create.toast.error.duplicate.detail",
                    ),
                    life: 6000,
                });
                close();
            } else {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            }
            savingTemplate.value = false;
        },
    });
});
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
            class="z-50 mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-6/12 xl:w-5/12"
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
                    <T key-name="journey.template.create" />
                </h3>
                <span class="h-0.5 w-full bg-calypso-400 md:mr-2" />
            </template>
            <form
                class="flex h-full flex-col justify-between bg-background font-nunito text-text dark:bg-background-dark dark:text-natural-50"
                @submit="onSubmitCreateTemplate"
            >
                <div class="mb-5 flex flex-col">
                    <p
                        class="col-span-full col-start-1 row-span-1 -mt-1 mb-5 text-sm text-natural-600 dark:text-natural-200 md:text-base"
                    >
                        <T key-name="journey.template.create.description" />
                    </p>
                    <div
                        class="mb-1 grid grid-cols-5 grid-rows-2 items-center xs:grid-cols-8 sm:grid-cols-4 sm:gap-x-20 xl:gap-x-0"
                    >
                        <label
                            for="template-name"
                            class="text-md col-start-1 row-start-1 font-medium md:text-lg"
                        >
                            <T key-name="form.input.template.name" />
                        </label>
                        <Field
                            id="template-name"
                            v-model="templateName"
                            name="name"
                            :validate-on-input="true"
                            class="col-span-full col-start-3 row-start-1 block w-full rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text placeholder:text-natural-400 focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50 xs:col-start-4 sm:col-start-2"
                        />
                        <div
                            class="col-start-3 row-start-2 -mt-3 xs:col-start-4 sm:col-start-2"
                        >
                            <ErrorMessage
                                name="name"
                                class="text-nowrap text-xs text-mahagony-600 dark:font-bold dark:text-mahagony-300 sm:text-sm"
                            />
                        </div>
                    </div>
                    <div
                        class="-mt-4 grid grid-cols-2 grid-rows-6 sm:grid-rows-5 md:-mt-5"
                    >
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
                            name="description"
                            :placeholder="
                                t('form.input.template.description.placeholder')
                            "
                            class="md:placeholder:text-md col-span-full row-span-5 block h-full w-full resize-none rounded-lg border-2 border-calypso-300 bg-natural-50 px-2.5 pb-1 pt-1 font-nunito font-normal text-text placeholder:text-sm placeholder:text-natural-400 focus:outline-none focus:ring-1 dark:border-calypso-400 dark:bg-natural-900 dark:text-natural-50 sm:row-span-4 md:placeholder:text-base"
                        />
                    </div>
                </div>
                <div
                    class="flex h-full flex-row justify-between gap-2 bg-background align-bottom font-nunito dark:bg-background-dark md:mt-1"
                >
                    <Button
                        type="button"
                        :label="t('common.button.cancel')"
                        icon="pi pi-times"
                        class="mt-auto h-9 w-40 rounded-xl border-2 border-mahagony-400 bg-natural-50 px-2 font-bold text-text hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
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
    </div>
</template>
