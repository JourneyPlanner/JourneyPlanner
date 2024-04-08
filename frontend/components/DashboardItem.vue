<script setup lang="ts">
import { useTranslate } from "@tolgee/vue";
import { format } from "date-fns";
import { ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import type { MenuItemCommandEvent } from "primevue/menuitem";
import { useForm } from "vee-validate";
import * as yup from "yup";

const props = defineProps({
  id: { type: String, required: true },
  name: { type: String, required: true },
  destination: { type: String, required: true },
  from: { type: Date, required: true },
  to: { type: Date, required: true },
  role: { type: Number, required: true },
});

const emit = defineEmits(["journeyDeleted", "journeyEdited"]);

const { t } = useTranslate();
const confirm = useConfirm();
const toast = useToast();
const client = useSanctumClient();

const roleType = computed(() => {
  return props.role === 1 ? "dashboard.role.guide" : "dashboard.role.member";
});
const link = computed(() => {
  return "/journey/" + props.id;
});

const isEditMenuVisible = ref(false);
const menu = ref();
const loadingEdit = ref(false);

const toggle = (event: Event) => {
  menu.value.toggle(event);
};

const confirmDelete = (event: Event) => {
  confirm.require({
    group: "delete",
    target: event.currentTarget as HTMLElement,
    header: t.value("dashboard.delete.header"),
    message: t.value("dashboard.delete.confirm"),
    icon: "pi pi-exclamation-triangle",
    rejectClass: "hover:underline",
    acceptClass: "text-error dark:text-error-dark hover:underline font-bold",
    rejectLabel: t.value("common.button.cancel"),
    acceptLabel: t.value("common.delete"),
    accept: () => {
      toast.add({
        severity: "info",
        summary: t.value("common.toast.info.heading"),
        detail: t.value("delete.journey.toast.message"),
        life: 3000,
      });
      isEditMenuVisible.value = false;
      deleteJourney();
    },
  });
};

async function deleteJourney() {
  await client(`/api/journey/${props.id}`, {
    method: "DELETE",
    async onResponse({ response }) {
      if (response.ok) {
        toast.add({
          severity: "success",
          summary: t.value("delete.journey.toast.success.heading"),
          detail: t.value("delete.journey.toast.success"),
          life: 6000,
        });
        emit("journeyDeleted", props.id);
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

const itemsJourneyGuide = ref([
  {
    label: t.value("dashboard.options.header"),
    items: [
      {
        label: t.value("dashboard.options.edit"),
        icon: "pi pi-pencil",
        className: "text-cta-border",
        command: () => {
          isEditMenuVisible.value = true;
        },
      },
      {
        label: t.value("dashboard.options.delete"),
        icon: "pi pi-trash",
        command: ($event: MenuItemCommandEvent) => {
          confirmDelete($event.originalEvent);
        },
      },
      /* leave for JP-34
            {
                label: t.value('dashboard.options.leave'),
                icon: 'pi pi-sign-out',
                command: ($event: MenuItemCommandEvent) => {
                    confirmLeave($event.originalEvent);
                }
            },
            */
    ],
  },
]);

const itemsJourneyMember = ref([
  {
    label: t.value("dashboard.options.header"),

    items: [
      { label: "No options available yet" },
      /* leave for JP-34
            {
                label: t.value('dashboard.options.leave'),
                icon: 'pi pi-sign-out'
            },*/
    ],
  },
]);

/**
 * form validation
 * when submitting form, fields are checked for validation
 */
const { handleSubmit } = useForm({
  validationSchema: yup.object({
    name: yup
      .string()
      .required(t.value("form.error.journey.name"))
      .label(t.value("form.input.journey.name")),
    destination: yup
      .string()
      .required(t.value("form.error.journey.destination"))
      .label(t.value("form.input.journey.destination")),
    range: yup
      .array()
      .of(
        yup
          .date()
          .required(t.value("form.error.journey.dates"))
          .label(t.value("form.input.journey.dates"))
      )
      .required(t.value("form.error.journey.dates"))
      .label(t.value("form.input.journey.dates")),
  }),
});

/**
 * form save
 * when saving the edit form, values are checked for validation with handleSubmit
 * and then a journey object is created and sent to the backend
 */
const onSave = handleSubmit(async (values) => {
  let name = values.name;
  let destination = values.destination;
  let from = format(values.range[0], "yyyy-MM-dd");
  let to = format(values.range[1], "yyyy-MM-dd");

  loadingEdit.value = true;
  toast.add({
    severity: "info",
    summary: t.value("common.toast.info.heading"),
    detail: t.value("common.toast.info.save"),
    life: 6000,
  });

  const journey = {
    name,
    destination,
    from,
    to,
  };

  await client(`/api/journey/${props.id}`, {
    method: "PUT",
    body: journey,
    async onResponse({ response }) {
      if (response.ok) {
        toast.add({
          severity: "success",
          summary: t.value("edit.journey.toast.success.heading"),
          detail: t.value("edit.journey.toast.success"),
          life: 6000,
        });
        emit("journeyEdited", journey, props.id);
        isEditMenuVisible.value = false;
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
  loadingEdit.value = false;
});
</script>

<template>
  <div>
    <Toast />
    <div id="journey-desktop" class="hidden lg:block relative hover:cursor-pointer">
      <SvgDashboardJourney :link="link" class="dark:hidden" />
      <SvgDashboardJourneyDark :link="link" class="hidden dark:block" />
      <div class="absolute top-6 left-10">
        <div class="flex justify-between w-56">
          <NuxtLink :to="link" class="overflow-ellipsis overflow-hidden whitespace-nowrap w-full">
            <h1 class="font-semibold text-2xl overflow-hidden whitespace-nowrap overflow-ellipsis" v-tooltip.top="name">
              {{ name }}
            </h1>
          </NuxtLink>
          <button class="pi pi-ellipsis-v justify-end" @click="toggle" aria-haspopup="true"
            aria-controls="overlay_menu"></button>
        </div>
        <NuxtLink :to="link">
          <h2 class="text-xl font-medium -mt-1.5 w-56 overflow-ellipsis overflow-hidden whitespace-nowrap"
            v-tooltip.bottom="destination">
            {{ destination }}
          </h2>
          <div class="border-b-2 border-dashed border-border-grey dark:border-input-placeholder w-56 text-sm mt-1.5">
            <span class="text-border mr-1">
              <T keyName="dashboard.date" />
            </span>
            <span class="text-text dark:text-white -mb-1">{{ format(from, "dd/MM/yyyy") }}-{{
              format(to, "dd/MM/yyyy")
            }}</span>
          </div>
          <h3 class="mt-1 border-b-2 border-dashed border-border-grey dark:border-input-placeholder w-56 text-sm">
            <span class="text-border mr-1">
              <T keyName="dashboard.role" />
            </span>
            <span class="text-text dark:text-white">
              <T :keyName="roleType" />
            </span>
          </h3>
        </NuxtLink>
      </div>
    </div>
    <div id="journey-mobile"
      class="lg:hidden bg-card dark:bg-card-dark border border-border rounded-md p-1 md:p-2 min-w-36 h-32">
      <div class="flex justify-between">
        <NuxtLink :to="link" class="overflow-hidden whitespace-nowrap overflow-ellipsis">
          <h1 class="font-semibold text-xl overflow-hidden whitespace-nowrap overflow-ellipsis" v-tooltip.top="name">
            {{ name }}
          </h1>
        </NuxtLink>
        <Button type="button" icon="pi pi-ellipsis-v" @click="toggle" aria-haspopup="true" aria-controls="overlay_menu"
          class="justify-end" />
      </div>
      <NuxtLink :to="link">
        <h2 class="text-lg font-medium -mt-1.5 overflow-ellipsis overflow-hidden whitespace-nowrap"
          v-tooltip.bottom="destination">
          {{ destination }}
        </h2>
        <h3 class="border-b-2 border-dashed border-border-grey dark:border-input-placeholder text-xs md:text-sm mt-1.5">
          <span class="text-border mr-0.5">
            <T keyName="dashboard.date" />
          </span>
          <br class="sm:hidden" />
          <span class="text-text dark:text-white whitespace-nowrap">{{ format(from, "dd/MM/yyyy") }} -
            {{ format(to, "dd/MM/yyyy") }}</span>
        </h3>
        <h3 class="mt-1 border-b-2 border-dashed border-border-grey dark:border-input-placeholder text-xs md:text-sm">
          <span class="text-border mr-0.5">
            <T keyName="dashboard.role" />
          </span>
          <span class="text-text dark:text-white">
            <T :keyName="roleType" />
          </span>
        </h3>
      </NuxtLink>
    </div>
    <Menu ref="menu" id="overlay_menu" :model="props.role === 1 ? itemsJourneyGuide : itemsJourneyMember"
      class="bg-input dark:bg-input-dark" :popup="true" :pt="{
        root: { class: 'font-nunito bg-input dark:bg-input-dark' },
        menuitem: {
          class:
            'bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white',
        },
        content: {
          class:
            'bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white',
        },
        submenuHeader: {
          class:
            'text-input-placeholder dark:text-text-light-dark bg-input dark:bg-input-dark',
        },
        label: { class: 'text-text dark:text-white' },
        icon: { class: 'text-text dark:text-white' },
      }" />

    <Dialog v-model:visible="isEditMenuVisible" modal :header="t('dashboard.edit.header')" :style="{ width: '30rem' }"
      class="bg-input dark:bg-input-dark" :pt="{
        root: { class: 'font-nunito text-text bg-input dark:bg-input-dark' },
        header: {
          class: 'bg-input dark:bg-input-dark text-text dark:text-white',
        },
        title: { class: 'text-2xl' },
        content: {
          class: 'bg-input dark:bg-input-dark text-text dark:text-white',
        },
      }">
      <form @submit.prevent="onSave()">
        <div class="flex flex-col">
          <div class="flex flex-row items-center justify-between">
            <label for="journey-name" class="font-bold text-base sm:text-xl">
              <T keyName="form.input.journey.name" />
            </label>
            <FormInput id="journey-name" name="name" translationKey="form.input.journey.name" :prefill="props.name"
              class="w-2/3 my-0 mb-1" />
          </div>
          <div class="flex flex-row items-center justify-between">
            <label for="journey-destination" class="font-bold text-base sm:text-xl">
              <T keyName="form.input.journey.destination" />
            </label>
            <FormInput id="journey-destination" name="destination" translationKey="form.input.journey.destination"
              class="w-2/3 my-0 mb-1" :prefill="props.destination" />
          </div>
          <div class="flex flex-row items-center justify-between">
            <label for="journey-range-calendar" class="font-bold text-base sm:text-xl">
              <T keyName="dashboard.edit.dates" />
            </label>
            <FormCalendar id="journey-range-calendar" name="range" class="my-0 mt-5 w-2/3 mr-0"
              translationKey="form.input.journey.dates" :prefill="[new Date(props.from), new Date(props.to)]" />
          </div>
        </div>
        <div class="flex justify-between mt-10 gap-2">
          <Button @click="confirmDelete($event)" type="button" :label="t('common.delete')" icon="pi pi-trash"
            class="w-40 h-9 px-2 text-text dark:text-white font-bold border-2 bg-input dark:bg-input-dark hover:bg-cancel-bg dark:hover:bg-cancel-bg-dark border-cancel-border rounded-xl"
            :pt="{
              root: { class: 'flex items-center justify-center' },
              label: { class: 'display-block flex-none' },
            }" />
          <Button type="submit" :label="t('common.save')" icon="pi pi-check" :loading="loadingEdit" :pt="{
            root: { class: 'flex items-center justify-center' },
            label: { class: 'display-block flex-none' },
          }"
            class="w-40 h-9 flex flex-row justify-center text-center font-bold text-text dark:text-white border-2 bg-input dark:bg-input-dark hover:bg-fill-green-save dark:hover:bg-fill-green-save-dark border-border-green-save dark:border-border-green-save-dark rounded-xl" />
        </div>
      </form>
    </Dialog>
  </div>
</template>
