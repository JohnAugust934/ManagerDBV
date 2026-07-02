# Fase 2 — Autenticação e Autorização

**Escopo:** rotas sem auth/tenant, Policies ausentes/incompletas, mass assignment
(`$fillable`/`$guarded`, com foco em `club_id`/`role`/`is_*`/consentimento), e campos
sensíveis em `$appends`/serialização.

**Veredito geral:** autenticação e autorização **bem estruturadas**. Login com rate
limiting, registro deriva privilégios do convite (não do input), sem sinks de
mass-assignment cru, sem vazamento por serialização. Os achados são **latentes/defesa-em-profundidade**.

## Achados

| Severidade | Arquivo:Linha | Vulnerabilidade | Cenário de Exploração | Correção Sugerida |
|---|---|---|---|---|
| Média | `app/Models/User.php:13-24` | **Colunas de privilégio em `$fillable`.** `role`, `is_master`, `is_platform_admin`, `club_id` e `extra_permissions` são mass-assignable. | **Sem exploit ativo hoje** — todas as escritas usam arrays validados (`UsuarioController`) ou valores do convite (`RegisteredUserController`), e `ProfileUpdateRequest` só permite `name`/`email`. O risco é **latente**: qualquer futuro `User::create($request->all())`/`->update($request->...)` viraria escalada instantânea a `is_platform_admin=true` (comprometimento total cross-tenant). | Remover as colunas de privilégio de `$fillable` e atribuí-las **explicitamente** (`$user->is_platform_admin = ...`) nos poucos pontos que as definem. Elimina a classe inteira de bug. |
| Baixa | `app/Http/Controllers/UsuarioController.php:220-231`, `:113-164` | Um usuário com a permissão `gestao_acessos` (ex.: secretário a quem o master concedeu) pode atribuir/**auto-atribuir** cargos não-master (ex.: `diretor`), ganhando acesso amplo a módulos (financeiro, etc.). | Escalada **intra-tenant** limitada: bloqueada de virar `master` ou conceder `gestao_acessos` (`allowedAssignableRoles` + `sanitizeExtraPermissions`). Mas quem tem gestão de acessos pode se promover a `diretor` e obter `financeiro`/`secretaria`/etc. | Aceitável dado que `gestao_acessos` é poderosa e só o master concede. Opcional: impedir que o usuário edite o **próprio** `role`/`extra_permissions` (autogestão de privilégio). |
| Baixa | `app/Http/Controllers/Auth/PasswordResetLinkController.php:26` | `POST /forgot-password` sem `throttle` de rota (ao contrário de `passkeys.login`, `verification.*` que têm `throttle:6,1`). | Spam de e-mails de reset / enumeração leve por muitos endereços distintos. O broker do Laravel limita o **reenvio por usuário** (~60s), mas não há limite por IP. | Adicionar `->middleware('throttle:6,1')` na rota (detalhado também na Fase 4). |

## O que foi verificado e está correto (não é achado)

- **Rate limiting de login:** `app/Http/Requests/Auth/LoginRequest.php:60-84` — 5 tentativas por
  `email|IP`, dispara `Lockout`. Passkey login (`throttle:6,1`), verificação de e-mail e reenvio
  (`throttle:6,1`), verify signed. Corretos.
- **Registro por convite:** `RegisteredUserController::store` deriva `role`, `club_id` e
  `is_platform_admin` do **convite** (fonte da verdade), nunca do request; o request só fornece
  `name`/`password`/`token`. Transação com `lockForUpdate` evita corrida de reutilização de convite.
  Sem escalada de privilégio.
- **Sem sinks de mass-assignment cru:** grep por `::create($request->...)`, `->update($request->all())`,
  `->fill($request->all())` em controllers web = **nenhuma ocorrência**. Todo CRUD usa
  `$request->validated()` (`DesbravadorController`, `Evento`, etc.).
- **`StoreDesbravadorRequest`/`UpdateDesbravadorRequest`:** não permitem `club_id` nem `role`;
  `unidade_id` validado com regra `UnidadePertenceAoClube`; CPF único por clube via `cpf_hash`.
- **Serialização:** **nenhum** `$appends` em qualquer model (grep = 0). `User::$hidden` esconde
  `password` e `remember_token`. Não há `routes/api.php` nem API Resources — sem superfície de
  vazamento por `toArray()`/`toJson()`.
- **Cadeia de permissões consistente** (`User::temPermissao`/`getPermissoesPadrao`/`podeGerenciarMasters`):
  só `master`/`platform_admin` têm acesso total; `gestao_acessos` não é padrão de nenhum cargo e só
  o master o concede; `sanitizeExtraPermissions` faz allowlist e bloqueia auto-concessão de
  `gestao_acessos`.
- **Todas as rotas de aplicação** estão sob `auth`+`verified`+termos+clube-ativo; rotas públicas
  são estáticas/onboarding; grupos protegidos por gates de módulo/`platform-admin`.

## Referência cruzada

- **Ausência de camada de Policy** (autorização de posse depende só do `ClubScope`) — registrada na
  **Fase 1** como severidade Média; permanece a recomendação arquitetural mais relevante.

## Conclusão da Fase 2

Sem brecha ativa de autenticação/autorização. Ação de maior valor (Média): tirar as colunas de
privilégio de `User::$fillable`, fechando de forma definitiva o risco latente de escalada por
mass assignment.
