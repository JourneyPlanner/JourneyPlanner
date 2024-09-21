<script setup lang="ts">
const route = useRoute();
const name = route.params.name;
</script>

<template>
    <div>
        <h1>{{ name }}</h1>
    </div>
</template>
