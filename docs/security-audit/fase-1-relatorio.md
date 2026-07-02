# Fase 1 — Isolamento de Tenant (`club_id`)

**Escopo:** varredura de `withoutGlobalScope(s)`, queries cruas `DB::`, Eloquent em Blade,
relationships, IDOR via `{id}` de rota, jobs/comandos em background sem `ClubContext`, e
seeders/factories que possam mascarar bugs de isolamento.

**Veredito geral:** o isolamento por tenant está **maduro e bem defendido**. Não foi
encontrado nenhum vazamento cross-tenant explorável via web (severidade Crítica/Alta).
Os padrões de risco (`withoutGlobalScopes`, `DB::table`) aparecem quase sempre com filtro
`club_id` **explícito** e comentário justificando. Os achados abaixo são de
**defesa-em-profundidade / robustez**, não brechas ativas.

## Achados

| Severidade | Arquivo:Linha | Vulnerabilidade | Cenário de Exploração | Correção Sugerida |
|---|---|---|---|---|
| Média | (arquitetural) `app/Policies/` vazio; `app/Providers/AppServiceProvider.php:98-117` | **Ausência de camada de Policy.** A checagem de posse de recurso (registro pertence ao clube) depende **inteiramente** do global scope `ClubScope` + route-model binding. Não há segunda camada de autorização por registro. | Se um novo Model com dados de clube for criado **sem** o trait `BelongsToTenant` (fácil de esquecer — 13 dos 27 models já não o têm), ou se um dev usar `withoutGlobalScopes()` sem re-filtrar, o vazamento é **silencioso** e não é pego por nenhuma verificação secundária. | Manter/expandir o teste automatizado que garante que todo model de dados de clube registra `ClubScope` (o comando `tenant:check-integrity` já cobre dados órfãos em runtime — falta um teste estático de "model tem o trait"). Considerar Policies para operações destrutivas críticas. |
| Baixa | `app/Http/Controllers/EventoController.php:185` (`gerarAutorizacao`) | A autorização em PDF é gerada para qualquer par `{evento}`+`{desbravador}` do **mesmo clube**, sem verificar que o desbravador está **inscrito** naquele evento. | Não é cross-tenant (ambos os binds passam pelo `ClubScope` → 404 para outro clube). Impacto: um usuário com permissão `eventos` gera autorização de um desbravador não inscrito. Consistência de negócio, não vazamento. | Antes de gerar, validar `abort_unless($evento->desbravadores()->where('desbravador_id',$desbravador->id)->exists(), 404)`. |
| Baixa (informativo) | `app/Models/Scopes/ClubScope.php:26-29` | **Fail-open sem autenticação.** Sem usuário autenticado e sem `actAs()`, o scope **não filtra** (retorna todas as linhas). É intencional (seeders/console/factories), mas é o ponto mais sensível do desenho. | Qualquer código que consulte um model de tenant em contexto **HTTP não autenticado** enxergaria todos os clubes. Verifiquei as rotas públicas (`/`, `/health`, `/privacidade`, `/termos`, `/register-invite`) — **nenhuma** consulta model de tenant, então não há exposição atual. | Manter a disciplina: rotas públicas nunca devem consultar models de tenant. Documentado em CLAUDE.md; sem ação imediata. |
| Baixa (informativo) | `app/Console/Commands/LgpdAnonimizarDesligados.php:30-36` | Sem a opção `--club-id`, o comando roda **globalmente** (sem `actAs`), anonimizando desbravadores de **todos os clubes**. | Comando **apenas CLI** (não roteado na web) — não explorável por usuário de clube. É operação de plataforma por design. Risco: execução acidental agregada. | Sem ação de segurança. Opcional: exigir `--club-id` ou `--all` explícito para evitar disparo global acidental. |

## O que foi verificado e está correto (não é achado)

- **`ClubExportService` / `ClubImportService` / `ClubRestoreService` / `ClubLifecycleService`**: usam
  `withoutGlobalScopes()` e `DB::table()` cruas, mas **sempre** com `where('club_id', $clubId)`
  explícito ou `whereIn(parent_ids)` derivado de ids já filtrados por clube. Justificado em comentário.
- **`GerarRelatorioPDF` (job em fila)**: roda sem sessão → o `ClubScope` não filtraria. O job recebe
  `clubId` no construtor e **amarra `where('club_id', $clube->id)` explicitamente** nas queries de
  ficha/financeiro (`app/Jobs/GerarRelatorioPDF.php:156,199`). Faz `loginUsingId` do solicitante e
  usa `withoutGlobalScopes()->find()` só para localizar o próprio registro do relatório pelo id que
  ele mesmo criou. Correto.
- **IDOR via route-model binding**: `MensalidadeController::pagar` (`findOrFail` via `doClube`),
  `PatrimonioController::destroyManutencao` (checa `patrimonio_id`), `ConsentimentoPrivacidadeController::revogar`
  (checa `desbravador_id`), `InvitationController` (`garantirConvitePertenceAoContexto` — guard
  explícito porque `Invitation` **não** tem global scope), `UsuarioController::ensureCanManageTargetUser`
  (checa `club_id` do alvo). Todos os models de tenant resolvem binding pelo `ClubScope` → 404 cross-tenant.
- **`EventoController::inscrever`/`inscreverEmLote`**: valida `Rule::exists(...)->where('club_id', ...)`
  e filtra ids pelo scope antes de `attach` — não confia no input do cliente.
- **Nenhuma query Eloquent/`DB::` dentro de `.blade.php`** (grep em `resources/views` = 0 ocorrências).
- **`DashboardController`**: `DB::raw` usado só para agregação (`count`/`sum`), dentro de query
  `Frequencia` com `whereHas('desbravador.unidade', club_id)`; cache keyed por `club_id`.
- **`ClubBackupController` + `ClubBackupService::assertBelongsToClub`**: valida prefixo de path por
  slug do clube, bloqueia `../`, exige `.zip`, valida disco. Sem cruzamento de clube.

## Conclusão da Fase 1

Sem correções bloqueantes de tenant. Recomendação principal (Média): blindar o desenho contra
regressão futura — um teste que falhe se um model de dados de clube não registrar `ClubScope`,
já que hoje toda a segurança de isolamento repousa nesse único mecanismo.
