# Guia Visual Rápido (UI)

Este guia resume os padrões visuais do sistema para novas telas e ajustes.

## 1. Princípios

- Mobile first: tudo funciona primeiro em telas pequenas.
- Consistência: use componentes e classes utilitárias já existentes.
- Clareza de ação: destaque apenas a ação principal da tela.
- Acessibilidade: foco visível e bom contraste sempre.

## 2. Estrutura de Página

- Use `x-app-layout` e `ui-page` como base.
- O `header` deve conter apenas título/contexto.
- Botões de ação ficam fora do header, no início do conteúdo.

Exemplo:

```blade
<x-slot name="header">
    <h2 class="font-bold text-xl text-dbv-blue dark:text-gray-100 leading-tight">
        Título da Tela
    </h2>
</x-slot>

<div class="ui-page space-y-6">
    <div class="px-4 sm:px-0 flex justify-end">
        <a href="#" class="ui-btn-primary w-full sm:w-auto">Nova Ação</a>
    </div>
</div>
```

## 3. Botões (Padrão Oficial)

- Primário: `ui-btn-primary`
- Secundário: `ui-btn-secondary`
- Crítico/perigoso: `ui-btn-danger`

Regras:

- Em barras de ação: `w-full sm:w-auto`
- Ícone à esquerda quando ajuda reconhecimento
- Evite classes manuais de botão (prefira `ui-btn-*`)

Exemplos:

```blade
<a class="ui-btn-secondary w-full sm:w-auto">Voltar</a>
<button class="ui-btn-primary w-full sm:w-auto">Salvar</button>
<button class="ui-btn-danger w-full sm:w-auto">Excluir</button>
```

## 4. Hierarquia de Ações

- 1 ação primária por bloco.
- Ações secundárias com `ui-btn-secondary`.
- Exclusão sempre com `ui-btn-danger` + confirmação.

## 5. Campos e Blocos

- Labels: `x-input-label` ou `ui-input-label`
- Inputs/Selects/Textareas: `ui-input` quando aplicável
- Cards: `ui-card`
- Blocos de suporte: `ui-card-muted`
- Estado vazio: `x-empty-state`

## 6. Mensagens e Feedback

- Notificações usam componente global:
  - `resources/views/components/flash-messages.blade.php`
- Fechamento automático ativo.
- Hover no desktop pausa o fechamento temporariamente.

## 7. Checklist Antes de Publicar

- Header sem botões de ação.
- Botões principais com `ui-btn-*` e `w-full sm:w-auto`.
- Sem texto quebrado (acentos/pontuação revisados).
- Responsivo validado em mobile e desktop.
- Build validado com `npm run build`.

## 8. Referências de Implementação

- Estilos base: `resources/css/app.css`
- Exemplo de listagem padrão: `resources/views/desbravadores/index.blade.php`
- Exemplo de ações fora do header:
  - `resources/views/eventos/show.blade.php`
  - `resources/views/unidades/show.blade.php`
