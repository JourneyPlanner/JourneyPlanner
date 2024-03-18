<script setup lang="ts">

defineProps({
    name: String,
    destination: String,
    from: String,
    to: String,
})

</script>

<template>
    <div class="bg-surface my-5 mx-5 rounded-md inline-block">
        <h1>{{ name }}</h1>
        <p>{{ destination }}</p>
        <p>{{ from }} - {{ to }}</p>
    </div>
</template>