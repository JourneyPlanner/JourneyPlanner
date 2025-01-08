<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";
import resendConfirmationMail from "~/utils/resend-confirmation-mail";

const { t } = useTranslate();
const toast = useToast();
const router = useRouter();
const client = useSanctumClient();
const route = useRoute();

const isUserExistsToastVisible = ref<boolean>(false);
const isConfirmEmailDialogVisible = ref<boolean>(false);
const isResendButtonDisabled = ref<boolean>(true);
const resendEmailTime = ref<number>(60);
const countdown = ref();

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
    toast.removeGroup("user-exists");
    isUserExistsToastVisible.value = false;
    console.log(isResendButtonDisabled.value);

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
                isConfirmEmailDialogVisible.value = true;
                startResendCountdown();
            } else if (response.status === 422) {
                if (!isUserExistsToastVisible.value) {
                    toast.removeAllGroups();
                    toast.add({
                        severity: "warn",
                        summary: t.value(
                            "form.register.error.toast.user.exists.summary",
                        ),
                        detail: t.value(
                            "form.register.error.toast.user.exists.detail",
                        ),
                        group: "user-exists",
                    });
                    isUserExistsToastVisible.value = true;
                }
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

const close = () => {
    isConfirmEmailDialogVisible.value = false;
};

/**
 * start the countdown for the resend email button
 */
function startResendCountdown() {
    resendEmailTime.value = 60;
    countdown.value = setInterval(() => {
        if (resendEmailTime.value > 0) {
            resendEmailTime.value -= 1;
        } else {
            isResendButtonDisabled.value = false;
            clearInterval(countdown.value);
        }
    }, 1000);
}

function resend() {
    isResendButtonDisabled.value = true;
    resendConfirmationMail();
    startResendCountdown();
}
</script>

<template>
    <div>
        <Toast
            group="user-exists"
            class="w-3/4 font-nunito sm:w-auto"
            :pt="{ root: 'font-nunito' }"
            @close="isUserExistsToastVisible = false"
        >
            <template #message="slotProps">
                <div class="mr-10 flex gap-x-2">
                    <div>
                        <i class="pi pi-exclamation-triangle text-lg" />
                    </div>
                    <div clas="flex flex-col">
                        <p>{{ slotProps.message.summary }}</p>
                        <button
                            class="mt-2 flex items-baseline gap-x-1 text-sm"
                            @click="router.push('/login')"
                        >
                            <i class="pi pi-sign-in text-xs" />
                            <span class="underline">
                                <T
                                    key-name="form.register.error.toast.user.exists.hint.login.instead"
                                />
                            </span>
                        </button>
                    </div>
                </div>
            </template>
        </Toast>
        <div class="absolute left-4 top-4">
            <NuxtLink to="/">
                <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
            </NuxtLink>
        </div>
        <div
            class="flex w-full items-center justify-center bg-background font-nunito dark:bg-background-dark"
        >
            <div class="h-[90vh] sm:w-0 md:w-1/4 xl:w-1/3">
                <SvgAircraft
                    class="z-0 -ml-[20vw] mt-6 w-0 overflow-hidden object-none md:w-[350%] lg:mt-20 xl:mt-20 xl:w-[230%]"
                />
            </div>
            <div
                class="mt-32 flex w-full items-center justify-center sm:w-full md:w-2/4 xl:w-2/5"
            >
                <div class="z-20 -mt-20 h-3/4 w-full text-center sm:w-4/5">
                    <fieldset
                        id="outerBlock"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex h-auto flex-col items-center rounded-3xl border-2 border-calypso-300 bg-calypso-200 bg-opacity-30 px-3 py-2 shadow-sm focus:outline-none dark:border-calypso-400 dark:bg-gothic-300 dark:bg-opacity-20"
                    >
                        <legend
                            for="outerBlock"
                            class="mb-3 px-2 text-center text-3xl font-bold text-text dark:text-natural-50 lg:ml-7 lg:text-left"
                        >
                            <T key-name="form.header.register" />
                        </legend>
                        <form class="w-3/4" @submit="onSubmit">
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
                            <div class="mt-0.5">
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
                                class="mb-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-14 py-1 font-nunito text-lg font-bold hover:bg-dandelion-200 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600"
                            >
                                <T key-name="form.button.register" />
                            </button>
                        </form>
                        <div
                            class="mb-5 mt-1 flex w-full flex-col justify-center gap-x-5 gap-y-2"
                        >
                            <FormOAuth />
                        </div>
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
        <div id="dialogs">
            <Dialog
                v-model:visible="isConfirmEmailDialogVisible"
                modal
                :block-scroll="true"
                :auto-z-index="true"
                :draggable="false"
                close-on-escape
                dismissable-mask
                class="mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-2/5 xl:w-4/12"
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
                    mask: {
                        class: 'max-sm:collapse bg-natural-50',
                    },
                }"
                @hide="close"
            >
                <template #header>
                    <span
                        class="h-0.5 w-10 rounded-l-sm bg-calypso-400 dark:bg-calypso-600"
                    />
                    <h3
                        class="text-nowrap text-2xl font-medium text-text dark:text-natural-50"
                    >
                        <T key-name="form.register.verify.email.header" />
                    </h3>
                    <span
                        class="h-0.5 w-full rounded-r-sm bg-calypso-400 dark:bg-calypso-600 md:mr-2"
                    />
                </template>
                <div
                    class="flex flex-col gap-y-4 font-normal text-natural-950 dark:text-natural-50"
                >
                    <p>
                        <T key-name="form.register.verify.email.text" />
                    </p>
                    <div class="flex justify-center">
                        <button
                            type="button"
                            :disabled="isResendButtonDisabled"
                            class="mb-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-5 py-1 font-nunito text-lg hover:bg-dandelion-200 disabled:cursor-not-allowed disabled:border-dandelion-200 disabled:text-natural-500 disabled:hover:bg-natural-50 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 disabled:dark:hover:bg-natural-800"
                            @click="resend()"
                        >
                            <T key-name="form.register.button.email.resend" />
                            <span v-if="isResendButtonDisabled">
                                <T key-name="common.in" />
                                {{ resendEmailTime }}
                            </span>
                        </button>
                    </div>
                </div>
            </Dialog>
            <Sidebar
                v-model:visible="isConfirmEmailDialogVisible"
                modal
                position="bottom"
                :auto-z-index="true"
                :draggable="false"
                class="z-50 mt-auto flex h-fit w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
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
                        class: 'justify-start w-full h-full items-center collapse',
                    },
                    mask: {
                        class: 'sm:collapse bg-natural-50',
                    },
                }"
            >
                <template #header>
                    <button
                        class="-ml-6 flex justify-center pr-4"
                        @click="close"
                    >
                        <span class="pi pi-angle-down text-2xl" />
                    </button>
                    <div class="font-nunito text-3xl font-semibold">
                        <T key-name="form.register.verify.email.header.short" />
                    </div>
                </template>
                <div class="flex items-center justify-center">
                    <div
                        class="ml-2 w-11/12 flex-col gap-y-5 pb-5 text-natural-950 dark:text-natural-50"
                    >
                        <p>
                            <T key-name="form.register.verify.email.text" />
                        </p>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button
                        type="button"
                        :disabled="isResendButtonDisabled"
                        class="mb-2 rounded-xl border-2 border-dandelion-300 bg-natural-50 px-5 py-1 font-nunito text-lg hover:bg-dandelion-200 disabled:cursor-not-allowed disabled:border-dandelion-200 disabled:text-natural-500 disabled:hover:bg-natural-50 dark:bg-natural-800 dark:text-natural-50 dark:hover:bg-pesto-600 disabled:dark:hover:bg-natural-800"
                        @click="resend()"
                    >
                        <T key-name="form.register.button.email.resend" />
                        <span v-if="isResendButtonDisabled">
                            <T key-name="common.in" />
                            {{ resendEmailTime }}
                        </span>
                    </button>
                </div>
            </Sidebar>
        </div>
    </div>
</template>
