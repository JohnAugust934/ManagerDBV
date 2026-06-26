---
name: pr-check
description: Roda o fluxo de verificação pré-PR do ManagerDBV — checa formatação Pint nos arquivos do diff e a suíte Pest. Use antes de abrir/atualizar um PR ou pedir revisão.
disable-model-invocation: true
---

# pr-check

Verifica se o branch atual está pronto para PR. Não corrige nada automaticamente — apenas reporta
o que precisa de atenção.

## Passos

1. **Descobrir os arquivos PHP alterados** vs. a `main`:
   ```
   git diff --name-only --diff-filter=ACMR main...HEAD -- '*.php'
   ```
   Se vazio, caia para `git diff --name-only --diff-filter=ACMR -- '*.php'` (working tree).

2. **Conferir formatação** apenas nesses arquivos (sem reformatar o projeto todo):
   ```
   ./vendor/bin/pint --test <arquivos>
   ```
   No Windows, use a invocação PowerShell equivalente do projeto. Se houver arquivos a formatar,
   liste-os e ofereça rodar `./vendor/bin/pint <arquivos>` (sem `--test`) para corrigir.

3. **Rodar a suíte de testes** (já limpa config obsoleta):
   ```
   composer test
   ```
   No Windows: `php artisan config:clear; php artisan test`.

4. **Gate multi-tenant** (mesmo do CI), se o diff tocou models/scopes/migrations:
   ```
   php artisan migrate:fresh --seed --force && php artisan tenant:check-integrity
   ```
   Atenção: isso recria o banco de dev — só sugira, confirme antes de rodar.

## Relatório

Resuma: ✅/❌ Pint, ✅/❌ testes (com a contagem e os que falharam), e o gate de integridade se
aplicável. Para falhas, mostre a saída relevante e proponha a correção. Não faça commit nem push.
