<template>
    <div class="flex min-h-screen flex-col">
        <main class="flex-grow">
            <slot class="" />
        </main>
        <footer
            class="mb-auto mt-auto w-full flex-wrap justify-center py-2 text-center font-nunito text-xs text-natural-400 md:text-sm"
        >
            <NuxtLink
                to="/imprint"
                class="hover:font-bold hover:text-calypso-400"
            >
                <T key-name="common.imprint" />
            </NuxtLink>
            <span class="mx-2"> | </span>
            <a
                href="mailto:contact@journeyplanner.io"
                class="hover:font-bold hover:text-calypso-400"
                >contact@journeyplanner.io</a
            >
            <br class="sm:hidden" />
            <span class="invisible mx-2 sm:visible"> | </span>
            <NuxtLink
                to="/privacy"
                class="hover:font-bold hover:text-calypso-400"
            >
                <T key-name="common.privacypolicy" />
            </NuxtLink>
            <span class="mx-2"> | </span>
            <a
                href="https://github.com/JourneyPlanner/JourneyPlanner"
                target="_blank"
                class="hover:font-bold hover:text-calypso-400"
                >GitHub</a
            >
        </footer>
    </div>
</template>
