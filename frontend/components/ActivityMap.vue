<script setup lang="ts">
const store = useActivityStore();
console.log(store.activityData);
</script>

<template>
    <div
        class="relative mt-5 flex h-96 w-full justify-center rounded-lg md:justify-start lg:ml-10 lg:w-[calc(33.33vw+38.5rem)] xl:ml-[10%] xl:w-[calc(33.33vw+44rem)]"
    >
        <MapboxMap
            map-id="{ID}"
            style="position: absolute; top: 0; bottom: 0"
            :options="{
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [-68.137343, 45.137451],
                zoom: 5,
            }"
        />
    </div>
</template>
