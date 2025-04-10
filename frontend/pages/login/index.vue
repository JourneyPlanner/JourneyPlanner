<script setup lang="ts">
import { T, useTolgee, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const tolgee = useTolgee(["language"]);
const toast = useToast();
const { login } = useSanctumAuth();
const route = useRoute();
const isVerifyEmailToastVisible = ref<boolean>(false);
const isConfirmEmailDialogVisible = ref<boolean>(false);
const email = ref<string>("");

const title = t.value("form.header.login");

useSeoMeta({
    title: () => `${title} | JourneyPlanner`,
    description: () => t.value("startpage.text"),
    ogImage: () => `/og/index/${tolgee.value?.getLanguage() || "en"}.png`,
});

definePageMeta({
    middleware: ["sanctum:guest"],
});

if (route.query.redirect?.toString().startsWith("/invite")) {
    localStorage.setItem(
        "JP_invite_journey_id",
        route.query.redirect as string,
    );
}

const { handleSubmit } = useForm({
    validationSchema: yup.object({
        email: yup
            .string()
            .email(t.value("form.input.email.error"))
            .required(t.value("form.input.required")),
        password: yup
            .string()
            .min(8, t.value("form.input.password.error"))
            .required(t.value("form.input.required")),
    }),
});

const onSubmit = handleSubmit((values) => {
    loginUser({
        email: values.email as string,
        password: values.password as string,
    });
});

interface User {
    email: string;
    password: string;
}
/**
 * use the sanctum client to login a user
 * shows toast messages if success/error
 * @param {User} userData
 */
async function loginUser(userData: User) {
    const userCredentials = {
        password: userData.password,
        email: userData.email,
    };
    toast.removeGroup("verify-email");
    isVerifyEmailToastVisible.value = false;

    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("form.login.toast.info"),
        life: 3000,
    });
    try {
        await login(userCredentials);
        toast.add({
            severity: "success",
            summary: t.value("common.toast.success.heading"),
            detail: t.value("form.login.toast.success"),
            life: 3000,
        });

        await navigateTo("/dashboard");
    } catch (error: unknown) {
        if (
            (error as Error & { response?: { status?: number } }).response
                ?.status === 422
        ) {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("form.login.toast.error"),
                life: 3000,
            });
            return;
        } else if (
            (error as Error & { response?: { _data?: { message?: string } } })
                .response?._data?.message == "CSRF token mismatch."
        ) {
            location.reload();
        } else if (
            (error as Error & { response?: { status?: number } }).response
                ?.status === 429
        ) {
            toast.add({
                severity: "warn",
                summary: t.value("common.too.many.requests.heading"),
                detail: t.value("common.too.many.requests.detail"),
                life: 3000,
            });
        } else if (
            (error as Error & { response?: { status?: number } }).response
                ?.status === 401 &&
            (error as Error & { response?: { _data?: { message?: string } } })
                .response?._data?.message ===
                "Your email address is not verified."
        ) {
            if (!isVerifyEmailToastVisible.value) {
                toast.add({
                    severity: "warn",
                    summary: t.value("form.register.verify.email.header"),
                    detail: t.value(
                        "form.login.toast.error.email_not_verified",
                    ),
                    group: "verify-email",
                });
                isVerifyEmailToastVisible.value = true;
            }
        } else {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 3000,
            });
        }
    }
}

function resend() {
    isVerifyEmailToastVisible.value = false;
    toast.removeGroup("verify-email");
    isConfirmEmailDialogVisible.value = true;
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
                        <p class="mt-2 text-sm text-text">
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
        <div class="absolute left-4 top-4 z-50">
            <NuxtLink to="/" class="z-50">
                <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
            </NuxtLink>
        </div>
        <div
            class="flex w-full items-center justify-center font-nunito dark:bg-background-dark"
        >
            <div
                class="dark:background-dark h-[90vh] w-0 sm:w-0 md:w-1/4 xl:w-1/3"
            >
                <SvgCloud
                    class="z-50 -ml-24 mt-[20vh] w-0 overflow-hidden object-none dark:fill-clouds-bg md:w-[80%] xl:w-[50%]"
                />
                <SvgCloud
                    class="absolute left-[35vh] top-[49vh] z-30 w-0 overflow-hidden object-none md:left-[18vh] md:w-[25%] xl:w-[20%]"
                />
                <div class="overflow-hidden">
                    <SvgCloudReversed
                        class="z-50 mt-[55vh] w-0 overflow-hidden object-none dark:fill-clouds-bg md:w-[70%] xl:w-[45%]"
                    />
                    <SvgBalloon
                        class="absolute left-[45vh] top-[45vh] z-0 w-0 md:left-[28vh] md:w-[5%] xl:w-[4%]"
                    />
                    <SvgBalloon
                        class="absolute left-[68vh] top-12 w-0 md:w-[8%] xl:w-[7%]"
                    />
                </div>
            </div>
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
                            <T key-name="form.header.login" />
                        </legend>
                        <form class="-mt-2 w-5/6" @submit="onSubmit">
                            <FormInput
                                id="email"
                                v-model="email"
                                name="email"
                                translation-key="form.input.email"
                            />

                            <FormPasswordInput
                                id="password"
                                name="password"
                                :show-reset-button="true"
                                :feedback-style="true"
                                translation-key="form.input.password"
                            />

                            <button
                                class="text-md mb-3 mt-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-12 py-1 font-nunito text-lg font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            >
                                <T key-name="form.button.login" />
                            </button>
                        </form>
                        <div
                            class="mb-4 flex w-full flex-col justify-center gap-x-5 gap-y-2"
                        >
                            <FormOAuth />
                        </div>
                        <NuxtLink
                            to="/register"
                            class="my-1 mt-auto font-nunito font-semibold hover:underline dark:text-natural-50"
                        >
                            <T key-name="form.text.no_account" />
                        </NuxtLink>
                    </fieldset>
                </div>
                <SvgCloudReversed
                    class="absolute -left-4 top-[50vh] z-0 w-[30vh] overflow-hidden sm:w-[30vh] md:w-0 xl:w-0"
                />
                <SvgBalloonWithPeople
                    class="absolute -right-24 top-[30vh] z-0 w-[30vh] overflow-hidden sm:-right-12 sm:w-[30vh] md:w-0 xl:w-0"
                />
            </div>
            <div
                class="flex h-[90vh] w-0 items-center justify-center sm:w-0 md:w-1/4 xl:w-1/3"
            >
                <SvgBalloonWithPeople class="w-[60%]" />
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
