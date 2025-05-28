-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28-Maio-2025 às 22:07
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `banco`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `coin` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cartas`
--

CREATE TABLE `cartas` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `path` varchar(255) NOT NULL,
  `vida` int(11) NOT NULL,
  `ataque1` varchar(50) NOT NULL,
  `ataque1_dano` int(11) NOT NULL,
  `ataque2` varchar(50) NOT NULL,
  `ataque2_dano` int(11) NOT NULL,
  `esquiva` int(11) NOT NULL,
  `critico` int(11) NOT NULL,
  `preco` int(100) NOT NULL,
  `preco_dinheiro` decimal(10,2) DEFAULT 0.00,
  `cor` varchar(20) DEFAULT 'neutro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `cartas`
--

INSERT INTO `cartas` (`id`, `nome`, `path`, `vida`, `ataque1`, `ataque1_dano`, `ataque2`, `ataque2_dano`, `esquiva`, `critico`, `preco`, `preco_dinheiro`, `cor`) VALUES
(1, 'la cucaracha', '/JDB/Assets/img/barata.jpg', 30, 'terror psicologico', 50, '', 0, 40, 10, 50, '5.99', 'psiquico'),
(2, 'Spider-Aranha', '/JDB/Assets/img/aranha.jpg', 30, 'Mordida poderosa', 80, 'Teia', 0, 30, 30, 50, '6.00', 'dark'),
(3, 'Buffalo Bill', '/JDB/Assets/img/bufalo.jpg', 100, 'Manada maluca', 20, '', 0, 5, 20, 60, '8.00', 'luta'),
(4, 'Cobra Coral', '/JDB/Assets/img/cobra.jpg', 30, 'Peçonhenta', 0, 'Navio', 70, 30, 30, 43, '43.00', 'fogo'),
(5, 'Ornitorrincos', '/JDB/Assets/img/ornitorrinco.jpg', 70, 'Rabada D’Job', 20, 'Eletrorrecepção', 0, 15, 25, 100, '10.00', 'agua'),
(6, 'Miquelito', '/JDB/Assets/img/enguia.jpg', 30, 'Habilidade Passiva', 0, 'Chokito', 30, 25, 20, 150, '15.50', 'eletrico'),
(7, 'Kerchak', '/JDB/Assets/img/gorila.jpg', 100, 'Choque de Fúria', 0, 'Pancada', 60, 25, 30, 250, '30.00', 'luta'),
(8, 'Pombo Imperial', '/JDB/Assets/img/harpia.jpg', 80, 'Garra Agarra', 60, '', 0, 20, 25, 180, '18.00', 'normal'),
(9, 'Moto-Moto', '/JDB/Assets/img/hipopotamo.jpg', 120, '6 se pega', 70, '0', 0, 10, 15, 200, '25.00', 'agua'),
(10, 'Pumba La Pumba', '/JDB/Assets/img/javali.jpg', 70, 'Rá pá lá', 50, '0', 0, 15, 20, 100, '10.00', 'luta'),
(11, 'Simba D’King', '/JDB/Assets/img/leao.jpg', 90, 'Rugido', 10, 'Ranca-Pedaço', 40, 15, 30, 250, '25.00', 'fogo'),
(12, 'Turbo', '/JDB/Assets/img/lesma.jpg', 30, 'Ataque Final Maligno 200', 200, '0', 0, 5, 5, 300, '35.00', 'normal'),
(13, 'Rato Anjo', '/JDB/Assets/img/morcego.jpg', 60, 'Bat-Asa', 30, 'Vampirismo', 10, 20, 15, 80, '8.50', 'dark'),
(14, 'Aquela Pintada', '/JDB/Assets/img/onca.jpg', 80, 'Na mata', 0, 'Ranca-Pedago', 40, 25, 35, 300, '35.00', 'planta'),
(15, 'Sandslash', '/JDB/Assets/img/pangolim.jpg', 120, 'Cravada', 10, '0', 0, 30, 25, 200, '22.50', 'normal'),
(16, 'Big Mouse', '/JDB/Assets/img/rato.jpg', 50, 'Rábada', 20, 'Leptospirose', 10, 25, 10, 75, '7.50', 'psiquico'),
(17, 'Dirty feet', '/JDB/Assets/img/sapo.jpg', 50, 'Lero Lero Lero', 30, 'Venenim', 10, 10, 20, 150, '15.00', 'dark'),
(18, 'Bruce', '/JDB/Assets/img/tubarao.jpg', 90, 'No Faro e no Fino', 30, '0', 0, 15, 35, 350, '40.00', 'agua'),
(19, 'Mengo rei', '/JDB/Assets/img/urubu.jpg', 60, 'Carniça', 0, 'Bicadinha', 30, 10, 15, 200, '20.00', 'normal'),
(20, 'Bernie Ernie', '/JDB/Assets/img/viva.jpg', 70, 'Habilidade Passiva', 50, '0', 0, 10, 15, 200, '25.00', 'eletrico'),
(21, 'Urso Pardo com Curso', '/JDB/Assets/img/urso.jpg', 110, 'Patada Monstra', 40, 'Hibernar', 0, 15, 25, 300, '35.00', 'planta');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cartas_usuario`
--

