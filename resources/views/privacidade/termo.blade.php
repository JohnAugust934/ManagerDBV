{{--
    Texto único do Termo de Privacidade (LGPD), versionado por $versao.
    Fonte da verdade tanto para o registro digital (snapshot) quanto para o PDF
    de assinatura física — nunca duplicar este texto em outro lugar.

    Variáveis: $versao (string). $clubeNome (opcional).
--}}
@php($clubeNome = $clubeNome ?? config('app.name'))

<h3>Termo de Consentimento para Tratamento de Dados Pessoais</h3>
<p><strong>Versão {{ $versao }}</strong></p>

<p>
    Em conformidade com a Lei nº 13.709/2018 (Lei Geral de Proteção de Dados
    Pessoais &ndash; LGPD), o(a) responsável legal pelo(a) desbravador(a) declara
    estar ciente e <strong>consentir livremente</strong> com o tratamento dos dados
    pessoais do(a) menor pelo clube <strong>{{ $clubeNome }}</strong>, para as
    finalidades descritas a seguir.
</p>

<p><strong>1. Dados tratados.</strong> Dados cadastrais (nome, data de nascimento,
    documento, contato), dados do responsável, e dados sensíveis de saúde
    (tipo sanguíneo, alergias, medicamentos, plano de saúde) quando informados,
    tratados exclusivamente para fins de segurança e cuidado do(a) menor.</p>

<p><strong>2. Finalidades.</strong> Gestão da participação no clube, controle de
    frequência e progresso, organização de eventos, emissão de documentos e
    contato com o responsável. Os dados não são vendidos nem compartilhados para
    fins comerciais.</p>

<p><strong>3. Uso de imagem.</strong> A autorização de uso de imagem é registrada
    separadamente no cadastro e pode ser concedida ou negada de forma independente
    deste consentimento.</p>

<p><strong>4. Direitos do titular.</strong> O responsável pode, a qualquer momento,
    solicitar acesso, correção ou exclusão dos dados, bem como <strong>revogar este
    consentimento</strong>, comunicando-se com a secretaria do clube. A revogação
    não tem efeito retroativo sobre tratamentos já realizados de forma lícita.</p>

<p><strong>5. Retenção.</strong> Os dados são mantidos enquanto durar o vínculo com
    o clube e pelo prazo legal aplicável, sendo então eliminados ou anonimizados.</p>

<p>
    Ao assinar/aceitar este termo, o(a) responsável declara ter lido, compreendido
    e concordado com as condições acima.
</p>
