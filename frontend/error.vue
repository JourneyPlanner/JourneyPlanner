<script setup>
const error = useError();

useHead({
    title: `${error.value.statusCode || "Error"} | JourneyPlanner`,
});

async function clear(target) {
    await clearError({ redirect: target });
}
</script>

<template>
    <div :error="error">
        <TolgeeProvider>
            <NuxtLayout>
                <div class="flex flex-col lg:flex-row">
                    <div
                        class="mx-10 mt-4 flex flex-col text-text dark:text-natural-50 max-md:items-center md:ml-10 lg:w-1/2"
                    >
                        <button
                            class="-ml-6 place-self-start md:-ml-0"
                            data-test="error-logo-to-startpage"
                            @click="clear('/dashboard')"
                        >
                            <SvgLogoHorizontalBlue class="w-44 lg:w-52" />
                        </button>
                        <h1
                            class="mt-8 text-7xl font-bold text-calypso-600 dark:text-calypso-400 md:mt-14 md:text-8xl"
                        >
                            {{ error.statusCode }}
                        </h1>
                        <h2
                            class="mt-3 max-h-40 min-h-12 overflow-y-scroll break-all pr-4 text-3xl font-semibold max-md:text-center md:mt-5 md:text-4xl"
                        >
                            <span v-if="error.data = 'isTolgeeKey'">
                                <T :key-name="error.message" />
                            </span>
                            <span v-else>{{ error.message }}</span>
                        </h2>
                        <p class="mt-3 text-base md:mt-5 md:text-lg">
                            <T key-name="error.information" />
                        </p>
                        <h3
                            class="mb-1.5 mt-5 place-self-start text-lg font-semibold md:mt-12 md:text-xl"
                        >
                            <T key-name="error.what.todo" />
                        </h3>
                        <ol
                            class="ml-5 mt-1 list-outside list-disc space-y-1.5 pl-1.5 text-base md:pl-3 md:text-lg"
                        >
                            <li>
                                <button
                                    data-test="error-back-button"
                                    class="text-calypso-600 hover:underline dark:text-calypso-300"
                                    @click="clear('/')"
                                >
                                    <T key-name="common.back" />
                                </button>
                                <T key-name="error.startpage" />
                            </li>
                            <li>
                                <button
                                    data-test="error-create-journey-button"
                                    class="text-calypso-600 hover:underline dark:text-calypso-300"
                                    @click="clear('/journey/new')"
                                >
                                    <T
                                        key-name="error.create.new.journey.link"
                                    />
                                </button>
                                <T key-name="error.create.new.journey" />
                            </li>
                            <li>
                                <NuxtLink
                                    data-test="error-mail-link"
                                    to="mailto:contact@journeyplanner.io"
                                    class="text-calypso-600 hover:underline dark:text-calypso-300"
                                >
                                    <T key-name="error.contact.link" />
                                </NuxtLink>
                                <T key-name="error.contact" />
                            </li>
                            <li>
                                <NuxtLink
                                    data-test="error-github-issue-link"
                                    to="https://github.com/JourneyPlanner/JourneyPlanner/issues/new/choose"
                                    target="_blank"
                                    class="text-calypso-600 hover:underline dark:text-calypso-300"
                                >
                                    <T key-name="error.github.link" />
                                </NuxtLink>
                                <T key-name="error.github" />
                            </li>
                        </ol>
                    </div>
                    <div class="flex justify-center lg:w-1/2 lg:justify-end">
                        <SvgErrorUniverse
                            class="mt-3 h-full w-[20rem] xs:mt-5 md:mt-10 md:w-[40rem]"
                        />
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
