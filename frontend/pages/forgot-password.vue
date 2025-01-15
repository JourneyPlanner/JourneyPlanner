<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const toast = useToast();
const client = useSanctumClient();
const isVerifyEmailToastVisible = ref<boolean>(false);
const isConfirmEmailDialogVisible = ref<boolean>(false);
const email = ref<string>("");

const title = t.value("password.forgot.title");

useHead({
    title: `${title} | JourneyPlanner`,
});

const { handleSubmit } = useForm({
    validationSchema: yup.object({
        email: yup
            .string()
            .email(t.value("form.input.email.error"))
            .required(t.value("form.input.required")),
    }),
});

const onSubmit = handleSubmit((values) => {
    sendPasswordResetEmail(values.email as string);
});

/**
 * use the sanctum client to send a password reset email
 * shows toast messages if success/error
 * @param {string} email
 */
async function sendPasswordResetEmail(email: string) {
    //toast.removeGroup("verify-email");
    //isVerifyEmailToastVisible.value = false;

    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("form.login.toast.info"),
        life: 3000,
    });

    try {
        await client("/forgot-password", {
            method: "POST",
            params: {
                email: email,
            },
            async onResponse({ response }) {
                if (response.ok) {
                    /*
                    toast.add({
                        severity: "success",
                        summary: t.value("email.verify.success.heading"),
                        detail: t.value("email.verify.success.detail"),
                        life: 6000,
                    });
                    navigateTo("/dashboard");
                    */
                    console.log(response);
                } else {
                    throw new Error();
                }
            },
        });
        toast.add({
            severity: "success",
            summary: t.value("common.toast.success.heading"),
            detail: t.value("form.login.toast.success"),
            life: 3000,
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("common.toast.error.detail"),
            life: 3000,
        });
    }
}

function resend() {
    //isVerifyEmailToastVisible.value = false;
    //toast.removeGroup("verify-email");
    //isConfirmEmailDialogVisible.value = true;
}
</script>

<template>
    <div>
        <Toast
            group="verify-email"
            class="w-3/4 font-nunito sm:w-auto"
            :pt="{ root: 'font-nunito' }"
            @close="isVerifyEmailToastVisible = false"
        >
            <template #message="slotProps">
                <div class="flex gap-x-2">
                    <div>
                        <i class="pi pi-exclamation-triangle text-lg" />
                    </div>
                    <div clas="flex flex-col">
                        <p>{{ slotProps.message.summary }}</p>
                        <p class="mt-2 text-sm text-text dark:text-natural-50">
                            {{ slotProps.message.detail }}
                        </p>
                        <button
                            class="flex items-baseline gap-x-1 text-sm"
                            @click="resend()"
                        >
                            <i class="pi pi-envelope text-xs" />
                            <span class="underline">
                                <T
                                    key-name="form.register.button.email.resend"
                                />
                            </span>
                        </button>
                    </div>
                </div>
            </template>
        </Toast>
        <div class="absolute right-4 top-4 z-50">
            <NuxtLink to="/" class="z-50">
                <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
            </NuxtLink>
        </div>
        <div class="absolute left-4 top-4 z-50">
            <NuxtLink to="/login" class="z-50 flex items-center gap-x-2">
                <i
                    class="pi pi-chevron-left text-xl text-text dark:text-natural-50"
                />
                <span
                    class="text-xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="form.button.login" />
                </span>
            </NuxtLink>
        </div>
        <div
            class="flex w-full items-center justify-center font-nunito dark:bg-background-dark"
        >
            <div
                class="relative z-40 flex h-[90vh] w-full items-center justify-center overflow-hidden sm:w-full md:w-2/4 xl:w-1/3"
            >
                <div
                    class="z-20 mt-6 h-[75vh] w-full text-center sm:w-3/4 md:flex md:items-center md:justify-center xl:flex xl:items-center xl:justify-center"
                >
                    <fieldset
                        id="outerBlock"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex h-auto w-full flex-col items-center rounded-3xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-3 py-2 pl-2 shadow-sm focus:outline-none dark:border-calypso-400 dark:bg-gothic-300 dark:bg-opacity-20"
                    >
                        <legend
                            for="outerBlock"
                            class="mb-5 px-2 text-center text-3xl font-bold text-text dark:text-natural-50 lg:ml-7 lg:text-left"
                        >
                            <T key-name="password.forgot.title" />
                        </legend>
                        <form class="-mt-2 w-5/6" @submit="onSubmit">
                            <FormInput
                                id="email"
                                v-model="email"
                                name="email"
                                translation-key="form.input.email"
                            />
                            <button
                                class="text-md mb-3 mt-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-5 py-1 font-nunito text-lg font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            >
                                <T key-name="password.forgot.reset" />
                            </button>
                        </form>
                        <NuxtLink
                            to="/login"
                            class="my-1 mt-auto font-nunito font-semibold hover:underline dark:text-natural-50"
                        >
                            <T key-name="form.button.login" />
                        </NuxtLink>
                    </fieldset>
                </div>
            </div>
        </div>
        <div id="dialogs">
            <MailVerifyDialog
                :is-confirm-email-dialog-visible="isConfirmEmailDialogVisible"
                :do-resend="true"
                :email="email"
                @close="isConfirmEmailDialogVisible = false"
            />
        </div>
    </div>
</template>
