<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    visible: { type: Boolean, required: true },
    requiresPassword: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "changedPassword"]);
const { t } = useTranslate();
const passwordInvalid = ref(false);
const passwordConfirmInvalid = ref(false);
const client = useSanctumClient();
const toast = useToast();

const validationSchema = props.requiresPassword
    ? yup.object({
          password: yup.string().required(() => t.value("form.input.required")),
          newPassword: yup
              .string()
              .min(8, () => t.value("form.input.password.error"))
              .required(() => t.value("form.input.required")),
          newPasswordConfirmation: yup
              .string()
              .oneOf([yup.ref("newPassword")], () =>
                  t.value("form.input.password.repeat.error"),
              )
              .required(() => t.value("form.input.required")),
      })
    : yup.object({
          newPassword: yup
              .string()
              .min(8, () => t.value("form.input.password.error"))
              .required(() => t.value("form.input.required")),
          newPasswordConfirmation: yup
              .string()
              .oneOf([yup.ref("newPassword")], () =>
                  t.value("form.input.password.repeat.error"),
              )
              .required(() => t.value("form.input.required")),
      });

const { errors, handleSubmit, defineField, handleReset } = useForm({
    validationSchema,
});

const [password] = defineField("password");
const [newPassword] = defineField("newPassword");
const [newPasswordConfirmation] = defineField("newPasswordConfirmation");

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    handleReset();
    password.value = "";
    emit("close");
};

const onSubmit = handleSubmit(() => {
    changePassword();
});

/**
 * changes the users password
 */
async function changePassword() {
    if (!passwordInvalid.value && !passwordConfirmInvalid.value) {
        await client(`/api/user/change-password`, {
            method: "PUT",
            body: {
                password: password.value,
                new_password: newPassword.value,
                new_password_confirmation: newPasswordConfirmation.value,
            },
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value("common.toast.success.heading"),
                        detail: t.value("password.changed.toast.success"),
                        life: 6000,
                    });
                    close();
                    emit("changedPassword");
                }
            },
            async onResponseError({ response }) {
                if (
                    response._data.message ==
                    "The email field must be a valid email address."
                ) {
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("email.not.valid.toast.error"),
                        life: 6000,
                    });
                } else if (response.status === 401) {
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("wrong.password.toast.error"),
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
}
</script>

