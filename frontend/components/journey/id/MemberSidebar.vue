<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";

const props = defineProps({
    journeyID: {
        type: String,
        required: true,
    },
    isMemberSidebarVisible: {
        type: Boolean,
        required: true,
    },
    invite: {
        type: String,
        required: true,
    },
    users: {
        type: Array as PropType<User[]>,
        required: true,
    },
    currUser: {
        type: Object as PropType<User>,
        required: true,
    },
});

const emit = defineEmits([
    "leave-journey",
    "close",
    "open-qrcode",
    "open-unlock-dialog",
    "kick",
    "regenerated-invite",
]);

const toast = useToast();
const { t } = useTranslate();
const client = useSanctumClient();
const { isAuthenticated } = useSanctumAuth();
const journeyStore = useJourneyStore();

const isEditEnabled = ref(false);
const users = ref(props.users);
const isVisible = ref(props.isMemberSidebarVisible);
const shareDialog = ref();
const isRegenerating = ref(false);

watch(
    () => props.isMemberSidebarVisible,
    (value) => {
        isVisible.value = value;
    },
);

watch(
    () => props.users,
    (value) => {
        users.value = value;
    },
);

/**
 * copy the invite link to the clipboard
 */
function copyToClipboard() {
    navigator.clipboard.writeText(props.invite);
    toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("common.invite.toast.info"),
        life: 2000,
    });
}

/*
 * Change the role of a user
 * @param userid - the id of the user
 * @param selectedRole - the selected role
 */
