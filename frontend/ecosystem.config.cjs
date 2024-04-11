module.exports = {
    apps: [
        {
            name: "JourneyPlanner",
            port: "3000",
            exec_mode: "cluster",
            instances: "max",
            script: "./server/index.mjs",
        },
    ],
};
