<script setup lang="ts">
import { T, useTolgee, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    visible: { type: Boolean, required: true },
    propUsername: { type: String, required: true },
    propDisplayname: { type: String, required: true },
    propEmail: { type: String, required: true },
});
const emit = defineEmits(["close"]);

const isVisible = ref(props.visible);
const { t } = useTranslate();
const colorScheme = ref("");
const darkThemeMq = window.matchMedia("(prefers-color-scheme: dark)");
const tolgee = useTolgee(["language"]);
const { logout } = useSanctumAuth();
const isEmailChangeDialogVisible = ref(false);
const isPasswordChangeDialogVisible = ref(false);
const isDeleteAccountDialogVisible = ref(false);
const isUsernameChangeDialogVisible = ref(false);
const isDisplaynameChangeDialogVisible = ref(false);
const selectedColorMode = ref();
const selectedLanguage = ref();
const language = ref();
const client = useSanctumClient();
const toast = useToast();
const usernameRegex = /^[a-z0-9_-]+$/;
const isUsernameInvalid = ref(false);
const colorMode = useColorMode();
const currUsername = ref(props.propUsername);
const currDisplayname = ref(props.propDisplayname);
const currEmail = ref(props.propEmail);

const { errors, handleSubmit, defineField } = useForm({
    validationSchema: yup.object({
        username: yup
            .string()
            .matches(usernameRegex, () =>
                t.value("dashboard.user.settings.username.invalid"),
            )
            .required(() => t.value("form.input.required")),
        displayname: yup
            .string()
            .required(() => t.value("form.input.required")),
    }),
});

const [username] = defineField("username");
const [displayname] = defineField("displayname");
username.value = props.propUsername;
displayname.value = props.propDisplayname;

const onSubmitUsername = handleSubmit((values) => {
    changeUsername(values.username);
});

onMounted(() => {
    if (
        colorMode.preference === "dark" ||
        (darkThemeMq.matches && colorMode.preference === "system")
    ) {
        colorScheme.value = "Darkmode";
    } else {
        colorScheme.value = "Lightmode";
    }

    if (tolgee.value.getLanguage() == "en") {
        language.value = "English";
    } else if (tolgee.value.getLanguage() == "de") {
        language.value = "Deutsch";
    }
});

const close = () => {
    emit("close");
};

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const changeLanguage = () => {
    tolgee.value.changeLanguage(selectedLanguage.value.value);
};

/**
 * Changes the Color mode
 * could be darkmode, lightmode or system
 */
function changeColorMode() {
    if (selectedColorMode.value.name == "Darkmode") {
        colorMode.preference = "dark";
    } else if (selectedColorMode.value.name == "Lightmode") {
        colorMode.preference = "light";
    } else {
        colorMode.preference = "system";
    }
}

function checkUsername() {
    if (username.value != "") {
        onSubmitUsername();
    } else {
        username.value = currUsername.value;
    }
}

/**
 * changes the username
 * @param newUsername the new username
 */
async function changeUsername(newUsername: string) {
    if (newUsername != "") {
        username.value = newUsername;
        isUsernameChangeDialogVisible.value = false;
        isUsernameInvalid.value = false;
    }

    if (currUsername.value != username.value) {
        await client(`/api/user/change-username`, {
            method: "PUT",
            body: {
                username: username.value,
            },
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value("common.toast.success.heading"),
                        detail: t.value("username.changed.toast.success"),
                        life: 6000,
                    });
                    currUsername.value = response._data.username;
                    username.value = response._data.username;
                }
            },
            async onResponseError({ response }) {
                if (
                    response._data.message ==
                    "The username has already been taken."
                ) {
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("username.already.taken.toast.error"),
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
                username.value = currUsername.value;
            },
        });
    }
}

/**
 * changes the displayname
 *  @param newDisplayname the new displayname
 */
