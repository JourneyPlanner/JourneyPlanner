<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    isSidebarVisible: {
        type: Boolean,
        required: true,
    },
    texts: {
        type: Object,
        required: true,
    },
    link: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["close", "updateTexts"]);

const isVisible = ref(props.isSidebarVisible);
const { t } = useTranslate();
const textGerman = ref(props.texts.de?.text);
const textEnglish = ref(props.texts.en?.text);
const buttonTextGerman = ref(props.texts.de?.button);
const buttonTextEnglish = ref(props.texts.en?.button);
const headlineTextGerman = ref(props.texts.de?.company_name);
const headlineTextEnglish = ref(props.texts.en?.company_name);
const link = ref(props.link);
const client = useSanctumClient();
const route = useRoute();
const toast = useToast();

watch(
    () => props.isSidebarVisible,
    (value) => {
        isVisible.value = value;
        textGerman.value = props.texts.de?.text;
        textEnglish.value = props.texts.en?.text;
        buttonTextGerman.value = props.texts.de?.button;
        buttonTextEnglish.value = props.texts.en?.button;
        headlineTextGerman.value = props.texts.de?.company_name;
        headlineTextEnglish.value = props.texts.en?.company_name;
        link.value = props.link;
    },
);

const close = () => {
    emit("close");
};

