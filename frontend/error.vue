<script setup>
useHead({
    title: "Error | JourneyPlanner",
});

const error = useError();

const handleError = () => {
    clearError({
        redirect: "/",
    });
};
</script>

<template>
    <NuxtLayout>
        <div
            class="mt-16 flex flex-col items-center text-text dark:text-natural-50"
        >
            <h1 class="text-xl">Error</h1>
            <p>
                <strong>{{ error?.message }}</strong>
            </p>
            <p>
                <button
                    class="m-3 rounded-lg border-2 border-dandelion-300 p-1 px-2 text-sm text-text hover:bg-dandelion-200 dark:text-natural-50 dark:hover:bg-pesto-600 md:text-base"
                    @click="handleError"
                >
                    Back to start page
                </button>
            </p>
            <span class="text-sm">--- Site is still in development ---</span>
            <span class="font-nunito text-xs text-natural-400"
                >Report issues to:
                <a
                    href="mailto:contact@journeyplanner.io"
                    class="hover:font-bold hover:text-calypso-400"
                    >contact@journeyplanner.io</a
                ></span
            >
        </div>
    </NuxtLayout>
</template>
