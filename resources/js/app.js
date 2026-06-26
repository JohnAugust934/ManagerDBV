import "./bootstrap";
import "./passkeys";
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

// --- PWA: registra o service worker (instalável + fallback offline) ---
// SW só roda em contexto seguro (HTTPS ou localhost); em outros hosts de dev
// o navegador simplesmente ignora, sem erro.
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/sw.js").catch(() => {
            /* registro do SW é best-effort; falha não deve quebrar a app */
        });
    });
}

// 3. Ao clicar em links (Saída Suave)
// Respeita prefers-reduced-motion (pula o fade) e protege contra href ausente,
// esquemas externos e tela branca presa caso a navegação não conclua.
const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
).matches;

document.addEventListener("click", (e) => {
    // Sem fade quando o usuário pede menos movimento: deixa o navegador navegar.
    if (prefersReducedMotion) return;

    const link = e.target.closest("a");
    if (!link) return;

    const href = link.getAttribute("href");

    // Filtros de segurança: ignora sem href, âncora, vazio, nova aba/frame alvo,
    // download, esquemas externos (mailto:/tel:) ou host diferente.
    if (
        !href ||
        href === "" ||
        href.startsWith("#") ||
        href.startsWith("mailto:") ||
        href.startsWith("tel:") ||
        link.target ||
        link.hasAttribute("download") ||
        link.hostname !== window.location.hostname
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

    // Rede de segurança: se a navegação não concluir (link cancelado, 4xx, etc.),
    // restaura a visibilidade para não deixar a tela branca presa.
    const restaurar = setTimeout(() => {
        document.body.style.opacity = "1";
    }, 1200);
    window.addEventListener("pagehide", () => clearTimeout(restaurar), {
        once: true,
    });

    // Aguarda a animação (300ms) e troca de página
    setTimeout(() => {
        window.location.href = link.href;
    }, 300);
});
