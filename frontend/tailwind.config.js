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
            background: "#fcfcfc" /* background color */,
            "background-dark": "#2c2c2c" /* dark background color */,
            "cta-border": "#f8d351" /* yellow banana */,
            "cta-bg": "#fee384" /* cta fill */,
            "cta-bg-dark": "#7A7052" /* cta fill dark */,
            "cta-bg-light": "#fff1c3" /* lighter cta */,
            "cta-bg-fill":
                "#E3C454" /* fill for e.g. new journey button dashboard */,
            "clouds-bg": "#f0f0f0" /* clouds background*/,
            "clouds-bg-dark": "#404040" /* clouds background dark */,
            "countdown-bg": "rgba(228,234,244,0.4)" /* countdown background */,
            "countdown-stroke-dark": "#497c93" /* countdown stroke dark */,
            "ticket-top-dark-bg": "#80A2B1" /* ticket top dark background */,
            surface: "rgba(228, 239, 244, 0.8)" /* form surface with opacity */,
            "surface-dark":
                "rgba(228, 239, 244, 0.22)" /* dark form surface with opacity */,
            card: "#rgba(228, 239, 244, 0.2)" /* card background */,
            "card-dark": "#353F44",
            text: "#333333" /* grey text */,
            "text-disabled": "#686868" /* disabled text */,
            "text-light-dark":
                "#DEDEDE" /* light dark text e.g. search text dashboard */,
            "form-input-dark": "#607781" /* dark form input */,
            input: "#f8f8f8" /* input background */,
            "input-dark": "#454849" /* input background dark */,
            "input-label": "#5ba5c5" /* input label */,
            "input-gray": "#eaeaea" /* gray input */,
            "input-grey": "#f2f5f9" /* grey input */,
            "input-placeholder": "#7b7b7b" /* input placeholder */,
            "input-disabled": "#f1f1f1",
            "input-disabled-dark": "#636667" /* disabled input dark */,
            "input-disabled-dark-gray":
                "#BBBBBB" /* disabled input dark gray */,
            "input-disabled-dark-grey": "#5a5a5a" /* disabled input gray */,
            border: "#69aecd" /* border */,
            "border-darker": "#61A0BD" /* darker border */,
            "border-blue-dark": "#58869B" /* dark blue border */,
            "border-gray": "#dcdcdc" /* gray border */,
            "border-grey": "#d9d9d9",
            "border-light": "#d0d0d0" /* light border */,
            "border-dark": "rgba(105, 174, 205, 0.15)" /* dark border */,
            footer: "#7b7b7b" /* footer text */,
            error: "#d43d3d" /* error text */,
            "error-dark": "#f25e5e" /* error text dark */,
            link: "#5ba5c5" /* link text */,
            "cancel-border": "#BB7474" /* cancel button */,
            "cancel-bg": "#d98f8f" /* cancel button */,
            "cancel-bg-dark": "#7c6464" /* cancel button dark */,
            cancel: "#BB7474" /* cancel button */,
            white: "#ffffff" /* white*/,
            "border-green-save": "#96D446",
            "border-green-save-dark": "#79AC38",
            "fill-green-save-dark": "rgba(131, 165, 119, 0.6)",
            "fill-green-save": "#DEF9BD",
            "gradient-start": "#E3F1F6" /* gradient start */,
            "gradient-end": "#C6E1EC" /* gradient end */,
            "gradient-start-light": "#F2FCFF" /* light gradient start */,
            "gradient-start-dark": "#95c0d2" /* dark gradient start */,
            "gradient-end-dark": "#699fb4" /* dark gradient end */,
            "color-gray-200": "#373737",
            "chip-blue": "#B0D9EC" /* blue chip */,
            "chip-grey": "#E8E8E8" /* grey chip */,
            "chip-blue-dark": "#506D7B" /* dark blue chip */,
            "chip-grey-dark": "#717171" /* dark grey chip */,
            "blue-text": "#589EBC",
            "grey-text": "#6B6565",
            "blue-text-dark": "#7FAAC0",
            "grey-text-dark": "#ADACAC",
        },
    },
    plugins: [],
};
