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
    },
    colors: {
      background: "#fcfcfc" /* background color */,
      "background-dark": "#2c2c2c" /* dark background color */,
      "cta-border": "#f8d351" /* yellow banana */,
      "cta-bg": "#fee384" /* cta fill */,
      "cta-bg-dark": "#7A7052" /* cta fill dark */,
      "cta-bg-light": "#fff1c3" /* lighter cta */,
      "cta-bg-fill": "#E3C454" /* fill for e.g. new journey button dashboard */,
      "clouds-bg": "#f0f0f0" /* clouds background*/,
      "clouds-bg-dark": "#404040" /* clouds background dark */,
      "countdown-bg": "rgba(228,234,244,0.4)" /* countdown background */,
      surface: "rgba(228, 239, 244, 0.8)" /* form surface with opacity */,
      "surface-dark":
        "rgba(228, 239, 244, 0.22)" /* dark form surface with opacity */,
      card: "#rgba(228, 239, 244, 0.2)" /* card background */,
      "card-dark": "#353F44",
      text: "#333333" /* grey text */,
      "text-disabled": "#686868" /* disabled text */,
      "text-light-dark":
        "#DEDEDE" /* light dark text e.g. search text dashboard */,
      input: "#f8f8f8" /* input background */,
      "input-dark": "#454849" /* input background dark */,
      "input-label": "#5ba5c5" /* input label */,
      "input-gray": "#eaeaea" /* gray input */,
      "input-placeholder": "#7b7b7b" /* input placeholder */,
      "input-disabled": "#f1f1f1",
      border: "#69aecd" /* border */,
      "border-darker": "#61A0BD" /* darker border */,
      "border-gray": "#dcdcdc" /* gray border */,
      "border-grey": "#d9d9d9",
      footer: "#7b7b7b" /* footer text */,
      error: "#d43d3d" /* error text */,
      "error-dark": "#f25e5e" /* error text dark */,
      link: "#5ba5c5" /* link text */,
      "cancel-border": "#BB7474" /* cancel button */,
      "cancel-bg": "#d98f8f" /* cancel button */,
      "cancel-bg-dark": "#7c6464" /* cancel button dark */,
      cancel: "#BB7474" /* cancel button */,
      white: "#ffffff" /* white*/,
      "gradient-start": "#E3F1F6" /* gradient start */,
      "gradient-end": "#C6E1EC" /* gradient end */,
      "gradient-start-light": "#F2FCFF" /* light gradient start */,
    },
  },
  plugins: [],
};
