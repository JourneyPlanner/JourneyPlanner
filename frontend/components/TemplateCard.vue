<script setup lang="ts">
defineProps({
    name: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    id: {
        type: String,
        required: true,
    },
    length: {
        type: Number,
        required: true,
    },
    destination: {
        type: String,
        required: true,
    },
    mapboxFullAddress: {
        type: String,
        required: true,
    },
    creator: {
        type: String,
        required: true,
    },
});
//TODO [Vue warn]: Invalid prop: type check failed for prop "mapbox_full_address". Expected String with value "null", got Null
</script>

<template>
    <div class="rounded-xl border-2 border-natural-200">
        <div
            class="flex h-5 w-full items-center rounded-t-lg bg-calypso-300 px-2"
        >
            <div class="w-full border border-dashed border-natural-50" />
            <SvgAirplaneIcon class="h-4 pl-1 text-natural-50" />
        </div>
        <div class="px-2.5 pb-3 pt-2">
            <h3 class="truncate text-xl font-medium">{{ name }}</h3>
            <h4 class="truncate text-xl text-natural-600">
                <T key-name="template.by" />{{ creator }}
            </h4>
            <div id="template-details" class="mt-2">
                <div class="flex flex-row items-center gap-x-1">
                    <i class="pi pi-map-marker text-lg text-calypso-600" />
                    <h5 class="truncate text-lg">{{ destination }}</h5>
                </div>
                <div class="flex flex-row items-center gap-x-1">
                    <i class="pi pi-calendar text-lg text-calypso-600" />
                    <h5 class="truncate text-lg">
                        {{ length
                        }}<T
                            :key-name="
                                length === 1 ? 'template.day' : 'template.days'
                            "
                        />
                    </h5>
                </div>
            </div>
        </div>
    </div>
</template>
