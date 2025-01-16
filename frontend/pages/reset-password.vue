<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { ref } from "vue";
import { useRoute } from "vue-router";
import * as yup from "yup";

const toast = useToast();
const { t } = useTranslate();
const client = useSanctumClient();

useHead({
    title: `${t.value("password.reset.title")} | JourneyPlanner`,
});

const route = useRoute();
const loading = ref(true);
const isSendNewLinkToastVisible = ref(false);
const isResetButtonDisabled = ref(false);
const query = ref(route.query);

if (!query.value.token || !query.value.email) {
    toast.add({
        severity: "error",
        summary: t.value("password.reset.error.parameters.heading"),
        detail: t.value("password.reset.error.parameters.detail"),
        life: 6000,
        group: "invalid-link",
    });
    isSendNewLinkToastVisible.value = true;
}

const { handleSubmit } = useForm({
    validationSchema: yup.object({
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
    }),
});

const onSubmit = handleSubmit((values) => {
    resetPassword(
        values.password as string,
        values.password_confirmation as string,
    );
});

/*
 * reset address by calling the API
 */
async function resetPassword(password: string, password_confirmation: string) {
    isResetButtonDisabled.value = true;
    try {
        await client("/reset-password", {
            method: "POST",
            params: {
                token: query.value.token,
                email: query.value.email,
                password: password,
                password_confirmation: password_confirmation,
            },
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value("password.reset.success.heading"),
                        detail: t.value("password.reset.success.detail"),
                        life: 6000,
                    });
                    navigateTo("/dashboard");
                } else {
                    throw new Error();
                }
            },
        });
    } catch {
        toast.add({
            severity: "error",
            summary: t.value("password.reset.error.invalid.heading"),
            detail: t.value("password.reset.error.invalid.detail"),
            life: 6000,
            group: "invalid-link",
        });
        isSendNewLinkToastVisible.value = true;
        loading.value = false;
    }
    isResetButtonDisabled.value = false;
}

async function resend() {
    try {
        await client("/forgot-password", {
            method: "POST",
            params: {
                email: query.value.email,
            },
            async onResponse({ response }) {
                if (response.ok) {
                    isSendNewLinkToastVisible.value = false;
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
}
</script>

<template>
    <div class="flex flex-col overflow-hidden">
        <Toast
            group="invalid-link"
            class="w-3/4 font-nunito sm:w-auto"
            :pt="{ root: 'font-nunito' }"
            @close="isSendNewLinkToastVisible = false"
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
            class="flex w-full items-center justify-center bg-background font-nunito dark:bg-background-dark"
        >
            <div
                class="flex w-full items-center justify-center sm:w-full md:w-2/4 lg:mt-32 xl:w-2/5"
            >
                <div class="z-20 h-3/4 w-full text-center sm:w-4/5">
                    <fieldset
                        id="reset-password-form"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex h-auto flex-col items-center rounded-3xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-3 py-2 shadow-sm focus:outline-none dark:border-calypso-400 dark:bg-gothic-300 dark:bg-opacity-20"
                    >
                        <legend
                            for="reset-password-form"
                            class="px-2 text-center text-3xl font-bold text-text dark:text-natural-50 lg:ml-11 lg:text-left"
                        >
                            <T key-name="password.reset.button" />
                        </legend>
                        <form class="w-3/4" @submit="onSubmit">
                            <p class="my-2 text-left text-sm">
                                <T key-name="password.reset.text" />
                            </p>
                            <FormPasswordInput
                                id="password"
                                name="password"
                                :feedback="true"
                                :feedback-style="true"
                                translation-key="form.input.password"
                            />
                            <div class="mt-0.5">
                                <FormPasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    :feedback="false"
                                    translation-key="form.input.password.repeat"
                                />
                            </div>
                            <button
                                :disabled="isResetButtonDisabled"
                                class="text-md mb-3 mt-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-5 py-1 font-nunito text-lg hover:bg-dandelion-200 disabled:cursor-not-allowed disabled:border-dandelion-200 disabled:text-natural-500 disabled:hover:bg-natural-50 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 disabled:dark:hover:bg-natural-800"
                            >
                                <T key-name="password.reset.button" />
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
