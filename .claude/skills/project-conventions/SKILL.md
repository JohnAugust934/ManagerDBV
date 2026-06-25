---
name: project-conventions
description: Convenções não óbvias do ManagerDBV que devem ser respeitadas ao escrever ou alterar código — pt_BR no domínio, Pint só nos arquivos tocados, sem soft deletes, no_ranking invertido, multi-tenancy, foco em qualidade. Conhecimento de fundo, carregado automaticamente quando relevante.
user-invocable: false
---

# Convenções do ManagerDBV

Regras sutis do projeto que não são óbvias pelo código e cuja violação causa retrabalho. (Fonte
canônica: `CLAUDE.md` na raiz — consulte-o para detalhes.)

## Linguagem e estilo
- **Domínio em pt_BR**: controllers, rotas, models, colunas, textos de UI e nomes de teste em
  português. Locale forçado para `pt_BR` em `AppServiceProvider`.
- **Pint só nos arquivos que você tocar** — há débito de formatação pré-existente; **não**
  reformate o projeto inteiro sem pedido.
- **Foco atual: endurecer qualidade** (robustez, testes, consistência financeira), não novas
  features. Ao propor próximos passos, priorize correção/cobertura.

## Armadilhas de domínio
- **Sem soft deletes** (decisão de produto): exclusão é em cascata definitiva.
- **`Unidade::no_ranking` é invertido**: `no_ranking = true` significa que a unidade **participa**
  do ranking (coluna mal-nomeada).
- **Ranking é lógica DUPLICADA**: `AppServiceProvider::snapshotRankingYear` (console) vs
  `RankingController` (ao vivo) — mudou um, mude o outro.
- **Frequência tem modo legado** (colunas booleanas) ainda entrelaçado — não remover sem pedido.

## Multi-tenancy
- Dados isolados por `club_id` via global scopes (`ClubScope` direto; `DesbravadorClubScope` via
  `unidade`). Ao adicionar model com escopo de tenant, registre o scope em `booted()`.
- `master`/platform admin tem `club_id = null` → enxerga tudo. Scopes fazem curto-circuito sem
  autenticação (seeders/console veem todas as linhas).

## Autorização
- Acesso por `role` + `extra_permissions`, resolvido em `User::temPermissao()`. Proteja
  funcionalidades definindo o **Gate** em `AppServiceProvider::boot()` + `middleware('can:...')` em
  `routes/web.php` — não invente verificações ad-hoc.

## Backup
- Subsistema frágil que já quebrou em produção — antes de mexer, leia a seção de backup do CLAUDE.md
  e acione o subagente `backup-reviewer`.
