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


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `notesdb`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `cadeiras`
--

CREATE TABLE `cadeiras` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `escola_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `cadeiras`
--

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

--
-- Estrutura da tabela `escolas`
--

CREATE TABLE `escolas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `escolas`
--

INSERT INTO `escolas` (`id`, `nome`) VALUES
(1, 'Escola Superior Agrária'),
(2, 'Escola Superior de Artes Aplicadas'),
(3, 'Escola Superior de Educação'),
(4, 'Escola Superior de Saúde Dr. Lopes Dias'),
(5, 'Escola Superior de Gestão'),
(6, 'Escola Superior de Tecnologia');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id_cadeira` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `private_status` tinyint(2) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `notes`
--

INSERT INTO `notes` (`id`, `user_id`, `id_cadeira`, `title`, `content`, `private_status`, `created_at`, `updated_at`) VALUES
(17, 10, NULL, 'TESTE', 'tA', 0, '2025-05-15 14:16:37', '2025-05-15 14:47:37'),
(19, 10, NULL, '2', '2', 1, '2025-05-15 14:21:06', '2025-05-15 14:21:06');

-- --------------------------------------------------------

--
-- Estrutura da tabela `note_files`
--

CREATE TABLE `note_files` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `note_files`
--

INSERT INTO `note_files` (`id`, `note_id`, `file_path`, `file_type`, `uploaded_at`) VALUES
(20, 19, './docs/10/19/202504141912_capa.jpeg', 'jpeg', '2025-05-15 14:21:06');

-- --------------------------------------------------------

--
-- Estrutura da tabela `userdata`
--

CREATE TABLE `userdata` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `aprovado` tinyint(1) NOT NULL DEFAULT 0,
  `admin` tinyint(2) NOT NULL DEFAULT 0,
  `escola` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `userdata`
--

INSERT INTO `userdata` (`id`, `username`, `email`, `password`, `aprovado`, `admin`, `escola`) VALUES
(6, 'Mariana5', 'bcoutinho@ipcb.pt', '$2y$10$WineHAMoN.aYlwWF7lEh8uqYzLf8DnU2immvOambteqGnmT3eOdZS', 1, 2, 'EST'),
(7, 'Tiago123', 'tiago.pinheiro1@ipcb.pt', '$2y$10$xz6nZiVFAwzhpz06bL5uUucTBqmOnVkuIfQvkrTVaNKOs5pbX05gC', 1, 1, 'EST'),
(8, 'Martim3', 'martim.marques@ipcb.pt', '$2y$10$XEhy9IyV9XtLgTNRBBtbqenXQ75mKHaGmaYuBlAevkosJNpBP1sOW', 1, 1, 'EST'),
(9, 'João Maria', 'JMaria@ipcbcampus.pt', '$2y$10$GaoMu/cVnKIGtMXI/jQ3Ve.fVB9nNQ3RlhKh8h5/5/yYR.jTrROi2', 1, 0, 'EST'),
(10, 'PauloLage17', 'paulo.cardoso@ipcb.pt', '$2y$10$V.H1sM5Rjs9RK1wUp8rDeu/QC7Y8rHvsOpgnaTtf9bNsLx.nXtNJC', 1, 2, 'EST'),
(15, 'Jesus', 'tiago.pinheiro1@ipcbcampus.pt', '$2y$10$h5ZYbD5hip/ACHwLTCVV5u3.Uu7p/fLdmgtFhp/ZDqArRx2jin5IW', 0, 0, 'EST'),
(17, 'PauloAluno', 'paulo.cardoso@ipcbcampus.pt', '$2y$10$jRWLlvcPOfTuLdZAWPobbeX0fLp8J3aAtEozRYqFjT9ZPm0jxNTGS', 1, 0, 'EST');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `cadeiras`
--
ALTER TABLE `cadeiras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escola_id` (`escola_id`);

--
-- Índices para tabela `escolas`
--
ALTER TABLE `escolas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_notes` (`user_id`);

--
-- Índices para tabela `note_files`
--
ALTER TABLE `note_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `note_id` (`note_id`);

--
-- Índices para tabela `userdata`
--
ALTER TABLE `userdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cadeiras`
--
ALTER TABLE `cadeiras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `escolas`
--
ALTER TABLE `escolas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `note_files`
--
ALTER TABLE `note_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `userdata`
--
ALTER TABLE `userdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `cadeiras`
--
ALTER TABLE `cadeiras`
  ADD CONSTRAINT `cadeiras_ibfk_1` FOREIGN KEY (`escola_id`) REFERENCES `escolas` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_user_notes` FOREIGN KEY (`user_id`) REFERENCES `userdata` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `note_files`
--
ALTER TABLE `note_files`
  ADD CONSTRAINT `note_files_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
