# RESTORE.md — Guia de Restauração do ManagerDBV

Guia completo para restaurar o sistema a partir de um backup, na ordem segura
**banco de dados → arquivos → configs → verificação**.

O fluxo automatizado existe na tela **/backups** (`BackupController::restore`).
Este guia cobre o entendimento desse fluxo e a restauração **manual** por linha de
comando, para um desastre em que o painel não esteja disponível.

> **Regra de ouro:** restaure o **banco primeiro**, depois os arquivos. Nunca
> apague o banco sem antes ter um snapshot de emergência.

---

## 1. Pré-requisitos

- Acesso SSH ao servidor (ou ambiente local com o código já implantado via Git).
- **Código da aplicação** implantado (o código **não** está no backup — é versionado no Git).
- Binários do banco no PATH, conforme o motor de destino:
  - **PostgreSQL:** `psql`, `pg_dump` (caminho em `config/database.php → connections.pgsql.dump.dump_binary_path`).
  - **MySQL/MariaDB:** `mysql`, `mysqldump` (em produção/Hostinger o PATH já resolve).
- O arquivo `.zip` do backup baixado (do disco `local` ou do bucket **R2**).
- O `manifest.json` correspondente (gravado ao lado do zip) **ou** o checksum
  SHA-256 vindo da notificação do Telegram, para conferir o arquivo antes de restaurar.
- Credenciais do banco de destino (`DB_*`) e, se for restaurar do R2, as `R2_*`.

### Conferir integridade do arquivo antes de restaurar
```bash
# Linux
sha256sum backup.zip
# PowerShell
Get-FileHash backup.zip -Algorithm SHA256
```
Compare com o `archive.sha256` do `manifest.json` (ou com o checksum do Telegram).
Os checksums **por arquivo interno** também estão no `manifest.json` (campo `files[]`)
e no histórico da tabela `backup_logs`.

---

## 2. O que há dentro de um backup

Cada `.zip` (gerado por `spatie/laravel-backup`) contém:

- **Dump do banco** — `db-dumps/database.sql` (MySQL/PostgreSQL) **ou**
  `database/database.sqlite` (SQLite).
- **Uploads dos usuários** — tudo sob `app/public/` (fotos em `app/public/fotos/`,
  logo do clube em `app/public/logos/`).

Ao lado do zip há um `*.manifest.json` com metadados e checksums. **Arquivos de
configuração (`.env`) NÃO entram no backup** por segurança (contêm segredos) — veja
a seção 6 para recriá-los.

---

## 3. Restauração completa pelo painel (recomendada)

1. Acesse **/backups** como usuário `master`.
2. Localize o backup (lista local + R2). Confira data, tamanho e (se quiser) o checksum.
3. Clique em **Restaurar**. O sistema automaticamente:
   - entra em manutenção (`artisan down`);
   - cria **snapshot de emergência** (`storage/app/pre_restore_snapshot.sql`);
   - extrai o zip com proteção contra path traversal;
   - **apaga** o banco (`db:wipe`), restaura o dump e roda `migrate` (exceto SQLite);
   - restaura os arquivos de `app/public`;
   - sai da manutenção (`artisan up`), encerra a sessão e redireciona ao login;
   - **reverte** ao snapshot de emergência se algo falhar após o wipe.
4. Faça login com as credenciais **da época do backup**.

---

## 4. Restauração completa manual (sem o painel)

```bash
# 0) Manutenção
php artisan down

# 1) Snapshot de emergência do banco ATUAL (rede de segurança)
#    PostgreSQL:
pg_dump --no-owner --no-privileges -h "$DB_HOST" -U "$DB_USERNAME" "$DB_DATABASE" > pre_restore_snapshot.sql
#    MySQL:
mysqldump -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE" > pre_restore_snapshot.sql

# 2) Extrair o backup
mkdir restore_tmp && cd restore_tmp && unzip ../backup.zip && cd ..

# 3) BANCO PRIMEIRO — zera e restaura o dump
php artisan db:wipe --force
#    PostgreSQL (dump .sql):
psql -v ON_ERROR_STOP=1 -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -f restore_tmp/db-dumps/database.sql
#    MySQL (dump .sql):
mysql -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE" < restore_tmp/db-dumps/database.sql
#    SQLite: copie o arquivo para config('database.connections.sqlite.database')
#    cp restore_tmp/database/database.sqlite database/database.sqlite

# 4) Migrations (NÃO rodar para SQLite restaurado por cópia)
php artisan migrate --force

# 5) ARQUIVOS DEPOIS — uploads para storage/app/public
cp -r restore_tmp/app/public/* storage/app/public/

# 6) Configs (ver seção 6) e finalização
php artisan optimize:clear
php artisan up

# 7) Limpeza
rm -rf restore_tmp backup.zip
```

