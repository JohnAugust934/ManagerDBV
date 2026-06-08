# Guia de Deploy — ManagerDBV

## Pré-requisitos

- PHP 8.2+, extensões: `pdo_sqlite` (ou pdo_pgsql/pdo_mysql), `gd`, `zip`, `mbstring`, `openssl`
- Composer 2.x
- Node.js 20+ e npm
- Servidor web (nginx/Apache) apontando `document root` para `public/`
- Usuário do servidor web com permissão de escrita em `storage/` e `bootstrap/cache/`

---

## Primeiro Deploy (instalação do zero)

### 1. Configurar ambiente

```bash
cp .env.production.example .env
# Editar .env e preencher TODOS os valores marcados com TODO
php artisan key:generate
```

### 2. Instalar dependências (sem pacotes de dev)

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 3. Banco de dados

```bash
# SQLite: criar o arquivo do banco antes da migration
touch database/database.sqlite

# Rodar migrations (apenas tabelas, SEM seeders de demo)
php artisan migrate --force

# Criar usuário master de produção
php artisan db:seed --class=MasterOnlySeeder
```

### 4. Configurar storage

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache   # ajustar ao usuário do servidor
```

### 5. Cachear configurações (obrigatório em produção)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Configurar worker de filas

Criar arquivo de configuração do Supervisor (exemplo para Ubuntu/Debian):

```ini
# /etc/supervisor/conf.d/managerdbv-worker.conf
[program:managerdbv-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/managerdbv/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/managerdbv-worker.log
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start managerdbv-worker:*
```

### 7. Configurar cron (scheduler do Laravel)

Adicionar ao crontab do servidor (`crontab -e` como www-data ou root):

```
* * * * * php /var/www/managerdbv/artisan schedule:run >> /dev/null 2>&1
```

### 8. Testar backup

```bash
php artisan backup:run
php artisan backup:monitor
```

---

## Deploy de Atualização

> **SEMPRE fazer backup antes de qualquer deploy com migrations.**

```bash
# 1. Ativar modo de manutenção
php artisan down --retry=60

# 2. Fazer backup do banco atual
php artisan backup:run

# 3. Atualizar código
git pull origin main

# 4. Instalar dependências atualizadas
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 5. Rodar migrations (se houver)
php artisan migrate --force

# 6. Limpar e recachear
php artisan config:clear && php artisan config:cache
php artisan route:clear  && php artisan route:cache
php artisan view:clear   && php artisan view:cache

# 7. Reiniciar worker de filas
php artisan queue:restart

# 8. Desativar modo de manutenção
php artisan up
```

---

## Rollback em caso de falha

```bash
# 1. Manter modo de manutenção ativo
# 2. Reverter código para commit anterior
git checkout <commit-anterior>

# 3. Reverter migration (se aplicável)
php artisan migrate:rollback

# 4. Restaurar backup do banco (se necessário)
# Via painel de admin: /backups > botão Restaurar
# Ou manualmente: extrair ZIP do backup e sobrescrever database/database.sqlite

# 5. Recachear e subir
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

---

## Checklist de verificação pós-deploy

- [ ] Site abre em HTTPS sem erro de certificado
- [ ] Login com usuário master funciona
- [ ] Foto de desbravador exibe corretamente (verifica storage:link)
- [ ] Geração de PDF funciona (acesse Relatórios > qualquer relatório)
- [ ] Backup manual executa sem erro: `php artisan backup:run`
- [ ] Worker de filas está rodando: `supervisorctl status`
- [ ] Logs sem erros: `tail -f storage/logs/laravel.log`

---

## Seeders

| Seeder | Uso |
|--------|-----|
| `MasterOnlySeeder` | **Produção** — cria apenas o usuário master, classes e especialidades base |
| `DatabaseSeeder` | **Desenvolvimento** — cria dados demo completos (30+ desbravadores, movimentações etc.) |

> Em produção, o `DatabaseSeeder` redireciona automaticamente para `MasterOnlySeeder`.
> Nunca execute `php artisan db:seed` sem `--class=MasterOnlySeeder` em produção.
