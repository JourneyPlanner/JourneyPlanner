<template>
    <TolgeeProvider>
        <NuxtLayout>
            <div>
                <Toast class="w-3/4 sm:w-auto" />
                <NuxtPage />
            </div>
        </NuxtLayout>
        <template #fallback>
            <div class="flex justify-center">Loading...</div>
        </template>
    </TolgeeProvider>
</template>

<style>
/* default background in light and dark: mode  */
body {
    @apply min-h-screen bg-background dark:bg-background-dark;
}

.page-enter-active,
.page-leave-active {
    transition: all 0.4s;
}

.page-enter-from,
.page-leave-to {
    opacity: 0;
    filter: blur(1rem);
}
</style>
