import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

// --- ALERTAS & CONFIRMAÇÕES PADRONIZADOS ---
// Diálogo de confirmação (<x-confirm-dialog />)
// Uso: confirmAction({ message, formId, title, confirmText, cancelText, variant, payload })
window.confirmAction = (opts = {}) =>
    window.dispatchEvent(new CustomEvent("open-confirm", { detail: opts }));

// Toast de aviso (<x-toast-host />)
// Uso: notify('Mensagem', 'success' | 'error' | 'warning' | 'info')
window.notify = (message, type = "info") =>
    window.dispatchEvent(new CustomEvent("app-notify", { detail: { message, type } }));

// --- CORREÇÃO DA TELA BRANCA E TRANSIÇÃO ---

function mostrarPagina() {
    // Força a opacidade para 1 (Visível)
    document.body.style.opacity = "1";
}

// --- PERSISTÊNCIA DA ROLAGEM DA SIDEBAR ---
// Mantém a posição de scroll do menu lateral entre navegações de página,
// evitando que a sidebar volte ao topo a cada troca de tela.
const SIDEBAR_SCROLL_KEY = "sidebarScroll";

function restaurarScrollSidebar() {
    const nav = document.getElementById("sidebar-nav");
    if (!nav) return;
    const salvo = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);
    if (salvo !== null) nav.scrollTop = parseInt(salvo, 10) || 0;
}

function monitorarScrollSidebar() {
    const nav = document.getElementById("sidebar-nav");
    if (!nav) return;
    let ticking = false;
    nav.addEventListener("scroll", () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            sessionStorage.setItem(SIDEBAR_SCROLL_KEY, String(nav.scrollTop));
            ticking = false;
        });
    });
}

// 1. Ao carregar o DOM (HTML pronto), restaura o scroll (antes do fade-in) e mostra a tela
window.addEventListener("DOMContentLoaded", () => {
    restaurarScrollSidebar();
    monitorarScrollSidebar();
    mostrarPagina();
});

// 2. Ao usar o botão "Voltar" do navegador (BFCache), garante que mostre e restaura o scroll
window.addEventListener("pageshow", () => {
    restaurarScrollSidebar();
    mostrarPagina();
});

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
