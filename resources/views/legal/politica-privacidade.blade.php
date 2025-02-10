<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de Privacidade — WhatsApp AI Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .doc-conteudo h2 { font-size: 1.125rem; font-weight: 600; color: #111827; margin-top: 2rem; margin-bottom: 0.5rem; }
        .doc-conteudo h3 { font-size: 1rem; font-weight: 600; color: #111827; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .doc-conteudo p { margin-bottom: 1rem; line-height: 1.7; }
        .doc-conteudo ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .doc-conteudo li { margin-bottom: 0.375rem; line-height: 1.6; }
        .doc-conteudo a { color: #4f46e5; text-decoration: underline; }
        .doc-conteudo strong { font-weight: 600; }
        .doc-conteudo table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: 0.8125rem; }
        .doc-conteudo th, .doc-conteudo td { border: 1px solid #e5e7eb; padding: 0.5rem 0.625rem; text-align: left; vertical-align: top; }
        .doc-conteudo th { background: #f9fafb; font-weight: 600; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
    <div class="max-w-3xl mx-auto px-6 py-12">
        <header class="mb-8 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Sua Empresa" class="h-16 mx-auto object-contain mb-4">
            <p class="text-sm text-gray-500">Sua Empresa Web Comunicação LTDA</p>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Política de Privacidade — WhatsApp AI Panel</h1>
            <p class="text-sm text-gray-500 mt-2">Última atualização: 22/07/2026</p>
        </header>

        <div class="bg-white shadow-sm rounded-lg p-6 sm:p-10 text-sm doc-conteudo">

            <p>
                Esta Política de Privacidade explica como <strong>Sua Empresa LTDA</strong>, CNPJ
                00.000.000/0001-00, com sede em Cidade/UF ("EMPRESA"), trata dados pessoais no contexto do
                <strong>WhatsApp AI Panel</strong> — plataforma de atendimento via WhatsApp com inteligência artificial,
                hospedada e operada integralmente pela EMPRESA. Esta Política integra os
                <a href="{{ route('legal.termos-uso') }}">Termos de Uso</a> do WhatsApp AI Panel.
            </p>

            <h2>1. Definições</h2>
            <ul>
                <li><strong>Cliente.</strong> Empresa ou profissional que contrata o WhatsApp AI Panel para atender seus
                    próprios contatos via WhatsApp.</li>
                <li><strong>Usuário.</strong> Pessoa indicada pelo Cliente para acessar o Portal do Cliente.</li>
                <li><strong>Contato Final.</strong> Pessoa que troca mensagens com o Agente de IA do Cliente pelo
                    WhatsApp.</li>
                <li><strong>Titular.</strong> Pessoa natural a quem se referem os dados pessoais tratados.</li>
                <li><strong>Controlador / Operador.</strong> Conceitos da LGPD: o Controlador decide sobre o
                    tratamento; o Operador trata os dados conforme as instruções do Controlador.</li>
                <li><strong>LGPD.</strong> Lei nº 13.709/2018 — Lei Geral de Proteção de Dados Pessoais.</li>
            </ul>

            <h2>2. A quem se aplica esta Política</h2>
            <p>Esta Política se aplica a três grupos de titulares, tratados de forma distinta:</p>
            <ul>
                <li><strong>Clientes e Usuários</strong> — cujos dados cadastrais a EMPRESA trata como
                    Controladora (ver Capítulo 6);</li>
                <li><strong>Contatos Finais</strong> — pessoas que conversam com o Agente de IA de um Cliente,
                    cujos dados a EMPRESA trata como Operadora, por conta e ordem do Cliente (ver Capítulo 6);</li>
                <li><strong>Visitantes</strong> do site institucional do WhatsApp AI Panel.</li>
            </ul>

            <h2>3. Diferença importante em relação a softwares self-hosted</h2>
            <p>
                Ao contrário de softwares instalados no próprio servidor do cliente, o WhatsApp AI Panel é uma plataforma
                <strong>hospedada pela EMPRESA</strong>: todo o banco de dados — cadastro de clientes, configuração
                dos agentes, histórico de conversas e leads — fica armazenado na infraestrutura da EMPRESA, não em
                um servidor de titularidade do Cliente. Por isso, diferente de um modelo self-hosted, a EMPRESA
                tem acesso técnico operacional a esses dados, na qualidade de Operadora, para poder efetivamente
                prestar o serviço.
            </p>

            <h2>4. Quais dados coletamos</h2>

            <h3>4.1. Dados do Cliente e dos Usuários (cadastro)</h3>
            <ul>
                <li>Identificação e contato: nome da empresa, nome do responsável, e-mail, telefone, CNPJ/CPF;</li>
                <li>Credenciais de acesso ao Portal (senha armazenada com hash);</li>
                <li>Dados de faturamento referentes ao Plano de Serviço contratado;</li>
                <li>Credenciais técnicas da integração com o WhatsApp Business Platform do Cliente (App ID,
                    identificador da conta comercial, identificador do número de telefone, token de acesso —
                    armazenados de forma criptografada).</li>
            </ul>

            <h3>4.2. Dados operacionais dos Contatos Finais (tratados por conta do Cliente)</h3>
            <ul>
                <li>Número de telefone e nome de perfil do WhatsApp;</li>
                <li>Conteúdo das mensagens trocadas com o Agente de IA (texto e, quando permitido pelo Cliente,
                    anexos de imagem, áudio, vídeo ou documento);</li>
                <li>Dados eventualmente identificados como lead durante a conversa (nome, interesse,
                    classificação comercial);</li>
                <li>Avaliação de satisfação, quando respondida pelo Contato Final.</li>
            </ul>
            <p>
                Esses dados são coletados exclusivamente porque o Contato Final inicia ou participa de uma conversa
                de WhatsApp com o número comercial do Cliente. A EMPRESA não decide a finalidade dessa coleta —
                quem decide é o Cliente, como Controlador desses dados (ver Capítulo 6).
            </p>

            <h3>4.3. Dados técnicos coletados automaticamente</h3>
            <ul>
                <li>Registros de acesso ao Portal: endereço IP, data e hora, dispositivo e navegador, nos termos
                    do art. 15 da Lei nº 12.965/2014 (Marco Civil da Internet);</li>
                <li>Cookies estritamente necessários ao funcionamento e à autenticação do Portal (sessão e proteção
                    contra CSRF) — o WhatsApp AI Panel não utiliza cookies de publicidade ou de mensuração de terceiros.</li>
            </ul>

            <h2>5. Finalidades do tratamento</h2>
            <table>
                <thead>
                    <tr><th>Dado</th><th>Finalidade</th><th>Base legal (LGPD)</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cadastro de Cliente e Usuário</td>
                        <td>Formação e execução do contrato de prestação do serviço WhatsApp AI Panel</td>
                        <td>Execução de contrato (art. 7º, V)</td>
                    </tr>
                    <tr>
                        <td>Conversas e leads dos Contatos Finais</td>
                        <td>Prestar o serviço de atendimento automatizado contratado pelo Cliente</td>
                        <td>Execução de contrato entre EMPRESA e Cliente (art. 7º, V); base legal para a coleta
                            junto ao Contato Final é de responsabilidade do Cliente, como Controlador</td>
                    </tr>
                    <tr>
                        <td>Logs de acesso ao Portal</td>
                        <td>Segurança e cumprimento do Marco Civil da Internet</td>
                        <td>Obrigação legal (art. 7º, II) e legítimo interesse (art. 7º, IX)</td>
                    </tr>
                    <tr>
                        <td>Cookies estritamente necessários</td>
                        <td>Autenticação e segurança do Portal</td>
                        <td>Legítimo interesse (art. 7º, IX)</td>
                    </tr>
                </tbody>
            </table>

            <h2>6. Papéis: quando a EMPRESA é Controladora e quando é Operadora</h2>
            <p>
                <strong>EMPRESA como Controladora.</strong> A EMPRESA é Controladora apenas dos dados cadastrais
                do Cliente e dos Usuários (nome, e-mail, CNPJ/CPF, telefone, credenciais de acesso), tratados para
                faturamento, suporte e cumprimento do contrato de prestação de serviço.
            </p>
            <p>
                <strong>EMPRESA como Operadora.</strong> Em relação aos dados dos Contatos Finais (conversas,
                leads, avaliações), a EMPRESA atua como Operadora, tratando esses dados exclusivamente para
                prestar o serviço contratado pelo Cliente e conforme as configurações que o próprio Cliente define
                no Agente de IA (horários, regras, base de conhecimento).
            </p>
            <p>
                <strong>Cliente como Controlador.</strong> O Cliente é o Controlador dos dados pessoais dos seus
                Contatos Finais — é ele quem decide iniciar e conduzir o relacionamento comercial com essas pessoas
                pelo WhatsApp. Cabe ao Cliente garantir base legal adequada para tratar os dados de seus próprios
                contatos (por exemplo, execução de contrato, legítimo interesse ou consentimento, conforme o caso),
                atender às solicitações desses titulares e manter sua própria política de privacidade voltada a
                eles, quando aplicável.
            </p>

            <h2>7. Com quem compartilhamos dados</h2>
            <p>Compartilhamos dados pessoais apenas com prestadores indispensáveis à operação do WhatsApp AI Panel:</p>
            <ul>
                <li><strong>Meta Platforms, Inc. / WhatsApp LLC</strong> — provedora da WhatsApp Business Platform,
                    por onde as mensagens transitam entre o Contato Final e o Agente de IA
                    (<a href="https://www.whatsapp.com/legal/business-terms/" target="_blank" rel="noopener">termos</a>);</li>
                <li><strong>Provedor de inteligência artificial</strong> (atualmente, OpenAI) — recebe o conteúdo
                    textual das conversas na medida necessária para gerar as respostas do Agente de IA;</li>
                <li><strong>Provedor de infraestrutura em nuvem</strong> — hospeda os servidores onde o WhatsApp AI Panel
                    roda, podendo envolver transferência internacional de dados (ver Capítulo 10);</li>
                <li><strong>Provedor de e-mail</strong> — usado para envio de e-mails transacionais do Portal
                    (convite de acesso, redefinição de senha).</li>
            </ul>
            <p>
                Esta enumeração é exemplificativa: a EMPRESA pode adicionar, substituir ou remover prestadores de
                mesma natureza e finalidade ao longo do tempo, sem necessidade de atualização individual desta
                Política. A EMPRESA não vende dados pessoais a terceiros.
            </p>

            <h2>8. Por quanto tempo guardamos os dados</h2>
            <ul>
                <li><strong>Conversas e mensagens.</strong> Mantidas por até 12 (doze) meses a partir do início da
                    conversa, sendo excluídas automaticamente após esse prazo (junto com eventuais anexos de
                    mídia), salvo solicitação de exclusão antecipada pelo titular.</li>
                <li><strong>Leads.</strong> Mantidos por prazo indeterminado, enquanto durar a relação comercial
                    entre o Cliente e seus próprios contatos, por se tratarem de dados de natureza comercial —
                    cabe ao Cliente, como Controlador, definir e justificar esse prazo perante seus contatos.</li>
                <li><strong>Cadastro do Cliente e Usuários.</strong> Mantido enquanto vigente o contrato de
                    prestação de serviço, podendo ser conservado por até 5 (cinco) anos após o término, com base em
                    legítimo interesse, para defesa em eventuais processos judiciais ou administrativos.</li>
                <li><strong>Logs de acesso.</strong> Mantidos por, no mínimo, 6 (seis) meses, conforme o Marco
                    Civil da Internet.</li>
            </ul>
            <p>
                Qualquer titular (Cliente ou, por intermédio do Cliente, um Contato Final) pode solicitar a
                exclusão antecipada de conversas associadas a um número de telefone específico, atendendo ao
                direito de eliminação previsto na LGPD.
            </p>

            <h2>9. Como protegemos os dados</h2>
            <p>
                Adotamos medidas técnicas e organizacionais para proteger os dados sob nossa responsabilidade,
                incluindo controle de acesso por papel (administrador/cliente), criptografia de credenciais
                sensíveis em banco de dados, comunicação criptografada (HTTPS/TLS) e mascaramento automático de
                dados sensíveis (como CPF e números de cartão) identificados no conteúdo das conversas, quando
                configurado pelo Cliente.
            </p>

            <h2>10. Transferência internacional de dados</h2>
            <p>
                Para operar o WhatsApp AI Panel, alguns dados podem ser transferidos para fora do Brasil — em especial,
                para os Estados Unidos, onde estão sediados a Meta Platforms e o provedor de inteligência
                artificial utilizado para gerar as respostas do Agente de IA, e eventualmente para os países onde
                se localizam os servidores do provedor de infraestrutura em nuvem contratado pela EMPRESA. Essas
                transferências observam o art. 33 da LGPD.
            </p>

            <h2>11. Direitos do Titular</h2>
            <p>
                Nos termos da LGPD, todo titular tem direito à confirmação e acesso aos seus dados, correção,
                anonimização, bloqueio ou eliminação de dados desnecessários ou tratados irregularmente,
                portabilidade, eliminação de dados tratados com consentimento, informação sobre compartilhamento,
                revogação de consentimento e oposição a tratamento baseado em legítimo interesse.
            </p>
            <p>
                Solicitações relativas aos dados de <strong>Clientes e Usuários</strong> devem ser dirigidas
                diretamente à EMPRESA. Solicitações relativas aos dados de <strong>Contatos Finais</strong> devem
                ser dirigidas ao Cliente responsável por aquele número de WhatsApp — que é o Controlador desses
                dados —, mas a EMPRESA auxiliará tecnicamente o Cliente a atendê-las sempre que solicitado.
            </p>

            <h2>12. Cookies</h2>
            <p>
                O WhatsApp AI Panel utiliza apenas cookies estritamente necessários ao funcionamento do Portal — sessão
                autenticada e proteção contra ataques CSRF. Não utilizamos cookies de publicidade, rastreamento
                ou mensuração de terceiros.
            </p>

            <h2>13. Encarregado e canais de contato</h2>
            <p>
                Em cumprimento ao art. 41 da LGPD, dúvidas, solicitações de titulares ou reportes de incidentes
                relacionados a dados pessoais podem ser enviados para
                <a href="mailto:ia@fbrandao.com">ia@fbrandao.com</a>.
            </p>

            <h2>14. Alterações desta Política</h2>
            <p>
                Esta Política pode ser atualizada a qualquer tempo para refletir mudanças na operação do
                WhatsApp AI Panel ou na legislação aplicável. A versão vigente é sempre a publicada nesta página, com a
                data de última atualização indicada no topo.
            </p>
        </div>
    </div>
</body>
</html>
