<script setup lang="ts">
const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    translationKey: { type: String, required: true },
});

const { value, errorMessage } = useField<string[]>(() => props.name);

</script>

<template>
    <div class="mb-2">
        <label :for="id" class="font-medium">
            <T :keyName="translationKey" />
        </label>
        <!-- TODO: richtiger Type? wird noch als datum angezeigt -->
        <Calendar :id="id" :name="name" v-model="value" timeOnly inputClass="block rounded-lg px-2.5 pb-1 pt-1
            w-full text-md text-text font-normal bg-input border-2 border-border focus:outline-none focus:ring-1" />
        <span class="text-error text-sm">{{ errorMessage }}</span>
    </div>
</template>