<template>
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            block-scroll
            :auto-z-index="true"
            :draggable="false"
            :header="t('dashboard.user.settings')"
            class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/2 2xl:w-1/3"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex justify-start pb-2 font-nunito bg-background dark:bg-background-dark ',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-5 h-full',
                },
                footer: { class: 'h-0' },
                icons: {
                    class: 'justify-end items-center w-fit pl-5',
                },
                closeButtonIcon: {
                    class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 focus:outline-none focus-ring-1 h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex w-[90%] items-center">
                    <div class="h-0.5 w-5 bg-calypso-400" />
                    <div
                        class="flex-grow-5 mx-3 text-3xl text-text dark:text-natural-50"
                    >
                        <T key-name="dashboard.user.settings.password.change" />
                    </div>
                    <div class="h-0.5 flex-grow bg-calypso-400" />
                </div>
            </template>
            <div class="">
                <div
                    class="-pt-4 text-sm text-natural-700 dark:text-natural-300"
                >
                    <T
                        key-name="dashboard.user.settings.password.change.description"
                    />
                </div>
                <div
                    v-if="requiresPassword"
                    class="flex flex-col items-center justify-center pt-5"
                >
                    <div
                        class="flex w-4/6 justify-start text-text dark:text-natural-50"
                    >
                        <T
                            key-name="dashboard.user.settings.enter.current.password"
                        />
                    </div>
                    <input
                        id="password"
                        v-model="password"
                        name="password"
                        type="password"
                        class="focus-ring-1 w-4/6 rounded-md border-2 border-natural-400 bg-natural-50 py-0.5 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                    <span
                        class="flex w-4/6 justify-start text-sm text-mahagony-600 dark:text-mahagony-300"
                        >{{ errors.password }}</span
                    >
                </div>
                <div class="flex flex-col items-center justify-center pt-5">
                    <div
                        class="flex w-4/6 justify-start text-text dark:text-natural-50"
                    >
                        <T
                            key-name="dashboard.user.settings.enter.new.password"
                        />
                    </div>
                    <input
                        id="newPassword"
                        v-model="newPassword"
                        name="newPassword"
                        type="password"
                        class="focus-ring-1 w-4/6 rounded-md border-2 border-natural-400 bg-natural-50 py-0.5 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                    <span
                        class="flex w-4/6 justify-start text-sm text-mahagony-600 dark:text-mahagony-300"
                        >{{ errors.newPassword }}</span
                    >
                </div>
                <div class="flex flex-col items-center justify-center pt-5">
                    <div class="flex w-4/6 justify-start dark:text-natural-50">
                        <T
                            key-name="dashboard.user.settings.confirm.new.password"
                        />
                    </div>
                    <input
                        id="newPasswordConfirmation"
                        v-model="newPasswordConfirmation"
                        name="newPasswordConfirmation"
                        type="password"
                        class="focus-ring-1 w-4/6 rounded-md border-2 border-natural-400 bg-natural-50 py-0.5 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        @keyup.enter="onSubmit"
                    />
                    <span
                        class="flex w-4/6 justify-start text-sm text-mahagony-600 dark:text-mahagony-300"
                        >{{ errors.newPasswordConfirmation }}</span
                    >
                </div>
                <div class="flex w-full justify-center pb-4 pt-10">
                    <button
                        class="w-44 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="onSubmit"
                    >
                        <T key-name="dashboard.user.settings.change.password" />
                    </button>
                </div>
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="right"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-[95%] w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden ',
                },
                header: {
                    class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50 rounded-3xl',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 -ml-2 sm:pr-12 h-full',
                },
                footer: { class: 'h-0' },
                closeButton: {
                    class: 'justify-start w-full h-full items-center collapse hidden',
                },
                mask: {
                    class: 'sm:collapse bg-natural-50',
                },
            }"
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-left text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="dashboard.user.settings.password.change" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-pt-4 w-11/12 overflow-hidden overflow-ellipsis text-[0.95rem] text-natural-700 dark:text-natural-200"
                >
                    <T
                        key-name="dashboard.user.settings.password.change.description"
                    />
                </div>
                <div class="flex items-center pl-6 pt-4">
                    <div class="flex w-full flex-col items-center">
                        <div
                            v-if="requiresPassword"
                            class="mb-1 mr-10 flex w-full items-start text-[0.95rem] text-text dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.current.password"
                            />
                        </div>
                        <input
                            v-if="requiresPassword"
                            id="passwordMobile"
                            v-model="password"
                            name="password"
                            type="password"
                            class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        />
                        <span
                            v-if="requiresPassword"
                            class="mr-10 flex w-full justify-start pt-0.5 text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.password }}</span
                        >
                        <div
                            class="mb-1 mr-10 flex w-full items-start pt-2 text-[0.95rem] text-text dark:text-natural-50"
                            :class="requiresPassword ? 'mt-2' : 'mt-0'"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.new.password"
                            />
                        </div>
                        <input
                            id="newPasswordMobile"
                            v-model="newPassword"
                            name="password"
                            type="password"
                            class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        />
                        <span
                            class="mr-10 flex w-full justify-start pt-0.5 text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.newPassword }}</span
                        >
                        <div
                            class="mb-1 mr-10 mt-2 flex w-full items-start pt-2 text-[0.95rem] text-text dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.confirm.new.password"
                            />
                        </div>
                        <input
                            id="newPasswordConfirmationMobile"
                            v-model="newPasswordConfirmation"
                            name="password"
                            type="password"
                            class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                            @keyup.enter="onSubmit"
                        />
                        <span
                            class="mr-10 flex w-full justify-start pt-0.5 text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.newPasswordConfirmation }}</span
                        >
                    </div>
                </div>
                <div class="mt-auto flex w-full justify-center">
                    <button
                        class="ml-1 mr-6 mt-auto w-full rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-1 pl-2 text-xl font-semibold hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="onSubmit"
                    >
                        <T key-name="common.save" />
                    </button>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
