# ⚜️ Desbravadores Manager

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.0+-38B2AC?style=for-the-badge&logo=tailwind-css)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16+-336791?style=for-the-badge&logo=postgresql)

> **Sistema Integrado de Gestão para Clubes de Desbravadores**

O **Desbravadores Manager** é uma solução completa para informatizar e facilitar a administração de clubes. Do controle financeiro à gamificação das unidades, o sistema centraliza as operações permitindo que a diretoria foque no que mais importa: os desbravadores.

---

## 🚀 Funcionalidades Principais

### 📋 Secretaria & Gestão de Pessoas
- **Cadastro Completo:** Gerenciamento de Desbravadores, Diretoria e Responsáveis.
- **Estrutura de Unidades:** Organização por unidades, com conselheiros e capitães definidos.
- **Livro de Atas Digital:** Registro oficial de reuniões da comissão executiva.
- **Atos Oficiais:** Histórico de nomeações e decisões administrativas.

### 💰 Tesouraria Inteligente
- **Fluxo de Caixa:** Registro de entradas e saídas categorizadas.
- **Controle de Mensalidades:** Geração em massa de cobranças mensais e baixa de pagamentos.
- **Relatórios Financeiros:** Visão clara da saúde financeira do clube.

### ⛺ Patrimônio & Inventário
- **Controle de Bens:** Cadastro de barracas, equipamentos de cozinha, bandeiras e materiais diversos.
- **Status de Conservação:** Monitoramento do estado dos itens (Novo, Bom, Regular, Ruim).

### 🏆 Gamificação & Frequência (Novo!)
- **Ranking em Tempo Real:** Dashboard com o "Top 3 Unidades" baseado em pontuação.
- **Chamada Inteligente:** Registro rápido de presença, pontualidade, bíblia e uniforme.
- **Cálculo Automático:** Pontuação atribuída automaticamente ao desbravador e somada à sua unidade.

### 🔒 Segurança & Acesso
- **Invite-Only:** Sistema de registro restrito. Apenas usuários com link de convite gerado pelo Master podem se cadastrar.
- **Multi-Nível:** Permissões diferenciadas para Administrador Master e Diretores de Clube.

---

## 🛠️ Tecnologias Utilizadas

* **Backend:** Laravel 12 (PHP)
* **Frontend:** Blade Templates + Alpine.js
* **Estilização:** Tailwind CSS
* **Banco de Dados:** PostgreSQL (Suporte a MySQL/SQLite)
* **Build Tool:** Vite

---

## ⚙️ Instalação e Configuração

Siga os passos abaixo para rodar o projeto localmente:

1.  **Clone o repositório**
    ```bash
    git clone [https://github.com/seu-usuario/desbravadores-manager.git](https://github.com/seu-usuario/desbravadores-manager.git)
    cd desbravadores-manager
    ```

2.  **Instale as dependências**
    ```bash
    composer install
    npm install
    ```

3.  **Configure o ambiente**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Configure as credenciais do seu banco de dados no arquivo `.env`.*

4.  **Popule o Banco de Dados (Essencial)**
    Este comando cria as tabelas e insere dados fictícios realistas (Membros, Financeiro, Ranking, etc.) para teste.
    ```bash
    php artisan migrate:fresh --seed
    ```

5.  **Inicie o servidor**
    ```bash
    npm run dev
    # Em outro terminal:
    php artisan serve
    ```

---

## 🔑 Acesso ao Sistema

Após rodar o comando `php artisan migrate:fresh --seed`, os seguintes usuários serão criados automaticamente:

| Perfil | Email | Senha | Função |
| :--- | :--- | :--- | :--- |
| **Diretor (Recomendado)** | `diretor@clube.com` | `password` | Acesso completo ao Clube populado |
| **Master Admin** | `admin@desbravadores.com` | `password` | Gestão de convites do sistema |

> **Dica:** Acesse com o usuário **Diretor** para ver o Dashboard com o Ranking e os dados financeiros já preenchidos.

---

## 📸 Visão Geral do Projeto

### Dashboard & Ranking
Visualização imediata das melhores unidades e atalhos rápidos.

### Controle de Frequência
Interface otimizada para chamada rápida durante a reunião, calculando pontos automaticamente.

### Financeiro
Gestão clara de quem pagou e quem está pendente na mensalidade.

---

## 🤝 Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir Issues ou enviar Pull Requests.

1.  Faça um Fork do projeto
2.  Crie uma Branch para sua Feature (`git checkout -b feature/NovaFeature`)
3.  Faça o Commit (`git commit -m 'Add: Nova Feature'`)
4.  Faça o Push (`git push origin feature/NovaFeature`)
5.  Abra um Pull Request

---

<p align="center">
  Desenvolvido com ❤️ para Desbravadores. <br>
  <i>"Salvar do pecado e guiar no serviço."</i>
</p>