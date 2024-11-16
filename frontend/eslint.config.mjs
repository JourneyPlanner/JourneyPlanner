// @ts-check
import withNuxt from "./.nuxt/eslint.config.mjs";

export default withNuxt({
    files: ["**/*.js", "**/*.vue", "**/*.ts"],
    rules: {
        "vue/html-self-closing": "off",
        "@typescript-eslint/no-unused-expressions": [
            "error",
            { allowShortCircuit: false },
        ],
    },
});
