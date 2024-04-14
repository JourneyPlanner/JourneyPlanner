<template>
    <div class="flex min-h-screen flex-col">
        <main class="flex-grow">
            <slot class="" />
        </main>
        <footer
            class="mb-auto mt-auto flex w-full justify-center py-2 font-nunito text-xs text-footer md:text-sm"
        >
            <a
                href="mailto:contact@journeyplanner.io"
                class="hover:font-bold hover:text-link"
                >contact@journeyplanner.io</a
            >
            <span class="mx-2"> | </span>
            <a
                href="https://github.com/JourneyPlanner/JourneyPlanner"
                target="_blank"
                class="hover:font-bold hover:text-link"
                >GitHub</a
            >
        </footer>
    </div>
</template>