async function changeRole(userid: string, selectedRole: number) {
    await client(`/api/journey/${props.journeyID}/user/${userid}`, {
        method: "PATCH",
        body: {
            role: selectedRole,
        },
        async onResponse() {
            users.value = users.value.map((user: User) => {
                if (user.id === userid) {
                    user.role = selectedRole;
                }
                return user;
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

/*
 * Kick specified user out of the journey
 * @param userid - the id of the user
 */
async function kick(userid: string) {
    if (!userid) return;
    await client(`/api/journey/${props.journeyID}/user/${userid}`, {
        method: "DELETE",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value("journey.kick.toast.success"),
                    detail: t.value("journey.kick.toast.success.detail"),
                    life: 6000,
                });

                emit("kick");
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

function close() {
    emit("close");
}

function leave(event: Event) {
    emit("leave-journey", event);
}

function openQRCode(tolgeeKey: string) {
    emit("open-qrcode", tolgeeKey);
}

function openUnlockDialog() {
    emit("open-unlock-dialog");
}

async function regenerateInvite() {
    isRegenerating.value = true;
    await client(`/api/journey/${props.journeyID}/regenerate-invite`, {
        method: "POST",
        async onResponse({ response }) {
            if (response.ok) {
                toast.add({
                    severity: "success",
                    summary: t.value(
                        "journey.invite.regenerate.toast.success.summary",
                    ),
                    detail: t.value(
                        "journey.invite.regenerate.toast.success.detail",
                    ),
                    life: 6000,
                });
                journeyStore.setInvite(response._data.invite);
                emit("regenerated-invite");
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
    isRegenerating.value = false;
}

function toggleShareDialog(event: Event) {
    shareDialog.value.toggle(event);
}
</script>

<template>
    <div>
        <Sidebar
            id="member-sidebar"
            v-model:visible="isVisible"
            position="right"
            :block-scroll="true"
            :pt="{
                closeButton: {
                    class: 'w-9 h-9 col-span-2 flex w-full justify-end pr-1',
                },
                closeIcon: {
                    class: 'w-7 h-7 text-natural-500 hover:text-text dark:text-natural-400 dark:hover:text-natural-50',
                },
                header: { class: 'p-2 pl-3 grid grid-rows-1 grid-cols-12' },
                content: {
                    class: 'pl-3 pr-2 py-2 flex h-full flex-col',
                },
                root: {
                    class: 'dark:bg-background-dark font-nunito relative',
                },
            }"
            @hide="close"
        >
            <template #header>
                <span class="h-0.5 w-full bg-calypso-300 dark:bg-calypso-600" />
                <div
                    class="col-span-5 flex w-full flex-row justify-center text-2xl font-medium text-text dark:text-natural-50"
                >
                    <h3>
                        <T key-name="journey.sidebar.members" />
                    </h3>
                </div>
                <span
                    class="col-span-4 col-start-7 h-0.5 w-full bg-calypso-300 dark:bg-calypso-600"
                />
            </template>
            <div class="relative flex h-full flex-col">
                <div
                    class="flex h-full flex-col"
                    :class="!isAuthenticated ? 'blur-[1.75px]' : ''"
                >
                    <div
                        v-if="currUser?.role === 1"
                        class="text-xl font-medium text-text dark:text-natural-50"
                    >
                        <T key-name="sidebar.invite.link" />
                    </div>
                    <div
                        v-if="currUser?.role === 1"
                        class="flex items-center border-b-2 border-natural-200 pb-4 dark:border-natural-900"
                    >
                        <input
                            :disabled="isRegenerating"
                            readonly
                            class="w-5/6 rounded-md bg-natural-100 px-1 pb-1 pt-1 text-base text-text focus:outline-none focus:ring-1 dark:bg-natural-600 dark:text-natural-50"
                            :class="
                                isRegenerating
                                    ? 'pointer-events-none cursor-not-allowed select-none'
                                    : 'cursor-default'
                            "
                            :value="invite"
                        />
                        <div class="flex w-1/5 justify-end">
                            <button
                                :disabled="isRegenerating"
                                class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                                :class="
                                    isRegenerating
                                        ? 'cursor-not-allowed'
                                        : 'cursor-pointer'
                                "
                                @click="toggleShareDialog"
                            >
                                <i
                                    class="pi pi-share-alt text-text dark:text-natural-50"
                                />
                            </button>
                        </div>
                        <div class="flex w-1/5 justify-end">
                            <button
                                :disabled="isRegenerating"
                                class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                                :class="
                                    isRegenerating
                                        ? 'cursor-not-allowed'
                                        : 'cursor-pointer'
                                "
                                @click="regenerateInvite"
                            >
                                <span
                                    class="pi scale-x-[-1] text-text dark:text-natural-50"
                                    :class="
                                        isRegenerating
                                            ? 'pi-spin pi-spinner'
                                            : 'pi-refresh'
                                    "
                                />
                            </button>
                        </div>
                    </div>
                    <div
                        class="flex flex-row items-center justify-center border-b-2 border-natural-200 pb-1 pt-1 dark:border-natural-900"
                    >
                        <h1
                            class="w-4/5 text-xl text-natural-600 dark:text-natural-200"
                        >
                            <T key-name="journey.sidebar.list.header" />
                        </h1>
                        <div
                            class="mb-1 mt-1 flex w-1/5 items-center justify-end"
                        >
                            <button
                                v-if="currUser?.role === 1"
                                class="ml-3 flex h-9 w-9 items-center justify-center rounded-full border-2 border-dandelion-300 hover:bg-dandelion-200 dark:bg-natural-800 dark:hover:bg-pesto-600"
                                @click="isEditEnabled = !isEditEnabled"
                            >
                                <SvgEdit v-if="!isEditEnabled" class="w-4" />
                                <SvgEditOff v-if="isEditEnabled" class="w-4" />
                            </button>
                        </div>
                    </div>
                    <div
                        id="list"
                        class="mt-3 flex flex-grow flex-col gap-3 overflow-y-auto pr-0.5"
                    >
                        <JourneyIdComponentsMemberItem
                            v-for="user in users"
                            :id="user.id"
                            :key="user.id"
                            :username="user.username"
                            :display-name="user.display_name"
                            :role="user.role"
                            :edit="isEditEnabled"
                            :current-id="currUser.id"
                            @change-role="changeRole"
                            @kick="kick"
                        />
                    </div>
                    <div
                        class="sticky bottom-0 border-t-2 border-natural-200 dark:border-natural-900"
                    >
                        <button
                            class="my-4 w-full rounded-lg border-2 border-mahagony-500 bg-natural-50 py-1 text-base font-semibold text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                            @click="leave($event)"
                        >
                            <T key-name="journey.leave.short" />
                        </button>
                    </div>
                </div>
                <div
                    v-if="!isAuthenticated"
                    class="absolute -mt-14 flex h-full w-full items-center justify-center"
                >
                    <button
                        class="w-32 rounded-md border-2 border-dandelion-300 bg-dandelion-200 px-4 py-1 text-center font-medium text-text hover:bg-dandelion-300 dark:border-dandelion-300 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-pesto-600"
                        @click="openUnlockDialog"
                    >
                        <T key-name="journey.unlock.button" />
                    </button>
                </div>
            </div>
        </Sidebar>
        <div id="dialogs">
            <OverlayPanel
                ref="shareDialog"
                class="z-20"
                :pt="{
                    content: 'p-0',
                    root: 'rounded-lg bg-natural-50 dark:bg-natural-950 border border-natural-200 dark:border-natural-900',
                }"
            >
                <div
                    class="flex flex-col gap-y-1 rounded-lg px-1 py-1 font-nunito text-text dark:text-natural-50"
                >
                    <div
                        class="flex cursor-pointer items-center gap-x-2 rounded-md p-1 pl-1.5 hover:bg-dandelion-100 dark:hover:bg-pesto-600"
                        @click="copyToClipboard"
                    >
                        <i class="pi pi-clone" />
                        <span>
                            <T key-name="journey.invite.copy" />
                        </span>
                    </div>
                    <div
                        class="flex cursor-pointer items-center gap-x-2 rounded-md p-1 pl-1.5 hover:bg-dandelion-100 dark:hover:bg-pesto-600"
                        @click="openQRCode('journey.qrcode')"
                    >
                        <i class="pi pi-qrcode" />
                        <span>
                            <T key-name="journey.invite.qrcode" />
                        </span>
                    </div>
                </div>
            </OverlayPanel>
        </div>
    </div>
</template>
