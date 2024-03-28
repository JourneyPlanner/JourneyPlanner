<script setup lang="ts">
import { useTranslate } from '@tolgee/vue';
import { format } from 'date-fns';
import { ref } from "vue";

const props = defineProps({
    name: { type: String, required: true },
    destination: { type: String, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true },
    role: { type: Number, required: true }
});

const { t } = useTranslate();
const roleType = computed(() => { return props.role === 1 ? "dashboard.role.guide" : "dashboard.role.member" });

const menu = ref();
const items = ref([
    {
        label: t.value('dashboard.options.header'),
        items: [
            {
                label: t.value('dashboard.options.edit'),
                icon: 'pi pi-pencil'
            },
            {
                label: t.value('dashboard.options.delete'),
                icon: 'pi pi-trash'
            }
        ]
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <div class="hidden lg:block relative hover:cursor-pointer">
        <SvgDashboardJourney class="" />
        <div class="absolute top-6 left-10">
            <div class="flex">
                <h1 class="font-semibold text-2xl overflow-hidden whitespace-nowrap overflow-ellipsis w-48"
                    v-tooltip.top="name">{{
                        name }}</h1>
                <Button type="button" icon="pi pi-ellipsis-v" @click="toggle" aria-haspopup="true"
                    aria-controls="overlay_menu" />
                <Menu ref="menu" id="overlay_menu" :model="items" :popup="true"
                    :pt="{ root: { class: 'font-nunito' }, menuitem: { class: 'hover:bg-cta-bg-light rounded-md' }, content: { class: 'hover:bg-cta-bg-light rounded-md' } }" />
            </div>
            <h2 class="text-xl font-medium -mt-1.5" v-tooltip.bottom="destination">{{ destination }}</h2>
            <h3 class="border-b-2 border-dashed border-border-grey w-56 text-sm mt-1.5">
                <span class="text-border mr-1">
                    <T keyName="dashboard.date" />
                </span>
                <span class="text-text">{{ format(from, "dd/MM/yyyy") }}-{{ format(to,
                        "dd/MM/yyyy") }}</span>
            </h3>
            <h3 class="mt-1 border-b-2 border-dashed border-border-grey w-56 text-sm">
                <span class="text-border mr-1">
                    <T keyName="dashboard.role" />
                </span>
                <span class="text-text">
                    <T :keyName="roleType" />
                </span>
            </h3>
        </div>
    </div>
    <div class="lg:hidden">
        <div class="flex">
            <h1 class="font-semibold text-2xl overflow-hidden whitespace-nowrap overflow-ellipsis" v-tooltip.top="name">
                {{
                        name }}</h1>
            <Button type="button" icon="pi pi-ellipsis-v" @click="toggle" aria-haspopup="true"
                aria-controls="overlay_menu" />
            <Menu ref="menu" id="overlay_menu" :model="items" :popup="true"
                :pt="{ root: { class: 'font-nunito' }, menuitem: { class: 'hover:bg-cta-bg-light rounded-md' }, content: { class: 'hover:bg-cta-bg-light rounded-md' } }" />
        </div>
    </div>
</template>