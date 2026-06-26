# Upgrade em Produção — ManagerDBV

Guia passo a passo para **atualizar uma instalação já existente** em produção (deploy de nova
versão do código, com ou sem migrations). Para a **instalação do zero**, use
[`docs/DEPLOY.md`](DEPLOY.md). Para **restaurar um backup**, use [`docs/RESTORE.md`](RESTORE.md).

> **Regra de ouro:** **SEMPRE faça backup do banco antes de qualquer deploy com migrations.**
> Se algo der errado, o backup é o seu plano de retorno garantido.

---

## Visão geral do fluxo

1. Ativar o **modo de manutenção** (tela customizada) — opcionalmente com URL secreta de bypass.
2. **Backup** do banco atual.
3. Atualizar o **código** (`git pull`).
4. Reinstalar **dependências** e **rebuild** do frontend.
5. Rodar **migrations**.
6. **Recachear** config/rotas/views.
7. **Reiniciar** o worker de filas.
8. **Validar** pela URL secreta.
9. **Desativar** o modo de manutenção.

Tempo típico de indisponibilidade: 1–3 minutos (mais, se houver migrations pesadas).

---

## Modo de manutenção (tela customizada)

Durante o upgrade, ative o modo de manutenção para que os usuários vejam uma **página amigável**
em vez de erros de meio de atualização. A tela é personalizada com a identidade visual do sistema
(fundo azul DBV, logo, engrenagens animadas) e fica em
[`resources/views/errors/503.blade.php`](../resources/views/errors/503.blade.php).

A view é **autocontida** — todo o CSS é inline, sem depender do Vite/Tailwind. Isso é proposital:
com a flag `--render`, o Laravel serve a página *antes* de carregar o framework, então ela não pode
depender do build do frontend.

### Comando recomendado

```bash
php artisan down --render="errors::503" --retry=60 --secret="token-secreto-do-deploy"
```

### Opções explicadas

| Opção | O que faz | Por que usar |
|-------|-----------|--------------|
| `--render="errors::503"` | **Pré-renderiza** a tela customizada e a serve *antes* do framework subir. | Garante que a página apareça mesmo durante migrations ou se o boot falhar. Sem ela, o Laravel só usa a `503.blade.php` depois de bootar a aplicação inteira. |
| `--retry=60` | Envia o header HTTP `Retry-After: 60`. | Dica para navegadores e buscadores tentarem novamente em 60 segundos (melhor para cache/SEO). |
| `--secret="token-secreto"` | Cria uma **URL de bypass**. | Acessando `https://SEU-DOMINIO/token-secreto`, você navega no site normalmente (recebe um cookie de bypass) enquanto o público continua vendo a tela de manutenção. Perfeito para validar o deploy antes de liberar para todos. |

> **Use um token aleatório e difícil de adivinhar** no `--secret` (ex.: um UUID). Qualquer pessoa
> com a URL completa entra no site durante a manutenção.
>
> Para gerar rapidamente:
> ```bash
> php artisan down --render="errors::503" --retry=60 --secret="$(php -r 'echo bin2hex(random_bytes(16));')"
> ```
> Anote o token impresso — você vai precisar dele para acessar pela URL de bypass.

### Como funciona a URL de bypass

1. Ative com `--secret="meu-token"`.
2. Acesse uma vez `https://SEU-DOMINIO/meu-token` no navegador → o Laravel grava um cookie e
   redireciona para a home.
3. A partir daí, **aquele navegador** navega o site normalmente; todos os outros veem a manutenção.
4. Ao rodar `php artisan up`, o modo de manutenção é desligado para todos e o cookie deixa de
   importar.

---

## Passo a passo completo

