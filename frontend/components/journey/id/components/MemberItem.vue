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
    isConfirmVisible.value = false;
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
                class="pi pi-user-minus mr-1 cursor-pointer text-lg text-mahagony-400 hover:text-mahagony-500 dark:text-mahagony-300 dark:hover:text-mahagony-400"
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
        <JourneyIdComponentsConfirmKickMember
            :visible="isConfirmVisible"
            :name="displayName"
            @close="isConfirmVisible = false"
            @kick="kick"
        />
    </div>
</template>
