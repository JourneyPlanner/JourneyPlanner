<template class="bg-white">
    <div class="bg-surface flex min-h-screen items-center justify-center">
        <div class="group h-96 w-96 [perspective:1000px]" @click="flip">
            <div :class="isFlipped ? '[transform:rotateX(180deg)]' : ''"
                class="relative h-full w-full rounded-xl shadow-xl transition-all duration-500 [transform-style:preserve-3d]">
                <div class="bg-cta-bg absolute inset-0 rounded-xl text-center">Seite 1</div>
                <div
                    class="text-text absolute inset-0 h-full w-full rounded-xl bg-white px-12 text-center [transform:rotateX(180deg)] [backface-visibility:hidden]">
                    Seite 2</div>
            </div>
        </div>
    </div>
</template>

<script setup>
const isFlipped = ref(false);
const flip = () => {
    isFlipped.value = !isFlipped.value;
};
</script>
