# Upgrade: single-tenant (v4.0.0-beta) → multi-tenant (v5.0.0+)

Guia para migrar uma instalação **em produção** da versão single-tenant para a
versão multi-tenant **sem perda de dados**.

> **Princípio:** fazemos o upgrade **in-place** no próprio banco que já está rodando.
> O schema multi-tenant é aditivo (nenhuma coluna/linha é removida); os dados
> existentes são reatribuídos ao clube via backfill automático. Tudo é reversível
> pelo backup do passo 0.

---

## Script automatizado (recomendado)

O script `scripts/upgrade-para-multitenant.sh` executa todos os passos abaixo de
forma automática, com redundâncias, log persistente e rollback guiado em caso de
falha.

```bash
# Uso básico (uma única linha):
./scripts/upgrade-para-multitenant.sh \
    --platform-admin=admin@seuclube.com

# Simular sem gravar nada (dry-run):
./scripts/upgrade-para-multitenant.sh \
    --platform-admin=admin@seuclube.com \
    --dry-run

# Múltiplos admins, banco com vários clubes:
./scripts/upgrade-para-multitenant.sh \
    --platform-admin=admin1@seuclube.com,admin2@seuclube.com \
    --club=1 \
    --yes

# Se o código já foi atualizado e o npm build já foi gerado:
./scripts/upgrade-para-multitenant.sh \
    --platform-admin=admin@seuclube.com \
    --skip-pull --skip-npm
```

**O script:**
1. Valida PHP 8.2+, Composer, `.env` e conexão com o banco.
2. Entra em modo manutenção.
3. Cria backup automático via `backup:run --only-db` (+ cópia do arquivo SQLite, se aplicável).
4. Faz `git pull` + `composer install --no-dev` + `npm run build`.
5. Roda `migrate --force`.
6. Roda `tenant:upgrade-legacy` com as opções fornecidas.
7. Valida com `tenant:check-integrity` — **aborta se encontrar problemas**.
8. Exibe contagem de registros por tabela para conferência visual.
9. Verifica categorias de caixa fora do padrão (aviso, não bloqueante).
10. Regenera caches e reinicia workers de fila (Supervisor, se disponível).
11. Sobe a aplicação (`artisan up`).

O log completo fica em `storage/logs/upgrade-multitenant-YYYYMMDD-HHMMSS.log`.

> Se preferir executar manualmente, siga o passo a passo abaixo.

---

## O que muda (e por que o upgrade é necessário)

A v4.0.0-beta já tinha `club_id` na maioria das tabelas, mas:

1. O isolamento era **fail-open** (sem clube → via tudo). Agora é **fail-closed**
   (usuário sem clube ativo não vê nada). Qualquer linha com `club_id NULL`
   ficaria **invisível** → o upgrade faz o backfill desses NULLs.
2. `desbravadores`, `frequencias` e `mensalidades` **não tinham** `club_id` direto —
   foram adicionados com backfill automático durante a migration.
3. Surgiram `users.is_platform_admin`, `ranking_snapshots.club_id` + unique composta
   e índices compostos `(club_id, <coluna quente)` em várias tabelas.
4. O conceito de **platform admin** (super admin cross-tenant, sem clube) é novo.
   O antigo "master" que via tudo precisa ser mapeado: vira platform admin ou fica
   vinculado ao clube.
5. `clubs.is_active` foi adicionado (padrão `true`) — clubes existentes são
   automaticamente marcados como ativos.

---

## Procedimento (passo a passo)

### 0. Backup ANTES de qualquer coisa (obrigatório)

```bash
php artisan down        # entra em modo manutenção
php artisan backup:run  # ou: cp database/database.sqlite database/database.sqlite.bak
```

Se o painel estiver disponível, o backup também pode ser disparado por lá
(**Backups → Fazer backup agora**). Guarde o arquivo `.zip` em local seguro
antes de continuar.

---

### 1. Subir o novo código

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

---

### 2. Migrar o schema + backfill automático

```bash
php artisan migrate --force
```

As migrations executadas são aditivas e incluem dois comportamentos automáticos
de proteção de dados:

