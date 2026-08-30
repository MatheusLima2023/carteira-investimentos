# 📊 Carteira de Investimentos

Sistema web responsivo para gerenciamento de aportes financeiros, alocação de patrimônio e controle de ativos por categorias e instituições financeiras.

---

## 🚀 Funcionalidades

* **Painel Geral:** Exibição do patrimônio total investido e contagem de operações.
* **Gráfico Dinâmico:** Visualização da distribuição percentual por categoria (Ações, FIIs, Renda Fixa, etc.) via Chart.js.
* **Cadastro de Aportes:** Registro de novos investimentos vinculados a corretoras e categorias.
* **Gestão de Instituições:** Modal dinâmico para cadastrar novos bancos ou corretoras sem mexer no banco de dados.
* **Exclusão de Registros:** Gerenciamento direto na tabela principal com confirmação de ação.

---

## 🛠️ Tecnologias Utilizadas

* **Front-end:** HTML5, CSS3, Bootstrap 5, Chart.js
* **Back-end:** PHP 8.x
* **Banco de Dados:** MySQL / MariaDB (via XAMPP / phpMyAdmin)

---

## 📁 Estrutura do Banco de Dados

O banco foi projetado com suporte a relacionamento entre tabelas para evitar redundância de dados:

* `ativos` (id, ticker_nome, valor_aportado, categoria_id, instituicao_id, data_aporte)
* `categorias` (id, nome)
* `instituicoes` (id, nome)

---

## 🔧 Como Executar o Projeto Localmente

1. **Clone o repositório:**
   ```bash
   git clone [https://github.com/SEU_USUARIO/carteira-investimentos.git](https://github.com/SEU_USUARIO/carteira-investimentos.git)