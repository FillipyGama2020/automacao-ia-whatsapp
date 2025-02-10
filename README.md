# WhatsApp AI Panel

Plataforma SaaS multi-tenant para atendimento automatizado via WhatsApp
com agentes de inteligência artificial. Painel administrativo (agência) +
portal de autoatendimento (cliente final), orquestração de conversas via
n8n, integração com a WhatsApp Cloud API da Meta e OpenAI.

## Principais funcionalidades

- **Agentes de IA configuráveis por cliente** — personalidade, tom de
  voz, base de conhecimento (FAQ, produtos, políticas, documentos),
  horário de atendimento, limites de uso e regras de negócio, tudo
  editável sem código.
- **Múltiplos agentes por número de WhatsApp**, com classificação
  automática por IA (qual especialista deve atender) e transferência
  entre agentes durante a mesma conversa.
- **IA multimodal** — entende texto, imagem, áudio (transcrição) e
  documentos (PDF/DOCX/TXT) no mesmo fluxo de conversa.
- **Coexistência com o WhatsApp Business App** — a IA responde
  automaticamente, mas o dono do número continua podendo atender pelo
  próprio celular; o sistema detecta a intervenção humana e pausa a IA.
- **Retomada automática inteligente** — recupera conversas perdidas por
  falha técnica, respeita pausas manuais, e retoma sozinha quando um
  horário de atendimento reabre.
- **Mensagens proativas (campanhas)** — templates aprovados pela Meta,
  envio individual ou em lote, agendamento, opt-out automático por
  palavra-chave.
- **Kanban de leads** com classificação automática (frio/morno/quente)
  extraída da própria conversa pela IA.
- **Portal do cliente final** — self-service para editar o próprio
  agente, ver conversas, relatórios e financeiro, sem acesso ao admin.
- **Ferramentas de conformidade com a LGPD** — exclusão de dados por
  telefone, mascaramento automático de CPF/cartão em mensagens.
- **Financeiro** — fechamento mensal por cliente, cálculo de excedente
  de plano, custo real de IA por conversa.

## Arquitetura

```
WhatsApp Cloud API (Meta)
        │  webhook
        ▼
    n8n (orquestração do fluxo de conversa,
         fila com Redis + workers, modo de execução distribuído)
        │  HTTP (API interna)
        ▼
  Painel Laravel (regras de negócio, multi-tenancy,
         autenticação, painel admin + portal do cliente)
        │
        ▼
   MySQL (dados de negócio) + Postgres (n8n)
```

- **Backend**: Laravel 13, PHP 8.3, MySQL.
- **Orquestração de IA**: n8n em modo fila (Redis + workers), decidindo
  em tempo real qual agente responde, se está dentro do horário, se
  atingiu limite de uso, e chamando a OpenAI com o contexto certo.
- **Frontend**: Blade + Tailwind CSS + Alpine.js, sem SPA — server-side
  rendering com interatividade pontual (ex: quadro Kanban com
  drag-and-drop nativo).
- **Infraestrutura**: Docker Compose, Traefik como proxy reverso com TLS
  automático (Let's Encrypt), monitoramento com alertas.
- **Testes**: PHPUnit, suíte de testes de feature cobrindo autenticação,
  isolamento multi-tenant, webhooks e regras de negócio críticas.

## Rodando localmente

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Requer PHP 8.3+, Composer, Node 18+, e um banco MySQL configurado no
`.env`.

## Testes

```bash
php artisan test
```