CREATE TABLE `cartas_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_carta` int(11) NOT NULL,
  `equipada` tinyint(1) DEFAULT 0,
  `preco_dinheiro` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `cartas_usuario`
--

INSERT INTO `cartas_usuario` (`id`, `id_usuario`, `id_carta`, `equipada`, `preco_dinheiro`) VALUES
(8, 1, 12, 0, '0.00'),
(9, 1, 8, 0, '0.00'),
(10, 1, 15, 0, '0.00'),
(11, 1, 11, 0, '0.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `cartoes`
--

CREATE TABLE `cartoes` (
  `id_cartao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `portador` varchar(100) NOT NULL,
  `validade` varchar(7) NOT NULL,
  `cvv` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_transacoes`
--

CREATE TABLE `historico_transacoes` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_transacao` enum('pacote','carta','moeda','icone') NOT NULL,
  `id_item` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `metodo_pagamento` enum('moedas','cartao','pix') NOT NULL,
  `data_transacao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `icones_usuario`
--

CREATE TABLE `icones_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_icone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `icones_usuario`
--

INSERT INTO `icones_usuario` (`id`, `id_usuario`, `id_icone`) VALUES
(1, 1, 2),
(2, 1, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `img_perfil`
--

CREATE TABLE `img_perfil` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `path` varchar(200) NOT NULL,
  `preco` int(100) NOT NULL,
  `preco_dinheiro` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `img_perfil`
--

INSERT INTO `img_perfil` (`id`, `nome`, `path`, `preco`, `preco_dinheiro`) VALUES
(1, 'Red', '/JDB/Assets/img/red.gif', 900, '25.00'),
(2, 'Gavião j5', '/JDB/Assets/img/gaviaoj5.jfif', 500, '10.00'),
(3, 'Cachorro de Juliete', '/JDB/Assets/img/cachorrojuliete.PNG', 600, '12.00'),
(4, 'Pikachurros', '/JDB/Assets/img/pikachu.gif', 750, '16.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pacote`
--

CREATE TABLE `pacote` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `path` varchar(255) NOT NULL,
  `preco` int(100) NOT NULL,
  `preco_dinheiro` decimal(10,2) DEFAULT 0.00,
  `cor` varchar(20) DEFAULT 'todos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `pacote`
--

INSERT INTO `pacote` (`id`, `nome`, `descricao`, `path`, `preco`, `preco_dinheiro`, `cor`) VALUES
(1, 'Pacote Normal', 'Pacote com qualquer carta aleatória', '/JDB/Assets/img/pacoteGeral.png', 700, '50.00', 'normal'),
(2, 'Pacote Fogo', 'Pacote com cartas de Fogo', '/JDB/Assets/img/pacoteCarniforo.png', 1000, '60.00', 'fogo'),
(3, 'Pacote Planta', 'Pacote de cartas de Planta', '/JDB/Assets/img/pacoteHerbiforo.png', 1000, '60.00', 'planta'),
(4, 'Pacote Eletrico', 'Contém 3 cartas aleatórias Eletricas', 'JDB/Assets/img/pacote_vermelho.png', 1000, '60.00', 'eletrico'),
(5, 'Pacote Dark', 'Contém 3 cartas aleatórias de Escuridão', 'JDB/Assets/img/pacote_verde.png', 1000, '60.00', 'dark'),
(6, 'Pacote Agua', 'Contém 3 cartas aleatórias Aquaticas', 'JDB/Assets/img/pacote_azul.png', 1000, '60.00', 'agua'),
(7, 'Pacote Luta', 'Contém 3 cartas aleatórias Lutadoras', 'JDB/Assets/img/pacote_amarelo.png', 1000, '60.00', 'luta'),
(8, 'Pacote Psiquico', 'Contém 3 cartas aleatórias Psiquicas', 'JDB/Assets/img/pacote_roxo.png', 1000, '60.00', 'psiquico');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pacotes_moedas`
--

CREATE TABLE `pacotes_moedas` (
  `id_pacote` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `nome_pacote` varchar(100) NOT NULL,
  `quantidade_moedas` int(11) NOT NULL,
  `valor_dinheiro` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `pacotes_moedas`
--

INSERT INTO `pacotes_moedas` (`id_pacote`, `path`, `nome_pacote`, `quantidade_moedas`, `valor_dinheiro`) VALUES
(1, '/JDB/Assets/img/moedaPouca.png', 'Pacote Básico', 150, '15.00'),
(2, '/JDB/Assets/img/moedaMedia.png', 'Pacote Médio ', 300, '30.00'),
(3, '/JDB/Assets/img/moedaMuita.png', 'Pacote Grande', 1000, '80.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pacote_cartas`
--

CREATE TABLE `pacote_cartas` (
  `id` int(11) NOT NULL,
  `id_pacote` int(11) NOT NULL,
  `id_carta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `senha` varchar(40) NOT NULL,
  `coin` int(11) NOT NULL DEFAULT 0,
  `id_icone_perfil` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id`, `nome`, `email`, `senha`, `coin`, `id_icone_perfil`) VALUES
(1, 'Gustavo', 'gu@gmail.com', '123', 2300, 2),
(2, 'João', 'joao@gmail.com', '123', 0, NULL),
(3, 'Victor', 'Vic@gmail.com', '123', 0, NULL),
(4, 'Gustavo', 'gu@gmail.com', '123', 5000, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `cartas`
--
ALTER TABLE `cartas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `cartas_usuario`
--
ALTER TABLE `cartas_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_carta` (`id_carta`);

--
-- Índices para tabela `cartoes`
--
ALTER TABLE `cartoes`
  ADD PRIMARY KEY (`id_cartao`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `historico_transacoes`
--
ALTER TABLE `historico_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `icones_usuario`
--
ALTER TABLE `icones_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_icone` (`id_icone`);

--
-- Índices para tabela `img_perfil`
--
ALTER TABLE `img_perfil`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `pacote`
--
ALTER TABLE `pacote`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `pacotes_moedas`
--
ALTER TABLE `pacotes_moedas`
  ADD PRIMARY KEY (`id_pacote`);

--
-- Índices para tabela `pacote_cartas`
--
ALTER TABLE `pacote_cartas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pacote` (`id_pacote`),
  ADD KEY `id_carta` (`id_carta`);

--
-- Índices para tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cartas`
--
ALTER TABLE `cartas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `cartas_usuario`
--
ALTER TABLE `cartas_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `cartoes`
--
ALTER TABLE `cartoes`
  MODIFY `id_cartao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_transacoes`
--
ALTER TABLE `historico_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `icones_usuario`
--
ALTER TABLE `icones_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `img_perfil`
--
ALTER TABLE `img_perfil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `pacote`
--
ALTER TABLE `pacote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `pacotes_moedas`
--
ALTER TABLE `pacotes_moedas`
  MODIFY `id_pacote` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pacote_cartas`
--
ALTER TABLE `pacote_cartas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `cartas_usuario`
--
ALTER TABLE `cartas_usuario`
  ADD CONSTRAINT `cartas_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `cartas_usuario_ibfk_2` FOREIGN KEY (`id_carta`) REFERENCES `cartas` (`id`);

--
-- Limitadores para a tabela `cartoes`
--
ALTER TABLE `cartoes`
  ADD CONSTRAINT `cartoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Limitadores para a tabela `historico_transacoes`
--
ALTER TABLE `historico_transacoes`
  ADD CONSTRAINT `historico_transacoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Limitadores para a tabela `icones_usuario`
--
ALTER TABLE `icones_usuario`
  ADD CONSTRAINT `icones_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `icones_usuario_ibfk_2` FOREIGN KEY (`id_icone`) REFERENCES `img_perfil` (`id`);

--
-- Limitadores para a tabela `pacote_cartas`
--
ALTER TABLE `pacote_cartas`
  ADD CONSTRAINT `pacote_cartas_ibfk_1` FOREIGN KEY (`id_pacote`) REFERENCES `pacote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pacote_cartas_ibfk_2` FOREIGN KEY (`id_carta`) REFERENCES `cartas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
