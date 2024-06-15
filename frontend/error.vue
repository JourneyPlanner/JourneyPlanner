<script setup>
useHead({
    title: "Error | JourneyPlanner",
});

const error = useError();

const handleError = () => {
    clearError({
        redirect: "/dashboard",
    });
};
</script>

<template>
    <NuxtLayout>
        <div class="mt-16 flex flex-col items-center">
            <h1 class="text-xl">Error</h1>
            <p>
                <strong>{{ error?.message }}</strong>
            </p>
            <p>
                <button class="m-3 rounded-lg border p-1" @click="handleError">
                    Back to dashboard
                </button>
            </p>
            <span class="text-sm">--- Site is still in development ---</span>
            <a class="text-xs" href="mailto:contact@journeyplanner.io"
                >Report issues to: contact@journeyplanner.io</a
            >
        </div>
    </NuxtLayout>
</template>