async function handleSubmit() {
    const texts = {
        texts: [
            {
                language: "de",
                texts: [
                    {
                        key: "text",
                        value: textGerman.value,
                    },
                    {
                        key: "button",
                        value: buttonTextGerman.value,
                    },
                    {
                        key: "button_link",
                        value: link.value,
                    },
                    {
                        key: "company_name",
                        value: headlineTextGerman.value,
                    },
                ],
            },
            {
                language: "en",
                texts: [
                    {
                        key: "text",
                        value: textEnglish.value,
                    },
                    {
                        key: "button",
                        value: buttonTextEnglish.value,
                    },
                    {
                        key: "button_link",
                        value: link.value,
                    },
                    {
                        key: "company_name",
                        value: headlineTextEnglish.value,
                    },
                ],
            },
        ],
    };

    const buisnessTexts = {
        texts: [
            {
                company_name: headlineTextGerman.value,
                button_link: link.value,
                button: buttonTextGerman.value,
                text: textGerman.value,
            },
            {
                company_name: headlineTextEnglish.value,
                button_link: link.value,
                button: buttonTextEnglish.value,
                text: textEnglish.value,
            },
        ],
    };

    await client(`/api/business/${route.params.slug}/texts `, {
        method: "POST",
        body: texts,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value(
                        "form.input.activity.edit.toast.success.heading",
                    ),
                    detail: t.value(
                        "form.input.activity.edit.toast.success.detail",
                    ),
                    life: 6000,
                });
                emit("updateTexts", buisnessTexts);
                close();
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
</script>
<template>
    <div>
        <Sidebar
            id="member-sidebar"
            v-model:visible="isVisible"
            position="right"
            :block-scroll="true"
            class="-md z-50 mt-auto flex flex-col bg-background font-nunito dark:bg-background-dark"
            :pt="{
                root: {
                    class: 'font-nunito bg-background dark:bg-background-dark z-10 w-[22rem]',
                },
                header: {
                    class: 'flex justify-start pb-2 pl-9 font-nunito bg-background dark:bg-background-dark dark:text-natural-50',
                },
                title: {
                    class: 'font-nunito text-4xl font-semibold',
                },
                content: {
                    class: 'pl-3 font-nunito bg-background dark:bg-background-dark px-0 sm:pr-4 h-full',
                },
                footer: { class: 'h-0' },
                closeButton: {
                    class: 'justify-start w-full h-full items-center collapse',
                },
            }"
            @hide="close"
        >
            <template #header>
                <button class="-ml-6 flex justify-center pr-4" @click="close">
                    <span
                        class="pi pi-times text-2xl text-natural-500 hover:text-natural-900 dark:text-natural-400 dark:hover:text-natural-100"
                    />
                </button>
                <div class="w-full">
                    <h1
                        class="mr-3 truncate text-nowrap text-2xl font-semibold text-text dark:text-natural-50"
                    >
                        <T key-name="business.edit.text" />
                    </h1>
                </div>
            </template>
            <form class="flex flex-col" @submit.prevent="handleSubmit">
                <div class="flex items-center">
                    <div
                        class="flex flex-row justify-start pr-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <h3>
                            <T key-name="business.text.edit.headline" />
                        </h3>
                    </div>
                    <span
                        class="col-span-6 col-start-5 mt-1 inline-block h-0.5 w-full flex-1 bg-calypso-400"
                    />
                </div>
                <div
                    class="pt-2 font-nunito font-medium text-text dark:text-natural-50"
                >
                    <T key-name="business.text.edit.headline.detail" />
                </div>
                <div class="max-w-lg pt-6">
                    <div
                        class="font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <T key-name="common.deutsch" />
                    </div>
                    <input
                        id="german-headline-text"
                        v-model="headlineTextGerman"
                        type="text"
                        maxlength="50"
                        class="w-full rounded-lg border-2 border-natural-300 bg-natural-50 px-2.5 py-0.5 text-lg font-medium text-text placeholder:text-natural-500 hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                    <div
                        class="items-end-end flex w-full justify-end pt-1 text-natural-500 dark:text-natural-300"
                    >
                        {{ headlineTextGerman.length }}/50
                        <T key-name="business.edit.text.characters" />
                    </div>

                    <div
                        class="pt-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <T key-name="common.english" />
                    </div>
                    <input
                        id="english-headline-text"
                        v-model="headlineTextEnglish"
                        type="text"
                        maxlength="50"
                        class="w-full rounded-lg border-2 border-natural-300 bg-natural-50 px-2.5 py-0.5 text-lg font-medium text-text placeholder:text-natural-500 hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                    <div
                        class="items-end-end mb-6 flex w-full justify-end pt-1 text-natural-500 dark:text-natural-300"
                    >
                        {{ headlineTextEnglish.length }}/50
                        <T key-name="business.edit.text.characters" />
                    </div>
                </div>
                <div class="flex items-center pt-8">
                    <div
                        class="flex flex-row justify-start pr-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <h3><T key-name="business.text.edit.text" /></h3>
                    </div>
                    <span
                        class="col-span-6 col-start-5 mt-1 inline-block h-0.5 w-full flex-1 bg-calypso-400"
                    />
                </div>
                <div
                    class="pt-4 font-nunito font-medium text-text dark:text-natural-50"
                >
                    <T key-name="business.text.edit.text.detail" />
                </div>
                <div class="max-w-lg pt-6">
                    <div class="mb-6">
                        <label
                            class="mb-1 block text-xl font-medium text-text dark:text-natural-50"
                            ><T key-name="common.deutsch"
                        /></label>
                        <textarea
                            v-model="textGerman"
                            maxlength="600"
                            class="h-32 w-full rounded-lg border-2 border-natural-300 bg-natural-50 p-3 text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        ></textarea>
                        <div
                            class="items-end-end flex w-full justify-end pt-1 text-natural-500 dark:text-natural-300"
                        >
                            {{ textGerman.length }}/600
                            <T key-name="business.edit.text.characters" />
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xl font-medium text-text dark:text-natural-50"
                            ><T key-name="common.english"
                        /></label>
                        <textarea
                            v-model="textEnglish"
                            maxlength="600"
                            class="h-32 w-full rounded-lg border-2 border-natural-300 bg-natural-50 p-3 text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        ></textarea>
                        <div
                            class="items-end-end flex w-full justify-end pt-1 text-natural-500 dark:text-natural-300"
                        >
                            {{ textEnglish.length }}/600
                            <T key-name="business.edit.text.characters" />
                        </div>
                    </div>
                </div>
                <div class="flex items-center pt-8">
                    <div
                        class="flex flex-row justify-start pr-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <h3><T key-name="business.text.edit.link" /></h3>
                    </div>
                    <span
                        class="col-span-6 col-start-5 mt-1 inline-block h-0.5 w-full flex-1 bg-calypso-400"
                    />
                </div>
                <div
                    class="pt-4 font-nunito font-medium text-text dark:text-natural-50"
                >
                    <T key-name="business.text.edit.link.detail" />
                </div>
                <div class="max-w-lg pt-6">
                    <input
                        id="link"
                        v-model="link"
                        type="text"
                        placeholder="www.journeyplanner.io"
                        class="w-full rounded-lg border-2 border-natural-300 bg-natural-50 px-2.5 py-0.5 text-lg font-medium text-text placeholder:text-natural-500 hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                </div>
                <div class="flex items-center pt-8">
                    <div
                        class="flex flex-row justify-start pr-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <h3><T key-name="business.text.edit.button" /></h3>
                    </div>
                    <span
                        class="col-span-6 col-start-5 mt-1 inline-block h-0.5 w-full flex-1 bg-calypso-400"
                    />
                </div>
                <div
                    class="pt-4 font-nunito font-medium text-text dark:text-natural-50"
                >
                    <T key-name="business.text.edit.button.detail" />
                </div>
                <div class="max-w-lg pt-6">
                    <div
                        class="font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <T key-name="common.deutsch" />
                    </div>
                    <input
                        id="german-button-text"
                        v-model="buttonTextGerman"
                        type="text"
                        maxlength="25"
                        placeholder="Besuche unsere Webseite"
                        class="w-full rounded-lg border-2 border-natural-300 bg-natural-50 px-2.5 py-0.5 text-lg font-medium text-text placeholder:text-natural-500 hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                    <div
                        class="items-end-end flex w-full justify-end pt-1 text-natural-500 dark:text-natural-300"
                    >
                        {{ buttonTextGerman.length }}/25
                        <T key-name="business.edit.text.characters" />
                    </div>

                    <div
                        class="pt-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <T key-name="common.english" />
                    </div>
                    <input
                        id="english-button-text"
                        v-model="buttonTextEnglish"
                        type="text"
                        maxlength="25"
                        placeholder="Visit our site"
                        class="w-full rounded-lg border-2 border-natural-300 bg-natural-50 px-2.5 py-0.5 text-lg font-medium text-text placeholder:text-natural-500 hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                    />
                    <div
                        class="items-end-end mb-6 flex w-full justify-end pt-1 text-natural-500 dark:text-natural-300"
                    >
                        {{ buttonTextEnglish.length }}/25
                        <T key-name="business.edit.text.characters" />
                    </div>
                </div>
                <Button
                    type="submit"
                    :label="t('common.save')"
                    icon="pi pi-save"
                    :pt="{
                        root: { class: 'flex items-center justify-center' },
                        label: {
                            class: 'display-block flex-none font-nunito',
                        },
                    }"
                    class="mt-auto flex h-9 w-full flex-row justify-center rounded-xl border-2 border-atlantis-400 bg-natural-50 text-center text-text hover:bg-atlantis-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-atlantis-30040"
                />
            </form>
        </Sidebar>
    </div>
</template>
