<script setup lang="ts">
import { T, useTranslate } from "@tolgee/vue";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
    visible: { type: Boolean, required: true },
    requiresPassword: { type: Boolean, required: true },
});

const isVisible = ref(props.visible);
const isConfirmVisible = ref(false);
const emit = defineEmits(["close", "changeEmail"]);
const { t } = useTranslate();
const client = useSanctumClient();
const { refreshIdentity } = useSanctumAuth();
const toast = useToast();

watch(
    () => props.visible,
    (value) => {
        isVisible.value = value;
    },
);

const validationSchema = props.requiresPassword
    ? yup.object({
          password: yup
              .string()
              .min(8, () => t.value("form.input.password.error"))
              .required(() => t.value("form.input.required")),
      })
    : "";

const { errors, handleSubmit, defineField, handleReset } = useForm({
    validationSchema,
});

const [password] = defineField("password");

const close = () => {
    handleReset();
    emit("close");
};

const onSubmit = handleSubmit(() => {
    isConfirmVisible.value = true;
});

/**
 * deletes the account of the user
 */
async function deleteAccount() {
    await client(`/api/user/delete-account`, {
        method: "DELETE",
        body: {
            password: password.value,
        },
        async onResponseError({ response }) {
            if (response.status === 401) {
                toast.add({
                    severity: "error",
                    summary: t.value("common.toast.error.heading"),
                    detail: t.value("wrong.password.toast.error"),
                    life: 6000,
                });
            }
        },
    });
    localStorage.removeItem("upload_token");
    await refreshIdentity();
    await navigateTo("/");
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
            class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/2 2xl:w-1/3"
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
                    class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 focus:outline-none focus-ring-1 h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex w-[90%] items-center">
                    <div class="h-0.5 w-5 bg-mahagony-500" />
                    <div
                        class="flex-grow-5 mx-3 text-3xl text-text dark:text-natural-50"
                    >
                        <T key-name="dashboard.user.settings.delete.account" />
                    </div>
                    <div class="h-0.5 flex-grow bg-mahagony-500" />
                </div>
            </template>
            <div class="pl-1">
                <div
                    class="-pt-4 text-sm text-natural-700 dark:text-natural-300"
                >
                    <T
                        key-name="dashboard.user.settings.delete.account.description.part1"
                    />
                    <b class="text-text dark:text-natural-50">
                        <T
                            key-name="dashboard.user.settings.delete.account.description.part2"
                    /></b>
                    <T
                        key-name="dashboard.user.settings.delete.account.description.part3"
                    />
                    <div v-if="requiresPassword" class="mt-2">
                        <T
                            key-name="dashboard.user.settings.delete.account.description.part4"
                        />
                    </div>
                </div>
                <div class="flex items-center pl-6 pt-4">
                    <div
                        v-if="requiresPassword"
                        class="flex w-full flex-col items-center"
                    >
                        <div
                            class="flex w-2/3 items-start text-text dark:text-natural-50"
                        >
                            <T key-name="common.enter.password" />
                        </div>
                        <input
                            id="passwordDelete"
                            v-model="password"
                            name="password"
                            type="password"
                            class="focus-ring-1 mb-1 w-2/3 rounded-md border-2 border-natural-400 bg-natural-50 py-0.5 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-900 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        />
                        <span
                            class="flex w-2/3 justify-start text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.password }}</span
                        >
                    </div>
                </div>
                <div class="flex w-full justify-center pb-2 pt-4">
                    <button
                        class="mr-5 w-40 rounded-md bg-natural-50 px-2 pr-7 text-lg font-medium text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                        @click="close"
                    >
                        <T key-name="common.button.cancel" />
                    </button>
                    <button
                        class="w-44 rounded-md border-2 border-mahagony-500 bg-natural-50 px-2 py-0.5 text-base font-medium hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="onSubmit"
                    >
                        <T key-name="dashboard.user.settings.delete.account" />
                    </button>
                </div>
            </div>
        </Dialog>
        <Dialog
            v-model:visible="isConfirmVisible"
            modal
            :auto-z-index="true"
            :draggable="false"
            class="z-50 flex w-2/3 flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:rounded-xl lg:w-1/2 2xl:w-1/4"
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
                    class: 'z-30 self-center text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 focus:outline-none focus-ring-1 h-10 w-10 ',
                },
                mask: {
                    class: 'max-sm:collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex w-[90%] items-center">
                    <div class="h-0.5 w-3 bg-mahagony-500" />
                    <div
                        class="flex-grow-5 mx-2 text-3xl text-text dark:text-natural-50"
                    >
                        <T key-name="dashboard.user.settings.delete.account" />
                    </div>
                    <div class="h-0.5 flex-grow bg-mahagony-500" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-ml-8 w-[110%] overflow-hidden overflow-ellipsis text-natural-700 dark:text-natural-300"
                >
                    <T
                        key-name="dashboard.user.settings.delete.account.confirm.part1"
                    />
                    <b class="text-natural-700 dark:text-natural-50">
                        <T key-name="dashboard.user.settings.delete.little"
                    /></b>
                    <T
                        key-name="dashboard.user.settings.delete.account.confirm.part2"
                    />
                    <b>
                        <T
                            key-name="dashboard.user.settings.delete.account.confirm.part3"
                        />
                    </b>
                </div>

                <div class="flex w-full justify-center pb-1 pt-7">
                    <button
                        class="ml-2 w-40 rounded-md bg-natural-50 px-2 pr-5 text-xl text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                        @click="isConfirmVisible = false"
                    >
                        <T key-name="common.button.cancel" />
                    </button>
                    <button
                        class="ml-1 mr-6 mt-auto w-[93%] text-nowrap rounded-md border-[3px] border-mahagony-500 bg-natural-50 px-2 py-1 pl-2 text-base font-semibold text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="deleteAccount"
                    >
                        <T key-name="dashboard.user.settings.delete.account" />
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
                    <span class="pi pi-angle-left text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="dashboard.user.settings.delete" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-pt-4 w-11/12 overflow-hidden overflow-ellipsis text-[0.95rem] text-natural-700 dark:text-natural-200"
                >
                    <T
                        key-name="dashboard.user.settings.delete.account.description.part1"
                    />
                    <b class="text-text dark:text-natural-50">
                        <T
                            key-name="dashboard.user.settings.delete.account.description.part2"
                    /></b>
                    <T
                        key-name="dashboard.user.settings.delete.account.description.part3"
                    />
                    <div v-if="requiresPassword" class="mt-2">
                        <T
                            key-name="dashboard.user.settings.delete.account.description.part4"
                        />
                    </div>
                </div>
                <div
                    v-if="requiresPassword"
                    class="flex items-center pl-6 pt-4"
                >
                    <div class="flex w-full flex-col items-center">
                        <div
                            class="</div> mb-2 mr-12 mt-2 flex w-full items-start dark:text-natural-50"
                        >
                            <T
                                key-name="dashboard.user.settings.enter.current.password"
                            />
                        </div>
                        <input
                            id="passwordDeleteMobile"
                            v-model="password"
                            name="password"
                            type="password"
                            class="focus-ring-1 mb-1 mr-12 w-full rounded-md border-2 border-natural-400 bg-natural-100 py-1 pl-3 text-text placeholder:text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-700 dark:bg-natural-800 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        />
                        <span
                            class="mr-12 flex w-full justify-start text-sm text-mahagony-600 dark:text-mahagony-300"
                            >{{ errors.password }}</span
                        >
                    </div>
                </div>
                <div class="mt-auto flex w-full flex-col justify-center">
                    <div class="-ml-2 mb-6 flex w-full justify-center">
                        <button
                            class="w-40 rounded-md bg-natural-50 px-2 text-xl font-semibold text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                            @click="close"
                        >
                            <T key-name="common.button.cancel" />
                        </button>
                    </div>
                    <button
                        class="ml-1 mr-6 mt-auto w-[93%] rounded-xl border-[3px] border-mahagony-500 bg-natural-50 px-2 py-0.5 pl-2 text-xl font-semibold hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="onSubmit"
                    >
                        <T key-name="dashboard.user.settings.delete" />
                    </button>
                </div>
            </div>
        </Sidebar>
        <Sidebar
            v-model:visible="isConfirmVisible"
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
            @hide="close"
        >
            <template #header>
                <button
                    class="-ml-6 flex justify-center pr-4"
                    @click="isConfirmVisible = false"
                >
                    <span class="pi pi-angle-down text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="dashboard.user.settings.delete" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-pt-4 w-11/12 overflow-hidden overflow-ellipsis text-base text-natural-700 dark:text-natural-200"
                >
                    <T
                        key-name="dashboard.user.settings.delete.account.confirm.part1"
                    />
                    <b class="text-natural-700 dark:text-natural-50">
                        <T key-name="dashboard.user.settings.delete.little"
                    /></b>
                    <T
                        key-name="dashboard.user.settings.delete.account.confirm.part2"
                    />
                    <b>
                        <T
                            key-name="dashboard.user.settings.delete.account.confirm.part3"
                        />
                    </b>
                </div>

                <div class="mt-auto flex w-full flex-col justify-center">
                    <div class="-ml-2 mb-4 mt-5 flex w-full justify-center">
                        <button
                            class="w-40 rounded-md bg-natural-50 px-2 text-xl font-semibold text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                            @click="isConfirmVisible = false"
                        >
                            <T key-name="common.button.cancel" />
                        </button>
                    </div>
                    <button
                        class="ml-1 mr-6 mt-auto w-[93%] rounded-xl border-[3px] border-mahagony-500 bg-natural-50 px-2 py-0.5 pl-2 text-xl font-semibold hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="deleteAccount"
                    >
                        <T key-name="dashboard.user.settings.delete" />
                    </button>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
