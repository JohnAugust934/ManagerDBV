---
name: release
description: Monta notas de release do ManagerDBV a partir dos Conventional Commits desde a última tag, agrupados por tipo/escopo em pt_BR. Use ao preparar uma nova versão/tag.
disable-model-invocation: true
---

# release

Gera as notas de release a partir do histórico de commits desde a última tag.

## Passos

1. **Achar a última tag** e o intervalo:
   ```
   git describe --tags --abbrev=0
   git log <ultima-tag>..HEAD --pretty=format:"%s"
   ```
   Se não houver tag, use todo o histórico da branch.

2. **Agrupar por tipo** (Conventional Commits), em **português**, na ordem:
   - 🚀 **Funcionalidades** (`feat`)
   - 🐛 **Correções** (`fix`)
   - ⚡ **Performance** (`perf`)
   - ♻️ **Refatorações** (`refactor`)
   - 📝 **Documentação** (`docs`)
   - 🔧 **Outros** (`chore`, `style`, `test`)
   Dentro de cada grupo, mostre o escopo entre parênteses (`(telegram)`, `(financeiro)`...).
   Ignore commits de merge e ruído.

3. **Sugerir a versão** (SemVer): `feat` → minor, `fix`/`perf` → patch, breaking change
   (`!` ou `BREAKING CHANGE`) → major. Proponha a próxima tag (ex.: `v1.4.0`).

4. **Entregar** as notas em markdown prontas para colar. **Não crie a tag nem faça push** a menos
   que o usuário peça — então:
   ```
   git tag -a vX.Y.Z -m "Release vX.Y.Z"
   ```

## Regras
- Linguagem das notas em pt_BR, coerente com o histórico do projeto.
- Não invente itens — derive só dos commits reais do intervalo.
