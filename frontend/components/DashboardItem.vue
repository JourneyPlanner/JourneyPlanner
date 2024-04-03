<script setup lang="ts">
import { useTranslate } from '@tolgee/vue';
import { format } from 'date-fns';
import { ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    destination: { type: String, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true },
    role: { type: Number, required: true }
});

const { t } = useTranslate();
const confirm = useConfirm();
const toast = useToast();

const roleType = computed(() => { return props.role === 1 ? "dashboard.role.guide" : "dashboard.role.member" });
const link = computed(() => { return "/journey/" + props.id });

const isEditMenuVisible = ref(false);
const menu = ref();

const itemsJourneyGuide = ref([
    {
        label: t.value('dashboard.options.header'),
        items: [
            {
                label: t.value('dashboard.options.edit'),
                icon: 'pi pi-pencil',
                className: 'text-cta-border',
                command: () => {
                    isEditMenuVisible.value = true;
                }
            },
            {
                label: t.value('dashboard.options.delete'),
                icon: 'pi pi-trash',
                command: () => {
                    console.log('delete');
                }
            }
        ]
    }
]);

const itemsJourneyMember = ref([
    {
        label: t.value('dashboard.options.header'),
        items: [
            {
                label: t.value('dashboard.options.leave'),
                icon: 'pi pi-sign-out'
            },
        ]
    }
]);


const toggle = (event: Event) => {
    menu.value.toggle(event);
};

const confirmDelete = (event: Event) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: 'Are you sure you want to proceed?',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined p-button-sm',
        acceptClass: 'p-button-sm',
        rejectLabel: 'Cancel',
        acceptLabel: 'Save',
        accept: () => {
            toast.add({ severity: 'info', summary: 'Confirmed', detail: 'You have accepted', life: 3000 });
        },
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rejected', detail: 'You have rejected', life: 3000 });
        }
    });
};
</script>

<template>
    <div>
        <div id="journey-desktop" class="hidden lg:block relative hover:cursor-pointer">
            <SvgDashboardJourney :link="link" class="dark:hidden" />
            <SvgDashboardJourneyDark :link="link" class="hidden dark:block" />
            <div class="absolute top-6 left-10">
                <div class="flex justify-between w-56">
                    <NuxtLink :to="link" class="overflow-ellipsis overflow-hidden whitespace-nowrap w-full">
                        <h1 class="font-semibold text-2xl overflow-hidden whitespace-nowrap overflow-ellipsis"
                            v-tooltip.top="name">{{
                name }}</h1>
                    </NuxtLink>
                    <button class="pi pi-ellipsis-v justify-end" @click="toggle" aria-haspopup="true"
                        aria-controls="overlay_menu">
                    </button>
                </div>
                <NuxtLink :to="link">
                    <h2 class="text-xl font-medium -mt-1.5 w-56 overflow-ellipsis overflow-hidden whitespace-nowrap"
                        v-tooltip.bottom="destination">{{ destination
                        }}</h2>
                    <div
                        class="border-b-2 border-dashed border-border-grey dark:border-input-placeholder w-56 text-sm mt-1.5">
                        <span class="text-border mr-1">
                            <T keyName="dashboard.date" />
                        </span>
                        <span class="text-text dark:text-white -mb-1">{{ format(from, "dd/MM/yyyy") }}-{{ format(to,
                "dd/MM/yyyy") }}</span>
                    </div>
                    <h3
                        class="mt-1 border-b-2 border-dashed border-border-grey dark:border-input-placeholder w-56 text-sm">
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
                    <h1 class="font-semibold text-xl overflow-hidden whitespace-nowrap overflow-ellipsis"
                        v-tooltip.top="name">
                        {{
                name }}</h1>
                </NuxtLink>
                <Button type="button" icon="pi pi-ellipsis-v" @click="toggle" aria-haspopup="true"
                    aria-controls="overlay_menu" class="justify-end" />
            </div>
            <NuxtLink :to="link">
                <h2 class="text-lg font-medium -mt-1.5 overflow-ellipsis overflow-hidden whitespace-nowrap"
                    v-tooltip.bottom="destination">{{ destination }}</h2>
                <h3
                    class="border-b-2 border-dashed border-border-grey dark:border-input-placeholder text-xs md:text-sm mt-1.5">
                    <span class="text-border mr-0.5">
                        <T keyName="dashboard.date" />
                    </span>
                    <br class="sm:hidden">
                    <span class="text-text dark:text-white whitespace-nowrap">{{ format(from, "dd/MM/yyyy") }} - {{
                format(to,
                    "dd/MM/yyyy") }}</span>
                </h3>
                <h3
                    class="mt-1 border-b-2 border-dashed border-border-grey dark:border-input-placeholder text-xs md:text-sm">
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
            class="bg-input dark:bg-input-dark" :popup="true"
            :pt="{ root: { class: 'font-nunito bg-input dark:bg-input-dark' }, menuitem: { class: 'bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white' }, content: { class: 'bg-input dark:bg-input-dark hover:bg-cta-bg-light dark:hover:bg-cta-bg-dark rounded-md text-text dark:text-white' }, submenuHeader: { class: 'text-input-placeholder dark:text-text-light-dark bg-input dark:bg-input-dark' }, label: { class: 'text-text dark:text-white' }, icon: { class: 'text-text dark:text-white' } }" />

        <Dialog v-model:visible="isEditMenuVisible" modal :header="t('dashboard.edit.header')"
            :style="{ width: '30rem' }"
            :pt="{ root: { class: 'font-nunito text-text' }, title: { class: 'text-2xl' } }">
            <div class="flex flex-col justify-between">
                <div class="flex flex-row items-center justify-between">
                    <label for="journey-name" class="font-bold text-xl">
                        <T keyName="form.input.journey.name" />
                    </label>
                    <FormInput id="journey-name" name="journeyName" translationKey="form.input.journey.name"
                        class="w-2/3" />
                </div>
                <div class="flex flex-row items-center justify-between">
                    <label for="journey-destination" class="font-bold text-xl">
                        <T keyName="form.input.journey.destination" />
                    </label>
                    <FormInput id="journey-destination" name="journeyDestination"
                        translationKey="form.input.journey.destination" class="w-2/3" />
                </div>
                <div class="flex flex-row items-center justify-between">
                    <label for="journey-range-calendar" class="font-bold text-xl">
                        <T keyName="dashboard.edit.dates" />
                    </label>
                    <FormCalendar id="journey-range-calendar" name="journeyRange"
                        translationKey="form.input.journey.dates" />
                </div>
            </div>
            <div class="flex justify-between mt-10">
                <button @click="confirmDelete($event)"
                    class="px-7 py-1 text-text dark:text-white font-bold border-2 bg-input dark:bg-input-dark hover:bg-cancel-bg dark:hover:bg-cancel-bg-dark border-cancel-border rounded-xl">
                    <T keyName="common.delete" />
                </button>
                <button @click="isEditMenuVisible = false"
                    class="px-12 py-1 font-bold text-text dark:text-white border-2 bg-input dark:bg-input-dark hover:bg-cta-bg dark:hover:bg-cta-bg-dark border-border-green-save rounded-xl">
                    <T keyName="common.save" />
                </button>
            </div>
        </Dialog>
        <ConfirmPopup></ConfirmPopup>
    </div>
</template>
