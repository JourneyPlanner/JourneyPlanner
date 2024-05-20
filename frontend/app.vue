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

/*default scrollbar style*/
::-webkit-scrollbar {
    width: 5px;
}

.dark ::-webkit-scrollbar-track {
    background: #2c2c2c;
}

.dark ::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 1rem;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.light ::-webkit-scrollbar-track {
    background: #fcfcfc;
}

.light ::-webkit-scrollbar-thumb {
    background: #dcdcdc;
    border-radius: 1rem;
}

.light ::-webkit-scrollbar-thumb:hover {
    background: #d0d0d0;
}
</style>
