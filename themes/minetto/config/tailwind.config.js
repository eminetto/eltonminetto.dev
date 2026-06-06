const path = require("path");

// Raiz do projeto Hugo (dois níveis acima de themes/minetto/config).
const baseDir = path.join(__dirname, "..", "..", "..");

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: "class",
  content: [
    `${baseDir}/themes/**/layouts/**/*.html`,
    `${baseDir}/layouts/**/*.html`,
    `${baseDir}/content/**/*.html`,
    `${baseDir}/content/**/*.md`,
    `${baseDir}/public/**/*.html`,
  ],
  theme: {
    extend: {
      fontFamily: {
        // Inspirado no maketime.blog: Archivo em tudo (títulos + corpo)
        sans: [
          '"Archivo"',
          "-apple-system",
          "BlinkMacSystemFont",
          "Segoe UI",
          "Roboto",
          "Helvetica",
          "Arial",
          "sans-serif",
        ],
        // "serif" mantido como apelido apontando para Archivo (corpo)
        serif: [
          '"Archivo"',
          "-apple-system",
          "BlinkMacSystemFont",
          "Segoe UI",
          "Roboto",
          "Helvetica",
          "Arial",
          "sans-serif",
        ],
        mono: [
          '"JetBrains Mono"',
          "ui-monospace",
          "SFMono-Regular",
          "Menlo",
          "Consolas",
          "monospace",
        ],
      },
      maxWidth: {
        prose: "44rem",
      },
      colors: {
        // Paleta Make Time
        cream: { DEFAULT: "#faf8f4", 100: "#f3efe8", 200: "#e8e1d4" },
        night: { DEFAULT: "#17140f", 100: "#221d16", 200: "#322b22", text: "#ece7df", soft: "#a39d92" },
        ink: { DEFAULT: "#141414", soft: "#4d4d4d" },
        brand: { DEFAULT: "#ffd40a", 600: "#e6bf09" }, // amarelo da marca
        link: { DEFAULT: "#156974", dark: "#7fd6df" }, // teal/ciano legível
        accent: {
          light: "#156974",
          dark: "#7fd6df",
        },
      },
    },
  },
  plugins: [],
};
