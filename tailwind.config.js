import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                dbv: {
                    red: "#D9222A", // Vermelho oficial (Sacrifício)
                    blue: "#002F6C", // Azul oficial (Lealdade)
                    yellow: "#FCD116", // Amarelo oficial (Excelência)
                    gold: "#C5B358", // Dourado para detalhes
                    light: "#F3F4F6", // Fundo claro moderno
                },
            },
        },
    },

    plugins: [forms],
};
