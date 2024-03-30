<script setup lang="ts">
import { useTranslate } from '@tolgee/vue';
import { format } from 'date-fns';
import { ref } from "vue";

const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    destination: { type: String, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true },
    role: { type: Number, required: true }
});

const { t } = useTranslate();
const roleType = computed(() => { return props.role === 1 ? "dashboard.role.guide" : "dashboard.role.member" });
const link = computed(() => { return "/journey/" + props.id });

const menu = ref();

const itemsJourneyGuide = ref([
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
</script>

<template>
    <div>
        <div id="journey-desktop" class="hidden lg:block relative hover:cursor-pointer">
            <SvgDashboardJourney :link="link" class="dark:hidden" />
            <SvgDashboardJourneyDark :link="link" class="hidden dark:block" />
            <div class="absolute top-6 left-10">
                <div class="flex justify-between w-56">
                    <NuxtLink :to="link" class="overflow-ellipsis overflow-hidden whitespace-nowrap">
                        <h1 class="font-semibold text-2xl overflow-hidden whitespace-nowrap overflow-ellipsis"
                            v-tooltip.top="name">{{
                name }}</h1>
                    </NuxtLink>
                    <Button type="button" icon="pi pi-ellipsis-v" @click="toggle" aria-haspopup="true"
                        aria-controls="overlay_menu" class="justify-end" />
                </div>
                <NuxtLink :to="link">
                    <h2 class="text-xl font-medium -mt-1.5 w-56 overflow-ellipsis overflow-hidden whitespace-nowrap"
                        v-tooltip.bottom="destination">{{ destination
                        }}</h2>
                    <h3 class="border-b-2 border-dashed border-border-grey w-56 text-sm mt-1.5">
                        <span class="text-border mr-1">
                            <T keyName="dashboard.date" />
                        </span>
                        <span class="text-text dark:text-white">{{ format(from, "dd/MM/yyyy") }}-{{ format(to,
                "dd/MM/yyyy") }}</span>
                    </h3>
                    <h3 class="mt-1 border-b-2 border-dashed border-border-grey w-56 text-sm">
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
                <h3 class="border-b-2 border-dashed border-border-grey text-xs md:text-sm mt-1.5">
                    <span class="text-border mr-0.5">
                        <T keyName="dashboard.date" />
                    </span>
                    <br class="sm:hidden">
                    <span class="text-text dark:text-white whitespace-nowrap">{{ format(from, "dd/MM/yyyy") }} - {{
                format(to,
                    "dd/MM/yyyy") }}</span>
                </h3>
                <h3 class="mt-1 border-b-2 border-dashed border-border-grey text-xs md:text-sm">
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
            :popup="true"
            :pt="{ root: { class: 'font-nunito' }, menuitem: { class: 'hover:bg-cta-bg-light rounded-md' }, content: { class: 'hover:bg-cta-bg-light rounded-md' } }" />
    </div>
</template>