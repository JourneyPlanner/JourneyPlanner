<template>
    <TolgeeProvider>
        <NuxtLoadingIndicator
            color="repeating-linear-gradient(to right,#B9DCE8 0%,#2B5973 100%)"
        />
        <NuxtLayout>
            <div>
                <NuxtPage />
            </div>
        </NuxtLayout>
        <template #fallback>
            <div
                class="flex h-screen flex-col items-center justify-center gap-5 text-text dark:text-input"
            >
                <ProgressSpinner class="w-14" />
                <h1 class="text-lg font-medium">
                    Getting ready for your adventures...
                </h1>
            </div>
        </template>
    </TolgeeProvider>
</template>

<style>
/* default background in light and dark: mode  */
body {
    @apply min-h-screen bg-background font-nunito dark:bg-background-dark;
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