### Rollback (se o passo 3/4 falhar após o `db:wipe`)
```bash
php artisan db:wipe --force
psql -v ON_ERROR_STOP=1 -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -f pre_restore_snapshot.sql
#   (MySQL: mysql ... < pre_restore_snapshot.sql)
php artisan migrate --force
php artisan up
```

---

## 5. Restauração PARCIAL

### 5.1 Somente o banco de dados
Use quando os uploads estão intactos e só os dados precisam voltar.
```bash
php artisan down
# snapshot de emergência (ver passo 1 acima)
unzip backup.zip 'db-dumps/*' -d restore_tmp        # extrai só o dump
php artisan db:wipe --force
psql -v ON_ERROR_STOP=1 -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -f restore_tmp/db-dumps/database.sql
php artisan migrate --force
php artisan up
```

### 5.2 Somente os arquivos (uploads)
Use quando o banco está íntegro e só faltam fotos/logo.
```bash
unzip backup.zip 'app/public/*' -d restore_tmp      # extrai só os uploads
cp -r restore_tmp/app/public/* storage/app/public/
php artisan storage:link   # se o link público ainda não existir
```
Nenhuma das restaurações parciais mexe no que não foi pedido — o banco não é
tocado em 5.2, e os uploads não são tocados em 5.1.

---

## 6. Configs e `.env` (não estão no backup)

Por segurança, o `.env` (com segredos: `APP_KEY`, `DB_*`, `R2_*`, `TELEGRAM_*`)
**não** é incluído no backup nem enviado ao R2. Para recriá-lo num ambiente novo:

1. Copie o modelo: `cp .env.production.example .env` (produção) ou `.env.example` (local).
2. Preencha os valores reais (marcados com `# TODO`): banco, R2, Telegram, mail.
3. Gere a chave da aplicação: `php artisan key:generate`.
   - **Atenção:** se houver dados criptografados com uma `APP_KEY` antiga, use a
     **mesma** `APP_KEY` da época do backup — caso contrário não serão decifráveis.
     Guarde a `APP_KEY` de produção num cofre de segredos separado do backup.
4. `php artisan config:clear` para aplicar.

---

## 7. Verificação pós-restauração (obrigatória)

1. **Login** com um usuário conhecido do período do backup.
2. **Financeiro:** abrir o caixa e conferir os últimos lançamentos (`caixas`).
3. **Membros:** abrir o perfil de um desbravador e confirmar que a **foto** carrega
   (prova que `app/public/fotos` voltou).
4. **Logo do clube** aparece no cabeçalho.
5. Nenhum erro novo no canal de erros do Telegram.

---

## 8. MySQL (produção/Hostinger) × PostgreSQL (local/Supabase)

O fluxo é o mesmo; muda **o binário e a sintaxe**:

| Etapa | PostgreSQL (`pgsql`) | MySQL (`mysql`) |
|-------|----------------------|-----------------|
| Snapshot | `pg_dump ... > f.sql` | `mysqldump ... > f.sql` |
| Restore | `psql -v ON_ERROR_STOP=1 ... -f f.sql` | `mysql ... < f.sql` |
| Migrations | `php artisan migrate --force` | `php artisan migrate --force` |

**Atenção:** um dump MySQL **não** é restaurável diretamente num PostgreSQL (e
vice-versa). Restaure sempre no **mesmo motor** em que o backup foi gerado, e
garanta que o `DB_CONNECTION` do destino bate com o do dump.
