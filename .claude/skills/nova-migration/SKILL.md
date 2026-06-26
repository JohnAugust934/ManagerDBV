---
name: nova-migration
description: Cria uma migration multi-tenant no padrão do ManagerDBV — club_id + FK/cascade, índices, uniques escopados por tenant, compatível com SQLite/PostgreSQL/MySQL. Use ao adicionar tabela ou coluna que pertence a um clube.
disable-model-invocation: true
---

# nova-migration

Gera uma migration seguindo as convenções multi-tenant e a matriz de 3 bancos do projeto.

## Antes de escrever

1. Gere o arquivo base: `php artisan make:migration <nome_descritivo>` (não edite o nome do
   timestamp). Estude migrations recentes de referência em `database/migrations/` — especialmente
   as `*_add_club_id_*`, `*_enforce_club_id_integrity` e `*_tenant_scoped_uniques_and_indexes`.

## Convenções obrigatórias

- **Tenant**: tabelas de dados de clube levam `club_id` (FK para `clubs`, `cascadeOnDelete()` —
  soft delete foi dispensado, exclusão é em cascata por design). Registre o global scope no Model
  (`ClubScope` direto, ou trait `BelongsToTenant`).
- **Índices**: indexe `club_id` e as colunas usadas em filtros/ordenação. Uniques de domínio devem
  ser **compostos com `club_id`** (unique por tenant, não global) — ex.:
  `$table->unique(['club_id', 'numero'])`.
- **Matriz de 3 bancos** (SQLite test, PostgreSQL dev, MySQL prod) — portabilidade:
  - SQLite **não** suporta vários `ALTER` no mesmo statement nem dropar/alterar coluna facilmente;
    evite operações que o SQLite recusa em teste. Rode a suíte (`:memory:`) para validar.
  - Datetimes: use `timestamp`/`datetime` do schema builder, não strings ISO 8601 cruas (já houve
    bug de import SQLite→MySQL com formato `T...Z`).
  - Cheque comportamento de FK: SQLite exige `PRAGMA foreign_keys`; o projeto controla isso.
- Sempre implemente `down()` coerente (reversível).

## Depois de escrever

1. `./vendor/bin/pint <arquivo-da-migration>`.
2. `php artisan migrate` no dev e `php artisan migrate:fresh --seed` para validar do zero.
3. `php artisan tenant:check-integrity` (gate do CI) se a migration toca `club_id`.
4. Rode a suíte: `composer test`.
