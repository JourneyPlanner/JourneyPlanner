<template>
  <div class="flex flex-col h-screen justify-between">
    <slot />
    <footer
      class="text-footer text-sm font-nunito pb-3 flex items-center justify-center"
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
