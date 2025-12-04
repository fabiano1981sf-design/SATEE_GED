-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 04, 2025 at 05:07 PM
-- Server version: 5.7.24
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gerencia_atv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `atividades`
--

CREATE TABLE `atividades` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text,
  `status` enum('ativa','inativa') DEFAULT 'ativa',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categorias_ged`
--

CREATE TABLE `categorias_ged` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categorias_ged`
--

INSERT INTO `categorias_ged` (`id`, `nome`) VALUES
(1, 'Certificado'),
(4, 'Comprovante de Pagamento'),
(2, 'Ficha de Inscrição'),
(3, 'Material Didático'),
(5, 'Outros');

-- --------------------------------------------------------

--
-- Table structure for table `documentos_ged`
--

CREATE TABLE `documentos_ged` (
  `id` int(11) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `nome_salvo` varchar(255) NOT NULL COMMENT 'Nome único do arquivo no servidor',
  `caminho_arquivo` varchar(512) NOT NULL COMMENT 'Caminho completo no servidor',
  `id_categoria` int(11) NOT NULL,
  `descricao` text,
  `data_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_usuario_upload` int(11) DEFAULT NULL COMMENT 'ID do usuário que realizou o upload (Opcional, se houver sistema de usuários)',
  `id_curso` int(11) DEFAULT NULL COMMENT 'ID do curso associado (Para integração com SATEE)',
  `id_participante` int(11) DEFAULT NULL COMMENT 'ID do participante associado (Para integração com SATEE)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `membros`
--

CREATE TABLE `membros` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `status_membro` enum('ativo','ausente','licenca','desligado') NOT NULL DEFAULT 'ativo',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `membros`
--

INSERT INTO `membros` (`id`, `nome`, `cargo`, `email`, `foto_url`, `status_membro`, `data_cadastro`) VALUES
(1, 'Fabiano Silva', 'Administrador | Desenvolvedor', 'fabiano@empresa.com', 'uploads/fotos_membros/membro_69316ba73c530.jpg', 'ativo', '2025-12-04 10:52:28'),
(2, 'Usuário Teste 1', 'Analista de Sistemas', 'usuario1@empresa.com', 'uploads/fotos_membros/membro_69316bb6ebc4b.png', 'ativo', '2025-12-04 10:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text,
  `prioridade` enum('baixa','media','alta') NOT NULL DEFAULT 'baixa',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prazo` date NOT NULL,
  `status_tarefa` enum('pendente','em_andamento','concluido','atrasado') NOT NULL DEFAULT 'pendente',
  `responsavel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tarefas`
--

INSERT INTO `tarefas` (`id`, `titulo`, `descricao`, `prioridade`, `data_criacao`, `prazo`, `status_tarefa`, `responsavel_id`) VALUES
(10, 'Atualização de Função', 'teste', 'baixa', '2025-12-04 13:36:51', '2025-12-03', 'pendente', 1),
(11, 'teste calendário', 'teste de codigo fonte', 'baixa', '2025-12-04 13:55:42', '2025-12-05', 'concluido', 2),
(12, 'teste', '123', 'baixa', '2025-12-04 14:11:02', '2025-12-12', 'em_andamento', 2);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `perfil` enum('admin','user') NOT NULL DEFAULT 'user',
  `foto_perfil_url` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `perfil`, `foto_perfil_url`, `data_cadastro`) VALUES
(1, 'Fabiano', 'info@kld.com.br', '$2y$10$fhleMY8r0fZIsI3bWu.UbOQEqedVOaEw1/7y1gridL4pb1VmyQD16', 'admin', 'uploads/fotos_usuarios/user_1.jpg', '2025-12-04 11:57:52'),
(2, 'info', 'fabiano@kld.com.br', '$2y$10$lfuHhv1gKHxXISiLx9e86epglvVVVs2HzH77zmXvYJ1JNF484gXNK', 'admin', 'uploads/fotos_usuarios/novo_user_1764851519.png', '2025-12-04 12:31:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categorias_ged`
--
ALTER TABLE `categorias_ged`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indexes for table `documentos_ged`
--
ALTER TABLE `documentos_ged`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_salvo` (`nome_salvo`),
  ADD UNIQUE KEY `caminho_arquivo` (`caminho_arquivo`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indexes for table `membros`
--
ALTER TABLE `membros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_responsavel` (`responsavel_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorias_ged`
--
ALTER TABLE `categorias_ged`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `documentos_ged`
--
ALTER TABLE `documentos_ged`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membros`
--
ALTER TABLE `membros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documentos_ged`
--
ALTER TABLE `documentos_ged`
  ADD CONSTRAINT `documentos_ged_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_ged` (`id`);

--
-- Constraints for table `tarefas`
--
ALTER TABLE `tarefas`
  ADD CONSTRAINT `fk_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `membros` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
