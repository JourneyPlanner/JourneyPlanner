/** @type {import('tailwindcss').Config} */
module.exports = {
  presets: [require("tailwindcss-preset-email")],
  content: [
    "./components/**/*.html",
    "./emails/**/*.html",
    "./layouts/**/*.html",
  ],
  theme: {
    extend: {
      fontFamily: {
        nunito: ["Nunito", "sans-serif"],
      },
      colors: {
        background: "#fcfcfc",
        "background-dark": "#2c2c2c",
        natural: {
          50: "#FCFCFC", //text-dark-default
          950: "#292929", // text-light-default
        },
        calypso: {
          300: "#88C4D8",
          600: "#327597",
          700: "#2B5973",
        },
        dandelion: {
          200: "#FAE58D",
          300: "#F8D351",
        },
      },
    },
  },
};
