import { addComponent, defineNuxtModule } from "@nuxt/kit";

export default defineNuxtModule({
    setup() {
        // import { T as T } from '@tolgee/vue'
        addComponent({
            name: "T",
            export: "T",
            filePath: "@tolgee/vue",
        });

        addComponent({
            name: "TolgeeProvider",
            export: "TolgeeProvider",
            filePath: "@tolgee/vue",
        });
    },
});
