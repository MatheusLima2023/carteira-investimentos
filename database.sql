-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS `carteira_investimento` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `carteira_investimento`;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insere categorias padrão
INSERT INTO `categorias` (`nome`) VALUES
('Ações'),
('FIIs'),
('Renda Fixa'),
('Criptomoedas'),
('Internacional')
ON DUPLICATE KEY UPDATE `nome`=`nome`;

-- Tabela de Instituições (Corretoras/Bancos)
CREATE TABLE IF NOT EXISTS `instituicoes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insere instituições padrão
INSERT INTO `instituicoes` (`nome`) VALUES
('XP Investimentos'),
('BTG Pactual'),
('NuInvest'),
('Binance'),
('C6 Bank')
ON DUPLICATE KEY UPDATE `nome`=`nome`;

-- Tabela de Ativos / Aportes
CREATE TABLE IF NOT EXISTS `ativos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticker_nome` VARCHAR(20) NOT NULL,
  `valor_aportado` DECIMAL(10,2) NOT NULL,
  `categoria_id` INT NOT NULL,
  `instituicao_id` INT NOT NULL,
  `data_aporte` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`),
  FOREIGN KEY (`instituicao_id`) REFERENCES `instituicoes`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;