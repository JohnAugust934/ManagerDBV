# Documentação — ManagerDBV

Índice da documentação técnica. Para visão geral do projeto, instalação e logins de
demonstração, veja o [`README.md`](../README.md) na raiz.

## Entendendo o sistema

| Documento | Para quê |
|---|---|
| [ARQUITETURA.md](ARQUITETURA.md) | Como o sistema funciona por dentro: multi-tenancy, autorização, frequência/ranking, LGPD, backup, agendamento. **Comece aqui.** |
| [guia-visual-ui.md](guia-visual-ui.md) | Padrões de UI para novas telas: layout, botões `ui-btn-*`, acessibilidade (WCAG 2.1 AA). |

## Operação (deploy, upgrade, restauração)

| Documento | Para quê |
|---|---|
| [DEPLOY.md](DEPLOY.md) | Instalação do zero em produção: servidor, Supervisor, cron, storage, seeders. |
| [UPGRADE-PRODUCAO.md](UPGRADE-PRODUCAO.md) | Atualizar uma instalação existente (deploy de nova versão, com/sem migrations). |
| [UPGRADE-MULTITENANT.md](UPGRADE-MULTITENANT.md) | Migrar uma instalação single-tenant (v4.x) → multi-tenant (v5.x) sem perda de dados. |
| [RESTORE.md](RESTORE.md) | Runbook de restauração de backup (banco → arquivos → configs → verificação). |

## Validação

| Documento | Para quê |
|---|---|
| [roteiro-validacao-operacional.md](roteiro-validacao-operacional.md) | Checklist de smoke-test dos fluxos críticos antes de liberar uma versão. |

---

> Orientações específicas para trabalho assistido por IA ficam no `CLAUDE.md` (raiz).
