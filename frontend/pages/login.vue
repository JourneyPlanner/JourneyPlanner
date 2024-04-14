<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import Toast from "primevue/toast";
import { useForm } from "vee-validate";
import * as yup from "yup";

const { t } = useTranslate();
const toast = useToast();
const { login } = useSanctumAuth();

const title = t.value("form.header.login");

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
            summary: t.value("form.registration.toast.success.heading"),
            detail: t.value("form.registration.toast.success"),
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
        }
        throw error;
    }
}
</script>

<template>
    <div
        class="flex w-full items-center justify-center font-nunito dark:bg-background-dark"
    >
        <Toast class="w-3/4 sm:w-auto" />
        <div class="dark:background-dark h-[90vh] w-0 sm:w-0 md:w-1/4 xl:w-1/3">
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
                    class="focus:ring-indigo-500 focus:border-indigo-500 flex h-auto w-full flex-col items-center rounded-3xl border-2 border-border bg-surface px-3 py-2 pl-2 shadow-sm focus:outline-none dark:bg-surface-dark"
                >
                    <legend
                        for="outerBlock"
                        class="px-2 text-center text-3xl font-bold text-text dark:text-white lg:ml-7 lg:text-left"
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
                            class="text-md my-5 mt-4 rounded-2xl border-2 border-cta-border bg-input px-6 py-2.5 font-nunito font-bold hover:bg-cta-bg dark:bg-input-dark dark:text-white dark:hover:bg-cta-bg-dark"
                        >
                            <T key-name="form.button.login" />
                        </button>
                    </form>
                    <NuxtLink
                        to="/register"
                        class="my-1 mt-auto font-nunito font-semibold underline dark:text-white"
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
</template>
