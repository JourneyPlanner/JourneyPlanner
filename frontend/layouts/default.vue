<template>
  <div class="flex flex-col min-h-screen">
    <main class="flex-grow">
      <slot class="" />
    </main>
    <footer
      class="text-footer text-xs md:text-sm font-nunito w-full flex justify-center mt-auto mb-auto py-2"
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
