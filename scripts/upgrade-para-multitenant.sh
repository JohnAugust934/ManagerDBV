#!/usr/bin/env bash
# =============================================================================
#  upgrade-para-multitenant.sh
#  Upgrade seguro: single-tenant (v4.0.0-beta) → multi-tenant (v5.0.0+)
#
#  USO:
#    ./scripts/upgrade-para-multitenant.sh [opções]
#
#  OPÇÕES:
#    --platform-admin=email   E-mail(s) do super admin cross-tenant (pode repetir
#                             ou separar por vírgula). Obrigatório na primeira execução.
#    --club=ID                ID do clube alvo (obrigatório se o banco tiver >1 clube).
#    --skip-backup            Pula o backup automático (NÃO recomendado).
#    --skip-pull              Pula o git pull (útil quando o deploy é via outro meio).
#    --skip-npm               Pula o npm build (útil quando os assets já foram gerados).
#    --dry-run                Relata o que seria feito, sem gravar nada.
#    --yes                    Não pede confirmação interativa.
#
#  EXEMPLOS:
#    ./scripts/upgrade-para-multitenant.sh \
#        --platform-admin=admin@meuclube.com
#
#    ./scripts/upgrade-para-multitenant.sh \
#        --platform-admin=admin@meuclube.com,outro@meuclube.com \
#        --skip-pull --dry-run
# =============================================================================
set -euo pipefail

# ── Cores ────────────────────────────────────────────────────────────────────
RED='\033[0;31m'; YELLOW='\033[1;33m'; GREEN='\033[0;32m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
ok()      { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[AVISO]${RESET} $*"; }
fatal()   { echo -e "${RED}[ERRO]${RESET}  $*" >&2; exit 1; }
section() { echo -e "\n${BOLD}══ $* ══${RESET}"; }

# ── Defaults ─────────────────────────────────────────────────────────────────
PLATFORM_ADMINS=()
CLUB_ID=""
SKIP_BACKUP=false
SKIP_PULL=false
SKIP_NPM=false
DRY_RUN=false
YES=false

# ── Parse de argumentos ───────────────────────────────────────────────────────
for arg in "$@"; do
  case "$arg" in
    --platform-admin=*) PLATFORM_ADMINS+=("${arg#*=}") ;;
    --club=*)           CLUB_ID="${arg#*=}" ;;
    --skip-backup)      SKIP_BACKUP=true ;;
    --skip-pull)        SKIP_PULL=true ;;
    --skip-npm)         SKIP_NPM=true ;;
    --dry-run)          DRY_RUN=true ;;
    --yes)              YES=true ;;
    --help|-h)
      sed -n '/#  USO:/,/^# ===/p' "$0" | sed 's/^# \{0,2\}//'
      exit 0 ;;
    *) warn "Argumento desconhecido ignorado: $arg" ;;
  esac
done

# ── Localiza a raiz do projeto ────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"
info "Diretório do projeto: $PROJECT_ROOT"

# ── Função artisan com passagem correta de argumentos ─────────────────────────
artisan() { php artisan "$@"; }

# ── Registra log de execução ──────────────────────────────────────────────────
LOG_FILE="$PROJECT_ROOT/storage/logs/upgrade-multitenant-$(date +%Y%m%d-%H%M%S).log"
mkdir -p "$(dirname "$LOG_FILE")"
exec > >(tee -a "$LOG_FILE") 2>&1
info "Log registrado em: $LOG_FILE"

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════════════════╗${RESET}"
echo -e "${BOLD}║  Upgrade: single-tenant → multi-tenant (ManagerDBV v5)  ║${RESET}"
echo -e "${BOLD}╚══════════════════════════════════════════════════════════╝${RESET}"
echo ""

$DRY_RUN && warn "MODO DRY-RUN — nenhuma alteração será gravada."

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 0 — Pré-validação do ambiente"
# ─────────────────────────────────────────────────────────────────────────────

