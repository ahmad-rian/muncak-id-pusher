import { fontFamily } from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],

  theme: {
    extend: {
      fontFamily: {
        lato: [
          "Lato",
          "system-ui",
          "-apple-system",
          "sans-serif",
          ...fontFamily.sans,
        ],
        merriweather: [
          "Merriweather",
          "system-ui",
          "-apple-system",
          "sans-serif",
          ...fontFamily.sans,
        ],
        sans: ["Figtree", ...fontFamily.sans],
      },
    },
  },

  safelist: [
    "alert-info",
    "alert-success",
    "alert-warning",
    "alert-error",
    "border-info",
    "border-success",
    "border-warning",
    "border-error",
  ],

  plugins: [require("daisyui"), require("@tailwindcss/typography")],

  daisyui: {
    themes: [
      "winter",
      {
        "dark-winter": {
          primary: "#0069ff",
          "primary-content": "#d1e4ff",
          secondary: "#4632aa",
          "secondary-content": "#d5d6f1",
          accent: "#c148ac",
          "accent-content": "#0e020b",
          neutral: "#021431",
          "neutral-content": "#c5cbd3",
          "base-100": "#1d232a",
          "base-200": "#181d23",
          "base-300": "#13171c",
          "base-content": "#ccced0",
          info: "#00b5fb",
          "info-content": "#000c15",
          success: "#81cfd1",
          "success-content": "#061010",
          warning: "#efd7bb",
          "warning-content": "#14110d",
          error: "#e58b8b",
          "error-content": "#120707",
        },
      },
    ],
  },
};
