<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
    username: { type: String, required: true },
    displayname: { type: String, required: true },
});

const emit = defineEmits(["close"]);

const router = useRouter();
const client = useSanctumClient();
const toast = useToast();
const { t } = useTranslate();

const isVisible = ref(props.visible);
const created_at = ref({ day: NaN, month: NaN, year: NaN });
const templates = ref<Template[]>();
const openedTemplate = ref<Template>();
const isTemplatePopupVisible = ref(false);

watch(
    () => props.visible,
    async (value) => {
        isVisible.value = value;

        if (isVisible.value == true) {
            await client(`/api/user/${props.username}`, {
                method: "GET",
                async onResponse({ response }) {
                    const date = new Date(response._data.created_at);
                    created_at.value = {
                        day: date.getDate(),
                        month: date.getMonth() + 1,
                        year: date.getFullYear(),
                    };
                },
                async onRequestError() {
                    close();
                    toast.add({
                        severity: "error",
                        summary: t.value("common.toast.error.heading"),
                        detail: t.value("common.error.unknown"),
                        life: 4000,
                    });
                },
                async onResponseError({ response }) {
                    close();
                    if (response.status === 404) {
                        toast.add({
                            severity: "error",
                            summary: t.value("common.toast.error.heading"),
                            detail: t.value("profile.error.notFound"),
                            life: 5000,
                        });
                    } else {
                        toast.add({
                            severity: "error",
                            summary: t.value("common.toast.error.heading"),
                            detail: t.value("common.error.unknown"),
                            life: 5000,
                        });
                    }
                },
            });

            await client(`/api/user/${props.username}/template?per_page=8`, {
                async onResponse({ response }) {
                    if (response.ok) {
                        templates.value = response._data.data;
                    }
                },
            });
        }
    },
);

const close = (): void => {
    emit("close");
};
</script>

