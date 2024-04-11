<script setup lang="ts">
const props = defineProps({
  id: { type: String, required: true },
  firstName: { type: String, required: true },
  lastName: { type: String },
  role: { type: Number, required: true },
  edit: { type: Boolean, required: true },
  currentID: { type: String, required: true },
});

const currentRole = ref(props.role);
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
const name = computed(() => {
  if (props.firstName && props.lastName) {
    return props.firstName + " " + props.lastName;
  } else {
    return props.firstName;
  }
});
</script>

<template>
  <div class="flex flex-row justify-between items-center">
    <h2
      class="font-medium text-xl text-text dark:text-input whitespace-nowrap overflow-hidden overflow-ellipsis w-2/3 pr-4 cursor-default"
      v-tooltip.left="{ value: name, pt: { root: 'font-nunito' } }"
    >
      {{ name }}
    </h2>
    <div
      class="rounded-md p-0.5 px-1 w-1/4 text-center"
      :class="
        currentRole === 1
          ? 'bg-chip-blue dark:bg-chip-blue-dark'
          : 'bg-chip-grey dark:bg-chip-grey-dark'
      "
    >
      <h3 class="text-base text-text dark:text-input">
        <T :keyName="roleType" />
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
          ? 'text-blue-text dark:text-blue-text-dark font-semibold'
          : 'text-text dark:text-input hover:text-blue-text dark:hover:text-blue-text-dark hover:font-semibold'
      "
      class="hover:cursor-pointer"
      @click="changeRole(1)"
    >
      <T keyName="journey.sidebar.list.guide" />
    </h4>
    <div class="w-4 flex justify-center">|</div>
    <h4
      :class="
        currentRole === 0
          ? 'text-grey-text dark:text-grey-text-dark font-semibold'
          : 'text-text dark:text-input hover:text-grey-text dark:hover:text-grey-text-dark hover:font-semibold'
      "
      class="hover:cursor-pointer"
      @click="changeRole(0)"
    >
      <T keyName="journey.sidebar.list.member" />
    </h4>
  </form>
</template>