async function changeDisplayname(newDisplayname: string) {
    if (newDisplayname != "") {
        displayname.value = newDisplayname;
    } else {
        displayname.value = currDisplayname.value;
    }

    isDisplaynameChangeDialogVisible.value = false;

    if (currDisplayname.value != displayname.value && displayname.value != "") {
        await client(`/api/user/change-display-name`, {
            method: "PUT",
            body: {
                display_name: displayname.value,
            },
            async onResponse({ response }) {
                if (response.ok) {
                    toast.add({
                        severity: "success",
                        summary: t.value("common.toast.success.heading"),
                        detail: t.value("displayname.changed.toast.success"),
                        life: 6000,
                    });
                    currDisplayname.value = response._data.display_name;
                    displayname.value = response._data.display_name;
                }
            },
            async onResponseError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
                displayname.value = currDisplayname.value;
            },
        });
    }
}

async function logoutUser() {
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("being.logged.out.toast.info"),
        life: 6000,
    });
    await logout();
}

const colorModes = ref([
    { name: "Lightmode", code: "pi pi-sun" },
    { name: "Darkmode", code: "pi pi-moon" },
    { name: "System", code: "pi pi-desktop" },
]);

const languages = ref([
    { name: "common.english", value: "en" },
    { name: "common.deutsch", value: "de" },
]);

/**
 * changes the email address
 * @param newEmail the new email
 */
async function changeEmail(newEmail: Ref) {
    currEmail.value = newEmail.value;
    isEmailChangeDialogVisible.value = false;
    toast.add({
        severity: "success",
        summary: t.value("change.email.toast.success.heading"),
        detail: t.value("change.email.toast.success.detail"),
        life: 3000,
    });
}

