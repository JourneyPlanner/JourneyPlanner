<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    isSidebarVisible: {
        type: Boolean,
        required: true,
    },
    imageEditType: {
        type: String,
        required: true,
    },
    texts: {
        type: Object as PropType<InternationalBusinessSiteTexts>,
        required: true,
    },
});

const emit = defineEmits(["close", "updateImage"]);

const isVisible = ref<boolean>(props.isSidebarVisible);
const toast = useToast();
const { t } = useTranslate();
const client = useSanctumClient();
const file = ref();
const fileType = ref<ImageEditFileType>(
    props.imageEditType as ImageEditFileType,
);
const altTextGerman = ref<string>("");
const altTextEnglish = ref<string>("");
const route = useRoute();
const confirmRemoveVisible = ref<boolean>(false);
const confirmSaveVisible = ref<boolean>(false);
const loadingEdit = ref<boolean>(false);
const shouldClose = ref<boolean>(false);

watch(
    () => props.isSidebarVisible,
    (value) => {
        isVisible.value = value;
        fileType.value = props.imageEditType as ImageEditFileType;
        altTextGerman.value = props.texts.de?.alt_texts[fileType.value] ?? "";
        altTextEnglish.value = props.texts.en?.alt_texts[fileType.value] ?? "";
    },
);

const close = () => {
    shouldClose.value = true;
    file.value = null;
    altTextEnglish.value = "";
    altTextGerman.value = "";
    imageUrl.value = null;
    fileName.value = null;
    emit("close");
};

const fileInput = ref<HTMLInputElement | null>(null);
const imageUrl = ref<string | null>(null);
const fileName = ref<string | null>(null);

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        handleFileUpload({ target: { files } } as unknown as Event);
    }
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target?.files?.length) {
        if (!target.files[0].type.startsWith("image/")) {
            toast.add({
                severity: "error",
                summary: t.value("business.upload.image.filetype.error"),
                detail: t.value("business.upload.image.filetype.error.detail"),
                life: 3000,
            });
            return;
        }
        file.value = target.files[0];

        fileName.value = file.value.name;

        if (file.value.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = () => {
                imageUrl.value = reader.result as string;
            };
            reader.readAsDataURL(file.value);
        } else {
            imageUrl.value = null;
        }
    }
};

const removeImage = () => {
    imageUrl.value = null;
    file.value = null;
    fileName.value = null;
};

