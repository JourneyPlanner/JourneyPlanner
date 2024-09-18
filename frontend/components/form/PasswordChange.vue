<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close"]);
const { t } = useTranslate();
const password = ref("");
const newPassword = ref("");
const newPasswordConfirmation = ref("");
const passwordInvalid = ref(false);
const passwordConfirmInvalid = ref(false);
const client = useSanctumClient();
const toast = useToast();

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};

function validatePassword() {
    if (newPassword.value.length < 8) {
        passwordInvalid.value = true;
    } else {
        passwordInvalid.value = false;
    }
}

function comparePassword() {
    console.log(passwordConfirmInvalid.value);
    if (newPassword.value != newPasswordConfirmation.value) {
        passwordConfirmInvalid.value = true;
    } else {
        passwordConfirmInvalid.value = false;
    }
}

async function changePassword() {
    if (!passwordInvalid.value && !passwordConfirmInvalid.value) {
        await client(`/api/user/change-password`, {
            method: "POST",
            body: {
                password: password.value,
                new_password: newPassword.value,
                new_password_confirm: newPasswordConfirmation.value,
            },
            async onResponse({ response }) {
                console.log(response);
                if (response.ok) {
                    emit("close");
                }
            },
            async onResponseError({ response }) {
                console.log(response.status);
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
    <Dialog
        v-model:visible="isVisible"
        modal
        block-scroll
        :auto-z-index="true"
        :draggable="false"
        :header="t('dashboard.user.settings')"
        class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:w-1/3 md:rounded-xl"
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
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-12 h-full',
            },
            footer: { class: 'h-0' },
            icons: {
                class: 'justify-end items-center w-fit pl-10',
            },
            closeButtonIcon: {
                class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10 ',
            },
        }"
        @hide="close"
    >
        <template #header>
            <div class="flex w-[90%] items-center">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.password.change" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
        </template>
        <div class="pl-4">
            <div class="-pt-4 text-sm text-natural-700 dark:text-natural-300">
                <T
                    key-name="dashboard.user.settings.password.change.description"
                />
            </div>
            <div class="flex flex-col items-center justify-center pl-6 pt-4">
                <div class="flex w-3/5 justify-start dark:text-natural-50">
                    <T
                        key-name="dashboard.user.settings.enter.current.password"
                    />
                </div>
                <input
                    id="password"
                    v-model="password"
                    name="password"
                    type="password"
                    class="w-3/5 rounded-md border-2 border-natural-400 bg-natural-50 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                />
            </div>
            <div class="flex flex-col items-center justify-center pl-6 pt-4">
                <div class="flex w-3/5 justify-start dark:text-natural-50">
                    <T key-name="dashboard.user.settings.enter.new.password" />
                </div>
                <input
                    id="newPassword"
                    v-model="newPassword"
                    name="newPassword"
                    type="password"
                    class="w-3/5 rounded-md border-2 border-natural-400 bg-natural-50 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                    @keyup="validatePassword"
                />
                <div
                    v-if="passwordInvalid"
                    class="flex w-3/5 justify-start text-sm text-mahagony-600"
                >
                    <T key-name="dashboard.user.settings.password.too.short" />
                </div>
            </div>
            <div class="flex flex-col items-center justify-center pl-6 pt-4">
                <div class="flex w-3/5 justify-start dark:text-natural-50">
                    <T
                        key-name="dashboard.user.settings.confirm.new.password"
                    />
                </div>
                <input
                    id="newPasswordConfirmation"
                    v-model="newPasswordConfirmation"
                    name="newPasswordConfirmation"
                    type="password"
                    class="w-3/5 rounded-md border-2 border-natural-400 bg-natural-50 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                    @keyup="comparePassword"
                    @keyup.enter="changePassword"
                />
                <div
                    v-if="passwordConfirmInvalid"
                    class="flex w-3/5 justify-start text-sm text-mahagony-600"
                >
                    <T key-name="dashboard.user.settings.passwords.not.match" />
                </div>
            </div>
            <div class="flex w-full justify-center pb-6 pt-6">
                <button
                    class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    @click="changePassword"
                >
                    <T key-name="dashboard.user.settings.change.password" />
                </button>
            </div>
        </div>
    </Dialog>
</template>
