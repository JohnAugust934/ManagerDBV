# Fase 3 — Consentimento LGPD e Geração de PDF

**Escopo:** geração de PDF (individual e lote) respeitando o tenant; proteção do histórico de
revogação contra alteração retroativa e contra `revogado_por` vindo do cliente; download de PDF/
arquivos validando propriedade antes de servir.

**Veredito geral:** o fluxo de consentimento (aceite/revogação) e a geração de PDF **respeitam o
tenant e a integridade do histórico**. Há, porém, **um problema real de armazenamento**: a via
física assinada (documento de menor) é gravada em disco **público**.

## Achados

| Severidade | Arquivo:Linha | Vulnerabilidade | Cenário de Exploração | Correção Sugerida |
|---|---|---|---|---|
| **Alta** | `app/Http/Controllers/ConsentimentoPrivacidadeController.php:113` | **Documento sensível de menor em disco público.** A via física assinada do termo de privacidade é salva com `->store("consentimentos/{id}", 'public')` — fica sob o symlink `public/storage/`, **servida por qualquer requisição HTTP sem autenticação nem verificação de tenant**. | Arquivo (assinatura do responsável, dados do menor) acessível em `/storage/consentimentos/{id}/{arquivo}` sem login. Fatores mitigantes: o nome do arquivo é um hash aleatório de 40 chars do Laravel e a UI **não emite o link** (a `index` só mostra a data). Ainda assim, não há **nenhum** controle de acesso — o vazamento depende só do sigilo da URL, e o arquivo entra em qualquer listagem/backup do disco público. Controle inadequado para dado de criança sob LGPD. | Gravar no disco **privado** (`local`) e servir por rota autenticada sob `can:secretaria` com route-model binding (tenant-scoped), como já é feito para os PDFs em lote em `RelatorioController::download`. |
| Média | `app/Http/Controllers/DesbravadorController.php:239-265`, `:232` | Fotos de desbravadores (menores) gravadas no disco **público** (`Storage::disk('public')`). | Foto de criança acessível sem autenticação por quem souber a URL. Diferente do consentimento, a foto **precisa** renderizar em `<img>`, então tornar 100% privada exige servir via rota/URL assinada. Severidade menor pela finalidade e pelo nome de arquivo não-enumerável. | Avaliar servir fotos por URL assinada temporária (`Storage::temporaryUrl`) ou rota autenticada; no mínimo, documentar a decisão de exposição. Logos do clube (`ClubController`) são menos sensíveis — aceitável. |
| Baixa (informativo) | `app/Models/ConsentimentoPrivacidade.php:20-33` | `revogado_por`, `aceito_ip`, `aceito_em` estão em `$fillable` (mass-assignable). | **Sem exploit ativo:** todos os caminhos de escrita (`aceitar`, `revogar`, `viaFisicaRecebida`) montam o array explicitamente com valores do **servidor** (`$request->user()->name`, `$request->ip()`, `now()`). O cliente nunca controla esses campos. | Defesa-em-profundidade: manter o cuidado de nunca alimentar esses campos com input; opcionalmente removê-los de `$fillable`. |

## O que foi verificado e está correto (não é achado)

- **Isolamento de tenant nos PDFs:** `autorizacao`/`carteirinha`/`fichaMedica`/`termoPrivacidade`
  (individuais) usam route-model binding de `Desbravador` (tenant-scoped → 404 cross-tenant);
  `termoPrivacidadeLote` usa `Desbravador::whereIn($ids)` filtrado pelo `ClubScope` (ids de outro
  clube são descartados); `financeiro`/`patrimonio` via `ClubScope`. Nenhum PDF cruza clubes.
- **Download em lote protegido:** `GerarRelatorioPDF` grava no disco **privado** (`local`) em
  `relatorios/{clubId}/...` e `RelatorioController::download(RelatorioGerado $relatorio)` serve via
  route-model binding tenant-scoped + `isPronto()`/`expires_at`. Correto.
- **Integridade da revogação:** rota sob `can:secretaria`; `revogar` valida
  `$consentimento->desbravador_id === $desbravador->id` (404) e `estaAtivo()` (422); grava
  `revogado_por = $request->user()->name` (usuário **autenticado**, não do cliente); **não apaga o
  histórico** (cria o carimbo de revogação no registro existente). Não há rota para editar
  `aceito_em`/reescrever registros passados — sem alteração retroativa.
- **Upload da via física:** valida `mimes:pdf,jpg,jpeg,png` + `max:5120`. (O problema é o **disco**,
  não a validação.)
- **Fotos re-processadas por GD:** `processarFoto` decodifica/re-encoda a imagem (não armazena o
  arquivo bruto do upload), o que remove payloads embutidos — bom controle contra upload malicioso.
- **Export LGPD** (`exportarDadosLgpd`): sob `can:secretaria`, `Desbravador` tenant-scoped; devolve
  CPF/RG descriptografados em JSON — intencional (Art. 18, portabilidade) e autorizado.

## Conclusão da Fase 3

Uma correção recomendada antes de deploy: **mover a via física de consentimento para o disco
privado** e servi-la por rota autenticada (severidade Alta). As fotos em disco público (Média)
merecem uma decisão explícita. O fluxo de consentimento em si (autorização, integridade do
histórico, isolamento) está correto.
