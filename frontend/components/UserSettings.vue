<script setup lang="ts">
import { T, useTolgee, useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const { t } = useTranslate();
const displayname = ref("");
const username = ref("");
const colorScheme = ref("test");
const darkThemeMq = window.matchMedia("(prefers-color-scheme: dark)");
const tolgee = useTolgee(["language"]);
const { logout } = useSanctumAuth();
const isEmailChangeVisible = ref(false);
const isPasswordChangeVisible = ref(false);
const isDeleteAccountVisible = ref(false);
const isUsernameChangeVisible = ref(false);
const isDisplaynameChangeVisible = ref(false);
const selectedColorMode = ref();
const selectedLanguage = ref();
const language = ref();
const client = useSanctumClient();
const toast = useToast();
const usernameRegex = /^[a-z0-9_-]+$/;
const usernameInvalid = ref(false);

onMounted(() => {
    if (
        colorMode.preference === "dark" ||
        (darkThemeMq.matches && colorMode.preference === "system")
    ) {
        colorScheme.value = "darkmode";
    } else {
        colorScheme.value = "lightmode";
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

const emit = defineEmits(["close"]);
const colorMode = useColorMode();

const changeLanguage = () => {
    tolgee.value.changeLanguage(selectedLanguage.value.value);
};

function handleChange() {
    if (selectedColorMode.value.name == "darkmode") {
        colorMode.preference = "dark";
    } else if (selectedColorMode.value.name == "lightmode") {
        colorMode.preference = "light";
    } else {
        colorMode.preference = "system";
    }
}

async function changeUsername(newUsername: string) {
    if (newUsername != "") {
        username.value = newUsername;
        isUsernameChangeVisible.value = false;
        usernameInvalid.value = false;
    }

    if (currUser.value.username == username.value) {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value(
                "dashboard.user.settings.toast.same.username.description",
            ),
            life: 6000,
        });
    } else {
        if (username.value.length > 0 && !usernameInvalid.value) {
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
                        currUser.value.username = username.value;
                    }
                },
                async onResponseError() {
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("common.error.unknown"),
                        life: 6000,
                    });
                },
            });
        }
    }
}

async function changeDisplayname(newDisplayname: string) {
    if (newDisplayname != "") {
        displayname.value = newDisplayname;
        isDisplaynameChangeVisible.value = false;
    }
    if (
        currUser.value.display_name != displayname.value &&
        displayname.value.length > 0
    ) {
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
                        detail: t.value("username.changed.toast.success"),
                        life: 6000,
                    });
                    currUser.value.display_name = displayname.value;
                }
            },
            async onResponseError() {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("common.error.unknown"),
                    life: 6000,
                });
            },
        });
    }
}

async function logoutUser() {
    await logout();
}

const colorModes = ref([
    { name: "lightmode", code: "pi pi-sun" },
    { name: "darkmode", code: "pi pi-moon" },
    { name: "system", code: "pi pi-desktop" },
]);

const languages = ref([
    { name: "common.english", value: "en" },
    { name: "common.deutsch", value: "de" },
]);

const { data: currUser } = await useAsyncData("userRole", () =>
    client(`/api/user`),
);

async function changeEmail(newEmail: Ref) {
    currUser.value.email = newEmail.value;
    isEmailChangeVisible.value = false;
    toast.add({
        severity: "success",
        summary: t.value("change.email.toast.success.heading"),
        detail: t.value("change.email.toast.success.detail"),
        life: 3000,
    });
}

function validateUsername() {
    if (usernameRegex.test(username.value)) {
        usernameInvalid.value = false;
    } else {
        usernameInvalid.value = true;
    }
}

