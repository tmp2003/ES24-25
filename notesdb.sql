-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15-Maio-2025 às 19:41
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Configurações de caracteres
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Banco de dados: `notesdb`

-- --------------------------------------------------------
-- Estrutura da tabela `escolas`
-- --------------------------------------------------------

CREATE TABLE `escolas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `escolas`
INSERT INTO `escolas` (`id`, `nome`) VALUES
(1, 'Escola Superior Agrária'),
(2, 'Escola Superior de Artes Aplicadas'),
(3, 'Escola Superior de Educação'),
(4, 'Escola Superior de Saúde Dr. Lopes Dias'),
(5, 'Escola Superior de Gestão'),
(6, 'Escola Superior de Tecnologia');

-- --------------------------------------------------------
-- Estrutura da tabela `userdata`
-- --------------------------------------------------------

CREATE TABLE `userdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `aprovado` tinyint(1) NOT NULL DEFAULT 0,
  `admin` tinyint(2) NOT NULL DEFAULT 0,
  `escola` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_userdata_escola` (`escola`),
  CONSTRAINT `fk_userdata_escola` FOREIGN KEY (`escola`) REFERENCES `escolas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `userdata`
INSERT INTO `userdata` (`id`, `username`, `email`, `password`, `aprovado`, `admin`, `escola`) VALUES
(6, 'Mariana5', 'bcoutinho@ipcb.pt', '$2y$10$WineHAMoN.aYlwWF7lEh8uqYzLf8DnU2immvOambteqGnmT3eOdZS', 1, 2, 6),
(7, 'Tiago123', 'tiago.pinheiro1@ipcb.pt', '$2y$10$xz6nZiVFAwzhpz06bL5uUucTBqmOnVkuIfQvkrTVaNKOs5pbX05gC', 1, 1, 6),
(8, 'Martim3', 'martim.marques@ipcb.pt', '$2y$10$XEhy9IyV9XtLgTNRBBtbqenXQ75mKHaGmaYuBlAevkosJNpBP1sOW', 1, 1, 6),
(9, 'João Maria', 'JMaria@ipcbcampus.pt', '$2y$10$GaoMu/cVnKIGtMXI/jQ3Ve.fVB9nNQ3RlhKh8h5/5/yYR.jTrROi2', 1, 0, 6),
(10, 'PauloLage17', 'paulo.cardoso@ipcb.pt', '$2y$10$V.H1sM5Rjs9RK1wUp8rDeu/QC7Y8rHvsOpgnaTtf9bNsLx.nXtNJC', 1, 2, 6);

-- --------------------------------------------------------
-- Estrutura da tabela `cadeiras`
-- --------------------------------------------------------

CREATE TABLE `cadeiras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `escola_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `escola_id` (`escola_id`),
  CONSTRAINT `cadeiras_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `cadeiras`
INSERT INTO `cadeiras` (`id`, `nome`, `escola_id`) VALUES
(13, 'Agronomia Geral', 1),
(14, 'Biotecnologia', 1),
(15, 'Design Gráfico', 2),
(16, 'Tecnologias Musicais', 2),
(17, 'Didática da Matemática', 3),
(18, 'Psicologia da Educação', 3),
(19, 'Anatomia', 4),
(20, 'Fisioterapia', 4),
(21, 'Gestão de Empresas', 5),
(22, 'Contabilidade', 5),
(23, 'Programação Web', 6),
(24, 'Redes de Computadores', 6);

-- --------------------------------------------------------
-- Estrutura da tabela `notes`
-- --------------------------------------------------------

CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `id_cadeira` int(11) DEFAULT NULL,
  `escola` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `private_status` tinyint(2) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_user_notes` (`user_id`),
  KEY `fk_notes_escola` (`escola`),
  CONSTRAINT `fk_user_notes` FOREIGN KEY (`user_id`) REFERENCES `userdata` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notes_escola` FOREIGN KEY (`escola`) REFERENCES `escolas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `notes`
INSERT INTO `notes` (`id`, `user_id`, `id_cadeira`, `escola`, `title`, `content`, `private_status`, `created_at`, `updated_at`) VALUES
(17, 10, NULL, 6, 'TESTE', 'tA', 0, '2025-05-15 14:16:37', '2025-05-15 14:47:37'),
(19, 10, NULL, 6, '2', '2', 1, '2025-05-15 14:21:06', '2025-05-15 14:21:06');

-- --------------------------------------------------------
-- Estrutura da tabela `note_files`
-- --------------------------------------------------------

CREATE TABLE `note_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `note_id` (`note_id`),
  CONSTRAINT `note_files_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `note_files`
INSERT INTO `note_files` (`id`, `note_id`, `file_path`, `file_type`, `uploaded_at`) VALUES
(20, 19, './docs/10/19/202504141912_capa.jpeg', 'jpeg', '2025-05-15 14:21:06');


CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_comments_note` (`note_id`),
  KEY `fk_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `userdata` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comments` (`note_id`, `user_id`, `comment`, `created_at`) 
VALUES (22, 7, 'Ótimo documento', NOW());

-- Finalizar transação
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;