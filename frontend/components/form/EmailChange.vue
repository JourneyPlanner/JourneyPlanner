<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    visible: { type: Boolean, required: true },
    currEmail: { type: String, required: true },
});

const isVisible = ref(props.visible);
const emit = defineEmits(["close", "changeEmail"]);
const { t } = useTranslate();
const password = ref("");
const toast = useToast();
const client = useSanctumClient();

const { errors, handleSubmit, defineField } = useForm({
    validationSchema: yup.object({
        newEmail: yup.string().email(t.value("form.input.email.error")),
    }),
});

const [newEmail] = defineField("newEmail");

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const close = () => {
    emit("close");
};

const onSubmit = handleSubmit((values) => {
    console.log(values);
    changeEmail();
});

/**
 * changes the email of the user
 */
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
                method: "PUT",
                body: {
                    email: newEmail.value,
                    password: password.value,
                },
                async onResponse({ response }) {
                    if (response.ok) {
                        emit("changeEmail", newEmail);
                        password.value = "";
                        newEmail.value = "";
                    }
                },
                async onResponseError({ response }) {
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
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            block-scroll
            :auto-z-index="true"
            :draggable="false"
            class="z-50 flex flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/3"
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
                    class: 'justify-end items-center w-fit pl-5',
                },
                closeButtonIcon: {
                    class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-text h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex w-[90%] items-center">
                    <div class="h-0.5 w-5 bg-calypso-400" />
                    <div
                        class="flex-grow-5 mx-3 text-3xl text-text dark:text-natural-50"
                    >
                        <T key-name="dashboard.user.settings.email.change" />
                    </div>
                    <div class="h-0.5 flex-grow bg-calypso-400" />
                </div>
            </template>
            <div class="pl-1">
                <div
                    class="-pt-2 overflow-hidden overflow-ellipsis text-base text-natural-700 dark:text-natural-300"
                >
                    <T
                        key-name="dashboard.user.settings.change.email.description"
                    />
                    {{ props.currEmail }}
                </div>
                <div class="flex items-center justify-center pl-0 pt-4">
                    <div class="flex w-full flex-col items-center">
                        <div
                            class="flex w-2/3 items-start text-text dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.new.email"
                            />
                        </div>
                        <input
                            id="email"
                            v-model="newEmail"
                            name="newEmail"
                            class="focus-ring-1 w-2/3 rounded-md border-2 border-natural-300 bg-natural-50 py-0.5 pl-3 pr-1 text-text placeholder:text-text hover:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                        />
                        <span
                            class="mb-3 flex w-2/3 justify-start text-sm text-mahagony-600"
                            >{{ errors.newEmail }}</span
                        >
                        <div
                            class="flex w-2/3 items-start text-text dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.current.password"
                            />
                        </div>
                        <input
                            id="passwordEmail"
                            v-model="password"
                            name="password"
                            type="password"
                            class="focus-ring-1 w-2/3 rounded-md border-2 border-natural-300 bg-natural-50 py-0.5 pl-3 pr-1 text-text placeholder:text-text hover:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400"
                            @keyup.enter="onSubmit"
                        />
                    </div>
                </div>
                <div class="flex w-full justify-center pb-6 pt-6">
                    <button
                        class="w-44 rounded-md border-2 border-dandelion-300 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="onSubmit"
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
            class="z-50 mt-auto flex h-[95%] w-full flex-col rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden sm:w-4/5 md:rounded-xl lg:-z-10"
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
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span class="pi pi-angle-left text-3xl" />
                </button>
                <div class="font-nunito text-4xl font-semibold">
                    <T key-name="dashboard.user.settings.email.change" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-pt-4 overflow-hidden overflow-ellipsis text-sm text-natural-700 dark:text-natural-300"
                >
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
                            class="mb-2 mr-10 flex w-full items-start text-sm text-text dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.new.email"
                            />
                        </div>
                        <input
                            id="emailMobile"
                            v-model="newEmail"
                            name="newEmail"
                            class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400"
                        />
                        <span
                            class="mb-3 mr-10 flex w-full justify-start text-sm text-mahagony-600"
                            >{{ errors.newEmail }}</span
                        >
                        <div
                            class="mb-2 mr-10 flex w-full items-start text-sm text-text dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.current.password"
                            />
                        </div>
                        <input
                            id="passwordEmailMobile"
                            v-model="password"
                            name="password"
                            type="password"
                            class="focus-ring-1 mr-10 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400"
                            @keyup.enter="onSubmit"
                        />
                    </div>
                </div>
                <div class="mt-auto flex w-full justify-center">
                    <button
                        class="ml-1 mr-6 mt-auto w-full rounded-md border-[3px] border-dandelion-300 bg-natural-50 px-2 py-1 pl-2 text-2xl font-semibold hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="onSubmit"
                    >
                        <T key-name="common.save" />
                    </button>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
