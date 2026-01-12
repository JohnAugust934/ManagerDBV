import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

// --- CORREÇÃO DA TELA BRANCA E TRANSIÇÃO ---

function mostrarPagina() {
    // Força a opacidade para 1 (Visível)
    document.body.style.opacity = "1";
}

// 1. Ao carregar o DOM (HTML pronto), mostra a tela
window.addEventListener("DOMContentLoaded", mostrarPagina);

// 2. Ao usar o botão "Voltar" do navegador (BFCache), garante que mostre
window.addEventListener("pageshow", mostrarPagina);

// 3. Ao clicar em links (Saída Suave)
document.addEventListener("click", (e) => {
    const link = e.target.closest("a");

    // Filtros de segurança: ignora se não for link, nova aba, ou ancora
    if (
        !link ||
        link.hostname !== window.location.hostname ||
        link.target === "_blank" ||
        link.getAttribute("href").startsWith("#") ||
        link.getAttribute("href") === ""
    ) {
        return;
    }

    // Se for logout (dentro de form), deixa o navegador processar
    if (link.closest("form")) return;

    // Se for o mesmo link da página atual, ignora
    if (link.href === window.location.href) return;

    e.preventDefault();

    // Desaparece suavemente
    document.body.style.opacity = "0";

    // Aguarda a animação (300ms) e troca de página
    setTimeout(() => {
        window.location.href = link.href;
    }, 300);
});
