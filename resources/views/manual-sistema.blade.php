<x-app-layout>
    <x-slot name="header">Manual do Sistema</x-slot>

    <div class="ui-page max-w-4xl mx-auto space-y-8 ui-animate-fade-up pb-20">

        {{-- Capa --}}
        <div class="ui-card overflow-hidden">
            <div class="p-8 sm:p-12 bg-gradient-to-br from-[#002F6C] to-blue-600 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white to-transparent"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-3xl bg-white/20 border border-white/20 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight mb-3">Manual do Sistema</h1>
                    <p class="text-blue-200 font-medium text-lg">Guia completo de uso do Desbravadores Manager — do primeiro acesso à operação diária de cada módulo.</p>
                </div>
            </div>

            {{-- Como usar este manual --}}
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800">
                <p class="text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed">
                    Este manual descreve o <strong>uso correto</strong> de cada parte do sistema na ordem em que o clube
                    normalmente a utiliza: primeiro a <strong>configuração inicial</strong>, depois os <strong>perfis de acesso</strong>
                    e, em seguida, cada módulo operacional. O que você enxerga no menu depende do seu cargo e das suas permissões —
                    consulte a seção <em>Perfis de Acesso e Permissões</em> em caso de dúvida sobre o que você pode fazer.
                </p>
            </div>

            {{-- Índice Rápido --}}
            <div class="p-6 sm:p-8 bg-slate-50/50 dark:bg-slate-900/50">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Índice</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach([
                        'Primeiros Passos e Configuração',
                        'Perfis de Acesso e Permissões',
                        'Módulo de Secretaria',
                        'Módulo Pedagógico',
                        'Frequência e Chamadas',
                        'Gestão de Eventos',
                        'Financeiro e Caixa',
                        'Ranking e Premiação',
                        'Relatórios e Documentos',
                        'Administração e Backups',
                    ] as $secao)
                    <a href="#{{ Str::slug($secao) }}" class="flex items-center gap-2.5 px-4 py-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 hover:border-[#002F6C]/40 dark:hover:border-blue-500/40 hover:bg-[#002F6C]/5 dark:hover:bg-blue-500/10 transition-all group">
                        <svg class="w-4 h-4 text-[#002F6C] dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-[#002F6C] dark:group-hover:text-blue-400 transition-colors">{{ $secao }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Seções do Manual --}}
        @php
        // Cada item pode conter:
        //  - titulo (string)            : nome do passo/recurso
        //  - desc   (string, opcional)  : explicação introdutória
        //  - passos (array, opcional)   : sequência ordenada do uso correto
        //  - nota   (string, opcional)  : alerta/regra importante exibida em destaque
        $secoes = [

            // 1. PRIMEIROS PASSOS
            ['id' => 'primeiros-passos-e-configuracao', 'titulo' => 'Primeiros Passos e Configuração', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'items' => [
                ['titulo' => 'Como se ganha acesso ao sistema', 'desc' => 'O sistema é fechado: ninguém se cadastra sozinho. O acesso acontece exclusivamente por convite enviado por e-mail por quem tem a permissão de Gestão de Acessos (Master ou Diretor).', 'passos' => [
                    'O administrador gera o convite informando o e-mail e o cargo da pessoa.',
                    'A pessoa recebe um e-mail com um link de cadastro válido por 7 dias.',
                    'Ao abrir o link, ela define nome e senha e confirma o e-mail para ativar a conta.',
                ], 'nota' => 'Convites expiram em 7 dias. Se vencer, use "Reenviar" na lista de convites para renovar o link por mais 7 dias. Um e-mail já cadastrado não pode receber novo convite — gerencie-o pela tela de Usuários.'],
                ['titulo' => 'Ordem correta da implantação', 'desc' => 'Em um clube novo, existe uma sequência obrigatória para que tudo funcione. O Master inicia o processo e o Diretor finaliza a base do clube.', 'passos' => [
                    'O Master convida o DIRETOR (enquanto o clube não existe, só é possível convidar o Diretor).',
                    'O Diretor acessa "Meu Clube" e preenche nome, cidade e associação — isso cria o clube e vincula os usuários a ele.',
                    'Com o clube criado, o Diretor/Master convida a equipe (Secretário, Tesoureiro, Conselheiros, Instrutores).',
                    'A equipe cadastra Unidades e, em seguida, os Desbravadores.',
                ], 'nota' => 'Só pode existir UM Diretor no sistema. O cadastro de um segundo diretor é bloqueado automaticamente.'],
                ['titulo' => 'Configurar os dados do clube', 'desc' => 'Em "Meu Clube" você define a identidade do clube (nome, cidade, associação) e envia o brasão/logo. Esses dados aparecem no topo do sistema, nos PDFs e nos documentos oficiais.', 'nota' => 'O logo aceita JPG, PNG ou GIF de até 2 MB. Use o botão de remover brasão para voltar ao logo padrão.'],
                ['titulo' => 'Confirmação de e-mail e senha', 'desc' => 'A confirmação de e-mail é obrigatória — sem ela o sistema não libera os módulos. Caso esqueça a senha, use "Esqueci minha senha" na tela de login; o link de redefinição também tem prazo de validade.'],
            ]],

            // 2. PERFIS E PERMISSÕES
            ['id' => 'perfis-de-acesso-e-permissoes', 'titulo' => 'Perfis de Acesso e Permissões', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'items' => [
                ['titulo' => 'O que cada cargo enxerga', 'desc' => 'O menu e as telas disponíveis mudam conforme o cargo. Cada cargo já vem com um conjunto de módulos liberados por padrão:', 'passos' => [
                    'Master: acesso total, incluindo gestão de acessos, backups e administração do sistema.',
                    'Diretor: Secretaria, Unidades, Pedagógico, Eventos, Financeiro e Relatórios.',
                    'Secretário: Secretaria, Unidades, Pedagógico, Eventos e Relatórios (sem Financeiro).',
                    'Tesoureiro: Financeiro, Eventos e Relatórios.',
                    'Conselheiro e Instrutor: módulo Pedagógico (classes, requisitos e especialidades).',
                ]],
                ['titulo' => 'Permissões extras por usuário', 'desc' => 'Além do padrão do cargo, o administrador pode conceder permissões adicionais marcando caixas no cadastro do usuário (ex.: liberar Relatórios para um Conselheiro). Assim você ajusta acessos sem mudar o cargo da pessoa.', 'nota' => 'A permissão de "Gestão de Acessos" (criar usuários e convites) só pode ser concedida pelo Master.'],
                ['titulo' => 'Regras específicas de Conselheiro e Instrutor', 'desc' => 'O conselheiro responsável por uma unidade pode ser vinculado a um usuário do sistema (campo opcional no cadastro da unidade) — é esse vínculo, e não apenas o nome digitado, que identifica o responsável de forma confiável. A gestão das unidades em si (criar, editar e excluir) exige a permissão de Unidades/Secretaria. O Instrutor é bloqueado de visualizar a listagem de Unidades, mantendo o foco no acompanhamento pedagógico.'],
                ['titulo' => 'Quem pode gerenciar colunas da chamada', 'desc' => 'A personalização dos critérios de frequência (colunas e pontuação) é restrita a Master, Diretor e Secretário, pois afeta o cálculo de pontos de todo o clube.'],
            ]],

            // 3. SECRETARIA
            ['id' => 'modulo-de-secretaria', 'titulo' => 'Módulo de Secretaria', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'items' => [
                ['titulo' => 'Cadastrar um desbravador', 'desc' => 'Acesse Desbravadores → Novo. O cadastro reúne dados pessoais, de contato e de saúde usados em fichas, carteirinhas e relatórios.', 'passos' => [
                    'Preencha os campos obrigatórios: nome, data de nascimento, sexo, CPF (único), unidade, e-mail, endereço, responsável, telefone do responsável e número do SUS.',
                    'Complete os campos opcionais: RG, telefone, classe atual, tipo sanguíneo, alergias, medicamentos contínuos e plano de saúde.',
                    'Envie a foto de perfil (opcional) — JPG, PNG ou WEBP de até 5 MB; o sistema redimensiona automaticamente.',
                    'Salve. O desbravador nasce com status "ativo".',
                ], 'nota' => 'A unidade selecionada precisa pertencer ao seu clube, e o CPF não pode estar repetido. Sem foto, o avatar exibe a inicial do nome.'],
                ['titulo' => 'Buscar, filtrar e organizar', 'desc' => 'A listagem permite buscar por nome, e-mail ou CPF e filtrar por unidade e por status (ativos, inativos ou todos). Os resultados são paginados de 10 em 10. Use o status "inativos" para desligar um membro sem perder o histórico, em vez de excluí-lo.'],
                ['titulo' => 'Avançar de classe', 'desc' => 'No perfil do desbravador, o botão "Avançar classe" promove o membro automaticamente para a próxima classe na ordem oficial. O sistema avisa caso ele não tenha classe definida ou já esteja na classe mais avançada.'],
                ['titulo' => 'Gerenciar unidades', 'desc' => 'Em Unidades você cadastra nome, grito de guerra e conselheiro responsável. A contagem de membros é calculada em tempo real e cada unidade pode ser incluída ou removida do ranking.', 'nota' => 'Uma unidade só pode ser excluída se não tiver nenhum desbravador vinculado. Reatribua os membros antes de excluir.'],
                ['titulo' => 'Atas e Atos oficiais', 'desc' => 'Registre as atas de reunião e os atos administrativos do clube. As atas podem ser impressas/exportadas diretamente para documentação e arquivo da secretaria.'],
                ['titulo' => 'Excluir um desbravador', 'desc' => 'A exclusão é definitiva e remove, em uma única transação, todos os dados vinculados ao membro (frequências, mensalidades, inscrições, especialidades). Prefira inativar quando quiser preservar o histórico.'],
                ['titulo' => 'Trilha de autoria', 'desc' => 'O sistema registra automaticamente quem cadastrou e quem fez a última atualização de cada desbravador (e também das movimentações de caixa). No perfil do desbravador essa informação aparece no rodapé — útil para auditoria e para saber a quem recorrer em caso de dúvida sobre um cadastro.'],
            ]],

            // 4. PEDAGÓGICO
            ['id' => 'modulo-pedagogico', 'titulo' => 'Módulo Pedagógico', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'items' => [
                ['titulo' => 'Acompanhar classes e requisitos', 'desc' => 'Em Pedagógico → Classes, cada classe lista os desbravadores que estão nela com o percentual de progresso. Abra a classe para ver o checklist de requisitos por aluno.', 'passos' => [
                    'Escolha a classe desejada na listagem (organizada pela ordem oficial).',
                    'Selecione o aluno para abrir o checklist completo de requisitos.',
                    'Marque um requisito para assiná-lo — o sistema registra quem assinou e a data; desmarque para remover a assinatura.',
                ], 'nota' => 'Cada marcação é salva na hora, sem recarregar a página. O percentual de progresso reflete os requisitos concluídos sobre o total da classe.'],
                ['titulo' => 'Gerenciar os requisitos de uma classe', 'desc' => 'Você pode adicionar, editar e excluir requisitos de cada classe (código, descrição e categoria), adaptando o checklist à realidade do clube. Excluir um requisito também remove as assinaturas associadas a ele.'],
                ['titulo' => 'Cadastrar e organizar especialidades', 'desc' => 'Em Especialidades, cadastre cada especialidade com nome e área/categoria. A combinação nome + área é única, e o sistema aplica cores temáticas por área para identificação visual.', 'passos' => [
                    'Crie a especialidade informando nome, área e (opcional) cor de fundo.',
                    'Adicione os requisitos oficiais — eles ficam numerados em ordem.',
                    'Use os filtros (busca, área, somente avançadas, com/sem investidos) para localizar rapidamente.',
                ], 'nota' => 'Cada alteração relevante fica registrada no histórico/auditoria da especialidade.'],
                ['titulo' => 'Investir um desbravador em especialidades', 'desc' => 'No perfil do membro, acesse "Especialidades", selecione uma ou mais e informe a data de conclusão (obrigatória). As especialidades já investidas não são duplicadas ao salvar novamente.'],
            ]],

            // 5. FREQUÊNCIA
            ['id' => 'frequencia-e-chamadas', 'titulo' => 'Frequência e Chamadas', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'items' => [
                ['titulo' => 'Realizar uma chamada', 'desc' => 'Vá em Frequência → Nova Chamada para registrar a presença de uma reunião.', 'passos' => [
                    'Selecione a data da reunião.',
                    'Marque, para cada desbravador, os critérios atingidos (Presente, Pontual, Bíblia, Uniforme ou as colunas personalizadas do clube).',
                    'Confirme as unidades que estão sendo lançadas e salve a chamada.',
                ], 'nota' => 'Os desbravadores de uma unidade lançada que não forem marcados são registrados automaticamente como ausentes. Relançar a chamada da mesma data sobrescreve os registros daquele dia.'],
                ['titulo' => 'Critérios e pontuação', 'desc' => 'Por padrão a chamada tem quatro critérios pontuados: Presente e Uniforme valem 10 pontos; Pontual e Bíblia valem 5. Esses pontos alimentam o ranking anual do clube.'],
                ['titulo' => 'Personalizar as colunas da chamada', 'desc' => 'Master, Diretor e Secretário podem acessar "Gerenciar Colunas" para criar critérios próprios com pontuação de 1 a 10 e ajustar os existentes.', 'nota' => 'Colunas fixas não podem ser excluídas, e qualquer coluna que já tenha sido usada em alguma chamada fica protegida contra exclusão para preservar o histórico de pontos.'],
                ['titulo' => 'Corrigir ou refazer uma chamada', 'desc' => 'No histórico mensal você pode excluir a chamada de uma data específica para relançá-la corretamente. O histórico colore o percentual de frequência: verde para ≥75%, amarelo para ≥50% e vermelho abaixo de 50%, facilitando identificar membros em risco.'],
            ]],

            // 6. EVENTOS
            ['id' => 'gestao-de-eventos', 'titulo' => 'Gestão de Eventos', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'items' => [
                ['titulo' => 'Criar um evento', 'desc' => 'Em Eventos → Novo Evento, informe nome, local, datas de início e fim, valor e descrição. A data de fim precisa ser igual ou posterior à de início. Eventos com valor 0 são marcados como "Gratuito".', 'nota' => 'A criação e edição de eventos exige permissão de Secretaria.'],
                ['titulo' => 'Inscrever desbravadores', 'desc' => 'Na tela do evento você inscreve membros individualmente ou em lote (vários de uma vez). O sistema ignora automaticamente quem já está inscrito, evitando duplicidade.'],
                ['titulo' => 'Controlar pagamento e autorização', 'desc' => 'Cada inscrito tem os controles "Pago" e "Autorização entregue", alterados com um clique (sem recarregar a página).', 'passos' => [
                    'Clique no status de pagamento para alternar entre Pendente e Pago.',
                    'Para eventos com valor, marcar "Pago" lança automaticamente uma ENTRADA no caixa.',
                    'Desmarcar "Pago" gera um estorno (SAÍDA) correspondente no caixa.',
                ], 'nota' => 'A alteração do status de pagamento exige permissão Financeira, pois movimenta o caixa do clube. Remover um inscrito que já estava pago também gera o estorno correspondente no caixa e, por isso, exige a mesma permissão.'],
                ['titulo' => 'Gerar a autorização parental', 'desc' => 'Para cada inscrito é possível gerar a autorização parental em PDF, pronta para impressão e assinatura dos responsáveis.'],
                ['titulo' => 'Excluir um evento', 'desc' => 'Um evento só pode ser excluído depois que todas as inscrições forem removidas. Isso evita perda acidental de dados de participação e pagamento.'],
            ]],

            // 7. FINANCEIRO
            ['id' => 'financeiro-e-caixa', 'titulo' => 'Financeiro e Caixa', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'items' => [
                ['titulo' => 'Lançar movimentações no caixa', 'desc' => 'Em Financeiro → Caixa, registre entradas e saídas informando descrição, valor, tipo, data e categoria (opcional). O saldo atual é recalculado automaticamente a cada lançamento.', 'nota' => 'Vários lançamentos são automáticos: pagamento de mensalidade e de evento entram no caixa sem digitação manual.'],
                ['titulo' => 'Gerar mensalidades em massa', 'desc' => 'Em Mensalidades, use "Gerar Carnê do Mês" para criar a cobrança de todos os desbravadores ativos de uma só vez.', 'passos' => [
                    'Informe o mês, o ano e o valor da mensalidade.',
                    'Confirme — o sistema cria as cobranças pendentes apenas para quem ainda não tem a mensalidade daquele período.',
                ], 'nota' => 'A geração nunca duplica cobranças: membros que já possuem a mensalidade do período são ignorados.'],
                ['titulo' => 'Receber pagamentos', 'desc' => 'Clique em "Receber" na mensalidade desejada. O sistema marca como paga, registra a data e lança automaticamente a entrada correspondente no caixa. Uma mensalidade já paga não pode ser recebida novamente.'],
                ['titulo' => 'Acompanhar a inadimplência', 'desc' => 'O painel de mensalidades mostra valores recebidos e pendentes do período, além de um resumo de inadimplência por unidade — útil para cobrança direcionada. Use os filtros de mês/ano para navegar entre competências.'],
                ['titulo' => 'Gerenciar o patrimônio', 'desc' => 'Em Patrimônio, cadastre os bens do clube com quantidade, valor estimado, estado de conservação, local de armazenamento e observações.', 'passos' => [
                    'Cadastre o item e seu estado de conservação inicial.',
                    'Registre manutenções e mudanças de estado ao longo do tempo.',
                    'Acompanhe as métricas: total de itens, valor estimado e quantidade em bom x mau estado.',
                ], 'nota' => 'Sempre que o estado de conservação muda, o sistema cria automaticamente um registro no histórico de manutenções do item.'],
            ]],

            // 8. RANKING
            ['id' => 'ranking-e-premiacao', 'titulo' => 'Ranking e Premiação', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'items' => [
                ['titulo' => 'Como o ranking é calculado', 'desc' => 'O ranking soma os pontos das chamadas do ano corrente. Existem duas visões: o ranking entre Unidades e o ranking Individual (desbravadores ativos). O pódio destaca os três primeiros colocados.', 'nota' => 'Somente unidades marcadas como "no ranking" participam. Use o botão de incluir/remover do ranking na tela de Unidades para controlar quem concorre.'],
                ['titulo' => 'Salvar o ranking do ano (snapshot)', 'desc' => 'Ao fechar um ciclo, salve um snapshot do ranking daquele ano. Ele congela as posições e pontuações para consulta histórica, mesmo que os dados mudem depois. Cada ano/visão guarda um único snapshot, que pode ser regravado se necessário.'],
                ['titulo' => 'Consultar anos anteriores', 'desc' => 'Use a opção de ver snapshot para abrir o ranking de anos passados já salvos, comparando a evolução das unidades e dos desbravadores ao longo do tempo.'],
            ]],

            // 9. RELATÓRIOS
            ['id' => 'relatorios-e-documentos', 'titulo' => 'Relatórios e Documentos', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'items' => [
                ['titulo' => 'Documentos individuais', 'desc' => 'A partir do membro você gera, em PDF, a Autorização de Evento, a Carteirinha do Clube e a Ficha Médica — todos formatados para impressão e com a identidade do clube.'],
                ['titulo' => 'Relatórios da operação', 'desc' => 'O hub de Relatórios reúne listagens completas em PDF para a rotina do clube:', 'passos' => [
                    'Membros: lista de desbravadores, fichas completas, fichas médicas e contatos de emergência.',
                    'Acompanhamento: frequência mensal, aniversariantes do mês e desempenho por unidade.',
                    'Financeiro: inadimplência, fluxo de caixa e inventário patrimonial.',
                    'Premiação: ranking de unidades e ranking individual.',
                ]],
                ['titulo' => 'Filtrar antes de gerar', 'desc' => 'Use "Gerar Personalizado" para refinar o relatório por status (ativos, inativos ou todos), unidade, período de datas e mês/ano, conforme o tipo escolhido. O PDF traz no cabeçalho os filtros aplicados, o nome do clube e quem emitiu.'],
                ['titulo' => 'Restrições de acesso', 'desc' => 'Os relatórios financeiro e patrimonial exigem permissão Financeira. Os demais relatórios seguem a permissão de Relatórios do seu cargo.'],
            ]],

            // 10. ADMIN E BACKUPS
            ['id' => 'administracao-e-backups', 'titulo' => 'Administração e Backups', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'items' => [
                ['titulo' => 'Gerir usuários e convites (Gestão de Acessos)', 'desc' => 'Quem tem essa permissão cria e edita usuários, define o cargo e marca as permissões extras, além de emitir, reenviar e cancelar convites.', 'nota' => 'Apenas o Master pode criar/gerir outros Masters e conceder a permissão de Gestão de Acessos. Você não consegue excluir o próprio usuário.'],
                ['titulo' => 'Fazer backup (somente Master)', 'desc' => 'Em Backups, o botão de gerar backup cria uma cópia completa e a sincroniza com a nuvem (R2). A lista mostra os backups disponíveis no armazenamento local e na nuvem, com tamanho e data.', 'passos' => [
                    'Gere o backup manualmente quando fizer mudanças importantes.',
                    'Baixe o arquivo .zip para guardar uma cópia externa, se desejar.',
                    'Use "Importar" para subir um .zip de backup gerado pelo próprio sistema.',
                ], 'nota' => 'Localmente no Windows o backup do banco pode falhar por permissão do servidor — em produção (Linux) funciona normalmente. Nesse caso, gere pelo terminal.'],
                ['titulo' => 'Restaurar um backup', 'desc' => 'A restauração substitui os dados atuais pelos dados do backup escolhido. É uma operação destrutiva e deve ser feita com cautela.', 'passos' => [
                    'O sistema entra em modo de manutenção e cria um snapshot de emergência do estado atual.',
                    'Os dados são substituídos pelos do backup e os arquivos (fotos, logos) são restaurados.',
                    'Sua sessão é encerrada — faça login novamente usando as credenciais da época daquele backup.',
                ], 'nota' => 'Se algo falhar durante a restauração, o sistema tenta recuperar automaticamente o estado anterior a partir do snapshot de emergência.'],
                ['titulo' => 'Monitoramento e notificações', 'desc' => 'Eventos críticos — conclusão e falhas de backup, problemas em filas e jobs — disparam notificações automáticas (Telegram) para a administração. O endereço /health permite que monitores externos verifiquem a disponibilidade do sistema e do banco.'],
            ]],
        ];
        @endphp

        @foreach ($secoes as $secao)
        <div id="{{ $secao['id'] }}" class="ui-card overflow-hidden scroll-mt-24">
            <div class="flex items-center gap-4 px-6 sm:px-8 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <div class="w-10 h-10 rounded-xl bg-[#002F6C]/10 dark:bg-blue-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#002F6C] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $secao['icon'] }}"/></svg>
                </div>
                <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">{{ $secao['titulo'] }}</h2>
            </div>
            <div class="p-6 sm:p-8 space-y-6">
                @foreach ($secao['items'] as $item)
                <div class="flex gap-4">
                    <div class="w-1.5 shrink-0 mt-1.5">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#D9222A]"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-black text-slate-800 dark:text-white mb-1 uppercase tracking-tight">{{ $item['titulo'] }}</h3>

                        @if (! empty($item['desc']))
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                        @endif

                        @if (! empty($item['passos']))
                        <ol class="mt-3 space-y-2">
                            @foreach ($item['passos'] as $i => $passo)
                            <li class="flex gap-3 items-start">
                                <span class="shrink-0 w-5 h-5 rounded-full bg-[#002F6C]/10 dark:bg-blue-500/20 text-[#002F6C] dark:text-blue-400 text-[11px] font-black flex items-center justify-center mt-0.5">{{ $i + 1 }}</span>
                                <span class="text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed">{{ $passo }}</span>
                            </li>
                            @endforeach
                        </ol>
                        @endif

                        @if (! empty($item['nota']))
                        <div class="mt-3 flex gap-2.5 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/70 dark:border-amber-500/20">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-[13px] font-semibold text-amber-800 dark:text-amber-300 leading-relaxed">{{ $item['nota'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>


    {{-- BOTÃO VOLTAR AO TOPO --}}
    {{-- x-teleport move o botão para o <body>, fora do <main overflow-y-auto> --}}
    {{-- Sem isso o position:fixed fica preso no container de scroll --}}
    <template
        x-data="{ visible: false }"
        x-init="
            const el = document.getElementById('app-content');
            el.addEventListener('scroll', () => { visible = el.scrollTop > 250; }, { passive: true });
        "
        x-teleport="body">
        <button
            x-show="visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            @click="document.getElementById('app-content').scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-24 sm:bottom-8 right-5 sm:right-8 z-[9999] w-12 h-12 bg-[#002F6C] hover:bg-[#001D42] dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-full shadow-xl shadow-blue-900/30 flex items-center justify-center active:scale-90 transition-colors"
            aria-label="Voltar ao topo"
            title="Voltar ao topo"
            style="display:none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </template>

</x-app-layout>
