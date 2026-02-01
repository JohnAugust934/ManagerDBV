import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    // Habilita o modo escuro via classe 'dark' (essencial para o botão de alternar tema)
    darkMode: "class",

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                // Mantém a fonte Figtree, que é moderna e limpa
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta Oficial dos Desbravadores
                dbv: {
                    red: "#D9222A", // Vermelho (Sacrifício) - Usar em botões de ação/alerta
                    blue: "#002F6C", // Azul (Lealdade) - Usar em sidebars e cabeçalhos
                    yellow: "#FCD116", // Amarelo (Excelência) - Usar em ícones e destaques
                    gold: "#C5B358", // Dourado - Detalhes nobres

                    // Variações para o modo escuro (Dark Mode)
                    dark: {
                        bg: "#0f172a", // Fundo principal escuro (Slate 900)
                        surface: "#1e293b", // Fundo de cartões/menus (Slate 800)
                    },
                },
            },
            // Animações suaves para entrada de elementos
            animation: {
                "fade-in": "fadeIn 0.3s ease-in-out",
                "slide-in": "slideIn 0.3s ease-out",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideIn: {
                    "0%": { transform: "translateY(10px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
            },
        },
    },

    plugins: [forms],
};
