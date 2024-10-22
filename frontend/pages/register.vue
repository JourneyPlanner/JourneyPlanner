<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const toast = useToast();
const client = useSanctumClient();
const route = useRoute();

const title = t.value("form.header.register");

useHead({
    title: `${title} | JourneyPlanner`,
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
        display_name: yup.string().required(t.value("form.input.required")),
        password: yup
            .string()
            .min(8, t.value("form.input.password.error"))
            .required(t.value("form.input.required")),
        password_confirmation: yup
            .string()
            .oneOf(
                [yup.ref("password")],
                t.value("form.input.password.repeat.error"),
            )
            .required(t.value("form.input.required")),
        terms: yup
            .boolean()
            .oneOf([true], t.value("form.input.terms.error"))
            .required(t.value("form.input.terms.error")),
    }),
});

const onSubmit = handleSubmit((values) => {
    registerUser(values);
});

/**
 * use the sanctum client to register a user
 * shows toast messages if success/error
 * @param {object} userData
 */
async function registerUser(userData: object) {
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("form.registration.toast.info"),
        life: 3000,
    });

    await client("/register", {
        method: "POST",
        body: userData,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("common.toast.success.heading"),
                    detail: t.value("form.registration.toast.success"),
                    life: 3000,
                });
                await navigateTo("/dashboard");
                /*
                if (localStorage.getItem("JP_invite_journey_id")) {
                    await navigateTo(
                        localStorage.getItem("JP_invite_journey_id"),
                    );
                } else {
                    await navigateTo("/dashboard");
                }
                */
            } else if (response.status === 422) {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("form.registration.toast.error"),
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
        async onRequestError() {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value("common.error.unknown"),
                life: 6000,
            });
        },
    });
}
</script>

<template>
    <div>
        <div
            class="flex w-full items-center justify-center bg-background font-nunito dark:bg-background-dark"
        >
            <div class="h-[90vh] sm:w-0 md:w-1/4 xl:w-1/3">
                <SvgAircraft
                    class="z-0 -ml-[20vw] mt-6 w-0 overflow-hidden object-none md:w-[350%] lg:mt-20 xl:mt-32 xl:w-[230%]"
                />
            </div>
            <div
                class="mt-32 flex w-full items-center justify-center sm:w-full md:w-2/4 xl:w-2/5"
            >
                <div class="z-20 -mt-20 h-3/4 w-full text-center sm:w-3/4">
                    <fieldset
                        id="outerBlock"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex h-auto flex-col items-center rounded-3xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-3 py-2 shadow-sm focus:outline-none dark:border-calypso-400 dark:bg-gothic-300 dark:bg-opacity-20"
                    >
                        <legend
                            for="outerBlock"
                            class="px-2 text-center text-3xl font-bold text-text dark:text-natural-50 lg:ml-7 lg:text-left"
                        >
                            <T key-name="form.header.register" />
                        </legend>
                        <form class="w-4/5" @submit="onSubmit">
                            <FormInput
                                id="email"
                                name="email"
                                autocomplete="email"
                                translation-key="form.input.email"
                            />

                            <FormInput
                                id="display_name"
                                name="display_name"
                                autocomplete="given-name"
                                translation-key="form.input.display.name"
                            />

                            <FormPassword
                                id="password"
                                name="password"
                                :feedback="true"
                                :feedback-style="true"
                                translation-key="form.input.password"
                            />
                            <div class="mt-4">
                                <FormPassword
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    :feedback="false"
                                    translation-key="form.input.password.repeat"
                                />
                            </div>
                            <div class="mt-3">
                                <FormCheckbox id="terms" name="terms" />
                            </div>

                            <button
                                class="text-md my-4 mt-4 rounded-2xl border-2 border-dandelion-300 bg-natural-50 px-6 py-2.5 font-nunito font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            >
                                <T key-name="form.button.register" />
                            </button>
                        </form>
                        <div class="flex flex-row gap-x-2">
                            <NuxtLink
                                to="/login"
                                class="my-1 mt-auto font-nunito font-semibold hover:underline dark:text-natural-50"
                            >
                                <T key-name="form.text.already_account" />
                            </NuxtLink>
                            <span class="my-1 border-l-2 border-natural-300" />
                            <NuxtLink
                                to="/journey/new"
                                class="my-1 mt-auto font-nunito font-semibold hover:underline dark:text-natural-50"
                            >
                                <T key-name="form.text.tryforfree" />
                            </NuxtLink>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="h-[90vh] w-0 sm:w-0 md:w-1/4 xl:w-1/3">
                <SvgCloud
                    class="z-0 mt-52 w-0 overflow-hidden object-none dark:fill-clouds-bg md:w-[90%] xl:w-[60%]"
                />
                <SvgCloud
                    class="z-0 -ml-[12vw] mt-32 w-0 overflow-hidden object-none md:w-[80%] xl:w-[50%]"
                />
                <div class="overflow-hidden">
                    <SvgCloud
                        class="z-0 ml-[20vw] mt-16 w-0 overflow-hidden overflow-x-hidden object-none md:w-[90%] xl:w-[60%]"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
