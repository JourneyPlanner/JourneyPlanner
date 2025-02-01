<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const toast = useToast();
const client = useSanctumClient();
const isForgotButtonDisabled = ref<boolean>(false);
const email = ref<string>("");

const title = t.value("password.forgot.title");

useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:guest"],
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
    isForgotButtonDisabled.value = true;

    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("password.forgot.sending.toast.info"),
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
                    toast.add({
                        severity: "success",
                        summary: t.value(
                            "password.forgot.email.sent.success.heading",
                        ),
                        detail: t.value(
                            "password.forgot.email.sent.success.detail",
                        ),
                        life: 6000,
                    });
                } else {
                    throw new Error();
                }
            },
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t.value("password.forgot.email.sent.error.heading"),
            detail: t.value("password.forgot.email.sent.error.detail"),
            life: 6000,
        });
    }

    isForgotButtonDisabled.value = false;
}
</script>

<template>
    <div class="flex flex-col overflow-hidden">
        <div class="mt-4 flex items-center justify-between px-2">
            <NuxtLink to="/login" class="group z-50 flex items-center gap-x-2">
                <i
                    class="pi pi-chevron-left text-xl text-text dark:text-natural-50"
                />
                <span
                    class="text-xl font-semibold text-text group-hover:underline dark:text-natural-50"
                >
                    <T key-name="form.button.login" />
                </span>
            </NuxtLink>
            <NuxtLink to="/" class="z-50">
                <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
            </NuxtLink>
        </div>
        <SvgCloud
            id="top-left"
            class="absolute right-0 top-80 w-48 scale-x-[-1] overflow-hidden object-none md:-left-28 md:top-28 md:w-[18%] md:scale-x-[1]"
        />
        <SvgCloud
            id="right-middle"
            class="absolute right-[35vh] top-[30vh] w-0 overflow-hidden object-none md:right-[15vh] md:w-[25%] xl:w-[22%]"
        />
        <SvgCloud
            id="bottom-right"
            class="absolute -left-20 bottom-[40vh] w-48 scale-x-[-1] overflow-hidden sm:left-auto sm:right-10 md:bottom-64 md:right-[28vw] md:w-[14%] md:scale-x-[1]"
        />
        <div
            class="flex w-full items-center justify-center font-nunito dark:bg-background-dark lg:mt-32"
        >
            <div
                class="relative z-40 flex w-full items-center justify-center overflow-hidden sm:w-full md:w-2/4 xl:w-1/3"
            >
                <div
                    class="z-20 mt-6 w-full text-center sm:w-3/4 md:flex md:items-center md:justify-center xl:flex xl:items-center xl:justify-center"
                >
                    <fieldset
                        id="forgot-password-form"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex h-auto w-full flex-col items-center rounded-3xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-3 py-2 pl-2 shadow-sm focus:outline-none dark:border-calypso-400 dark:bg-gothic-300 dark:bg-opacity-20"
                    >
                        <legend
                            for="forgot-password-form"
                            class="whitespace-nowrap px-2 text-center text-2xl font-bold text-text dark:text-natural-50 lg:ml-5 lg:text-left lg:text-3xl"
                        >
                            <T key-name="password.forgot.title" />
                        </legend>
                        <form class="-mt-2 w-5/6" @submit="onSubmit">
                            <p
                                class="my-2 text-left text-sm text-text dark:text-natural-50"
                            >
                                <T key-name="password.forgot.text" />
                            </p>
                            <FormInput
                                id="email"
                                v-model="email"
                                name="email"
                                translation-key="form.input.email"
                            />
                            <button
                                :disabled="isForgotButtonDisabled"
                                class="text-md mb-3 mt-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-5 py-1 font-nunito text-lg hover:bg-dandelion-200 disabled:cursor-not-allowed disabled:border-dandelion-200 disabled:text-natural-500 disabled:hover:bg-natural-50 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 disabled:dark:hover:bg-natural-800"
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
        <SvgForgotPassword
            class="absolute bottom-12 w-72 lg:bottom-0 lg:w-[30vw]"
        />
    </div>
</template>