function blur(e: Event) {
    const element = e.target as HTMLInputElement;
    element.blur();
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
            class="-z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-4/5 md:rounded-xl"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50',
                },
                title: {
                    class: 'font-nunito text-3xl font-semibold',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-12 h-full',
                },
                footer: { class: 'h-0' },
                icons: {
                    class: 'justify-end w-full items-center',
                },
                closeButtonIcon: {
                    class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 focus:outline-none focus-ring-1 h-10 w-10 ',
                },
            }"
            @hide="close"
        >
            <div class="pl-4 pt-4 text-text dark:text-natural-50">
                <div class="flex items-center pb-3">
                    <div
                        class="h-[1.6px] w-10 bg-calypso-300 dark:bg-calypso-600"
                    />
                    <div class="flex-grow-5 mx-4 text-[1.65rem] font-semibold">
                        <T key-name="dashboard.user.settings.profile" />
                    </div>
                    <div
                        class="h-[1.6px] flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T
                                key-name="dashboard.user.settings.display.name"
                            />
                        </div>
                        <div class="text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.display.name.description"
                            />
                        </div>
                    </div>
                    <div
                        class="flex w-full flex-col items-center justify-end self-center"
                    >
                        <input
                            id="displayname"
                            v-model="displayname"
                            name="displayname"
                            :placeholder="currDisplayname"
                            class="focus-ring-1 w-60 self-end rounded-md border-2 border-natural-300 bg-natural-100 py-0.5 pl-3 pr-1 text-text placeholder:text-text hover:border-calypso-400 hover:bg-natural-50 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:placeholder:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                            @blur="changeDisplayname(displayname)"
                            @keyup.enter="blur"
                        />
                        <span
                            class="flex w-56 justify-start self-end text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.displayname }}</span
                        >
                    </div>
                </div>
                <div class="flex pb-8 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.user.name" />
                        </div>
                        <div class="text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.user.name.description"
                            />
                        </div>
                    </div>
                    <div
                        class="flex w-full flex-col items-center justify-end self-center"
                    >
                        <input
                            id="username"
                            v-model="username"
                            name="username"
                            :placeholder="currUsername"
                            class="focus-ring-1 w-60 self-end rounded-md border-2 border-natural-300 bg-natural-100 py-0.5 pl-3 pr-1 text-text placeholder:text-text hover:border-calypso-400 hover:bg-natural-50 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:placeholder:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                            @blur="checkUsername()"
                            @keyup.enter="blur"
                        />
                        <span
                            class="flex w-56 justify-start self-end text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.username }}</span
                        >
                        <div class="w-44 self-end">
                            <div
                                v-if="isUsernameInvalid"
                                class="-ml-10 text-sm text-mahagony-600 dark:text-mahagony-300"
                            >
                                <T
                                    key-name="dashboard.user.settings.username.invalid"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center pb-3">
                    <div
                        class="bg h-[1.6px] w-10 bg-calypso-300 dark:bg-calypso-600"
                    />
                    <div class="flex-grow-5 mx-4 text-[1.65rem] font-semibold">
                        <T
                            key-name="dashboard.user.settings.account.security"
                        />
                    </div>
                    <div
                        class="h-[1.6px] flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.user.email" />
                        </div>
                        <div
                            class="overflow-hidden overflow-ellipsis text-text dark:text-natural-300"
                        >
                            <T
                                key-name="dashboard.user.settings.user.email.description"
                            />
                            <span class="font-semibold">
                                {{ currEmail }}
                            </span>
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <button
                            class="w-44 rounded-md border-2 border-dandelion-300 bg-natural-50 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="
                                isEmailChangeDialogVisible =
                                    !isEmailChangeDialogVisible
                            "
                        >
                            <T
                                key-name="dashboard.user.settings.email.change"
                            />
                        </button>
                    </div>
                </div>
                <div class="flex pb-8 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.password" />
                        </div>
                        <div class="text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.password.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <button
                            class="w-44 rounded-md border-2 border-dandelion-300 bg-natural-50 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="
                                isPasswordChangeDialogVisible =
                                    !isPasswordChangeDialogVisible
                            "
                        >
                            <T
                                key-name="dashboard.user.settings.password.change"
                            />
                        </button>
                    </div>
                </div>
                <div class="flex items-center pb-4">
                    <div
                        class="h-[1.6px] w-10 bg-calypso-300 dark:bg-calypso-600"
                    />
                    <div class="flex-grow-5 mx-4 text-[1.65rem] font-semibold">
                        <T key-name="dashboard.user.settings.appearance" />
                    </div>
                    <div
                        class="h-[1.6px] flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.appearance" />
                        </div>
                        <div class="text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.appearance.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <Dropdown
                            v-model="selectedColorMode"
                            :options="colorModes"
                            option-label="name"
                            :placeholder="colorScheme"
                            :highlight-on-select="false"
                            :focus-on-hover="false"
                            :pt="{
                                root: {
                                    class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-9 flex items-center',
                                },
                                input: {
                                    class: 'text-text dark:text-natural-50 ',
                                },
                                item: {
                                    class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                                },
                                wrapper: {
                                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                                },
                                trigger: {
                                    class: 'text-text dark:text-natural-50',
                                },
                            }"
                            @change="changeColorMode()"
                        >
                            <template #value="slotProps">
                                <div
                                    v-if="slotProps.value"
                                    class="align-items-center flex"
                                >
                                    <div>{{ slotProps.value.name }}</div>
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                            <template #option="slotProps">
                                <div
                                    class="align-items-center flex items-center"
                                >
                                    <span
                                        :class="slotProps.option.code"
                                        class="pr-2"
                                    />
                                    <div>{{ slotProps.option.name }}</div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <div class="flex pb-8 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.language" />
                        </div>
                        <div class="text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.language.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <Dropdown
                            v-model="selectedLanguage"
                            :options="languages"
                            option-label="name"
                            :placeholder="language"
                            :highlight-on-select="false"
                            :focus-on-hover="false"
                            :pt="{
                                root: {
                                    class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-9 flex items-center',
                                },
                                input: {
                                    class: 'text-text dark:text-natural-50',
                                },
                                item: {
                                    class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                                },
                                wrapper: {
                                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                                },
                                trigger: {
                                    class: 'text-text dark:text-natural-50',
                                },
                            }"
                            @change="changeLanguage"
                            ><template #value="slotProps">
                                <div
                                    v-if="slotProps.value"
                                    class="align-items-center flex"
                                >
                                    <T :key-name="slotProps.value.name" />
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                            <template #option="slotProps">
                                <div
                                    class="align-items-center flex items-center"
                                >
                                    <T :key-name="slotProps.option.name" />
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <div class="flex items-center pb-4">
                    <div
                        class="h-[1.6px] w-10 bg-calypso-300 dark:bg-calypso-600"
                    />
                    <div class="flex-grow-5 mx-4 text-[1.65rem] font-semibold">
                        <T key-name="dashboard.user.settings.delete.log.out" />
                    </div>
                    <div
                        class="h-[1.6px] flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-10">
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.log.out" />
                        </div>
                        <div class="text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.log.out.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <button
                            class="w-44 rounded-md border-2 border-dandelion-300 bg-natural-50 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="logoutUser"
                        >
                            <T key-name="dashboard.user.settings.log.out" />
                        </button>
                    </div>
                </div>
                <div
                    class="flex pb-8 pl-10 text-mahagony-600 dark:text-mahagony-300"
                >
                    <div class="flex w-full flex-col">
                        <div class="text-2xl">
                            <T key-name="dashboard.user.settings.delete" />
                        </div>
                        <T
                            key-name="dashboard.user.settings.delete.description"
                        />
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <button
                            class="w-44 rounded-md border-2 border-mahagony-500 bg-natural-50 py-0.5 text-base font-medium text-text hover:border-mahagony-600 hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                            @click="
                                isDeleteAccountDialogVisible =
                                    !isDeleteAccountDialogVisible
                            "
                        >
                            <T key-name="dashboard.user.settings.delete" />
                        </button>
                    </div>
                </div>
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="right"
            block-scroll
            :auto-z-index="true"
            :draggable="false"
            class="z-50 flex w-full flex-col bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 lg:-z-10"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden',
                },
                header: {
                    class: 'text-text flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50',
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
                    class: 'sm:collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-left text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="dashboard.user.settings" />
                </div>
            </template>
            <div class="pl-4 text-text dark:text-natural-50">
                <div class="flex items-center pb-2">
                    <div class="h-0.5 w-6 bg-calypso-300 dark:bg-calypso-600" />
                    <div class="flex-grow-5 mx-2 text-2xl font-semibold">
                        <T key-name="dashboard.user.settings.profile" />
                    </div>
                    <div
                        class="h-0.5 flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="mr-5 flex w-[50%] flex-col">
                        <div class="text-xl">
                            <T
                                key-name="dashboard.user.settings.display.name"
                            />
                        </div>
                        <div
                            class="overflow-hidden overflow-ellipsis text-sm text-text dark:text-natural-300"
                        >
                            <T
                                key-name="dashboard.user.settings.display.name.description"
                            />
                            <br />
                            <T key-name="dashboard.user.display.name" />
                            <b
                                class="overflow-hidden overflow-ellipsis text-text dark:text-natural-50"
                                >{{ currDisplayname }}</b
                            >
                        </div>
                    </div>
                    <div class="flex w-[45%] items-center justify-end">
                        <button
                            class="mr-4 flex w-32 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="
                                isDisplaynameChangeDialogVisible =
                                    !isDisplaynameChangeDialogVisible
                            "
                        >
                            <T key-name="common.change" />

                            <span class="pi pi-angle-right self-center" />
                        </button>
                        <SettingsDisplaynameChange
                            :visible="isDisplaynameChangeDialogVisible"
                            :displayname="currDisplayname"
                            @close="isDisplaynameChangeDialogVisible = false"
                            @change-displayname="changeDisplayname"
                        />
                    </div>
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="mr-5 flex w-[50%] flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.user.name" />
                        </div>
                        <div
                            class="overflow-hidden overflow-ellipsis text-sm text-text dark:text-natural-300"
                        >
                            <T
                                key-name="dashboard.user.settings.user.name.description"
                            />
                            <br />
                            <T key-name="dashboard.user.username" />
                            <b
                                class="overflow-hidden overflow-ellipsis text-text dark:text-natural-50"
                                >{{ currUsername }}</b
                            >
                        </div>
                    </div>
                    <div class="flex w-[45%] items-center justify-end">
                        <button
                            class="mr-4 flex w-32 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="
                                isUsernameChangeDialogVisible =
                                    !isUsernameChangeDialogVisible
                            "
                        >
                            <T key-name="common.change" />
                            <span class="pi pi-angle-right self-center" />
                        </button>
                        <SettingsUsernameChange
                            :visible="isUsernameChangeDialogVisible"
                            :username="currUsername"
                            :username-regex="usernameRegex"
                            @close="isUsernameChangeDialogVisible = false"
                            @change-username="changeUsername"
                        />
                    </div>
                </div>
                <div class="flex items-center pb-2">
                    <div class="h-0.5 w-6 bg-calypso-300 dark:bg-calypso-600" />
                    <div class="flex-grow-5 mx-2 text-2xl font-semibold">
                        <T
                            key-name="dashboard.user.settings.account.security"
                        />
                    </div>
                    <div
                        class="h-0.5 flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="mr-5 flex w-[50%] flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.user.email" />
                        </div>
                        <div
                            class="w-full overflow-hidden overflow-ellipsis text-sm text-text dark:text-natural-300"
                        >
                            <T
                                key-name="dashboard.user.settings.user.email.description"
                            />
                            <b
                                class="overflow-hidden overflow-ellipsis text-text dark:text-natural-50"
                            >
                                {{ currEmail }}
                            </b>
                        </div>
                    </div>
                    <div class="flex w-[45%] items-center justify-end">
                        <button
                            class="mr-4 flex w-32 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="
                                isEmailChangeDialogVisible =
                                    !isEmailChangeDialogVisible
                            "
                        >
                            <T key-name="common.change" />
                            <span class="pi pi-angle-right self-center" />
                        </button>
                        <SettingsEmailChange
                            :visible="isEmailChangeDialogVisible"
                            :curr-email="currEmail"
                            @close="isEmailChangeDialogVisible = false"
                            @change-email="changeEmail"
                        />
                    </div>
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="mr-5 flex w-[50%] flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.password" />
                        </div>
                        <div class="text-sm text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.password.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-[45%] items-center justify-end">
                        <button
                            class="mr-4 flex w-32 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="
                                isPasswordChangeDialogVisible =
                                    !isPasswordChangeDialogVisible
                            "
                        >
                            <T key-name="common.change" />
                            <span class="pi pi-angle-right self-center" />
                        </button>
                        <SettingsPasswordChange
                            :visible="isPasswordChangeDialogVisible"
                            @close="isPasswordChangeDialogVisible = false"
                        />
                    </div>
                </div>
                <div class="flex items-center pb-2">
                    <div class="h-0.5 w-6 bg-calypso-300 dark:bg-calypso-600" />
                    <div class="flex-grow-5 mx-2 text-2xl font-semibold">
                        <T key-name="dashboard.user.settings.appearance" />
                    </div>
                    <div
                        class="h-0.5 flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="flex w-full flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.appearance" />
                        </div>
                        <div class="text-sm text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.appearance.description"
                            />
                        </div>
                    </div>
                    <div class="mr-4 flex w-full items-center justify-end">
                        <Dropdown
                            v-model="selectedColorMode"
                            :options="colorModes"
                            option-label="name"
                            :placeholder="colorScheme"
                            :highlight-on-select="false"
                            :focus-on-hover="false"
                            :pt="{
                                root: {
                                    class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-9 flex items-center',
                                },
                                input: {
                                    class: 'text-text dark:text-natural-50',
                                },
                                item: {
                                    class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                                },
                                wrapper: {
                                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                                },
                                trigger: {
                                    class: 'text-text dark:text-natural-50',
                                },
                            }"
                            @change="changeColorMode()"
                        >
                            <template #value="slotProps">
                                <div
                                    v-if="slotProps.value"
                                    class="align-items-center flex"
                                >
                                    <div>{{ slotProps.value.name }}</div>
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                            <template #option="slotProps">
                                <div
                                    class="align-items-center flex items-center"
                                >
                                    <span
                                        :class="slotProps.option.code"
                                        class="pr-2"
                                    />
                                    <div>{{ slotProps.option.name }}</div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="flex w-full flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.language" />
                        </div>
                        <div class="text-sm text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.language.description"
                            />
                        </div>
                    </div>
                    <div class="mr-4 flex w-full items-center justify-end">
                        <Dropdown
                            v-model="selectedLanguage"
                            :options="languages"
                            option-label="name"
                            :placeholder="language"
                            :highlight-on-select="false"
                            :focus-on-hover="false"
                            :pt="{
                                root: {
                                    class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200 h-9 flex items-center',
                                },
                                input: {
                                    class: 'text-text dark:text-natural-50',
                                },
                                item: {
                                    class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-50 dark:bg-natural-900 dark:hover:bg-pesto-600',
                                },
                                wrapper: {
                                    class: 'bg-natural-50 dark:bg-natural-900 text-text dark:text-natural-50',
                                },
                                trigger: {
                                    class: 'text-text dark:text-natural-50',
                                },
                            }"
                            @change="changeLanguage"
                            ><template #value="slotProps">
                                <div
                                    v-if="slotProps.value"
                                    class="align-items-center flex"
                                >
                                    <T :key-name="slotProps.value.name" />
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                            <template #option="slotProps">
                                <div
                                    class="align-items-center flex items-center"
                                >
                                    <T :key-name="slotProps.option.name" />
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
                <div class="flex items-center pb-2">
                    <div class="h-0.5 w-6 bg-calypso-300 dark:bg-calypso-600" />
                    <div class="flex-grow-5 mx-2 text-2xl font-semibold">
                        <T key-name="dashboard.user.settings.delete.log.out" />
                    </div>
                    <div
                        class="h-0.5 flex-grow bg-calypso-300 dark:bg-calypso-600"
                    />
                </div>
                <div class="flex pb-5 pl-4">
                    <div class="flex w-full flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.log.out" />
                        </div>
                        <div class="text-sm text-text dark:text-natural-300">
                            <T
                                key-name="dashboard.user.settings.log.out.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <button
                            class="mr-4 w-32 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                            @click="logoutUser"
                        >
                            <T key-name="dashboard.user.settings.log.out" />
                        </button>
                    </div>
                </div>
                <div
                    class="flex pb-5 pl-4 text-mahagony-600 dark:text-mahagony-300"
                >
                    <div class="flex w-full flex-col">
                        <div class="text-xl">
                            <T key-name="dashboard.user.settings.delete" />
                        </div>
                        <div class="text-sm">
                            <T
                                key-name="dashboard.user.settings.delete.description"
                            />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-end">
                        <button
                            class="mr-4 w-32 rounded-md border-2 border-mahagony-500 bg-natural-50 px-2 py-0.5 font-medium text-text hover:border-mahagony-600 hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                            @click="
                                isDeleteAccountDialogVisible =
                                    !isDeleteAccountDialogVisible
                            "
                        >
                            <T key-name="dashboard.user.settings.delete" />
                        </button>
                        <SettingsDeleteAccount
                            :visible="isDeleteAccountDialogVisible"
                            @close="isDeleteAccountDialogVisible = false"
                        />
                    </div>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