| Migration | O que faz |
|---|---|
| `add_is_platform_admin_to_users` | Adiciona `users.is_platform_admin` (default `false`). |
| `fix_ranking_snapshots_for_multitenant` | Adiciona `ranking_snapshots.club_id` + troca unique `(year,scope)` por `(year,scope,club_id)`. |
| `add_club_id_indexes_to_remaining_tables` | Índices em `attendance_columns.club_id` e `invitations.club_id`. |
| `add_club_id_to_tenant_core_tables` | **Backfill automático:** adiciona `club_id` em `desbravadores/frequencias/mensalidades` e preenche via JOIN com `unidades → desbravadores → frequencias/mensalidades`. |
| `enforce_club_id_integrity` | Torna `club_id` NOT NULL nas tabelas de tenant + FK com cascade. Em banco de clube único, cura nulos residuais automaticamente antes de aplicar o NOT NULL. Aborta com erro claro se houver ambiguidade (vários clubes com linhas nulas). |
| `tenant_scoped_uniques_and_indexes` | CPF único por clube (ao invés de globalmente) + índices compostos `(club_id, <coluna quente>)`. Aborta se houver CPF duplicado dentro de um mesmo clube. |
| `add_is_active_to_clubs_table` | Adiciona `clubs.is_active` (default `true`). Clubes existentes ficam ativos. |
| `set_platform_admin_role` | No contexto de upgrade é **no-op** (roda antes de qualquer usuário ter `is_platform_admin=true`). |
| `create_caixa_audit_logs_table` | Cria `caixa_audit_logs` (auditoria de lançamentos). Adiciona `created_by`/`updated_by` (nuláveis) em `patrimonios` e `mensalidades` — registros antigos ficam com `NULL`, sem impacto. |
| `add_missing_performance_indexes` | Índices de performance adicionais em tabelas quentes. |
| `lgpd_fields_and_registros_table` | Adiciona campos LGPD em `desbravadores` (`consentimento_lgpd`, `usa_imagem_autorizado`, etc.), `termos_aceitos_em` em `users`, e cria `lgpd_registros` (ROPA). Todos os campos novos são anuláveis/com default — registros antigos não são afetados. |
| `create_relatorios_gerados_table` | Cria `relatorio_gerados` (fila assíncrona de geração de PDFs por clube). Tabela nova, sem dados legados. |
| `create_club_backup_logs_table` | Cria `club_backup_logs` (auditoria de backups por clube). Tabela nova, sem dados legados. |

> **Se `migrate` abortar** com `RuntimeException`, leia a mensagem: ela indica qual
> tabela tem problema (nulos ambíguos ou club_id pendente) e qual comando rodar
> para corrigir. O schema **não fica pela metade** — toda a cura de nulos acontece
> antes de qualquer DDL.

---

### 3. Verificar o que será ajustado (dry-run)

```bash
php artisan tenant:upgrade-legacy --dry-run
```

O relatório mostra:
- Quantas linhas ainda têm `club_id NULL` por tabela (após o backfill automático
  das migrations, devem ser poucos ou zero nas tabelas de dados).
- Quantos usuários estão órfãos (sem clube, sem ser platform admin).
- Qual club será o alvo.

**Nada é gravado** no dry-run.

Se houver mais de um clube no banco, especifique o alvo:
```bash
php artisan tenant:upgrade-legacy --club=ID --dry-run
```

---

### 4. Executar o upgrade de dados

```bash
php artisan tenant:upgrade-legacy --platform-admin=admin@seuclube.com
```

O que o comando faz, dentro de uma **transação** (se falhar, reverte tudo):

1. **Backfill de `club_id`** em todas as tabelas de tenant com linhas ainda nulas:
   `unidades`, `desbravadores`, `frequencias`, `mensalidades`, `caixas`,
   `patrimonios`, `eventos`, `atas`, `atos`, `attendance_columns`, `invitations`,
   `ranking_snapshots`.
2. **Promove os e-mails de `--platform-admin`** a super admin:
   `is_platform_admin = true`, `club_id = null`, `role = 'platform_admin'`.
3. **Vincula ao clube** quaisquer usuários com `club_id NULL` que *não* foram
   marcados como platform admin — para que ninguém fique travado pelo fail-closed.

Flags disponíveis:

| Flag | Descrição |
|---|---|
| `--club=ID` | Club alvo explícito. Obrigatório se houver mais de um clube. Default: o único clube existente. |
| `--platform-admin=email` | Pode repetir a flag ou passar lista separada por vírgula. |
| `--dry-run` | Só relata, sem gravar. |

> O comando é **idempotente** — rodar de novo não causa dano.

> Se nenhum `--platform-admin` for passado e não houver nenhum existente, o
> comando avisa: sem platform admin, ninguém acessa `/platform` nem os Backups.
> Não é bloqueante, mas **é altamente recomendado definir ao menos um**.

---

### 5. Verificar integridade

```bash
php artisan tenant:check-integrity
```

Deve retornar `✅ Integridade multi-tenant OK`. Se retornar erros, leia a mensagem
e corrija antes de abrir o sistema (normalmente significa que alguma linha ainda
está órfã — rode `tenant:upgrade-legacy` de novo ou corrija manualmente).

---

### 6. Limpar caches e subir

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

### 7. Validação manual (obrigatória antes de liberar)

