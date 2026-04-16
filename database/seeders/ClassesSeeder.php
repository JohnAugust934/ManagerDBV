<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Requisito;
use Illuminate\Database\Seeder;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['nome' => 'Amigo', 'cor' => '#3B82F6', 'ordem' => 1],
            ['nome' => 'Companheiro', 'cor' => '#F59E0B', 'ordem' => 2],
            ['nome' => 'Pesquisador', 'cor' => '#10B981', 'ordem' => 3],
            ['nome' => 'Pioneiro', 'cor' => '#6B7280', 'ordem' => 4],
            ['nome' => 'Excursionista', 'cor' => '#8B5CF6', 'ordem' => 5],
            ['nome' => 'Guia', 'cor' => '#EF4444', 'ordem' => 6],
            ['nome' => 'Líder', 'cor' => '#1E3A8A', 'ordem' => 7],
            ['nome' => 'Líder Máster', 'cor' => '#DC2626', 'ordem' => 8],
            ['nome' => 'Líder Máster Avançado', 'cor' => '#000000', 'ordem' => 9],
        ];

        foreach ($classes as $dadosClasse) {
            $classe = Classe::firstOrCreate(
                ['nome' => $dadosClasse['nome']],
                ['cor' => $dadosClasse['cor'], 'ordem' => $dadosClasse['ordem']]
            );

            $this->criarRequisitos($classe);
        }
    }

    private function criarRequisitos(Classe $classe): void
    {
        $requisitosPorClasse = [
            'Amigo' => [
                ['cat' => 'Gerais', 'desc' => 'Ter, no mínimo, 10 anos de idade.'],
                ['cat' => 'Gerais', 'desc' => 'Ser membro ativo do Clube de Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Memorizar e explicar o Voto e a Lei do Desbravador.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro do Clube do Livro Juvenil do ano em curso.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro "Vaso de barro".'],
                ['cat' => 'Gerais', 'desc' => 'Participar ativamente da classe bíblica do seu clube.'],

                ['cat' => 'Descoberta Espiritual', 'desc' => 'Memorizar e demonstrar conhecimento sobre: Criação (dias da criação), 10 pragas do Egito, 12 tribos de Israel e os 39 livros do Antigo Testamento (com habilidade para localizar).'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e explicar os versos: João 3:16, Efésios 6:1-3, II Timóteo 3:16 e Salmo 1.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Leitura bíblica: Gênesis (1, 2, 3, 4:1-16, 6:11-22, 7, 8, 9:1-19, 11:1-9, 12:1-10, 13, 14:18-24, 15, 17:1-8;15-22, 18:1-15, 18:16-33, 19:1-29, 21:1-21, 22:1-19, 23, 24:1-46;48;24:52-67, 27, 28, 29, 30:25-31;31:2-3;17-18, 32, 33, 37, 40, 41, 42, 43, 44, 45, 47, 50) e Êxodo (1, 2, 3, 4:1-17;27-31, 5, 7, 8, 9, 10;11, 12, 13:17-22;14, 15:22-27;16, 17, 18, 19, 20, 24, 32, 33, 34:1-14;29-35, 35:4-29 e 40).'],

                ['cat' => 'Servindo a Outros', 'desc' => 'Dedicar duas horas ajudando alguém em sua comunidade, por meio de duas atividades (visita com oração, oferta de alimento a carente ou projeto ecológico/educativo).'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Escrever uma redação explicando como ser um bom cidadão no lar e na escola.'],

                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Mencionar 10 qualidades de um bom amigo e apresentar 4 situações diárias em que praticou a Regra Áurea (Mateus 7:12).'],
                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Saber cantar o Hino Nacional, conhecer sua história e identificar autores da letra e música.'],

                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Completar uma especialidade: Natação Principiante I, Cultura Física, Nós e Amarras ou Segurança Básica na Água.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Utilizando a experiência de Daniel: explicar princípios de temperança (ou apresentação/encenação de Daniel 1), memorizar Daniel 1:8 e escrever compromisso pessoal de estilo de vida saudável.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Aprender os princípios de uma dieta saudável e ajudar a preparar um quadro com os grupos básicos de alimentos.'],

                ['cat' => 'Organização e Liderança', 'desc' => 'Acompanhar, por observação, todo o processo de planejamento até a execução de uma caminhada de 5 quilômetros.'],

                ['cat' => 'Estudo da Natureza', 'desc' => 'Completar uma especialidade: Felinos, Cães, Mamíferos, Sementes ou Aves de Estimação.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Aprender e demonstrar uma maneira de purificar água e escrever um parágrafo sobre o significado de Jesus como água da vida.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Aprender e montar uma barraca em local apropriado.'],

                ['cat' => 'Arte de Acampar', 'desc' => 'Demonstrar cuidados com corda e fazer/explicar o uso prático dos nós: simples, cego, direito, cirurgião, lais de guia, lais de guia duplo, escota, catau, pescador, fateixa, volta da fiel, nó de gancho, volta da ribeira e ordinário.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Completar a especialidade de Acampamento I.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Apresentar 10 regras para uma caminhada e explicar o que fazer quando estiver perdido.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Aprender sinais de pista e preparar/seguir uma pista com no mínimo 10 sinais.'],

                ['cat' => 'Estilo de Vida', 'desc' => 'Completar uma especialidade na área de Artes e Habilidades Manuais.'],

                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Memorizar, cantar ou tocar o Hino dos Desbravadores e conhecer a história do hino.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Escolher um personagem do Antigo Testamento (José, Jonas, Ester ou Rute) e conversar com o grupo sobre amor, cuidado e livramento de Deus na vida dele.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Levar pelo menos dois amigos não adventistas à Escola Sabatina ou ao Clube de Desbravadores.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Conhecer princípios de higiene e boas maneiras à mesa/com pessoas de idades diferentes, demonstrando sua utilidade nas reuniões e acampamentos do clube.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Completar a especialidade de Arte de Acampar.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Conhecer e identificar 10 flores silvestres e 10 insetos de sua região.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Começar uma fogueira com apenas um fósforo, usando materiais naturais, e mantê-la acesa.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Usar corretamente faca, facão e machadinha e conhecer dez regras de segurança para seu uso.'],
                ['cat' => 'Classe Avançada - Amigo da Natureza', 'desc' => 'Escolher e completar uma especialidade em uma das áreas: Atividades Missionárias ou Atividades Agrícolas.'],
            ],
            'Companheiro' => [
                ['cat' => 'Gerais', 'desc' => 'Ter, no mínimo, 11 anos de idade.'],
                ['cat' => 'Gerais', 'desc' => 'Ser membro ativo do Clube de Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Ilustrar de forma criativa o significado do Voto dos Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro do Clube do Livro Juvenil do ano em curso e escrever um parágrafo sobre o que mais chamou atenção.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro "Um simples lanche".'],
                ['cat' => 'Gerais', 'desc' => 'Participar ativamente da classe bíblica do seu clube.'],

                ['cat' => 'Descoberta Espiritual', 'desc' => 'Memorizar e demonstrar conhecimento sobre: 10 Mandamentos e os 27 livros do Novo Testamento (com habilidade para localizar).'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e explicar os versos: Isaías 41:9-10, Hebreus 13:5, Provérbios 22:6, I João 1:9 e Salmo 8.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Leitura bíblica: Levítico 11; Números (9:15-23, 11, 12, 13, 14:1-38, 16, 17, 20:1-13;22-29, 21:4-9, 22, 23;24:1-10); Deuteronômio (1:1-17, 32:1-43, 33, 34); Josué (1, 2, 3, 4, 5:10;6, 7, 9, 24:1-15;29); Juízes (6, 7, 13:1-18;14, 15, 16); Rute (1, 2;3, 4); 1 Samuel (1, 2, 3, 4, 5, 6, 8, 9, 10;11:12-15, 12, 13, 15, 16, 17, 18:1-19, 20, 21:1-7;22, 24, 25, 26, 31); 2 Samuel (1, 5, 6, 7, 9, 11;12:1-25, 15, 18).'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Escolher, com o conselheiro, um tema (parábola, milagre, sermão da montanha ou sermão sobre a segunda vinda) e demonstrar conhecimento por troca de ideias, atividade em grupo ou redação.'],

                ['cat' => 'Servindo a Outros', 'desc' => 'Planejar e dedicar pelo menos duas horas servindo a comunidade e demonstrando companheirismo de forma prática.'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Dedicar pelo menos cinco horas em projeto que beneficie comunidade ou igreja.'],

                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Conversar com conselheiro/unidade sobre respeito a pessoas de diferentes culturas, raça e sexo.'],

                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Memorizar e explicar I Coríntios 9:24-27.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Conversar com o líder sobre aptidão física e exercícios regulares relacionados à vida saudável.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Aprender sobre os prejuízos do cigarro e escrever compromisso de não usar fumo.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Completar uma especialidade: Natação Principiante II ou Acampamento II.'],

                ['cat' => 'Organização e Liderança', 'desc' => 'Dirigir ou colaborar em uma meditação criativa para unidade ou clube.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Ajudar no planejamento de excursão/acampamento da unidade ou clube com pelo menos um pernoite.'],

                ['cat' => 'Estudo da Natureza', 'desc' => 'Participar de jogos da natureza ou caminhada ecológica por uma hora.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Completar duas especialidades: Anfíbios, Aves, Aves Domésticas, Pecuária, Répteis, Moluscos, Árvores ou Arbustos.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Recapitular estudo da criação e fazer diário de sete dias registrando observações por dia correspondente.'],

                ['cat' => 'Arte de Acampar', 'desc' => 'Descobrir pontos cardeais sem bússola e desenhar rosa dos ventos.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Participar de acampamento de final de semana e fazer relatório com destaques positivos.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Aprender/recapitular os nós: oito, volta do salteador, duplo, caminhoneiro, direito, volta do fiel, escota, lais de guia e simples.'],

                ['cat' => 'Estilo de Vida', 'desc' => 'Completar uma especialidade não realizada anteriormente na seção de Artes e Habilidades Manuais.'],

                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Aprender e demonstrar a composição, significado e uso correto da Bandeira Nacional.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Ler a primeira visão de Ellen White e discutir como Deus usa os profetas para apresentar Sua mensagem à igreja (Primeiros Escritos, p. 13-20).'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Participar de uma atividade missionária ou comunitária envolvendo também um amigo.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Conversar com conselheiro/unidade sobre respeito aos pais ou responsáveis e listar formas de cuidado recebidas.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Participar de uma caminhada de 6 quilômetros e preparar relatório final de uma página.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Escolher um item de saúde: curso para deixar de fumar, dois filmes sobre saúde, cartaz sobre prejuízo das drogas, apoio em material de exposição/passeata ou pesquisa na internet com texto de uma página.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Identificar e descrever 12 aves nativas e 12 árvores nativas.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Participar de uma cerimônia (Investidura, Admissão de Lenço ou Dia do Desbravador) e sugerir ideias criativas para sua realização.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Preparar uma refeição na fogueira durante acampamento do clube ou unidade.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Preparar um quadro com 15 nós diferentes.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Completar a especialidade de Excursionismo Pedestre com Mochila.'],
                ['cat' => 'Classe Avançada - Companheiro de Excursionismo', 'desc' => 'Completar uma especialidade não realizada anteriormente em: Habilidades Domésticas, Ciência e Saúde, Atividades Missionárias ou Atividades Agrícolas.'],
            ],
            'Pesquisador' => [
                ['cat' => 'Gerais', 'desc' => 'Ter, no mínimo, 12 anos de idade.'],
                ['cat' => 'Gerais', 'desc' => 'Ser membro ativo do Clube de Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Demonstrar compreensão da Lei do Desbravador através de representação, debate ou redação.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro do Clube do Livro Juvenil do ano e escrever dois parágrafos sobre o que chamou atenção.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro "Além da Magia".'],
                ['cat' => 'Gerais', 'desc' => 'Participar ativamente da classe bíblica do seu clube.'],

                ['cat' => 'Descoberta Espiritual', 'desc' => 'Memorizar e demonstrar conhecimento sobre Levítico 11 (alimentos comestíveis e não comestíveis).'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e explicar os versos: Eclesiastes 12:13-14, Romanos 6:23, Apocalipse 1:3, Isaías 43:1-2, Salmo 51:10 e Salmo 16.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Leitura bíblica: 1 Reis (1:28-53, 3, 4:20-34, 5, 6, 8:12-60, 10, 11:6-43, 12, 16:29-33;17:1-7, 17:8-24, 18, 19, 21); 2 Reis (2, 4:1-7, 4:8-41, 5, 6:1-23, 6:24-33;7, 20, 22, 23:36-37;24;25:1-7); 2 Crônicas (24:1-14, 36); Esdras (1, 3;6:14-15); Neemias (1, 2, 4, 8); Ester (1, 2, 3, 4, 5, 6, 7;8); Jó (1, 2, 42); Salmos (1, 15, 19, 23, 24, 27, 37, 39, 42, 46, 67, 90;91, 92;97, 98, 100, 117, 119:1-80, 119:81-176, 121, 125, 150); Provérbios (1, 3, 4, 10, 15, 20, 25); Eclesiastes 1.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Escolher uma história (João 3, João 4, Lucas 10, Lucas 15 ou Lucas 19) e demonstrar compreensão de como Jesus salva por conversa em grupo, mensagem, cartazes/maquete ou poesia/hino.'],

                ['cat' => 'Servindo a Outros', 'desc' => 'Conhecer projetos comunitários da cidade e participar de pelo menos um com unidade ou clube.'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Participar em três atividades missionárias da igreja.'],

                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Participar de debate/representação sobre pressão de grupo e identificar influência nas decisões.'],
                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Visitar um órgão público da cidade e descobrir como o clube pode ser útil à comunidade.'],

                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Escolher uma atividade sobre efeitos do álcool (discussão em classe ou vídeo com conversa) e escrever texto pessoal por um estilo de vida livre do álcool.'],

                ['cat' => 'Organização e Liderança', 'desc' => 'Dirigir cerimônia de abertura da reunião semanal do clube ou um programa da Escola Sabatina.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Ajudar a organizar a classe bíblica do clube.'],

                ['cat' => 'Estudo da Natureza', 'desc' => 'Identificar a estrela Alfa do Centauro e a constelação de Órion; conhecer o significado espiritual de Órion (Primeiros Escritos, pág. 41).'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Completar uma especialidade: Astronomia, Cactos, Climatologia, Flores ou Rastreio de Animais.'],

                ['cat' => 'Arte de Acampar', 'desc' => 'Apresentar seis segredos para bom acampamento e participar de acampamento de final de semana planejando e cozinhando duas refeições.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Completar as especialidades Acampamento III e Primeiros Socorros - básico.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Aprender a usar bússola ou GPS e demonstrar habilidade encontrando endereços em zona urbana.'],

                ['cat' => 'Estilo de Vida', 'desc' => 'Completar uma especialidade não realizada anteriormente em Artes e Habilidades Manuais.'],

                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Conhecer e usar adequadamente a Bandeira dos Desbravadores, bandeirim de unidade e comandos de ordem unida.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Ler a história de J. N. Andrews (ou pioneiro de seu país) e discutir importância missionária e a Grande Comissão (Mateus 28:18-20).'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Convidar uma pessoa para assistir ao Clube dos Desbravadores, Classe Bíblica ou Pequeno Grupo.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Completar uma especialidade: Asseio e Cortesia Cristã ou Vida Familiar.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Participar de caminhada de 10 km e listar equipamentos necessários, incluindo roupa e calçado adequados.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Participar da organização de um evento especial do clube: Investidura, Admissão de Lenço ou Dia do Desbravador.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Identificar 6 pegadas de animais/aves e fazer modelo (gesso, massa ou biscuit) de 3 delas.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Aprender quatro amarras básicas e construir um móvel de acampamento.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Planejar cardápio vegetariano para unidade em acampamento de 3 dias e apresentar ao instrutor.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Enviar e receber mensagem por semáforos, código Morse (lanterna), LIBRAS e Braille.'],
                ['cat' => 'Classe Avançada - Pesquisador de Campo e Bosque', 'desc' => 'Completar duas especialidades não realizadas anteriormente em: Habilidades Domésticas, Ciência e Saúde, Atividades Missionárias ou Atividades Agrícolas.'],
            ],
            'Pioneiro' => [
                ['cat' => 'Gerais', 'desc' => 'Ter, no mínimo, 13 anos de idade.'],
                ['cat' => 'Gerais', 'desc' => 'Ser membro ativo do Clube de Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Memorizar e entender o Alvo e o Lema JA.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro do Clube do Livro Juvenil do ano em curso e resumi-lo em uma página.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro "Expedição Galápagos".'],

                ['cat' => 'Descoberta Espiritual', 'desc' => 'Memorizar e demonstrar conhecimento sobre as Bem-Aventuranças (Sermão da Montanha).'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e explicar os versos: Isaías 26:3, Romanos 12:12, João 14:1-3, Salmo 37:5, Filipenses 3:12-14, Salmo 23 e I Samuel 15:22.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Conversar em clube/unidade sobre: o que é cristianismo, características do verdadeiro discípulo e o que fazer para ser um cristão verdadeiro.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Participar de estudo especial sobre inspiração da Bíblia (inspiração, revelação e iluminação) com ajuda de pastor.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Convidar três ou mais pessoas para assistirem classe bíblica ou pequeno grupo.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Leitura bíblica: Eclesiastes (3, 5, 7, 11;12); Isaías (5, 11, 26:1-12;35, 40, 43, 52:13-15;53, 58, 60, 61); Jeremias (9:23-26, 10:1-16, 18:1-6, 26, 36, 52:1-11); Daniel (1 a 12); Joel 2:12-31; Amós 7:10-16 e 8:4-11; Jonas (1 a 4); Miqueias 4; Ageu 2; Zacarias 4; Malaquias 3 e 4; Mateus 1 a 23.'],

                ['cat' => 'Servindo a Outros', 'desc' => 'Participar em dois projetos missionários definidos pelo clube.'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Trabalhar em um projeto comunitário da igreja, escola ou comunidade.'],

                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Participar de debate e avaliação pessoal sobre dois temas: auto-estima, amizade, relacionamentos, otimismo/pessimismo.'],

                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Preparar programa pessoal de exercícios diários, conversar sobre aptidão física e assinar compromisso de exercícios regulares.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Discutir vantagens do estilo de vida adventista conforme ensino bíblico.'],

                ['cat' => 'Organização e Liderança', 'desc' => 'Assistir a seminário/treinamento de Ministério Pessoal e Evangelismo oferecido pela igreja ou distrito.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Participar de uma atividade social da igreja.'],

                ['cat' => 'Estudo da Natureza', 'desc' => 'Estudar a história do dilúvio e o processo de fossilização.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Completar uma especialidade não realizada anteriormente em Estudos da Natureza.'],

                ['cat' => 'Arte de Acampar', 'desc' => 'Fazer um fogo refletor e demonstrar seu uso.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Participar de acampamento de final de semana com bolsa/mochila arrumada adequadamente.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Completar a especialidade de Resgate básico.'],

                ['cat' => 'Estilo de Vida', 'desc' => 'Completar uma especialidade não realizada anteriormente em: Atividades Missionárias, Atividades Profissionais ou Atividades Agrícolas.'],

                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Completar a especialidade de Cidadania Cristã, caso ainda não tenha sido realizada.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Encenar a história do bom samaritano e ajudar, de forma prática, três pessoas ou mais.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Participar de uma atividade (10 km de caminhada, 2 km a cavalo, 2 horas de canoa, 15 km de ciclismo, 200 m de natação, 1500 m de corrida, ou 2 km de patins/roller) e apresentar relatório de no mínimo duas páginas.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Completar a especialidade de Mapa e Bússola.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Demonstrar habilidade no uso correto de uma machadinha.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Ser capaz de acender fogueira em dia de chuva, conseguir lenha seca e manter o fogo aceso.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Completar um item: identificar 10 plantas comestíveis; enviar/receber 35 letras por minuto em semáforo; enviar/receber 35 letras por minuto em código náutico internacional; apresentar/entender Mateus 24 em LIBRAS; ou preparar o Salmo 23 em Braille.'],
                ['cat' => 'Classe Avançada - Pioneiro de Novas Fronteiras', 'desc' => 'Completar uma especialidade não realizada anteriormente em Atividades Recreativas.'],
            ],
            'Excursionista' => [
                ['cat' => 'Gerais', 'desc' => 'Ter, no mínimo, 14 anos de idade.'],
                ['cat' => 'Gerais', 'desc' => 'Ser membro ativo do Clube de Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Memorizar e explicar o significado do Objetivo JA.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro do Clube do Livro Juvenil do ano em curso e resumi-lo em uma página.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro "O Fim do Começo".'],

                ['cat' => 'Descoberta Espiritual', 'desc' => 'Memorizar e demonstrar conhecimento sobre: 12 apóstolos e frutos do Espírito.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e explicar os versos: Romanos 8:28, Apocalipse 21:1-3, II Pedro 1:20-21, I João 2:14, II Crônicas 20:20 e Salmo 46.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Estudar e entender a pessoa do Espírito Santo, sua relação e papel no crescimento espiritual humano.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Estudar com a unidade os eventos finais e a segunda vinda de Cristo.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Descobrir, pelo estudo da Bíblia, o verdadeiro significado da observância do sábado.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Leitura bíblica: Mateus (24, 25, 26:1-35, 26:36-75, 27:1-31, 27:32-56, 27:57-66, 28); Marcos (7, 9, 10, 11, 12, 16); Lucas (1:4-25, 1:26-66, 2:21-38, 2:39-52, 7:18-28, 8, 10:1-37, 10:38-42;11:1-13, 12, 13, 14, 15, 16:1-17, 17, 18, 19, 21, 22, 23, 24); João (1, 2, 3, 4, 5, 6:1-21, 6:22-71, 8:1-38, 9, 10, 11:1-46, 12, 13, 14, 15, 17, 18, 19, 20, 21); Atos (1, 2, 3, 4, 5, 6, 7, 8).'],

                ['cat' => 'Servindo a Outros', 'desc' => 'Convidar um amigo para participar de atividade social da igreja ou da Associação/Missão.'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Participar de projeto comunitário desde planejamento e organização até execução.'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Discutir como jovens adventistas devem se relacionar com vizinhos, escola, atividades sociais e recreativas.'],

                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Examinar atitudes em dois temas: auto-estima, relacionamento familiar, finanças pessoais, pressão de grupo.'],
                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Preparar lista com cinco sugestões recreativas para pessoas com necessidades específicas e colaborar na organização de uma atividade.'],

                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Completar a especialidade de Temperança.'],

                ['cat' => 'Organização e Liderança', 'desc' => 'Preparar organograma da igreja local e relacionar funções dos departamentos.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Participar em dois programas envolvendo diferentes departamentos da igreja local.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Completar a especialidade de Aventuras com Cristo.'],

                ['cat' => 'Estudo da Natureza', 'desc' => 'Recapitular a história de Nicodemos e relacioná-la ao ciclo de vida da lagarta/borboleta, com significado espiritual.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Completar uma especialidade de Estudos da Natureza, não realizada anteriormente.'],

                ['cat' => 'Arte de Acampar', 'desc' => 'Com grupo de no mínimo quatro pessoas e presença de conselheiro adulto experiente, caminhar 20 km em área rural/deserta com uma noite ao ar livre ou barraca; planejar antes, registrar observações e participar de discussão posterior.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Completar a especialidade de Pioneirias.'],

                ['cat' => 'Estilo de Vida', 'desc' => 'Completar especialidade não realizada anteriormente em: Atividades Missionárias, Atividades Agrícolas, Ciência e Saúde ou Habilidades Domésticas.'],

                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Fazer apresentação escrita ou falada sobre respeito à Lei de Deus e às autoridades civis, enumerando pelo menos 10 princípios de comportamento moral.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Acompanhar o pastor ou ancião em visita missionária ou estudo bíblico.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Completar a especialidade de Testemunho Juvenil.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Apresentar cinco atividades na natureza para serem realizadas no sábado à tarde.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Com a unidade, construir cinco móveis de acampamento e um portal para o clube.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Sob supervisão do líder/conselheiro, conversar na unidade ou clube sobre modéstia cristã, recreação, saúde ou observância do sábado.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Demonstrar conhecimento para encontrar alimentos por plantas silvestres da região e diferenciá-las de plantas tóxicas/venenosas.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Demonstrar procedimentos necessários em caso de ferimentos por animais peçonhentos e não peçonhentos.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Demonstrar técnicas para percorrer trilhas em diferentes terrenos: desertos, florestas, pântanos e rios.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Completar a especialidade de Vida Silvestre.'],
                ['cat' => 'Classe Avançada - Excursionista na Mata', 'desc' => 'Completar a especialidade de Ordem Unida, caso não tenha sido realizada anteriormente.'],
            ],
            'Guia' => [
                ['cat' => 'Gerais', 'desc' => 'Ter, no mínimo, 15 anos de idade.'],
                ['cat' => 'Gerais', 'desc' => 'Ser membro ativo do clube de Desbravadores.'],
                ['cat' => 'Gerais', 'desc' => 'Memorizar e explicar o Voto de Fidelidade à Bíblia.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro do Clube de Leitura Juvenil do ano em curso e resumi-lo em uma página.'],
                ['cat' => 'Gerais', 'desc' => 'Ler o livro "O livro amargo".'],

                ['cat' => 'Descoberta Espiritual', 'desc' => 'Memorizar e demonstrar conhecimento sobre: 3 mensagens angélicas (Apocalipse 14:6-12), 7 igrejas do Apocalipse e 12 fundamentos da Nova Jerusalém.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e explicar os versos: I Coríntios 13, II Crônicas 7:14, Apocalipse 22:18-20, II Timóteo 4:6-7, Romanos 8:38-39 e Mateus 6:33-34.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Descrever os dons espirituais nos escritos de Paulo (Coríntios, Efésios, Filipenses) e os objetivos desses dons para a igreja.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Estudar a estrutura e serviço do santuário no Antigo Testamento e relacionar com o ministério pessoal de Jesus e a cruz.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Ler e resumir três histórias de pioneiros adventistas e contá-las em reunião do clube, culto JA ou Escola Sabatina.'],
                ['cat' => 'Descoberta Espiritual', 'desc' => 'Leitura bíblica: Atos (9:1-31, 9:32-43, 10, 11, 12, 13, 14, 16, 17:1-15, 17:16-34, 18, 19:1-22, 19:23-41, 20, 21:17-40;22:1-16, 23, 24, 25, 26, 27, 28), Romanos 12-14, 1 Coríntios 13, 2 Coríntios (5:11-21, 11:16-33;12:1-10), Gálatas (5:16-26;6:1-10), Efésios (5:1-21, 6), Filipenses 4, Colossenses 3, 1 Tessalonicenses (4:13-18, 5), 2 Tessalonicenses (2, 3), 1 Timóteo (4:6-16, 5:1-16;6:11-21), 2 Timóteo (2, 3), Filemom, Hebreus 11, Tiago (1, 3, 5:7-20), 1 Pedro (1, 5:1-11), 2 Pedro 3, 1 João (2, 3, 4, 5), Judas 1:17-25, Apocalipse (1, 2, 3, 7:9-17, 12, 13, 14, 19, 20, 21).'],

                ['cat' => 'Servindo a Outros', 'desc' => 'Ajudar a organizar e participar de uma atividade: visita de cortesia a doente, adoção de pessoa/família necessitada ou projeto aprovado pelo líder.'],
                ['cat' => 'Servindo a Outros', 'desc' => 'Discutir métodos de evangelismo pessoal na unidade e colocar princípios em prática.'],

                ['cat' => 'Desenvolvendo Amizade', 'desc' => 'Assistir palestra/aula e examinar atitudes em dois temas: escolha profissional, relação com pais, escolha para namoro, plano de Deus para o sexo.'],

                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Fazer apresentação para ensino fundamental sobre os oito remédios naturais dados por Deus.'],
                ['cat' => 'Saúde e Aptidão Física', 'desc' => 'Completar uma atividade: poesia/artigo de saúde para divulgação; corrida/atividade similar com programa de treino; leitura de "Temperança" (p. 102-125) com 10 textos selecionados; ou especialidade de Nutrição / liderar Cultura Física.'],

                ['cat' => 'Organização e Liderança', 'desc' => 'Preparar organograma da estrutura administrativa da Igreja Adventista em sua Divisão.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Participar de um item: curso para conselheiros, convenção de liderança da Associação/Missão ou 2 reuniões de diretoria do clube.'],
                ['cat' => 'Organização e Liderança', 'desc' => 'Planejar e ensinar no mínimo dois requisitos de uma especialidade para grupo de desbravadores.'],

                ['cat' => 'Estudo da Natureza', 'desc' => 'Ler o capítulo 7 de "O Desejado de Todas as Nações" (infância de Jesus) e apresentar lições sobre importância da natureza na educação/ministério de Jesus.'],
                ['cat' => 'Estudo da Natureza', 'desc' => 'Completar uma especialidade: Ecologia ou Conservação Ambiental.'],

                ['cat' => 'Arte de Acampar', 'desc' => 'Participar com a unidade de acampamento com estrutura de pioneiria, planejando o que levar e atividades do acampamento.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Planejar, preparar e cozinhar três refeições ao ar livre.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Construir e utilizar móvel de acampamento em tamanho real com nós e amarras.'],
                ['cat' => 'Arte de Acampar', 'desc' => 'Completar especialidade não realizada anteriormente que conte para mestrado: Aquática, Esportes, Atividades Recreativas ou Vida Campestre.'],

                ['cat' => 'Estilo de Vida', 'desc' => 'Completar especialidade não realizada anteriormente em: Atividades Agrícolas, Ciência e Saúde, Habilidades Domésticas ou Atividades Profissionais.'],

                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Completar a especialidade de Mordomia.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Ler o livro "O maior discurso de Cristo" e escrever uma página sobre o efeito da leitura em sua vida.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Cumprir um item: levar dois amigos para duas reuniões da igreja diferentes; ou ajudar a planejar e participar de pelo menos quatro domingos de uma série de evangelismo jovem.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Escrever uma página ou apresentar palestra sobre como influenciar amigos para Cristo.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Observar por dois meses o trabalho dos diáconos e apresentar relatório detalhado sobre cuidado da propriedade da igreja, lava-pés, batismo e recolhimento de dízimos/ofertas.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Completar o mestrado em Vida Campestre.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Projetar três tipos de abrigo, explicar seu uso e utilizar um deles em acampamento.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Assistir seminário ou apresentar palestra sobre dois temas: aborto, AIDS, violência ou drogas.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Completar a especialidade de Orçamento Familiar.'],
                ['cat' => 'Classe Avançada - Guia de Exploração', 'desc' => 'Completar a especialidade de Liderança Campestre.'],
            ],
            'Líder' => [
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ter no mínimo 16 anos completos para iniciar a classe e no mínimo 18 anos para a investidura.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ser membro batizado da Igreja Adventista do Sétimo Dia.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Possuir recomendação por escrito da comissão da igreja para iniciar a classe.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ter concluído todas as classes regulares, ou estar simultaneamente cumprindo classes agrupadas e líder.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ser membro ativo de um clube ou participar de coordenação distrital/regional, com cadastro atualizado no SGC.'],

                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Fazer um dos seguintes: completar o Ano Bíblico Jovem ou ler a Bíblia toda em dois anos.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Ler "O Libertador" (Ellen White) e apresentar reação à leitura de duas páginas.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Ler um livro sobre liderança ou desenvolvimento juvenil e apresentar reação de duas páginas.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Demonstrar crescimento em liderança e ensino completando quatro itens: dissertação sobre falar para adolescentes (3-4 páginas), ajudar treinamento para evento da Associação/Missão, ensinar duas especialidades, planejar/coordenar acampamento, participar de 75% das reuniões de diretoria com relatório, ou liderar/participar de pequeno grupo por 6 meses.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Conduzir ou colaborar por pelo menos seis meses em classe de juvenis/adolescentes, projeto Desbravador por um dia, feira de saúde/escola cristã de férias, ou Calebe de lenço.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Estudar o Manual Administrativo do Clube de Desbravadores e prestar exame DSA com nota mínima 7,0 (70%).'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Estudar "Nisto Cremos" (crenças 1 a 10) e prestar exame DSA com nota mínima 7,0 (70%).'],

                ['cat' => 'II. Fundamentos do Aconselhamento dos Desbravadores', 'desc' => 'Participar de seminário de 4 horas da Associação/Missão sobre o papel do conselheiro.'],
                ['cat' => 'II. Fundamentos do Aconselhamento dos Desbravadores', 'desc' => 'Atuar por no mínimo um ano como Conselheiro, Conselheiro associado, Instrutor, Diretor, Diretor associado, Secretário ou Capelão.'],
                ['cat' => 'II. Fundamentos do Aconselhamento dos Desbravadores', 'desc' => 'Fazer o curso do Estatuto da Criança e do Adolescente aplicado aos Desbravadores no SGC-EaD e apresentar certificado.'],
                ['cat' => 'II. Fundamentos do Aconselhamento dos Desbravadores', 'desc' => 'Ler os capítulos 4, 5, 6, 7, 8 e 31 de "Orientação da Criança" e apresentar reação de uma página.'],
                ['cat' => 'II. Fundamentos do Aconselhamento dos Desbravadores', 'desc' => 'Fazer ao menos quatro visitas (uma por trimestre) a família de desbravador para inspirar confiança e compreender melhor o juvenil, com breve meditação/oração quando permitido.'],

                ['cat' => 'III. Serviço ao Clube', 'desc' => 'Ser instrutor de uma classe até a investidura.'],
                ['cat' => 'III. Serviço ao Clube', 'desc' => 'Completar um mestrado: Zoologia, Botânica ou Atividades Recreativas.'],
                ['cat' => 'III. Serviço ao Clube', 'desc' => 'Completar a especialidade de Arte de Contar História.'],

                ['cat' => 'IV. Liderança Aplicada', 'desc' => 'Apresentar certificado de Treinamento de Diretoria - Nível básico (Associação/Missão).'],
                ['cat' => 'IV. Liderança Aplicada', 'desc' => 'Participar de curso de liderança de 10 horas da Associação/Missão e apresentar certificado.'],
                ['cat' => 'IV. Liderança Aplicada', 'desc' => 'Participar por 7 dias ou mais em projeto missionário: Missão Calebe/Calebe de Lenço, Evangelismo Público, Semana Santa ou Semana de Colheita.'],
            ],
            'Líder Máster' => [
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ter no mínimo um ano de experiência como Líder investido para iniciar os requisitos.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ter 18 anos completos para iniciar a classe.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ser membro ativo da Igreja Adventista do Sétimo Dia.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Possuir recomendação por escrito da comissão da igreja para iniciar a classe.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ser membro ativo de clube ou participar de coordenação distrital/regional e estar com cadastro atualizado no SGC.'],

                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Escolher e completar um hábito devocional: Ano Bíblico ou Ano Bíblico em áudio.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Ler os capítulos 2,3,10,11,13,18,19,20,23,24,25,26,32,34,35,37,38,39 e 42 de "A Ciência do Bom Viver" e apresentar reação de uma página.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Estudar "Nisto Cremos" (crenças 11 a 20) e prestar exame DSA com nota mínima 7,0 (70%).'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Realizar um dos seguintes: conduzir série completa de estudos bíblicos para família de desbravador não adventista, ou dirigir série para classe bíblica visando Batismo da Primavera.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Apresentar certificado do curso Treinamento de Diretoria - Nível Avançado.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Participar do curso de liderança para Líder Máster realizado pela Associação/Missão e apresentar certificado.'],

                ['cat' => 'II. Serviço ao Clube', 'desc' => 'Ensinar uma classe regular e uma avançada durante um ano.'],
                ['cat' => 'II. Serviço ao Clube', 'desc' => 'Atuar por no mínimo oito meses como Conselheiro, Diretor, Diretor Associado, Secretário ou Capelão.'],

                ['cat' => 'III. Capacitação Aplicada', 'desc' => 'Preparar cronograma visual/escrito (duas páginas) dos principais eventos da história da IASD com foco em Divisão, União e Campo; apresentar para grupo de pelo menos seis pessoas.'],
                ['cat' => 'III. Capacitação Aplicada', 'desc' => 'Escolher duas áreas (Liderança, Processo de aprendizagem, Desenvolvimento do adolescente, Habilidades pessoais, Desenvolvimento pessoal, Comunicação/relacionamentos), ler um livro de cada e apresentar reação de uma página por livro.'],
                ['cat' => 'III. Capacitação Aplicada', 'desc' => 'Selecionar e completar duas áreas aplicadas entre: Administração/Relações Humanas, Acampamento, Evangelismo Juvenil/Atividades Comunitárias, Criatividade, Ordem Unida/Civismo, Educação Campestre e Recreação, conforme itens oficiais do cartão.'],
            ],
            'Líder Máster Avançado' => [
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ter no mínimo um ano de experiência como Líder Máster investido para iniciar os requisitos.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ter 20 anos completos para iniciar a classe.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ser membro ativo da Igreja Adventista do Sétimo Dia.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Possuir recomendação por escrito da comissão da igreja para iniciar a classe.'],
                ['cat' => 'Pré-Requisitos', 'desc' => 'Ser membro ativo de clube ou participar de coordenação distrital/regional.'],

                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Escolher um hábito devocional: Ano bíblico ou Ano bíblico em áudio.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Estudar "Nisto Cremos" (crenças 21 a 28) e prestar exame DSA com nota mínima 7,0 (70%).'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Ler "Retrato dos Pioneiros", escolher um pioneiro e explicar em duas páginas como seu legado influencia sua vida hoje.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Ler "O Grande Conflito" e apresentar reação à leitura de três páginas.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Ler um livro sobre liderança cristã e apresentar reação de duas páginas.'],
                ['cat' => 'I. Crescimento Pessoal e Espiritual', 'desc' => 'Participar do curso de liderança para Máster Avançado realizado pela Associação/Missão e apresentar certificado.'],

                ['cat' => 'II. Serviço ao Clube', 'desc' => 'Atuar por no mínimo oito meses como Conselheiro, Diretor, Diretor associado, Secretário ou Capelão.'],
                ['cat' => 'II. Serviço ao Clube', 'desc' => 'Servir como orientador de candidato à classe de Líder ou Líder Máster por no mínimo um ano ou até a investidura.'],

                ['cat' => 'III. Capacitação Aplicada', 'desc' => 'Selecionar e completar uma área oficial do cartão entre: Administração/Relações Humanas, Acampamento, Evangelismo Juvenil/Atividades Comunitárias, Criatividade, Ordem Unida/Civismo, Educação Campestre ou Recreação, cumprindo todos os subitens definidos para a área escolhida.'],
            ],
        ];

        $requisitos = $requisitosPorClasse[$classe->nome] ?? [];
        $prefixo = mb_strtoupper(mb_substr($classe->nome, 0, 3));

        foreach ($requisitos as $index => $req) {
            Requisito::firstOrCreate(
                ['classe_id' => $classe->id, 'descricao' => $req['desc']],
                [
                    'categoria' => $req['cat'],
                    'codigo' => $prefixo . '-' . ($index + 1),
                ]
            );
        }
    }
}
