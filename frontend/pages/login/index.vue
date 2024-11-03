<script setup lang="ts">
import { T, useTolgee, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";
import { useGoogleAuth } from "~/composable/useGoogleAuth";
import { useMicrosoftAuth } from "~/composable/useMicrosoftAuth";

const { t } = useTranslate();
const toast = useToast();
const { login } = useSanctumAuth();
const route = useRoute();
const config = useRuntimeConfig();

const title = t.value("form.header.login");
const tolgee = useTolgee(["language"]);

useHead({
    title: `${title} | JourneyPlanner`,
});

definePageMeta({
    middleware: ["sanctum:guest"],
});

useGoogleAuth();
const { loginWithMicrosoft } = useMicrosoftAuth();

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
        password: yup.string().required(t.value("form.input.required")),
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
        }
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("common.error.unknown"),
            life: 3000,
        });
        throw error;
    }
}
</script>

<template>
    <div>
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
                            class="px-2 text-center text-3xl font-bold text-text dark:text-natural-50 lg:ml-7 lg:text-left"
                        >
                            <T key-name="form.header.login" />
                        </legend>
                        <form class="w-5/6" @submit="onSubmit">
                            <FormInput
                                id="email"
                                name="email"
                                translation-key="form.input.email"
                            />

                            <FormPassword
                                id="password"
                                name="password"
                                :feedback-style="true"
                                translation-key="form.input.password"
                            />

                            <button
                                class="text-md my-5 mt-4 rounded-2xl border-2 border-dandelion-300 bg-natural-50 px-6 py-2.5 font-nunito font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            >
                                <T key-name="form.button.login" />
                            </button>
                        </form>
                        <div
                            class="mb-2 flex flex-col justify-center gap-x-5 gap-y-2"
                        >
                            <div class="flex justify-center">
                                <div
                                    id="g_id_onload"
                                    :data-client_id="
                                        config.public.NUXT_GOOGLE_CLIENT_ID
                                    "
                                    data-context="signin"
                                    data-ux_mode="popup"
                                    data-callback="handleGoogleCredentialResponse"
                                    data-itp_support="true"
                                ></div>

                                <div
                                    class="g_id_signin"
                                    data-type="standard"
                                    data-shape="rectangular"
                                    data-theme="outline"
                                    data-text="continue_with"
                                    data-size="large"
                                    :data-locale="tolgee.getLanguage()"
                                    data-logo_alignment="left"
                                ></div>
                            </div>
                            <button @click="loginWithMicrosoft">
                                <SvgMicrosoft />
                            </button>
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
    </div>
</template>
