# Roteiro De Validacao Operacional

## Objetivo
Confirmar o funcionamento dos fluxos mais criticos do sistema em ambiente real antes da liberacao para uso continuo.

## Secretaria
1. Cadastrar um novo desbravador completo.
2. Editar o cadastro e depois inativar esse desbravador.
3. Tentar excluir um desbravador e confirmar que o aviso recomenda inativar antes da exclusao.
4. Criar, editar e visualizar uma ata.
5. Criar e visualizar um ato administrativo.

## Financeiro
1. Gerar mensalidades do mes atual.
2. Pagar uma mensalidade e conferir o lancamento automatico no caixa.
3. Cadastrar um item de patrimonio.
4. Tentar acessar telas financeiras com um usuario sem permissao e validar o bloqueio.

## Eventos E Relatorios
1. Criar um evento.
2. Inscrever um desbravador, marcar pagamento e gerar autorizacao.
3. Gerar os relatorios de lista, ficha completa, frequencia e financeiro.
4. Atualizar a aba do PDF e confirmar que nao ocorre erro de metodo GET/POST.

## Backup E Recuperacao
1. Gerar backup pela tela.
2. Confirmar recebimento de notificacao administrativa no Telegram.
3. Importar um backup ZIP valido.
4. Executar uma restauracao em ambiente seguro de homologacao.

## Observabilidade
1. Confirmar se o Telegram recebe:
   backup executado
   falha simulada de job
   erro critico do sistema
2. Confirmar se a fila database esta sendo monitorada.
3. Conferir se o comando anual `ranking:snapshot` gera registros em `ranking_snapshots`.
