import daisyui from "daisyui"

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/welcome/**/*.blade.php",
        "./resources/**/welcome/**/*.js",
        "./resources/**/welcome/**/*.vue",
    ],
    theme: {
        extend: {},
    },
    plugins: [daisyui],
    daisyui: {
        themes: [
            {
                light: {
                    ...require("daisyui/src/theming/themes")["light"],
                    "primary": "#c72730",
                    "secondary": "#8EC7D2",
                    "accent": "#DBA507",
                    "neutral": "#3d4451",
                    "base-100": "#ffffff",
                },
                dark: {
                    ...require("daisyui/src/theming/themes")["dark"],
                    "primary": "#c72730",
                    "secondary": "#8EC7D2",
                    "accent": "#DBA507",
                },
            },
        ],
    },
}

