# Desbravadores Manager

Sistema web para gestão de clubes de Desbravadores, com foco em secretaria, financeiro, pedagógico, eventos, patrimônio e relatórios.

## Visão Geral

O **Desbravadores Manager** centraliza as rotinas do clube em um único sistema:

- Cadastro e acompanhamento de desbravadores e unidades
- Controle administrativo (atas e atos)
- Fluxo financeiro (caixa e mensalidades)
- Inventário patrimonial
- Gestão de eventos e inscrições
- Frequência, classes, especialidades e ranking
- Emissão de relatórios em PDF

## Principais Módulos

### Secretaria

- Clube (dados institucionais)
- Desbravadores (cadastro completo)
- Unidades
- Atas e atos oficiais

### Pedagógico

- Classes e requisitos
- Especialidades por desbravador
- Frequência e pontuação

### Financeiro

- Caixa (entradas e saídas)
- Mensalidades (geração e baixa de pagamento)
- Patrimônio (itens e estado de conservação)

### Eventos

- Criação e gestão de eventos
- Inscrição individual e em lote
- Controle de pagamento/status
- Geração de autorização

### Relatórios

- Hub de relatórios personalizados
- Relatórios por módulo
- PDFs de fichas e documentos operacionais

## Stack Tecnológica

- **Backend:** Laravel 12 + PHP 8.2+
- **Frontend:** Blade + Alpine.js + Tailwind CSS
- **Build:** Vite
- **Banco de dados:** SQLite (padrão), PostgreSQL ou MySQL
- **PDF:** `barryvdh/laravel-dompdf`
- **Backup:** `spatie/laravel-backup`

## Requisitos

- PHP 8.2+
- Composer
- Node.js 20+ e npm
- Banco de dados (SQLite, PostgreSQL ou MySQL)

## Instalação (Local)

### 1. Clonar o projeto

```bash
git clone <URL_DO_REPOSITORIO>
cd desbravadores-manager
```

### 2. Instalar dependências

```bash
composer install
npm install
```

### 3. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Banco de dados

Opção rápida com SQLite (padrão do `.env.example`):

```bash
php artisan migrate --seed
```

Para reset completo com dados de demonstração:

```bash
php artisan migrate:fresh --seed
```

### 5. Rodar aplicação

Em terminais separados:

```bash
php artisan serve
npm run dev
```

A aplicação ficará disponível em `http://127.0.0.1:8000`.

## Setup em Um Comando

O projeto possui script de setup no Composer:

```bash
composer run setup
```

Esse script instala dependências, configura `.env`, gera key, roda migration e build de frontend.

## Scripts Úteis

```bash
# Frontend
npm run dev
npm run build

# Backend
php artisan serve
php artisan migrate
php artisan migrate:fresh --seed
php artisan test

# Fluxo de desenvolvimento completo (servidor + fila + logs + vite)
composer run dev
```

## Acessos de Desenvolvimento (Seeder)

Após `migrate --seed` ou `migrate:fresh --seed`, usuários padrão são criados:

| Perfil | E-mail | Senha |
|---|---|---|
| Master | `admin@clube.com` | `password` |
| Diretor | `diretor@clube.com` | `password` |
| Secretaria | `secretaria@clube.com` | `password` |
| Tesoureiro | `tesoureiro@clube.com` | `password` |
| Instrutor | `instrutor@clube.com` | `password` |

Também são criados usuários conselheiros e dados de demonstração para navegação dos módulos.

## Controle de Acesso

O sistema utiliza autenticação com verificação de e-mail e autorização por função/permissão (`can:*`).

Perfis principais:

- `master`
- `diretor`
- `secretario`
- `tesoureiro`
- `conselheiro`
- `instrutor`

Permissões por módulo (com extras por usuário):

- `secretaria`
- `financeiro`
- `unidades`
- `pedagogico`
- `eventos`
- `relatorios`

## Estrutura de Pastas

```text
app/                # Regras de negócio, controllers, models
bootstrap/
config/
database/           # Migrations, factories, seeders
public/
resources/
  css/              # Estilos base (design system)
  views/            # Telas Blade
routes/             # Rotas web
storage/
tests/              # Testes automatizados
```

## UI e Padrões Visuais

Para manter consistência de interface nas novas telas, use o guia:

- [Guia Visual UI](docs/guia-visual-ui.md)

Ele cobre:

- padrão de botões (`ui-btn-primary`, `ui-btn-secondary`, `ui-btn-danger`)
- ações fora do header
- checklist de revisão visual antes de publicar

## Relatórios e PDFs

O módulo de relatórios gera documentos em PDF para operação administrativa, médica, financeira e patrimonial.

## Backups

A base já inclui estrutura para backup e restauração usando Spatie Backup (`spatie/laravel-backup`) e telas administrativas de backup para perfil master.

## Qualidade e Testes

Executar testes:

```bash
php artisan test
```

Executar build de produção:

```bash
npm run build
```

## Contribuição

1. Crie uma branch (`feat/minha-melhoria`)
2. Faça commits pequenos e objetivos
3. Rode testes/build
4. Abra Pull Request com descrição clara

## Licença

Projeto sob licença MIT (base Laravel).