```bash
# ── 1. Ativar modo de manutenção com tela customizada + URL de bypass ──
php artisan down --render="errors::503" --retry=60 --secret="token-secreto-do-deploy"

# ── 2. Backup do banco ANTES de mexer em qualquer coisa ──
php artisan backup:run

# ── 3. Atualizar o código ──
git pull origin main

# ── 4. Dependências (sem pacotes de dev) + rebuild do frontend ──
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# ── 5. Migrations (se houver) ──
php artisan migrate --force

# ── 6. Limpar e recachear (obrigatório em produção) ──
php artisan config:clear && php artisan config:cache
php artisan route:clear  && php artisan route:cache
php artisan view:clear   && php artisan view:cache

# ── 7. Reiniciar o worker de filas (recarrega o código novo) ──
php artisan queue:restart

# ── 8. VALIDAR pela URL secreta (antes de liberar ao público) ──
#     Abra https://SEU-DOMINIO/token-secreto e confira:
#     - Login funciona
#     - Telas principais carregam
#     - Geração de PDF funciona
#     - storage:link OK (foto de desbravador aparece)

# ── 9. Desativar modo de manutenção (libera para todos) ──
php artisan up
```

> **`--force` nas migrations:** em produção o Laravel pede confirmação interativa antes de rodar
> migrations; `--force` pula essa confirmação. É necessário em deploy automatizado/SSH.

---

## Rollback em caso de falha

Se a validação (passo 8) revelar problema, **mantenha o modo de manutenção ativo** e reverta:

```bash
# 1. NÃO rode `php artisan up` — mantenha a manutenção ativa.

# 2. Reverter o código para o commit anterior
git checkout <commit-anterior>

# 3. Reverter migration (se a nova versão rodou alguma)
php artisan migrate:rollback

# 4. Restaurar o backup do banco (se os dados foram afetados)
#    Via painel: /backups → botão Restaurar
#    Ou manualmente (SQLite): extrair o ZIP do backup e sobrescrever database/database.sqlite
#    Runbook detalhado: docs/RESTORE.md

# 5. Reinstalar dependências da versão antiga + rebuild
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 6. Recachear e reiniciar o worker
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart

# 7. Validar pela URL secreta e, se estiver OK, liberar
php artisan up
```

---

## Checklist de verificação pós-upgrade

- [ ] Backup foi gerado **antes** do deploy: `php artisan backup:run` (conferir em `/backups`)
- [ ] Site abre em HTTPS sem erro de certificado
- [ ] Login com usuário master funciona
- [ ] Foto de desbravador exibe corretamente (valida `storage:link`)
- [ ] Geração de PDF funciona (Relatórios → qualquer relatório)
- [ ] Worker de filas rodando: `supervisorctl status`
- [ ] Scheduler ativo (cron de 1 min com `schedule:run`)
- [ ] Logs sem erros: `tail -f storage/logs/laravel.log`
- [ ] Modo de manutenção **desligado**: `php artisan up` (o site responde para o público)

---

## Notas e armadilhas

- **Hospedagem compartilhada (ex.: Hostinger):** se `php artisan up`/`down` não funcionar como
  esperado, confira se o usuário do servidor web tem escrita em `storage/framework/`. O estado da
  manutenção fica em `storage/framework/maintenance.php` / `storage/framework/down`.
- **`storage:link`:** se o host desabilitou `exec()`, o comando pode falhar — crie o link
  manualmente uma única vez (ver [`docs/DEPLOY.md`](DEPLOY.md), seção "Configurar storage").
  Não é necessário recriar o link a cada upgrade.
- **Sempre rebuild do frontend** (`npm ci && npm run build`) quando houver mudança em
  Blade/CSS/JS — o Tailwind reescaneia os templates e o Vite gera novos assets com hash.
- **`queue:restart` é obrigatório:** workers de fila carregam o código em memória; sem reiniciar,
  eles continuam executando a versão antiga até o `--max-time` expirar.
- **Caches:** nunca pule o `config:cache`/`route:cache`/`view:cache` em produção — além de
  performance, o `config:cache` evita que mudanças no `.env` sejam ignoradas de forma inconsistente.

---

## Referências

- [`docs/DEPLOY.md`](DEPLOY.md) — instalação do zero e configuração de servidor (Supervisor, cron, nginx).
- [`docs/RESTORE.md`](RESTORE.md) — runbook de restauração de backup.
- [`docs/UPGRADE-MULTITENANT.md`](UPGRADE-MULTITENANT.md) — upgrade específico da migração para o modelo multi-tenant.
