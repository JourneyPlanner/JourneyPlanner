<template>
    <div class="flex flex-col min-h-screen justify-between">
        <slot class="flex-grow" />
        <footer
            class="text-footer text-xs md:text-sm font-nunito pb-3 mt-2 flex items-center justify-center overflow-hidden">
            <a href="mailto:contact@journeyplanner.io"
                class="hover:font-bold hover:text-link">contact@journeyplanner.io</a>
            <span class="mx-2"> | </span>
            <a href="https://github.com/JourneyPlanner/JourneyPlanner" target="_blank"
                class="hover:font-bold hover:text-link">GitHub</a>
        </footer>
    </div>
</template>
