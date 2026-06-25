---
name: commit
description: Cria um commit no padrão do ManagerDBV — Conventional Commits em pt_BR (feat/fix/docs/refactor com escopo do módulo) e trailer Co-Authored-By. Use quando o usuário pedir para commitar mudanças.
disable-model-invocation: true
---

# commit

Monta e cria um commit seguindo o padrão de histórico do projeto.

## Passos

1. **Inspecionar o estado**:
   ```
   git status --short
   git diff --staged   # e git diff para não-staged
   ```
   Se nada estiver staged, mostre o que mudou e confirme com o usuário o que incluir
   (`git add <arquivos>`). Não faça `git add -A` cego se houver arquivos não relacionados.

2. **Conferir formatação** dos `.php` staged antes de commitar:
   `./vendor/bin/pint --test <arquivos>` — se sujo, rode o Pint e re-adicione.

3. **Montar a mensagem** no padrão do projeto (Conventional Commits, **em português**):
   - Tipos: `feat`, `fix`, `docs`, `refactor`, `chore`, `test`, `style`, `perf`.
   - Escopo = módulo afetado, como no histórico: `manutencao`, `telegram`, `deploy`, `backup`,
     `financeiro`, `secretaria`, `ranking`, `multi-tenant`, etc.
   - Exemplos reais: `fix(telegram): evita recursão ao falhar o envio de notificação`,
     `feat(manutencao): tela 503 customizada`.
   - Assunto imperativo, conciso. Corpo só se agregar (o porquê, não o quê).

4. **Criar o commit** com o trailer obrigatório:
   ```
   git commit -m "<tipo>(<escopo>): <assunto>" -m "<corpo opcional>" -m "Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>"
   ```

## Regras

- **Só commit** — não faça `push` a menos que o usuário peça explicitamente.
- Se estiver na branch `main`, avise e proponha criar uma branch antes.
- Nunca use `--no-verify` nem pule hooks.
