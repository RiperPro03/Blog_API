-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 24 mars 2023 à 23:12
-- Version du serveur : 10.3.37-MariaDB
-- Version de PHP : 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_r401`
--

-- --------------------------------------------------------

--
-- Structure de la table `aimer`
--

CREATE TABLE `aimer` (
  `id_user` int(255) NOT NULL,
  `id_post` int(255) NOT NULL,
  `is_like` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `aimer`
--

INSERT INTO `aimer` (`id_user`, `id_post`, `is_like`) VALUES
(2, 9, 1),
(2, 10, 1),
(2, 11, 0),
(4, 10, 1),
(4, 7, 1),
(2, 8, 1),
(2, 7, 1),
(3, 7, 0),
(3, 8, 0),
(3, 9, 0),
(3, 10, 0),
(3, 11, 0),
(1, 11, 1);

-- --------------------------------------------------------

--
-- Structure de la table `posts`
--

CREATE TABLE `posts` (
  `id_post` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `date_ajout` date NOT NULL,
  `date_modif` date DEFAULT NULL,
  `id_user` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `posts`
--

INSERT INTO `posts` (`id_post`, `title`, `content`, `date_ajout`, `date_modif`, `id_user`) VALUES
(7, 'Les bienfaits du sport', 'Le sport permet d&#039;améliorer notre condition physique, de réduire le stress et de favoriser le bien-être mental.', '2023-03-24', NULL, 2),
(8, 'Les meilleurs endroits pour voyager en France', 'La France regorge de destinations incroyables, telles que Paris, la Côte d&#039;Azur, les Alpes et la vallée de la Loire.', '2023-03-24', NULL, 2),
(9, 'Cuisine française : les incontournables', 'La gastronomie française est réputée pour ses plats savoureux, tels que le coq au vin, le cassoulet et les crêpes.', '2023-03-24', NULL, 3),
(10, 'Les avantages de la lecture', 'La lecture stimule l\'imagination, enrichit le vocabulaire et améliore la compréhension écrite.', '2023-03-24', NULL, 3),
(11, 'Comment prendre soin de ses plantes d\'intérieur', 'Pour maintenir vos plantes en bonne santé, il est essentiel de leur fournir une lumière adéquate, un arrosage régulier et une bonne nutrition.', '2023-03-24', NULL, 4);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id_user` int(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'modo', '$2y$12$diF4TmiL9SAianydLp71TOsY8zkldFZE/E3PB1WsPdPAxkECLuPCe', 'moderator'),
(2, 'publi1', '$2y$12$Y8aO2vYxmXstuxna27Bym.edkrnHszBJByOOupKg79VwyF.GPV2vO', 'publisher'),
(3, 'publi2', '$2y$12$oABERVHMkNlbf7ge6g3XJeztUbWnOwhjbTLBlsvL0tp.pvky4eNDa', 'publisher'),
(4, 'publi3', '$2y$12$cRqufM2BGC4v8T9gODwsh.2YX8EB0BsT8BNhhaTQ3/xUAX7RzIBf2', 'publisher');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `aimer`
--
ALTER TABLE `aimer`
  ADD PRIMARY KEY (`id_user`,`id_post`),
  ADD KEY `id_post` (`id_post`) USING BTREE;

--
-- Index pour la table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `id_user` (`id_user`) USING BTREE;

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `un_username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `posts`
--
ALTER TABLE `posts`
  MODIFY `id_post` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
