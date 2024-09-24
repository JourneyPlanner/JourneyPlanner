<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    display_name: { type: String, required: true },
    role: { type: Number, required: true },
    edit: { type: Boolean, required: true },
    currentID: { type: String, required: true },
});

const currentRole = ref(props.role);
const isProfileDialogVisible = ref(false);
const emit = defineEmits(["changeRole"]);

function changeRole(selectedRole: number) {
    if (props.currentID === props.id) return;

    emit("changeRole", props.id, selectedRole);
    currentRole.value = selectedRole;
}

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
                value: display_name,
                pt: { root: 'font-nunito' },
            }"
            class="w-2/3 cursor-pointer overflow-hidden overflow-ellipsis whitespace-nowrap pr-4 text-xl font-medium text-text dark:text-natural-50"
            @click="isProfileDialogVisible = true"
        >
            {{ display_name }}
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
        v-if="edit && currentID !== props.id"
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
    </form>
    <ProfileDialog
        :visible="isProfileDialogVisible"
        :username="username"
        :display_name="display_name"
        @close="isProfileDialogVisible = false"
    />
</template>
