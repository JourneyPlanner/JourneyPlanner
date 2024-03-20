<template>
    <SvgFilterIcon @click="toggle" aria-haspopup="true" aria-controls="overlay_tmenu"
        class="w-9 h-9 hover:cursor-pointer" />
    <div class="card flex justify-content-center mt-10 pt-10">
        <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup class="mt-10">
            <template #start>
                <h1>Sort by</h1>
            </template>
            <template #item="{ item, props, hasSubmenu }">
                <a v-ripple class="flex align-items-center" v-bind="props.action">
                    <span :class="item.icon" />
                    <span class="ml-2">{{ item.label }}</span>
                    <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto"></i>
                </a>
            </template>
        </TieredMenu>
    </div>
</template>

<script setup>
import { ref } from "vue";
const menu = ref();

const toggle = (event) => {
    menu.value.toggle(event);
};

const items = ref([
    {
        label: 'Name',
        icon: 'pi pi-book',
        items: [
            {
                label: 'dashboard.sort.ascending',
                icon: 'pi pi-sort-alpha-up',
                

            },
            {
                label: 'dashboard.sort.descending',
                icon: 'pi pi-sort-alpha-down',

            },
        ]
    },
    {
        label: 'Startdate',
        icon: 'pi pi-calendar',
        items: [
            {
                label: 'dashboard.sort.oldestToNewest',
                icon: 'pi pi-sort-amount-up',

            },
            {
                label: 'dashboard.sort.newestToOldest',
                icon: 'pi pi-sort-amount-down',

            },
        ]
    },
    {
        label: 'Destination',
        icon: 'pi pi-map-marker',
        items: [
            {
                label: 'dashboard.sort.ascending',
                icon: 'pi pi-sort-alpha-up',

            },
            {
                label: 'dashboard.sort.descending',
                icon: 'pi pi-sort-alpha-down',

            },
        ]
    },

]);
</script>
