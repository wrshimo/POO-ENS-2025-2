-- Cria o banco de dados se ele não existir.
CREATE DATABASE IF NOT EXISTS `tarefas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tarefas`;

-- CUIDADO: O restante do script apagará as tabelas existentes se elas já existirem.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionarios`
--

DROP TABLE IF EXISTS `tarefas`;
DROP TABLE IF EXISTS `funcionarios`;

CREATE TABLE `funcionarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `login` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL, -- Lembre-se: em produção, use hashes!
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Inserindo dados de exemplo na tabela `funcionarios`
--

INSERT INTO `funcionarios` (`nome`, `login`, `senha`) VALUES
('Administrador', 'admin', 'admin'),
('João Silva', 'joao', 'senha123'),
('Maria Souza', 'maria', 'qwerty');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prazo` date NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `concluido` tinyint(1) NOT NULL DEFAULT 0,
  `id_funcionario` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_funcionario` (`id_funcionario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Adicionando restrições para a tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id`);

COMMIT;