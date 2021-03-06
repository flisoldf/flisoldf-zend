-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Máquina: localhost
-- Data de Criação: 29-Abr-2014 às 08:19
-- Versão do servidor: 5.5.33-31.1
-- versão do PHP: 5.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de Dados: `sistema_flisol`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `atividade`
--

CREATE TABLE IF NOT EXISTS `atividade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_atividade` int(11) DEFAULT NULL,
  `id_palestrante` int(11) NOT NULL,
  `id_sala` int(11) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `dt_cadastro` datetime NOT NULL,
  `situacao` char(1) NOT NULL,
  `qt_horas` time DEFAULT NULL,
  `fk_unidade` int(11) DEFAULT NULL,
  `fk_atividade_periodo` int(11) DEFAULT NULL,
  `hora_inicio` time NOT NULL,
  `link_gdocs_proposta` varchar(255) NOT NULL,
  `link_gdocs_apresentacao` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_atividade_usuario1` (`id_palestrante`),
  KEY `fk_atividade_sala1` (`id_sala`),
  KEY `fk_atividade_unidade` (`fk_unidade`),
  KEY `fk_atividade_periodo` (`fk_atividade_periodo`),
  KEY `tipo_atividade` (`tipo_atividade`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `atividade_periodo`
--

CREATE TABLE IF NOT EXISTS `atividade_periodo` (
  `id_atividade_periodo` int(11) NOT NULL AUTO_INCREMENT,
  `st_nome` varchar(20) NOT NULL,
  PRIMARY KEY (`id_atividade_periodo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `inscricao`
--

CREATE TABLE IF NOT EXISTS `inscricao` (
  `id_atividade` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `dt_cadastro` datetime NOT NULL,
  `presenca` varchar(45) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`id_atividade`,`id_usuario`),
  KEY `fk_atividade_has_usuario_atividade` (`id_atividade`),
  KEY `fk_atividade_has_usuario_usuario1` (`id_usuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfil`
--

CREATE TABLE IF NOT EXISTS `perfil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `presenca`
--

CREATE TABLE IF NOT EXISTS `presenca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_primeira_presenca` datetime DEFAULT NULL,
  `data_segunda_presenca` datetime DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `sorteado` int(11) NOT NULL DEFAULT '0',
  `premio` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`,`data_primeira_presenca`,`data_segunda_presenca`,`evento_id`),
  KEY `sorteado` (`sorteado`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=370 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `sala`
--

CREATE TABLE IF NOT EXISTS `sala` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `bloco` varchar(4) DEFAULT NULL,
  `complemento` varchar(45) DEFAULT NULL,
  `qt_pessoas` int(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `uf`
--

CREATE TABLE IF NOT EXISTS `uf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) DEFAULT NULL,
  `codigo` char(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `unidade`
--

CREATE TABLE IF NOT EXISTS `unidade` (
  `id_unidade` int(11) NOT NULL AUTO_INCREMENT,
  `fk_uf` int(11) NOT NULL,
  `fk_usuario_responsavel` int(11) NOT NULL,
  `st_nome` varchar(150) NOT NULL,
  `st_cidade` varchar(45) DEFAULT NULL,
  `st_cep` varchar(9) NOT NULL,
  `st_endereco` varchar(250) NOT NULL,
  `dt_cadastro` datetime NOT NULL,
  PRIMARY KEY (`id_unidade`),
  KEY `fk_unidade_uf1` (`fk_uf`),
  KEY `fk_unidade_usuario1` (`fk_usuario_responsavel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE IF NOT EXISTS `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `email` varchar(200) NOT NULL,
  `senha` char(32) NOT NULL,
  `dt_cadastro` datetime NOT NULL,
  `cpf` char(14) NOT NULL,
  `cep` char(9) DEFAULT NULL,
  `cidade` varchar(45) DEFAULT NULL,
  `site` varchar(45) DEFAULT NULL,
  `perfil_id` int(11) NOT NULL,
  `uf_id` int(11) NOT NULL,
  `colaborador` char(1) NOT NULL,
  `facebook` text NOT NULL,
  `twitter` text NOT NULL,
  `minicur` text,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_perfil1` (`perfil_id`),
  KEY `fk_usuario_uf1` (`uf_id`),
  KEY `fk_perfil` (`perfil_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7755 ;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `unidade`
--
ALTER TABLE `unidade`
  ADD CONSTRAINT `unidade_ibfk_1` FOREIGN KEY (`fk_uf`) REFERENCES `uf` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`perfil_id`) REFERENCES `perfil` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`uf_id`) REFERENCES `uf` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
         
