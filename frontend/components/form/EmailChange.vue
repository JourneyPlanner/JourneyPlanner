<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
    currEmail: { type: String, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "changeEmail"]);
const { t } = useTranslate();
const password = ref("");
const newEmail = ref("");
const toast = useToast();
const client = useSanctumClient();

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};

async function changeEmail() {
    if (newEmail.value == "" || password.value == "") {
        toast.add({
            severity: "error",
            summary: t.value("common.toast.error.heading"),
            detail: t.value("empty.input.toast.error"),
            life: 6000,
        });
    } else {
        if (props.currEmail == newEmail.value) {
            toast.add({
                severity: "error",
                summary: t.value("common.toast.error.heading"),
                detail: t.value(
                    "dashboard.user.settings.toast.same.email.description",
                ),
                life: 6000,
            });
        } else {
            await client(`/api/user/change-email`, {
                method: "POST",
                body: {
                    email: newEmail.value,
                    password: password.value,
                },
                async onResponse({ response }) {
                    console.log(response);
                    if (response.ok) {
                        emit("changeEmail", newEmail);
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
        class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-lg:collapse sm:w-1/4 md:rounded-xl"
        :pt="{
            root: {
                class: 'font-nunito bg-background dark:bg-background-dark z-10',
            },
            header: {
                class: 'flex justify-start pb-2 font-nunito bg-background dark:bg-background-dark',
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
            mask: {
                class: 'max-md:collapse',
            },
        }"
        @hide="close"
    >
        <template #header>
            <div class="flex w-[90%] items-center">
                <div class="bg h-0.5 w-5 bg-calypso-400" />
                <div
                    class="flex-grow-5 dark:text-white mx-3 text-3xl text-text dark:text-natural-50"
                >
                    <T key-name="dashboard.user.settings.email.change" />
                </div>
                <div class="bg h-0.5 flex-grow bg-calypso-400" />
            </div>
        </template>
        <div class="pl-4">
            <div class="-pt-4 text-sm text-natural-700 dark:text-natural-300">
                <T
                    key-name="dashboard.user.settings.change.email.description"
                />
                {{ props.currEmail }}
            </div>
            <div class="flex items-center pl-6 pt-4">
                <div class="flex w-full flex-col items-center">
                    <div class="flex w-2/3 items-start dark:text-natural-50">
                        <T key-name="dashboard.user.settings.enter.new.email" />
                    </div>
                    <input
                        id="password"
                        v-model="newEmail"
                        name="password"
                        class="mb-3 w-2/3 rounded-md border-2 border-natural-400 bg-natural-50 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                    />
                    <div class="flex w-2/3 items-start dark:text-natural-50">
                        <T
                            key-name="dashboard.user.settings.enter.current.password"
                        />
                    </div>
                    <input
                        id="password"
                        v-model="password"
                        name="password"
                        type="password"
                        class="w-2/3 rounded-md border-2 border-natural-400 bg-natural-50 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                        @keyup.enter="changeEmail"
                    />
                </div>
            </div>
            <div class="flex w-full justify-center pb-6 pt-6">
                <button
                    class="w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    @click="changeEmail"
                >
                    <T key-name="dashboard.user.settings.change.email" />
                </button>
            </div>
        </div>
    </Dialog>
    <Sidebar
        v-model:visible="isVisible"
        modal
        position="right"
        :auto-z-index="true"
        :draggable="false"
        :header="t('dashboard.user.settings')"
        class="z-50 mt-auto flex h-[95%] w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:w-4/5 md:hidden md:rounded-xl lg:-z-10"
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
                class: 'md:collapse bg-natural-50',
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
                <T key-name="dashboard.user.settings.email.change" />
            </div>
        </template>
        <div class="h-full pl-8">
            <div class="-pt-4 text-sm text-natural-700 dark:text-natural-300">
                <T
                    key-name="dashboard.user.settings.change.email.description.part1"
                />
                <br />
                <T
                    key-name="dashboard.user.settings.change.email.description.part2"
                />
                <b>{{ props.currEmail }}</b>
            </div>
            <div class="flex items-center pl-6 pt-4">
                <div class="flex w-full flex-col items-center">
                    <div
                        class="mb-2 mr-10 flex w-full items-start text-sm dark:text-natural-50"
                    >
                        <T key-name="dashboard.user.settings.enter.new.email" />
                    </div>
                    <input
                        id="password"
                        v-model="newEmail"
                        name="password"
                        class="mb-3 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400"
                    />
                    <div
                        class="mb-2 mr-10 flex w-full items-start text-sm dark:text-natural-50"
                    >
                        <T
                            key-name="dashboard.user.settings.enter.current.password"
                        />
                    </div>
                    <input
                        id="password"
                        v-model="password"
                        name="password"
                        type="password"
                        class="mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400"
                        @keyup.enter="changeEmail"
                    />
                </div>
            </div>
            <div class="mt-auto flex w-full justify-center pb-6 pt-6">
                <button
                    class="mt-auto w-40 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                    @click="changeEmail"
                >
                    <T key-name="dashboard.user.settings.change.email" />
                </button>
            </div>
        </div>
    </Sidebar>
</template>
