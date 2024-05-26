/** @type {import('tailwindcss').Config} */
export default {
    content: [
        `components/**/*.{vue,js}`,
        `layouts/**/*.vue`,
        `pages/**/*.vue`,
        `composables/**/*.{js,ts}`,
        `plugins/**/*.{js,ts}`,
        `App.{js,ts,vue}`,
        `app.{js,ts,vue}`,
    ],
    darkMode: "class",
    theme: {
        extend: {
            fontFamily: {
                nunito: "Nunito",
                "nunito-sans": "Nunito Sans",
            },
            screens: {
                xs: "350px",
            },
        },
        colors: {
            background: "#fcfcfc",
            "background-dark": "#2c2c2c",
            "clouds-bg": "#f0f0f0",
            "clouds-bg-dark": "#404040",
            "marker-not-added": "#8CA5B0",
            "gradient-start": "#E3F1F6",
            "gradient-end": "#C6E1EC",
            "gradient-start-light": "#F2FCFF",
            "gradient-start-dark": "#95c0d2",
            "gradient-end-dark": "#699fb4",

            calypso: {
                50: "#F0F8FB",
                100: "#DAEDF3",
                200: "#B9DCE8",
                300: "#88C4D8",
                400: "#50A1C0",
                500: "#3485A6",
                600: "#327597",
                700: "#2B5973",
                800: "#2B4C5F",
                900: "#274052",
                950: "#182C39",
            },

            gothic: {
                50: "#F3F7F8",
                100: "#E0EAED",
                200: "#C4D7DD",
                300: "#9BBAC5",
                400: "#6A95A6",
                500: "#4F798B",
                600: "#446476",
                700: "#3C5462",
                800: "#374853",
                900: "#313F48",
                950: "#1D272F",
            },

            text: "#333333",

            natural: {
                50: "#FCFCFC",
                100: "#EFEFEF",
                200: "#DCDCDC",
                300: "#BDBDBD",
                400: "#989898",
                500: "#7C7C7C",
                600: "#656565",
                700: "#525252",
                800: "#464646",
                900: "#3D3D3D",
                950: "#292929",
            },

            dandelion: {
                100: "#FDF3C8",
                200: "#FAE58D",
                300: "#F8D351",
                400: "#F6C029",
            },

            "pesto-600": "#7B713F",

            mahagony: {
                300: "#EFB2B2",
                400: "#E58484",
                500: "#D65D5D",
                500_030: "#6E4F50",
                600: "#C13E3E",
            },

            atlantis: {
                200: "#D2F0A6",
                300_40: "#627548",
                400: "#96D446",
            },

            ronchi: {
                300: "#E3C454",
                400: "#DFB640",
                500: "#D79929",
            },

            dark: "#42484A",
            light: "#F6FAFB",
        },
    },
    plugins: [],
};