# PHP
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
IFS='.' read -r PHP_MAJ PHP_MIN <<< "$PHP_VERSION"
if [[ "$PHP_MAJ" -lt 8 || ("$PHP_MAJ" -eq 8 && "$PHP_MIN" -lt 2) ]]; then
  fatal "PHP 8.2+ é obrigatório (encontrado: $PHP_VERSION)"
fi
ok "PHP $PHP_VERSION"

# Composer
command -v composer >/dev/null 2>&1 || fatal "composer não encontrado no PATH"
ok "Composer $(composer --version --no-ansi 2>/dev/null | awk '{print $3}')"

# artisan
[[ -f "$PROJECT_ROOT/artisan" ]] || fatal "artisan não encontrado em $PROJECT_ROOT — certifique-se de estar na raiz do projeto"
ok "artisan encontrado"

# .env
[[ -f "$PROJECT_ROOT/.env" ]] || fatal ".env não encontrado. Copie .env.example e configure antes de prosseguir."
ok ".env presente"

# Banco acessível
if ! artisan db:show --json >/dev/null 2>&1; then
  fatal "Não foi possível conectar ao banco de dados. Verifique DB_* no .env"
fi
ok "Conexão com banco de dados OK"

# Verifica se o banco é de ORIGEM single-tenant ou já está no estado multi-tenant
ST_FLAG=$(php -r "
  require '${PROJECT_ROOT}/vendor/autoload.php';
  \$app = require '${PROJECT_ROOT}/bootstrap/app.php';
  \$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);
  \$pdo = \$app->make('db')->connection()->getPdo();
  \$driver = \$app->make('db')->getDriverName();
  if (\$driver === 'sqlite') {
    \$res = \$pdo->query(\"SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'\");
  } else {
    \$res = \$pdo->query(\"SHOW TABLES LIKE 'migrations'\");
  }
  echo \$res->rowCount() > 0 ? 'has_migrations' : 'no_migrations';
" 2>/dev/null || echo "unknown")

if [[ "$ST_FLAG" == "no_migrations" ]]; then
  fatal "Tabela 'migrations' não encontrada. Rode primeiro: php artisan migrate"
fi

# Detecta se migrations multi-tenant já foram rodadas (é idempotente rodar de novo)
MT_MIGRATED=$(php -r "
  require '${PROJECT_ROOT}/vendor/autoload.php';
  \$app = require '${PROJECT_ROOT}/bootstrap/app.php';
  \$pdo = \$app->make('db')->connection()->getPdo();
  \$driver = \$app->make('db')->getDriverName();
  if (\$driver === 'sqlite') {
    \$r = \$pdo->query(\"SELECT COUNT(*) FROM migrations WHERE migration LIKE '%add_is_platform_admin%'\");
  } else {
    \$r = \$pdo->query(\"SELECT COUNT(*) FROM migrations WHERE migration LIKE '%add_is_platform_admin%'\");
  }
  echo \$r->fetchColumn() > 0 ? 'yes' : 'no';
" 2>/dev/null || echo "unknown")

if [[ "$MT_MIGRATED" == "yes" ]]; then
  warn "Migrations multi-tenant já foram aplicadas anteriormente (re-execução idempotente)."
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 1 — Confirmação do usuário"
# ─────────────────────────────────────────────────────────────────────────────

if [[ ${#PLATFORM_ADMINS[@]} -eq 0 ]]; then
  warn "Nenhum --platform-admin fornecido."
  warn "Sem platform admin, NINGUÉM poderá acessar /platform nem os Backups após o upgrade."
  if [[ "$YES" != "true" && "$DRY_RUN" != "true" ]]; then
    read -rp "Continuar mesmo assim? [s/N] " confirm
    [[ "${confirm,,}" == "s" ]] || { info "Upgrade cancelado."; exit 0; }
  fi
else
  info "Platform admin(s): ${PLATFORM_ADMINS[*]}"
fi

[[ -n "$CLUB_ID" ]] && info "Clube alvo fixado: ID=$CLUB_ID"

if [[ "$YES" != "true" && "$DRY_RUN" != "true" ]]; then
  echo ""
  warn "ATENÇÃO: Esta operação modifica o banco de dados de produção."
  warn "Um backup automático será criado antes de qualquer alteração (a menos que --skip-backup)."
  echo ""
  read -rp "Confirmar upgrade? [s/N] " confirm
  [[ "${confirm,,}" == "s" ]] || { info "Upgrade cancelado."; exit 0; }
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 2 — Modo manutenção"
# ─────────────────────────────────────────────────────────────────────────────
if $DRY_RUN; then
  info "[DRY-RUN] Entraria em modo manutenção."
else
  artisan down --retry=60 --secret="upgrade-multitenant-$(date +%s)" 2>/dev/null || true
  ok "Aplicação em modo manutenção."
fi

# Garantir que a aplicação sobe ao sair (mesmo em caso de erro)
teardown() {
  local exit_code=$?
  if ! $DRY_RUN; then
    echo ""
    warn "Subindo a aplicação (teardown)..."
    artisan up 2>/dev/null || true
  fi
  if [[ $exit_code -ne 0 ]]; then
    echo ""
    fatal "Upgrade FALHOU com exit code $exit_code. Veja o log: $LOG_FILE"
  fi
}
trap teardown EXIT

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 3 — Backup de segurança"
# ─────────────────────────────────────────────────────────────────────────────
if $SKIP_BACKUP; then
  warn "Backup pulado (--skip-backup). Certifique-se de ter um backup recente!"
elif $DRY_RUN; then
  info "[DRY-RUN] Criaria backup: php artisan backup:run --only-db"
else
  info "Criando backup antes do upgrade (pode levar alguns minutos)..."
  if artisan backup:run --only-db; then
    ok "Backup do banco criado com sucesso."
  else
    warn "Backup falhou. Verifique a config de backup. Prosseguindo com cautela..."
    read -rp "Continuar mesmo sem backup? [s/N] " confirm_backup
    [[ "${confirm_backup,,}" == "s" ]] || { artisan up; exit 1; }
  fi

  # Backup adicional SQLite (se aplicável) — cópia direta do arquivo
  DB_CONNECTION=$(grep '^DB_CONNECTION=' "$PROJECT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
  if [[ "$DB_CONNECTION" == "sqlite" ]]; then
    DB_FILE=$(grep '^DB_DATABASE=' "$PROJECT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
    [[ "$DB_FILE" != /* ]] && DB_FILE="$PROJECT_ROOT/$DB_FILE"
    if [[ -f "$DB_FILE" ]]; then
      BKPFILE="${DB_FILE}.backup-antes-upgrade-$(date +%Y%m%d-%H%M%S).sqlite"
      cp "$DB_FILE" "$BKPFILE"
      ok "Cópia SQLite salva em: $BKPFILE"
    fi
  fi
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 4 — Atualização do código-fonte"
# ─────────────────────────────────────────────────────────────────────────────
if $SKIP_PULL; then
  info "Git pull pulado (--skip-pull)."
elif $DRY_RUN; then
  info "[DRY-RUN] Executaria: git pull"
else
  info "Atualizando código..."
  git pull || fatal "git pull falhou. Resolva conflitos antes de prosseguir."
  ok "Código atualizado."
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 5 — Dependências PHP (composer)"
# ─────────────────────────────────────────────────────────────────────────────
if $DRY_RUN; then
  info "[DRY-RUN] Executaria: composer install --no-dev --optimize-autoloader"
else
  info "Instalando dependências PHP..."
  composer install --no-dev --optimize-autoloader --no-interaction
  ok "Dependências PHP instaladas."
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 6 — Assets frontend (npm)"
# ─────────────────────────────────────────────────────────────────────────────
if $SKIP_NPM; then
  info "npm build pulado (--skip-npm)."
elif $DRY_RUN; then
  info "[DRY-RUN] Executaria: npm ci && npm run build"
else
  if command -v npm >/dev/null 2>&1; then
    info "Instalando dependências npm e buildando assets..."
    npm ci --prefer-offline 2>/dev/null || npm install --no-audit
    npm run build
    ok "Assets frontend gerados."
  else
    warn "npm não encontrado. Pulando build de frontend. Se usar assets via Vite, faça o build manualmente."
  fi
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 7 — Migrations do banco de dados"
# ─────────────────────────────────────────────────────────────────────────────
info "Verificando migrations pendentes..."
PENDING=$(artisan migrate:status --pending 2>/dev/null | grep -c "Pending" || echo "0")
info "Migrations pendentes: $PENDING"

if $DRY_RUN; then
  artisan migrate --pretend
  info "[DRY-RUN] Migrations acima seriam aplicadas."
else
  info "Rodando migrations..."
  artisan migrate --force
  ok "Migrations concluídas."
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 8 — Backfill de dados multi-tenant"
# ─────────────────────────────────────────────────────────────────────────────

# Monta o comando de upgrade com as opções recebidas
UPGRADE_CMD=(artisan tenant:upgrade-legacy)
for admin in "${PLATFORM_ADMINS[@]}"; do
  UPGRADE_CMD+=(--platform-admin="$admin")
done
[[ -n "$CLUB_ID" ]] && UPGRADE_CMD+=(--club="$CLUB_ID")
$DRY_RUN && UPGRADE_CMD+=(--dry-run)

info "Executando: ${UPGRADE_CMD[*]}"
"${UPGRADE_CMD[@]}" || fatal "tenant:upgrade-legacy falhou. Veja o log acima."
ok "Backfill de dados multi-tenant concluído."

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 9 — Verificação de integridade"
# ─────────────────────────────────────────────────────────────────────────────
info "Checando integridade do isolamento multi-tenant..."
if artisan tenant:check-integrity; then
  ok "Integridade multi-tenant: OK"
else
  echo ""
  fatal "Integridade falhou! NÃO suba a aplicação sem corrigir os problemas acima.
  Corrija via: php artisan tenant:upgrade-legacy
  Depois volte a rodar este script, ou rode manualmente:
    php artisan tenant:check-integrity
    php artisan up"
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 10 — Verificação extra de dados críticos"
# ─────────────────────────────────────────────────────────────────────────────

info "Contando registros por tabela para validação..."
php -r "
  require '${PROJECT_ROOT}/vendor/autoload.php';
  \$app = require '${PROJECT_ROOT}/bootstrap/app.php';
  \$db = \$app->make('db');

  \$tabelas = [
    'clubs', 'users', 'unidades', 'desbravadores', 'frequencias',
    'mensalidades', 'caixas', 'patrimonios', 'eventos', 'atas', 'atos',
    'attendance_columns', 'ranking_snapshots', 'invitations',
    'desbravador_especialidade', 'desbravador_requisito', 'desbravador_evento',
  ];

  echo str_pad('Tabela', 32) . str_pad('Linhas', 10) . PHP_EOL;
  echo str_repeat('-', 42) . PHP_EOL;
  foreach (\$tabelas as \$t) {
    try {
      \$count = \$db->table(\$t)->count();
      echo str_pad(\$t, 32) . str_pad(\$count, 10) . PHP_EOL;
    } catch (\Exception \$e) {
      echo str_pad(\$t, 32) . 'N/A' . PHP_EOL;
    }
  }
" 2>/dev/null || warn "Não foi possível exibir contagem de registros."

# Checa categorias de caixa fora do padrão (aviso, não erro)
info "Verificando categorias de caixa fora do padrão..."
php -r "
  require '${PROJECT_ROOT}/vendor/autoload.php';
  \$app = require '${PROJECT_ROOT}/bootstrap/app.php';
  \$db = \$app->make('db');
  \$categorias_ok = [
    'Mensalidade','Ofertas e Doações','Inscrições de Eventos',
    'Venda de Uniformes','Cantina','Campanha','Outros',
    'Materiais de Secretaria','Alimentação/Lanche','Transporte/Combustível',
    'Compra de Uniformes','Equipamentos','Taxas e Repasses','Devolução / Outros','Devolução',
  ];
  try {
    \$fora = \$db->table('caixas')
      ->whereNotIn('categoria', \$categorias_ok)
      ->selectRaw('tipo, categoria, COUNT(*) as total')
      ->groupBy('tipo', 'categoria')
      ->orderBy('total', 'desc')
      ->get();
    if (\$fora->isEmpty()) {
      echo 'OK — todas as categorias estão na lista padrão.' . PHP_EOL;
    } else {
      echo 'AVISO — categorias fora do padrão encontradas:' . PHP_EOL;
      foreach (\$fora as \$r) {
        echo '  ' . \$r->tipo . ' | ' . \$r->categoria . ' | ' . \$r->total . ' lançamento(s)' . PHP_EOL;
      }
      echo 'Esses lançamentos continuam visíveis, mas a categoria não aparecerá' . PHP_EOL;
      echo 'nos selects de novos lançamentos. Adicione-as ao Blade se necessário.' . PHP_EOL;
    }
  } catch (\Exception \$e) {
    echo 'Não foi possível verificar: ' . \$e->getMessage() . PHP_EOL;
  }
" 2>/dev/null

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 11 — Link de storage"
# ─────────────────────────────────────────────────────────────────────────────
if $DRY_RUN; then
  info "[DRY-RUN] Criaria link de storage."
else
  artisan storage:link --force 2>/dev/null || true
  ok "Link de storage criado/confirmado."
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 12 — Limpeza de caches"
# ─────────────────────────────────────────────────────────────────────────────
if $DRY_RUN; then
  info "[DRY-RUN] Limparia e regeneraria caches."
else
  artisan config:clear
  artisan route:clear
  artisan view:clear
  artisan config:cache
  artisan route:cache
  artisan view:cache
  ok "Caches regenerados."
fi

# ─────────────────────────────────────────────────────────────────────────────
section "PASSO 13 — Reiniciar worker de fila (se aplicável)"
# ─────────────────────────────────────────────────────────────────────────────
if command -v supervisorctl >/dev/null 2>&1; then
  info "Supervisor detectado. Reiniciando workers de fila..."
  supervisorctl restart all 2>/dev/null || warn "supervisorctl restart falhou (verifique permissões)."
elif command -v systemctl >/dev/null 2>&1 && systemctl is-active --quiet supervisor 2>/dev/null; then
  info "Supervisor via systemctl detectado. Reiniciando..."
  systemctl restart supervisor 2>/dev/null || warn "systemctl restart supervisor falhou."
else
  warn "Supervisor não detectado. Se usar queue:work, reinicie o worker manualmente."
  warn "  supervisorctl restart laravel-worker:*"
fi

# ─────────────────────────────────────────────────────────────────────────────
# O trap teardown() já chama artisan up — não precisamos chamar de novo.
# ─────────────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════════════════╗${RESET}"
echo -e "${GREEN}${BOLD}║  ✅  Upgrade concluído com sucesso!                      ║${RESET}"
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════════════════╝${RESET}"
echo ""
echo -e "  Log completo: ${CYAN}${LOG_FILE}${RESET}"
echo ""
echo -e "${BOLD}Validação manual obrigatória:${RESET}"
echo "  1. Login como platform admin → deve cair em /platform"
echo "  2. Login como master do clube → deve ver só dados do próprio clube"
echo "  3. Testar: desbravadores, caixa, frequência, atas e patrimônio"
echo "  4. Verificar que fotos de desbravadores carregam (storage/app/public/fotos/)"
echo "  5. Acessar /backups → deve mostrar os backups de sistema"
echo "  6. Acessar /backups/clube (como master) → backups isolados do clube"
echo ""
echo -e "  ${YELLOW}Rollback (se necessário):${RESET}"
echo "    php artisan down"
echo "    # Restaure o backup criado no Passo 3"
echo "    php artisan up"
echo ""
