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
      "background-dark": "#f1f1f1" /* dark background color */,
      cta: "#f8d351" /* yellow banana */,
      surface: "rgba(228, 239, 244, 0.8)" /* form surface with opacity */,
      text: "#333333" /* grey text */,
      input: "#f8f8f8" /* input background */,
      "input-label": "#5ba5c5" /* input label */,
      "input-placeholder": "#7b7b7b" /* input placeholder */,
      border: "#69aecd" /* border */,
      "disabled-bg": "#f1f1f1" /* disabled background */,
      "disabled-text": "#686868" /* disabled text */,
      footer: "#7b7b7b" /* footer text */,
      error: "#d43d3d" /* error text */,
      link: "#5ba5c5" /* link text */,
      cancel: "#BB7474" /* cancel button */,
    },
  },
  plugins: [],
};
