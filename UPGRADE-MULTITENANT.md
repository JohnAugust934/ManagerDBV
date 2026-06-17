# Upgrade: single-tenant (v4.0.0-beta) → multi-tenant

Guia para migrar uma instalação **em produção** da versão single-tenant para a
versão multi-tenant **sem perda de dados**.

> Princípio: **não copiamos dados entre bancos** (cópia = risco de perda). Fazemos o
> upgrade **in-place** no próprio banco que já está rodando. O schema multi-tenant é
> aditivo (nenhuma coluna/linha é removida) e o ajuste de dados é um backfill de
> `club_id`. Tudo reversível por backup.

## Por que o upgrade é necessário (e o que muda)
A v4.0.0-beta já tinha as colunas `club_id` na maioria das tabelas, mas:
1. O isolamento era **fail-open** (sem clube → via tudo). Agora é **fail-closed**
   (usuário sem clube ativo não vê nada). Logo, qualquer linha com `club_id NULL`
   ficaria **invisível** se não for atribuída ao clube.
2. Surgiram `users.is_platform_admin`, `ranking_snapshots.club_id` (+ unique novo) e
   índices em `club_id`.
3. O conceito de **platform admin** (super admin cross-tenant) é novo. O antigo
   "master que via tudo" (geralmente com `club_id NULL`) precisa ser mapeado: vira
   platform admin, ou é vinculado ao clube.

## Procedimento (passo a passo)

### 0. Backup ANTES de qualquer coisa (obrigatório)
```bash
php artisan down                 # opcional: modo manutenção
php artisan backup:run           # ou copie o database.sqlite / dump do Postgres/MySQL
```

### 1. Subir o novo código
```bash
git pull                         # versão multi-tenant
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 2. Migrar o schema (aditivo, seguro)
```bash
php artisan migrate --force
```
Isso adiciona `is_platform_admin`, `ranking_snapshots.club_id` (unique
`year,scope,club_id`) e índices. Nenhum dado é apagado.

### 3. Conferir o que será ajustado (DRY-RUN)
```bash
php artisan tenant:upgrade-legacy --dry-run
# Se houver mais de um clube no banco:
php artisan tenant:upgrade-legacy --club=ID --dry-run
```
O relatório mostra quantas linhas órfãs (`club_id NULL`) existem por tabela e o plano
de ação. **Nada é gravado** no dry-run.

### 4. Executar o upgrade de dados
```bash
php artisan tenant:upgrade-legacy --platform-admin=admin@seuclube.com
# (multi-clube) php artisan tenant:upgrade-legacy --club=ID --platform-admin=...
```
O comando, dentro de uma **transação**:
- Faz backfill de `club_id = <clube alvo>` em `unidades, caixas, patrimonios, eventos,
  atas, atos, attendance_columns, invitations, ranking_snapshots`.
  (Desbravadores e mensalidades são cobertos automaticamente, pois se vinculam via
  `unidade.club_id`.)
- Promove o(s) e-mail(s) de `--platform-admin` a super admin (`is_platform_admin=true`,
  `club_id=NULL`).
- Vincula ao clube os demais usuários órfãos (que não são platform admin), para que
  ninguém fique travado pelo fail-closed. **Platform admins existentes nunca são
  arrastados para um clube.**

Flags:
- `--club=ID` — alvo explícito (default: o único clube existente).
- `--platform-admin=email` — pode repetir ou usar lista separada por vírgula.
- `--dry-run` — só relata.

O comando é **idempotente** (rodar de novo não causa dano).

### 5. Recachear e validar
```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
Validação manual:
- Login como **platform admin** → cai em `/platform`, vê o(s) clube(s), entra em modo
  suporte, acessa **Backups**.
- Login como **master/diretor do clube** → vê apenas os dados do clube; **não** acessa
  `/backups`; tem "Exportar dados" em Configurações do Clube.
- Conferir que todos os registros antigos aparecem (caixa, desbravadores, atas, etc.).

## Rollback
Se algo der errado, restaure o backup do passo 0 (o painel de backups ou o dump do
banco). Como o upgrade é in-place e transacional, uma falha no passo 4 não deixa
estado parcial; ainda assim, o backup é a rede de segurança definitiva.

## Consolidar VÁRIAS instalações single-tenant em UMA plataforma (cenário avançado)
Se você tem **bancos separados** (uma instalação por clube) e quer juntá-los num único
banco multi-tenant, há um par export/import automatizado.

### Passos
1. Em cada instalação de origem, faça o upgrade in-place (passos acima) para garantir
   que o clube esteja íntegro, e **exporte** o clube em JSON:
   - `/platform` → botão **Exportar** no clube, **ou**
   - *Configurações do Clube → Exportar dados (JSON)* (master), **ou**
   - via código: `ClubExportService::export($club)`.
   O JSON é **completo**: usuários (com hash de senha), unidades, desbravadores,
   frequências e valores de colunas, financeiro, eventos e inscrições, documentos,
   patrimônio e manutenções, snapshots de ranking, especialidades e progresso de
   classe. Referências de catálogo (classes/especialidades/requisitos) viajam com
   **chaves naturais** na seção `_catalogo`.
2. No banco-plataforma de destino (com o catálogo já semeado —
   `ClassesSeeder`/`EspecialidadesSeeder`), **importe** criando um novo clube:
   ```bash
   php artisan tenant:import-club caminho/para/export-clube.json
   php artisan tenant:import-club arquivo.json --name="Nome do Clube no destino"
   ```
   O comando roda em transação, remapeia todas as chaves e resolve o catálogo por chave
   natural. Imprime contagens importadas e **avisos** (ex.: usuário com e-mail já
   existente — não é duplicado; referência de catálogo ausente — linha pulada).

### Garantias e limites
- **Transacional:** falha não deixa estado parcial.
- **Sem duplicar login:** usuário cujo e-mail já existe no destino é reaproveitado (as
  relações apontam para ele); o e-mail é a chave global de login.
- **Catálogo deve existir no destino:** rode os seeders de catálogo antes. Itens não
  resolvidos (classe/especialidade/requisito inexistentes no destino) são pulados com
  aviso — semeie o catálogo idêntico para evitar isso.
- **Não migra binários:** o arquivo de logo do clube e fotos não vão no JSON (apenas
  dados do banco). Recopie os uploads (`storage/app/public`) à parte se necessário.
- Sempre **`backup:run`** no destino antes de importar.