function blur(e: Event) {
    const element = e.target as HTMLInputElement;
    element.blur();
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
        class="-z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-4/5 md:rounded-xl"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50',
            },
            title: {
                class: 'font-nunito text-4xl font-semibold',
            },
            content: {
                class: 'font-nunito bg-background dark:bg-background-dark px-0 sm:pl-5 sm:pr-12 h-full',
            },
            footer: { class: 'h-0' },
            icons: {
                class: 'justify-end w-full items-center',
            },
            closeButtonIcon: {
                class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10 ',
            },
        }"
        @hide="close"
    >
        <div class="pl-4 pt-4 dark:text-natural-50">
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.profile" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.display.name" />
                    </div>
                    <div class="dark:text-natural-300">
                        <T
                            key-name="dashboard.user.settings.display.name.description"
                        />
                    </div>
                </div>
                <div class="flex w-full items-center justify-end">
                    <input
                        id="displayname"
                        v-model="displayname"
                        name="displayname"
                        :placeholder="currUser.display_name"
                        class="rounded-md border-2 border-natural-400 bg-natural-100 pl-3 text-text placeholder:text-text hover:border-calypso-400 hover:bg-natural-50 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:placeholder:text-natural-50 dark:hover:border-calypso-400"
                        @blur="changeDisplayname('')"
                        @keyup.enter="blur"
                    />
                </div>
            </div>
            <div class="flex pb-14 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.user.name" />
                    </div>
                    <div class="dark:text-natural-300">
                        <T
                            key-name="dashboard.user.settings.user.name.description"
                        />
                    </div>
                </div>
                <div class="flex w-full flex-col items-center justify-end">
                    <input
                        id="username"
                        v-model="username"
                        name="username"
                        :placeholder="currUser.username"
                        class="self-end rounded-md border-2 border-natural-400 bg-natural-100 pl-3 text-text placeholder:text-text hover:border-calypso-400 hover:bg-natural-50 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:placeholder:text-natural-50 dark:hover:border-calypso-400"
                        @blur="changeUsername('')"
                        @keyup.enter="blur"
                        @keyup="validateUsername"
                    />
                    <div class="w-44 self-end">
                        <div
                            v-if="usernameInvalid"
                            class="-ml-10 text-sm text-mahagony-600"
                        >
                            <T
                                key-name="dashboard.user.settings.username.invalid"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.account.security" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.user.email" />
                    </div>
                    <div
                        class="overflow-hidden overflow-ellipsis dark:text-natural-300"
                    >
                        <T
                            key-name="dashboard.user.settings.user.email.description"
                        />
                        {{ currUser.email }}
                    </div>
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="isEmailChangeVisible = !isEmailChangeVisible"
                    >
                        <T key-name="dashboard.user.settings.email.change" />
                    </button>
                    <FormEmailChange
                        :visible="isEmailChangeVisible"
                        :curr-email="currUser.email"
                        @close="isEmailChangeVisible = false"
                        @change-email="changeEmail"
                    />
                </div>
            </div>
            <div class="flex pb-14 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.password" />
                    </div>
                    <div class="dark:text-natural-300">
                        <T
                            key-name="dashboard.user.settings.password.description"
                        />
                    </div>
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="
                            isPasswordChangeVisible = !isPasswordChangeVisible
                        "
                    >
                        <T key-name="dashboard.user.settings.password.change" />
                    </button>
                    <FormPasswordChange
                        :visible="isPasswordChangeVisible"
                        @close="isPasswordChangeVisible = false"
                    />
                </div>
            </div>
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.appearance" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.appearance" />
                    </div>
                    <div class="dark:text-natural-300">
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
                                class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200',
                            },
                            input: {
                                class: 'text-text dark:text-natural-50',
                            },
                            item: {
                                class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-100 dark:bg-natural-900',
                            },
                            wrapper: {
                                class: 'bg-natural-100 dark:bg-natural-900 text-text dark:text-natural-50',
                            },
                        }"
                        @change="handleChange()"
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
                            <div class="align-items-center flex items-center">
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
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.language" />
                    </div>
                    <div class="dark:text-natural-300">
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
                                class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200',
                            },
                            input: {
                                class: 'text-text dark:text-natural-50',
                            },
                            item: {
                                class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-100 dark:bg-natural-900',
                            },
                            wrapper: {
                                class: 'bg-natural-100 dark:bg-natural-900 text-text dark:text-natural-50',
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
                            <div class="align-items-center flex items-center">
                                <T :key-name="slotProps.option.name" />
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
            <div class="flex items-center pb-5">
                <div class="bg h-0.5 w-10 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-5 text-3xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.delete.log.out" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-10">
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.log.out" />
                    </div>
                    <div class="dark:text-natural-300">
                        <T
                            key-name="dashboard.user.settings.log.out.description"
                        />
                    </div>
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="logoutUser"
                    >
                        <T key-name="dashboard.user.settings.log.out" />
                    </button>
                </div>
            </div>
            <div
                class="flex pb-5 pl-10 text-mahagony-600 dark:text-mahagony-300"
            >
                <div class="flex w-full flex-col">
                    <div class="text-2xl">
                        <T key-name="dashboard.user.settings.delete" />
                    </div>
                    <T key-name="dashboard.user.settings.delete.description" />
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-mahagony-500 bg-natural-50 px-2 text-text hover:border-mahagony-600 hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="
                            isDeleteAccountVisible = !isDeleteAccountVisible
                        "
                    >
                        <T key-name="dashboard.user.settings.delete" />
                    </button>
                    <FormDeleteAccount
                        :visible="isDeleteAccountVisible"
                        @close="isDeleteAccountVisible = false"
                    />
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
        :header="t('dashboard.user.settings')"
        class="z-50 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 sm:rounded-xl lg:-z-10"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10 lg:-z-10 lg:hidden',
            },
            header: {
                class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50',
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
            <button
                class="-ml-6 flex justify-center pr-4"
                @click="$emit('close')"
            >
                <span class="pi pi-angle-left text-3xl" />
            </button>
            <div class="font-nunito text-4xl font-semibold">
                <T key-name="dashboard.user.settings" />
            </div>
        </template>
        <div class="pl-4 dark:text-natural-50">
            <div class="flex items-center pb-2">
                <div class="bg h-0.5 w-6 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-2 text-2xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.profile" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-4">
                <div class="flex w-[55%] flex-col">
                    <div class="text-xl">
                        <T key-name="dashboard.user.settings.display.name" />
                    </div>
                    <div
                        class="overflow-hidden overflow-ellipsis text-sm dark:text-natural-300"
                    >
                        <T
                            key-name="dashboard.user.settings.display.name.description"
                        />
                        <br />
                        <T key-name="dashboard.user.display.name" />
                        <b
                            class="overflow-hidden overflow-ellipsis text-text dark:text-natural-50"
                            >{{ currUser.display_name }}</b
                        >
                    </div>
                </div>
                <div class="flex w-[45%] items-center justify-end">
                    <button
                        class="flex w-40 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 text-xl hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="
                            isDisplaynameChangeVisible =
                                !isDisplaynameChangeVisible
                        "
                    >
                        <T key-name="common.change" />
                        <div class="flex items-center">
                            <span class="pi pi-angle-right" />
                        </div>
                    </button>
                    <FormDisplaynameChange
                        :visible="isDisplaynameChangeVisible"
                        :displayname="currUser.display_name"
                        @close="isDisplaynameChangeVisible = false"
                        @change-displayname="changeDisplayname"
                    />
                </div>
            </div>
            <div class="flex pb-5 pl-4">
                <div class="flex w-[55%] flex-col">
                    <div class="text-xl">
                        <T key-name="dashboard.user.settings.user.name" />
                    </div>
                    <div
                        class="overflow-hidden overflow-ellipsis text-sm dark:text-natural-300"
                    >
                        <T
                            key-name="dashboard.user.settings.user.name.description"
                        />
                        <br />
                        <T key-name="dashboard.user.username" />
                        <b
                            class="overflow-hidden overflow-ellipsis text-text dark:text-natural-50"
                            >{{ currUser.username }}</b
                        >
                    </div>
                </div>
                <div class="flex w-[45%] items-center justify-end">
                    <button
                        class="flex w-40 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 text-xl hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="
                            isUsernameChangeVisible = !isUsernameChangeVisible
                        "
                    >
                        <T key-name="common.change" />
                        <div class="flex items-center">
                            <span class="pi pi-angle-right" />
                        </div>
                    </button>
                    <FormUsernameChange
                        :visible="isUsernameChangeVisible"
                        :username="currUser.username"
                        :username-regex="usernameRegex"
                        @close="isUsernameChangeVisible = false"
                        @change-username="changeUsername"
                    />
                </div>
            </div>
            <div class="flex items-center pb-2">
                <div class="bg h-0.5 w-6 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-2 text-2xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.account.security" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-4">
                <div class="flex w-1/2 flex-col">
                    <div class="text-xl">
                        <T key-name="dashboard.user.settings.user.email" />
                    </div>
                    <div
                        class="w-full overflow-hidden overflow-ellipsis text-sm dark:text-natural-300"
                    >
                        <T
                            key-name="dashboard.user.settings.user.email.description"
                        />
                        <b
                            class="overflow-hidden overflow-ellipsis text-text dark:text-natural-50"
                        >
                            {{ currUser.email }}
                        </b>
                    </div>
                </div>
                <div class="flex w-1/2 items-center justify-end">
                    <button
                        class="flex w-40 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 text-xl hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="isEmailChangeVisible = !isEmailChangeVisible"
                    >
                        <T key-name="common.change" />
                        <div class="flex items-center">
                            <span class="pi pi-angle-right" />
                        </div>
                    </button>
                    <FormEmailChange
                        :visible="isEmailChangeVisible"
                        :curr-email="currUser.email"
                        @close="isEmailChangeVisible = false"
                        @change-email="changeEmail"
                    />
                </div>
            </div>
            <div class="flex pb-5 pl-4">
                <div class="flex w-full flex-col">
                    <div class="text-xl">
                        <T key-name="dashboard.user.settings.password" />
                    </div>
                    <div class="text-sm dark:text-natural-300">
                        <T
                            key-name="dashboard.user.settings.password.description"
                        />
                    </div>
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="flex w-40 justify-center rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 text-xl hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
                        @click="
                            isPasswordChangeVisible = !isPasswordChangeVisible
                        "
                    >
                        <T key-name="common.change" />
                        <div class="flex items-center">
                            <span class="pi pi-angle-right" />
                        </div>
                    </button>
                    <FormPasswordChange
                        :visible="isPasswordChangeVisible"
                        @close="isPasswordChangeVisible = false"
                    />
                </div>
            </div>
            <div class="flex items-center pb-2">
                <div class="bg h-0.5 w-6 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-2 text-2xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.appearance" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-4">
                <div class="flex w-full flex-col">
                    <div class="text-xl">
                        <T key-name="dashboard.user.settings.appearance" />
                    </div>
                    <div class="text-sm dark:text-natural-300">
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
                                class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200',
                            },
                            input: {
                                class: 'text-text dark:text-natural-50',
                            },
                            item: {
                                class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-100 dark:bg-natural-900',
                            },
                            wrapper: {
                                class: 'bg-natural-100 dark:bg-natural-900 text-text dark:text-natural-50',
                            },
                        }"
                        @change="handleChange()"
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
                            <div class="align-items-center flex items-center">
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
                    <div class="text-sm dark:text-natural-300">
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
                                class: 'font-nunito text-text bg-natural-100 dark:bg-natural-900 dark:text-natural-50 z-10 hover:bg-natural-200',
                            },
                            input: {
                                class: 'text-text dark:text-natural-50',
                            },
                            item: {
                                class: 'hover:bg-dandelion-100 text-text dark:text-natural-50 bg-natural-100 dark:bg-natural-900',
                            },
                            wrapper: {
                                class: 'bg-natural-100 dark:bg-natural-900 text-text dark:text-natural-50',
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
                            <div class="align-items-center flex items-center">
                                <T :key-name="slotProps.option.name" />
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
            <div class="flex items-center pb-2">
                <div class="bg h-0.5 w-6 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-2 text-2xl font-semibold text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.delete.log.out" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
            <div class="flex pb-5 pl-4">
                <div class="flex w-full flex-col">
                    <div class="text-xl">
                        <T key-name="dashboard.user.settings.log.out" />
                    </div>
                    <div class="text-sm dark:text-natural-300">
                        <T
                            key-name="dashboard.user.settings.log.out.description"
                        />
                    </div>
                </div>
                <div class="flex w-full items-center justify-end">
                    <button
                        class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:hover:bg-pesto-600"
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
                        class="w-40 rounded-md border-2 border-mahagony-500 bg-natural-50 px-2 text-text hover:border-mahagony-600 hover:bg-mahagony-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="
                            isDeleteAccountVisible = !isDeleteAccountVisible
                        "
                    >
                        <T key-name="dashboard.user.settings.delete" />
                    </button>
                    <FormDeleteAccount
                        :visible="isDeleteAccountVisible"
                        @close="isDeleteAccountVisible = false"
                    />
                </div>
            </div>
        </div>
    </Sidebar>
</template>
