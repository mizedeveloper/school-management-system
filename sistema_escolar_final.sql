-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 02:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_escolar_final`
--

-- --------------------------------------------------------

--
-- Table structure for table `alunos_turmas`
--

CREATE TABLE `alunos_turmas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `atividades`
--

CREATE TABLE `atividades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `data_envio` datetime DEFAULT current_timestamp(),
  `data_limite` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `atividades`
--

INSERT INTO `atividades` (`id`, `titulo`, `descricao`, `arquivo`, `professor_id`, `turma_id`, `data_envio`, `data_limite`) VALUES
(24, 'atividade1', 'descricao1', '', 52, 11, '2026-01-15 18:41:15', '2069-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `ocorrencias`
--

CREATE TABLE `ocorrencias` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `data` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professores_turmas`
--

CREATE TABLE `professores_turmas` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `respostas_atividades`
--

CREATE TABLE `respostas_atividades` (
  `id` int(11) NOT NULL,
  `atividade_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `arquivo` varchar(255) NOT NULL,
  `data_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `turmas`
--

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` char(8) NOT NULL,
  `emoji` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `turmas`
--

INSERT INTO `turmas` (`id`, `nome`, `codigo`, `emoji`) VALUES
(11, 'turma1', '9W5KKYXE', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','professor','aluno') NOT NULL,
  `tema` varchar(20) DEFAULT 'claro',
  `senha_visivel` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `tipo`, `tema`, `senha_visivel`) VALUES
(3, 'Administrador', 'admin', '$2y$10$NT9V5GmGRC1jdHYXZcsfFeHG.eFS9dXAi0DZ9OHlzKvBCOWhwrUj.', 'admin', 'claro', NULL),
(52, 'professor', 'p1', '$2y$10$lpf1gNJOlqsS1e.DpKrpsuAfb27acOuU2x8kNBTpvem1Aa.VttUC2', 'professor', 'claro', 'hryaG1M0zZrpSUg4A+HEFA=='),
(53, 'aluno', 'a1', '$2y$10$EL.1yfbYUdCva4H9BgNCo.PUBweTx855FietSJHvJfS6kYrj1JXJ2', 'aluno', 'claro', 'uftgbbM5DYoenMwQF+SLUg==');

-- --------------------------------------------------------

--
-- Table structure for table `usuario_turma`
--

CREATE TABLE `usuario_turma` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `tipo` enum('professor','aluno') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuario_turma`
--

INSERT INTO `usuario_turma` (`id`, `usuario_id`, `turma_id`, `tipo`) VALUES
(26, 52, 11, 'professor'),
(27, 53, 11, 'aluno');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alunos_turmas`
--
ALTER TABLE `alunos_turmas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_aluno_turma` (`aluno_id`,`turma_id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Indexes for table `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Indexes for table `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno_id` (`aluno_id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `professores_turmas`
--
ALTER TABLE `professores_turmas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_professor_turma` (`professor_id`,`turma_id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Indexes for table `respostas_atividades`
--
ALTER TABLE `respostas_atividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `atividade_id` (`atividade_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Indexes for table `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indexes for table `usuario_turma`
--
ALTER TABLE `usuario_turma`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_turma` (`usuario_id`,`turma_id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alunos_turmas`
--
ALTER TABLE `alunos_turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `professores_turmas`
--
ALTER TABLE `professores_turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `respostas_atividades`
--
ALTER TABLE `respostas_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `usuario_turma`
--
ALTER TABLE `usuario_turma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alunos_turmas`
--
ALTER TABLE `alunos_turmas`
  ADD CONSTRAINT `alunos_turmas_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alunos_turmas_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `atividades_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `atividades_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD CONSTRAINT `ocorrencias_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ocorrencias_ibfk_2` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `professores_turmas`
--
ALTER TABLE `professores_turmas`
  ADD CONSTRAINT `professores_turmas_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `professores_turmas_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `respostas_atividades`
--
ALTER TABLE `respostas_atividades`
  ADD CONSTRAINT `respostas_atividades_ibfk_1` FOREIGN KEY (`atividade_id`) REFERENCES `atividades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `respostas_atividades_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usuario_turma`
--
ALTER TABLE `usuario_turma`
  ADD CONSTRAINT `usuario_turma_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_turma_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
