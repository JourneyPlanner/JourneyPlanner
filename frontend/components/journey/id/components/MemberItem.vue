<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    displayName: { type: String, required: true },
    username: { type: String, required: true },
    role: { type: Number, required: true },
    edit: { type: Boolean, required: true },
    currentId: { type: String, required: true },
});

const currentRole = ref(props.role);
const isProfileDialogVisible = ref(false);
const route = useRoute();
const router = useRouter();
const emit = defineEmits(["changeRole", "kick"]);
const isConfirmVisible = ref(false);

onMounted(() => {
    if (route.query.username) {
        if (route.query.username === props.username) {
            isProfileDialogVisible.value = true;
        }
    }

    watch(
        () => isProfileDialogVisible.value,
        (value) => {
            if (value) {
                router.push({ query: { username: props.username } });
            } else {
                router.push({ query: {} });
            }
        },
    );
});

function changeRole(selectedRole: number) {
    if (props.currentId === props.id) return;

    emit("changeRole", props.id, selectedRole);
    currentRole.value = selectedRole;
}

const kick = () => {
    emit("kick", props.id);
};

const roleType = computed(() => {
    return currentRole.value === 1
        ? "journey.sidebar.list.guide"
        : "journey.sidebar.list.member";
});
</script>

<template>
    <div class="flex flex-row items-center justify-between">
        <h2
            v-tooltip.left="{
                value: displayName,
                pt: { root: 'font-nunito' },
            }"
            class="w-2/3 cursor-pointer overflow-hidden overflow-ellipsis whitespace-nowrap pr-4 text-xl font-medium text-text dark:text-natural-50"
            @click="isProfileDialogVisible = true"
        >
            {{ displayName }}
        </h2>
        <div
            class="w-1/4 rounded-md p-0.5 px-1 text-center"
            :class="
                currentRole === 1
                    ? 'bg-calypso-200 dark:bg-gothic-600'
                    : 'bg-natural-100 dark:bg-natural-600'
            "
        >
            <h3 class="text-base text-text dark:text-natural-50">
                <T :key-name="roleType" />
            </h3>
        </div>
    </div>
    <form
        v-if="edit && currentId !== props.id"
        class="flex justify-end text-end font-nunito"
    >
        <h4
            :class="
                currentRole === 1
                    ? 'font-semibold text-calypso-600 dark:text-calypso-400'
                    : 'text-text hover:font-semibold hover:text-calypso-600 dark:text-natural-50 dark:hover:text-calypso-400'
            "
            class="hover:cursor-pointer"
            @click="changeRole(1)"
        >
            <T key-name="journey.sidebar.list.guide" />
        </h4>
        <div class="flex w-4 justify-center">|</div>
        <h4
            :class="
                currentRole === 0
                    ? 'font-semibold text-natural-600 dark:text-natural-400'
                    : 'text-text hover:font-semibold hover:text-natural-600 dark:text-natural-50 dark:hover:text-natural-400'
            "
            class="hover:cursor-pointer"
            @click="changeRole(0)"
        >
            <T key-name="journey.sidebar.list.member" />
        </h4>
        <div class="flex w-4 justify-center">|</div>
        <span
            class="flex items-center justify-center"
            @click="isConfirmVisible = true"
        >
            <i
                class="pi pi-user-minus cursor-pointer text-lg text-mahagony-300 hover:text-mahagony-400"
            />
        </span>
    </form>
    <div id="dialogs">
        <JourneyIdDialogsProfileDialog
            :visible="isProfileDialogVisible"
            :username="username"
            :displayname="displayName"
            @close="isProfileDialogVisible = false"
        />
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
        >
            <template #header>
                <div class="flex w-[90%] items-center">
                    <div class="h-0.5 w-3 bg-mahagony-500" />
                    <div
                        class="flex-grow-5 mx-2 text-3xl text-text dark:text-natural-50"
                    >
                        <T key-name="journey.kick.member" />
                    </div>
                    <div class="h-0.5 flex-grow bg-mahagony-500" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-ml-8 w-[110%] overflow-hidden overflow-ellipsis text-natural-700 dark:text-natural-300"
                >
                    <T key-name="journey.kick.member.confirm.part1" />
                    <b class="text-natural-700 dark:text-natural-50">
                        <T key-name="journey.kick.member.confirm.part2" />
                        {{ displayName }}
                    </b>

                    <T key-name="journey.kick.member.confirm.part3" />
                </div>

                <div class="flex w-full justify-center pb-1 pt-7">
                    <button
                        class="ml-2 w-40 rounded-md bg-natural-50 px-2 pr-5 text-xl text-text hover:underline dark:bg-background-dark dark:text-natural-50"
                        @click="isConfirmVisible = false"
                    >
                        <T key-name="common.button.cancel" />
                    </button>
                    <button
                        class="ml-1 mr-6 mt-auto w-[70%] text-nowrap rounded-md border-[3px] border-mahagony-500 bg-natural-50 px-2 py-1 pl-2 text-base font-semibold text-text hover:bg-mahagony-300 dark:border-mahagony-500 dark:bg-natural-900 dark:text-natural-50 dark:hover:bg-mahagony-500030"
                        @click="kick"
                    >
                        <T key-name="journey.kick.member" />
                    </button>
                </div>
            </div>
        </Dialog>
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
        >
            <template #header>
                <button
                    class="-ml-6 flex justify-center pr-4"
                    @click="isConfirmVisible = false"
                >
                    <span class="pi pi-angle-down text-2xl" />
                </button>
                <div class="font-nunito text-3xl font-semibold">
                    <T key-name="journey.kick.member" />
                </div>
            </template>
            <div class="flex h-full flex-col pl-8">
                <div
                    class="-pt-4 w-11/12 overflow-hidden overflow-ellipsis text-base text-natural-700 dark:text-natural-200"
                >
                    <T key-name="journey.kick.member.confirm.part1" />
                    <b class="text-natural-700 dark:text-natural-50">
                        <T key-name="journey.kick.member.confirm.part2" />
                        {{ displayName }}
                    </b>

                    <T key-name="journey.kick.member.confirm.part3" />
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
                        @click="kick"
                    >
                        <T key-name="journey.kick.member" />
                    </button>
                </div>
            </div>
        </Sidebar>
    </div>
</template>