- **Login como platform admin** (`admin@seuclube.com`) → deve cair em `/platform`,
  ver o(s) clube(s), conseguir entrar em modo suporte.
  - Acessar `/backups` → backup completo do sistema (Spatie).
  - Entrar em modo suporte → acessar `/backups` novamente → deve redirecionar
    automaticamente para `/backups/clube` (backup isolado do clube).
- **Login como master do clube** → deve ver apenas os dados do próprio clube;
  **não** deve acessar `/platform` nem `/backups` (sistema); deve ver o link
  "Backup do Clube" no menu lateral e acessar `/backups/clube`.
- **Navegar pelos módulos** (caixa, desbravadores, atas, etc.) e confirmar que todos
  os registros antigos aparecem corretamente.
- **Checar desbravadores**: abrir o perfil de um e confirmar que a foto carrega
  (prova que `storage/app/public/fotos` está íntegro).
- **Checar categorias de caixa** (ver nota abaixo): abrir um lançamento antigo em
  modo edição e confirmar que a categoria aparece corretamente no `<select>`.
- **Gerar um backup de clube**: como master, clicar em "Gerar Backup Agora" em
  `/backups/clube` e confirmar que o arquivo aparece na listagem.

#### Nota: categorias de caixa hardcoded

As categorias de entrada/saída em `caixas` são listas fixas no frontend (Alpine.js) —
não ficam em banco de dados. Os valores aceitos atualmente são:

| Entrada | Saída |
|---|---|
| Mensalidade | Materiais de Secretaria |
| Ofertas e Doações | Alimentação/Lanche |
| Inscrições de Eventos | Transporte/Combustível |
| Venda de Uniformes | Compra de Uniformes |
| Cantina | Equipamentos |
| Campanha | Taxas e Repasses |
| Outros | Devolução / Outros |

Se a instalação de origem gravou **categorias fora dessa lista** (ex.: "Dízimo",
"Aluguel"), o valor permanece correto no banco e aparece no índice, mas ao editar
o lançamento o `<select>` só o exibirá via fallback (o `edit.blade.php` trata isso
— a opção aparece selecionada mesmo não constando da lista). **Não há perda de
dado**, mas o clube não conseguirá reutilizar aquela categoria em lançamentos novos.

Antes de abrir o sistema após o upgrade, rode a query abaixo para identificar
categorias fora do padrão:

```sql
-- SQLite / MySQL / PostgreSQL
SELECT tipo, categoria, COUNT(*) AS total
FROM caixas
WHERE categoria NOT IN (
    'Mensalidade','Ofertas e Doações','Inscrições de Eventos',
    'Venda de Uniformes','Cantina','Campanha','Outros',
    'Materiais de Secretaria','Alimentação/Lanche','Transporte/Combustível',
    'Compra de Uniformes','Equipamentos','Taxas e Repasses','Devolução'
)
GROUP BY tipo, categoria
ORDER BY tipo, total DESC;
```

Se houver resultados, decida: ou **adiciona** a categoria nas listas dos dois Blade
views (`resources/views/financeiro/caixa/create.blade.php` e `edit.blade.php`)
antes de liberar, ou deixa como está (os lançamentos existentes continuam acessíveis,
mas a categoria fica inacessível para novos lançamentos).

---

## Rollback

Se algo der errado, restaure o backup do passo 0:

```bash
php artisan down
# SQLite:
cp database/database.sqlite.bak database/database.sqlite
# Ou via painel de Backups (com o código antigo).
php artisan up
```

Como o upgrade é in-place e o comando é transacional, uma falha no passo 4 não
deixa estado parcial. As migrations têm `down()` definido, mas o caminho mais
seguro em produção é sempre restaurar o backup.

---

## Consolidar várias instalações single-tenant em uma plataforma (cenário avançado)

Se você tem **bancos separados** (uma instalação por clube) e quer juntá-los num
único banco multi-tenant, há um par export/import automatizado.

### Passos

1. Em cada instalação de origem, faça o upgrade in-place (passos acima) e
   **exporte** o clube em JSON:
   - Via painel: `/platform` → botão **Exportar** no clube, **ou**
   - *Configurações do Clube → Exportar dados (JSON)* (master), **ou**
   - Via código: `ClubExportService::export($club)`.

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
   O comando roda em transação, remapeia todas as chaves e resolve o catálogo por
   chave natural. Imprime contagens importadas e **avisos** (ex.: usuário com
   e-mail já existente — não é duplicado; referência de catálogo ausente — linha
   pulada).

### Garantias e limites

- **Transacional:** falha não deixa estado parcial.
- **Sem duplicar login:** usuário cujo e-mail já existe no destino é reaproveitado.
- **Catálogo deve existir no destino:** rode os seeders de catálogo antes. Itens não
  resolvidos são pulados com aviso.
- **Não migra binários:** fotos e logo não vão no JSON. Recopie
  `storage/app/public` à parte.
- Sempre **`backup:run`** no destino antes de importar.
