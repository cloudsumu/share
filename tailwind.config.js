const colors = require("tailwindcss/colors");

module.exports = {
  darkMode: "class", // or 'media' or 'class'
  purge: {
    mode: "all",
    content: ["./resources/views/**/*.blade.php"],
  },
  theme: {
    extend: {},
  },
  variants: {
    extend: {
      textOpacity: ["dark"],
    },
  },
  plugins: [
    require("@tailwindcss/forms")({
      strategy: "class",
    }),
  ],
};
