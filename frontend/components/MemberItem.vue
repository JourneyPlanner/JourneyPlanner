<script setup lang="ts">

const props = defineProps({
    id: { type: String, required: true },
    firstName: { type: String, required: true },
    lastName: { type: String },
    role: { type: Number, required: true }
});

</script>

<template>
    <div>
        <h2>{{ props.firstName }} {{ props.lastName }}</h2>
    </div>
</template>