<template>
    <div>
        <Dialog
            v-model:visible="isVisible"
            modal
            :block-scroll="true"
            :auto-z-index="true"
            :draggable="false"
            close-on-escape
            dismissable-mask
            class="z-50 mx-5 flex w-full flex-col rounded-lg bg-background font-nunito dark:bg-background-dark max-sm:collapse sm:w-9/12 md:w-8/12 md:rounded-xl lg:w-6/12 xl:w-6/12"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10',
                },
                header: {
                    class: 'flex pb-0 font-nunito bg-background dark:bg-background-dark px-2 sm:px-4',
                },
                title: {
                    class: 'font-nunito text-2xl font-semibold text-text dark:text-natural-50',
                },
                content: {
                    class: 'font-nunito bg-background dark:bg-background-dark px-2 sm:px-4 h-full',
                },
                footer: { class: 'h-0' },
                closeButtonIcon: {
                    class: 'z-20 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50 h-10 w-10 ',
                },
            }"
            @hide="close"
        >
            <template #header>
                <div class="flex flex-row items-center">
                    <span
                        class="mr-2 h-0.5 w-2 bg-calypso-400 xs:mr-2 xs:w-4 sm:mr-3.5 sm:w-8 md:mr-3 md:w-5"
                    />
                    <h1
                        class="max-w-36 truncate text-nowrap text-2xl font-medium text-text dark:text-natural-50 xs:max-w-48 sm:max-w-72 md:max-w-80 lg:max-w-96 xl:max-w-[28rem] 2xl:max-w-[32rem]"
                    >
                        {{ displayname }}
                    </h1>
                </div>
                <span
                    class="ml-2 h-0.5 w-full bg-calypso-400 xs:ml-2 sm:ml-3.5 md:ml-3"
                />
                <span
                    class="pi pi-arrow-up-right-and-arrow-down-left-from-center mx-4 cursor-pointer text-xl text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50"
                    @click="router.push('/user/' + username)"
                />
            </template>
            <div class="mt-1">
                <NuxtLink
                    :to="'/user/' + username"
                    class="group ml-4 max-w-36 truncate text-xl text-natural-500 hover:text-calypso-600 dark:text-natural-300 dark:hover:text-calypso-600 xs:ml-6 xs:max-w-48 sm:ml-[2.875rem] sm:max-w-72 md:ml-8 md:max-w-80 lg:max-w-96 xl:max-w-[28rem] 2xl:max-w-[32rem]"
                >
                    @<span class="group-hover:underline">{{ username }}</span>
                </NuxtLink>
            </div>
            <div class="mt-6 flex flex-col">
                <h1
                    class="text-lg font-medium text-text dark:text-natural-50 xs:text-xl md:text-xl"
                >
                    <T key-name="profile.templates" />
                    {{ username }}
                </h1>
                <div
                    v-if="!templates"
                    id="templates-loading"
                    class="relative mt-2 grid grid-cols-2 gap-2 xs:gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <Skeleton
                        v-for="index in 8"
                        :key="index"
                        height="6.75rem"
                        class="rounded-md dark:bg-natural-600"
                    />
                </div>
                <div
                    v-else
                    id="templates"
                    class="relative mt-2 grid grid-cols-2 gap-2 xs:gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <TemplateCardSmall
                        v-for="template in templates"
                        :key="template.id"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="
                            openedTemplate = template;
                            isTemplatePopupVisible = true;
                        "
                    />
                    <div
                        v-if="templates && templates.length === 0"
                        class="col-span-full"
                    >
                        <T key-name="template.none" />
                    </div>
                </div>
                <div class="flex flex-row justify-center">
                    <NuxtLink
                        class="mt-6 flex rounded-xl border-2 border-dandelion-300 bg-natural-50 px-4 py-1 text-center text-text hover:bg-dandelion-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        :to="'/user/' + username"
                    >
                        <T key-name="profile.see.full" />
                    </NuxtLink>
                </div>
            </div>
        </Dialog>
        <Sidebar
            v-model:visible="isVisible"
            modal
            position="bottom"
            :show-close-icon="false"
            :block-scroll="true"
            :auto-z-index="true"
            :draggable="false"
            class="z-50 mt-auto flex h-[80%] flex-col overflow-x-hidden rounded-t-md bg-background font-nunito dark:bg-background-dark sm:hidden lg:-z-10"
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
                    <span class="pi pi-angle-down text-xl" />
                </button>
                <div class="flex w-full gap-x-3">
                    <h1
                        class="truncate text-nowrap text-xl font-medium text-text dark:text-natural-50"
                    >
                        {{ displayname }}
                    </h1>
                    <NuxtLink
                        :to="'/user/' + username"
                        class="group max-w-36 truncate text-xl text-natural-500 hover:text-calypso-600 dark:text-natural-300 xs:max-w-48 sm:ml-[2.875rem] sm:max-w-72 md:ml-8 md:max-w-80 lg:max-w-96 xl:max-w-[28rem] 2xl:max-w-[32rem]"
                    >
                        (@<span class="group-hover:underline">{{
                            username
                        }}</span
                        >)
                    </NuxtLink>
                </div>
            </template>
            <div class="flex h-full flex-col justify-center px-4">
                <h1
                    class="text-lg font-medium text-text dark:text-natural-50 xs:text-xl md:text-xl"
                >
                    <T key-name="profile.templates" />
                    {{ username }}
                </h1>
                <div
                    v-if="!templates"
                    id="templates-loading"
                    class="relative mt-2 grid grid-cols-2 gap-2 xs:gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <Skeleton
                        v-for="index in 6"
                        :key="index"
                        height="6.75rem"
                        class="rounded-md dark:bg-natural-600"
                    />
                </div>
                <div
                    v-else
                    id="templates"
                    class="relative mt-2 grid grid-cols-2 gap-2 xs:gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <TemplateCardSmall
                        v-for="template in templates.slice(0, 6)"
                        :key="template.id"
                        :template="template"
                        :displayed-in-profile="true"
                        @open-template="
                            openedTemplate = template;
                            isTemplatePopupVisible = true;
                        "
                    />
                    <div
                        v-if="templates && templates.length === 0"
                        class="col-span-full"
                    >
                        <T key-name="template.none" />
                    </div>
                </div>
                <div
                    class="mt-auto flex w-full justify-center text-text dark:text-natural-50"
                >
                    <NuxtLink
                        class="w-full text-nowrap rounded-xl border-[3px] border-dandelion-300 bg-natural-50 px-2 py-0.5 pl-2 text-center text-xl font-semibold text-natural-900 hover:bg-dandelion-200 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-200 dark:hover:bg-pesto-600"
                        :to="'/user/' + username"
                    >
                        <T key-name="profile.see.full" />
                    </NuxtLink>
                </div>
            </div>
        </Sidebar>
        <div id="dialogs">
            <TemplatePopup
                v-if="openedTemplate"
                :template="openedTemplate!"
                :is-template-dialog-visible="isTemplatePopupVisible"
                @close="isTemplatePopupVisible = false"
            />
        </div>
    </div>
</template>
