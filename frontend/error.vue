<script setup>
const error = useError();

useHead({
    title: `${error.value.statusCode || "Error"} | JourneyPlanner`,
});

function clear(navigateFunction) {
    clearError();
    navigateFunction();
}
</script>

<template>
    <div :error="error">
        <TolgeeProvider>
            <NuxtLayout>
                <div class="flex">
                    <div
                        class="ml-10 mt-4 w-1/2 text-text dark:text-natural-50"
                    >
                        <button
                            class="z-50"
                            data-test="logo-to-startpage"
                            @click="clear(navigateTo('/'))"
                        >
                            <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
                        </button>
                        <h1
                            class="mt-10 text-8xl font-bold text-calypso-600 dark:text-calypso-400"
                        >
                            {{ error.statusCode }}
                        </h1>
                        <h2 class="mt-5 text-3xl font-bold">
                            {{ error.message }}
                        </h2>
                        <p class="mt-5 text-xl">
                            <T key-name="error.information" />
                        </p>
                        <h3 class="mt-5 text-2xl font-semibold">
                            <T key-name="error.what.todo" />
                        </h3>
                        <ol class="ml-5 mt-1 list-inside list-disc text-xl">
                            <li>
                                <T key-name="error.back" />
                                <button
                                    class="text-calypso-500 hover:underline dark:text-calypso-300"
                                    @click="clear(router.back())"
                                >
                                    <T key-name="error.back.link" />
                                </button>
                            </li>
                            <li>
                                <button
                                    class="text-calypso-500 hover:underline dark:text-calypso-300"
                                    @click="clear(navigateTo('/journey/new'))"
                                >
                                    <T
                                        key-name="error.create.new.journey.link"
                                    />
                                </button>
                                <T key-name="error.create.new.journey" />
                            </li>
                            <li>
                                <span class="inline">
                                    <a
                                        href="mailto:contact@journeyplanner.io"
                                        class="text-calypso-500 hover:underline dark:text-calypso-300"
                                    >
                                        <T key-name="error.contact.link" />
                                    </a>
                                    <T key-name="error.contact" />
                                </span>
                            </li>
                        </ol>
                        <div class="mt-14 text-lg">
                            <b>
                                <T
                                    key-name="error.additional.information.bold"
                                />
                            </b>
                            <T key-name="error.additional.information" />
                        </div>
                    </div>
                    <div class="flex w-1/2 justify-end">
                        <SvgErrorUniverse class="mt-10 w-[40rem]" />
                    </div>
                </div>
            </NuxtLayout>
            <template #fallback>
                <div
                    class="flex h-screen flex-col items-center justify-center gap-5 text-text dark:text-natural-50"
                >
                    <ProgressSpinner class="w-14" />
                    <h1 class="text-lg font-medium">
                        Getting ready for your adventures...
                    </h1>
                </div>
            </template>
        </TolgeeProvider>
    </div>
</template>
