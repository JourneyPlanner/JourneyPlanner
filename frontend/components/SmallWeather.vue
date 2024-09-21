<script setup lang="ts">
const props = defineProps({
    qrCode: { type: String, required: true },
    day: { type: Number, required: true },
    rightLine: { type: Boolean, default: true },
});

const highestTemp = ref(24);
const lowestTemp = ref(9);
function daysInTheFuture(days: number) {
    const today = new Date();
    const futureDay = new Date(today.setDate(today.getDate() + days));

    console.log(futureDay);
    return futureDay.getDate();
}

function monthInDays(days: number) {
    const today = new Date();
    const futureDay = new Date(today.setDate(today.getDate() + days));

    console.log(futureDay);
    return futureDay.getMonth() + 1;
}
</script>
<template>
    <div class="flex max-lg:flex-col">
        <div class="w-[99%]">
            <div class="flex justify-center text-base underline">
                {{ daysInTheFuture(props.day) }} /
                {{ monthInDays(props.day) }}
            </div>
            <div class="max-md:flex">
                <div class="flex h-1/3 items-center justify-center">
                    <img class="h-1/5 w-10" :src="qrCode" alt="QR Code" />
                </div>
                <div
                    class="flex items-start justify-center pt-1 text-xs max-md:ml-2 max-md:flex-col lg:items-end"
                >
                    <div>H: {{ highestTemp }}°</div>
                    <div class="lg:pl-1">T: {{ lowestTemp }}°</div>
                </div>
            </div>
        </div>
        <div
            v-if="rightLine"
            class="mb-3 w-full self-center border-b-2 border-calypso-300 pb-3 lg:mb-10 lg:h-20 lg:w-[1%] lg:border-r-2"
        />
    </div>
</template>
