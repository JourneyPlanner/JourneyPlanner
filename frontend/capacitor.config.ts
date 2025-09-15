import type { CapacitorConfig } from "@capacitor/cli";

const config: CapacitorConfig = {
    appId: "io.journeyplanner.app",
    appName: "JourneyPlanner",
    webDir: ".output/public",
    server: {
        url: "https://journeyplanner.io",
        cleartext: true,
    },
};

export default config;
