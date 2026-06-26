---
name: gen-test
description: Gera testes Pest seguindo os padrões do ManagerDBV (SQLite :memory:, factories com escopo de tenant, estilo de tests/Feature/MultiTenant). Passe o alvo como argumento — controller, model, comando ou feature. Use ao adicionar cobertura para código novo ou descoberto.
disable-model-invocation: true
---

# gen-test

Gera um teste Pest para o alvo informado em `$ARGUMENTS` (ex.: `MensalidadeController`,
`App\Models\Frequencia`, `tenant:check-integrity`).

## Antes de escrever

1. **Leia o alvo** e identifique comportamento, autorização (Gate/role) e dados necessários.
2. **Estude os padrões existentes** antes de gerar — não invente convenções:
   - `tests/Pest.php` (bootstrap, helpers globais, traits aplicados).
   - Casos vizinhos em `tests/Feature/` e principalmente `tests/Feature/MultiTenant/` para o
     padrão de criar clube/usuário com `club_id` e autenticar.
   - As factories em `database/factories/` (incluindo `UserFactory`).

## Convenções obrigatórias

- Testes rodam em **SQLite `:memory:`** e não tocam o banco de dev — use `RefreshDatabase` conforme
  o `Pest.php` já configura.
- **Multi-tenant**: crie o clube e usuário com `club_id` correto e autentique (`actingAs`). Para
  verificar isolamento, crie **dois** clubes e confirme que um não enxerga dados do outro. Lembre
  que `master`/platform admin tem `club_id = null` e enxerga tudo.
- **Autorização**: respeite o `role` + Gates de `routes/web.php` (ex.: `financeiro`, `secretaria`).
  Teste tanto o caminho autorizado quanto o 403.
- **Domínio em pt_BR**: nomes de teste descritivos em português, coerentes com os arquivos vizinhos.
- Cubra o caminho feliz **e** ao menos uma borda (validação, permissão negada, ou isolamento).

## Depois de escrever

1. Formate: `./vendor/bin/pint <arquivo-de-teste>`.
2. Rode só o novo arquivo: `php artisan test <caminho>` e itere até passar.
3. Reporte o que cobriu e o que deliberadamente ficou de fora.