async function handleSubmit() {
    loadingEdit.value = true;
    const altTexts = [
        { language: "de", alt_text: altTextGerman.value },
        { language: "en", alt_text: altTextEnglish.value },
    ];

    const formData = new FormData();
    if (file.value) {
        formData.append("image", file.value);
    }

    formData.append("type", props.imageEditType);

    altTexts.forEach((alt, index) => {
        formData.append(`alt_texts[${index}][language]`, alt.language);
        formData.append(`alt_texts[${index}][alt_text]`, alt.alt_text);
    });

    await client(`/api/business/${route.params.slug}/image`, {
        method: "POST",
        body: formData,
        async onResponse({ response }) {
            loadingEdit.value = false;
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("business.upload.image.success", {
                        image:
                            props.imageEditType === "banner"
                                ? t.value("business.banner")
                                : t.value("business.image"),
                    }),
                    detail: t.value("business.upload.image.success.detail", {
                        image:
                            props.imageEditType === "banner"
                                ? t.value("business.banner")
                                : t.value("business.image"),
                    }),
                    life: 6000,
                });
                emit(
                    "updateImage",
                    response._data.alt_texts,
                    response._data.link,
                );
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

async function deleteImage() {
    confirmRemoveVisible.value = false;
    const type = {
        type: props.imageEditType,
    };
    await client(`/api/business/${route.params.slug}/image`, {
        method: "Delete",
        body: type,
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("business.delete.image", {
                        image:
                            props.imageEditType === "banner"
                                ? t.value("business.banner")
                                : t.value("business.image"),
                    }),
                    detail: t.value("business.delete.image.detail", {
                        image:
                            props.imageEditType === "banner"
                                ? t.value("business.banner")
                                : t.value("business.image"),
                    }),
                    life: 6000,
                });
                emit("updateImage");
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

function handleHide() {
    if (shouldClose.value) {
        shouldClose.value = false;
    } else {
        isVisible.value = true;
        confirmSaveVisible.value = true;
    }
}
</script>
<template>
    <div>
        <Sidebar
            id="image-edit-sidebar"
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
            @hide="handleHide"
        >
            <template #header>
                <button
                    class="-ml-6 flex justify-center pr-4"
                    @click="confirmSaveVisible = true"
                >
                    <span
                        class="pi pi-times text-2xl text-natural-500 hover:text-natural-900 dark:text-natural-400 dark:hover:text-natural-100"
                    />
                </button>
                <div class="flex w-full">
                    <h1
                        class="mr-3 truncate text-nowrap text-2xl font-semibold text-text dark:text-natural-50"
                    >
                        <T
                            key-name="business.edit.image"
                            :params="{
                                image:
                                    props.imageEditType === 'banner'
                                        ? t('business.banner')
                                        : t('business.image'),
                            }"
                        />
                    </h1>
                    <Button
                        icon="pi pi-save"
                        :loading="loadingEdit"
                        :pt="{
                            root: { class: 'flex items-center justify-center' },
                            label: {
                                class: 'display-block flex-none font-nunito',
                            },
                        }"
                        class="ml-auto mt-auto flex h-9 flex-row justify-center rounded-xl border-2 border-atlantis-400 bg-natural-50 text-center text-text hover:bg-atlantis-200 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-atlantis-30040"
                        @click="handleSubmit"
                    />
                </div>
            </template>
            <form
                class="flex min-h-full flex-col"
                @submit.prevent="handleSubmit"
            >
                <div class="flex items-center">
                    <div
                        class="flex flex-row justify-start pr-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <h3>
                            <T
                                key-name="business.choose.image"
                                :params="{
                                    image:
                                        props.imageEditType === 'banner'
                                            ? t('business.banner')
                                            : t('business.image'),
                                }"
                            />
                        </h3>
                    </div>
                    <span
                        class="col-span-6 col-start-5 mt-1 inline-block h-0.5 w-full flex-1 bg-calypso-400"
                    />
                </div>
                <div
                    class="pt-2 font-nunito font-medium text-text dark:text-natural-50"
                >
                    <T
                        key-name="business.upload.image.detail"
                        :params="{
                            image:
                                props.imageEditType === 'banner'
                                    ? t('business.banner')
                                    : t('business.image'),
                        }"
                    />
                </div>
                <div
                    class="relative mt-6 flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-calypso-400 bg-natural-100 p-6 dark:border-calypso-600 dark:bg-natural-900"
                    :class="file ? '' : 'py-16'"
                    @dragover.prevent
                    @drop="handleDrop"
                >
                    <input
                        id="upload"
                        ref="fileInput"
                        type="file"
                        class="absolute h-full w-full cursor-pointer opacity-0"
                        accept="image/*"
                        @change="handleFileUpload"
                    />
                    <label
                        for="upload"
                        class="cursor-pointer text-natural-600 dark:text-natural-300"
                        >{{ fileName || t("business.upload.file") }}</label
                    >
                    <img
                        v-if="imageUrl"
                        :src="imageUrl"
                        :alt="t('business.input.uploaded.image')"
                        class="max-h-40 object-contain"
                    />
                </div>
                <div
                    class="items-end-end flex w-full justify-end pt-1 text-mahagony-500 dark:text-mahagony-300"
                >
                    <div
                        class="cursor-pointer hover:underline"
                        @click="confirmRemoveVisible = true"
                    >
                        <T key-name="business.upload.current.image.remove" />
                    </div>
                    <span v-if="imageUrl" class="mx-1">|</span>
                    <div
                        v-if="imageUrl"
                        class="cursor-pointer hover:underline"
                        @click="removeImage"
                    >
                        <T key-name="business.upload.image.remove" />
                    </div>
                </div>
                <div class="flex items-center pt-8">
                    <div
                        class="flex flex-row justify-start pr-4 font-nunito text-xl font-medium text-text dark:text-natural-50"
                    >
                        <h3>Alt-Text</h3>
                    </div>
                    <span
                        class="col-span-6 col-start-5 mt-1 inline-block h-0.5 w-full flex-1 bg-calypso-400"
                    />
                </div>
                <div
                    class="pt-4 font-nunito font-medium text-text dark:text-natural-50"
                >
                    <T key-name="business.alt.text.detail" />
                </div>
                <div class="max-w-lg pt-6">
                    <div class="mb-6">
                        <label
                            class="mb-1 block text-xl font-medium text-text dark:text-natural-50"
                            ><T key-name="common.deutsch"
                        /></label>
                        <textarea
                            v-model="altTextGerman"
                            class="h-32 w-full rounded-lg border-2 border-natural-300 bg-natural-50 p-3 text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xl font-medium text-text dark:text-natural-50"
                            ><T key-name="common.english"
                        /></label>
                        <textarea
                            v-model="altTextEnglish"
                            class="mb-6 h-32 w-full rounded-lg border-2 border-natural-300 bg-natural-50 p-3 text-text hover:border-calypso-400 focus:border-calypso-400 focus:outline-none dark:border-natural-800 dark:bg-natural-700 dark:text-natural-50 dark:hover:border-calypso-400 dark:focus:border-calypso-400"
                        ></textarea>
                    </div>
                </div>
                <Button
                    type="submit"
                    :label="t('common.save')"
                    icon="pi pi-save"
                    :loading="loadingEdit"
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
        <div>
            <BusinessConfirmRemoveImage
                :visible="confirmRemoveVisible"
                :image-edit-type="props.imageEditType"
                @close="confirmRemoveVisible = false"
                @remove="deleteImage"
            />
            <BusinessConfirmSaveData
                :visible="confirmSaveVisible"
                @close="confirmSaveVisible = false"
                @remove="close"
            />
        </div>
    </div>
</template>
