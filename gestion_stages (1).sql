-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : lun. 03 août 2026 à 02:09
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_stages`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidature`
--

CREATE TABLE `candidature` (
  `id_candidature` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_offre` int(11) NOT NULL,
  `date_postulation` datetime DEFAULT current_timestamp(),
  `statut_candidature` enum('en_attente','valide_par_entreprise','en_attente_validation_admin','acceptee','refusee') DEFAULT 'en_attente',
  `documents_uploaded` tinyint(1) DEFAULT 0,
  `type_stage` enum('academique','professionnel') DEFAULT NULL,
  `rapport_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `candidature`
--

INSERT INTO `candidature` (`id_candidature`, `id_etudiant`, `id_offre`, `date_postulation`, `statut_candidature`, `documents_uploaded`, `type_stage`, `rapport_pdf`) VALUES
(1, 1, 1, '2026-07-15 16:59:34', 'refusee', 0, NULL, NULL),
(2, 1, 2, '2026-07-15 17:21:09', 'acceptee', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `configuration`
--

CREATE TABLE `configuration` (
  `cle` varchar(50) NOT NULL,
  `valeur` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `configuration`
--

INSERT INTO `configuration` (`cle`, `valeur`) VALUES
('annee_scolaire', '2025-2026'),
('autoriser_inscriptions', '1'),
('limite_candidatures', '5'),
('taille_max_pdf', '5');

-- --------------------------------------------------------

--
-- Structure de la table `convention`
--

CREATE TABLE `convention` (
  `id_convention` int(11) NOT NULL,
  `id_stage` int(11) NOT NULL,
  `fichier_pdf` varchar(255) NOT NULL,
  `date_generation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents_stage`
--

CREATE TABLE `documents_stage` (
  `id` int(11) NOT NULL,
  `id_candidature` int(11) DEFAULT NULL,
  `type_document` varchar(50) DEFAULT NULL,
  `chemin_fichier` varchar(255) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
  `id_user` int(11) NOT NULL,
  `siege_social` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `entreprise`
--

INSERT INTO `entreprise` (`id_user`, `siege_social`) VALUES
(2, 'Douala');

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
  `id_user` int(11) NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `id_enseignant` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`id_user`, `ville`, `id_enseignant`) VALUES
(1, 'Douala', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(30) DEFAULT NULL,
  `id_candidature` int(11) DEFAULT NULL,
  `id_stage` int(11) DEFAULT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offre_stage`
--

CREATE TABLE `offre_stage` (
  `id_offre` int(11) NOT NULL,
  `id_entreprise` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `date_limite` date DEFAULT NULL,
  `statut` enum('ouverte','fermee') DEFAULT 'ouverte',
  `lieu` varchar(255) NOT NULL,
  `type_stage` varchar(100) NOT NULL,
  `duree` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `offre_stage`
--

INSERT INTO `offre_stage` (`id_offre`, `id_entreprise`, `titre`, `description`, `date_limite`, `statut`, `lieu`, `type_stage`, `duree`) VALUES
(1, 2, 'developpeur frontend', 'êtes vous disponible', '2026-07-16', 'fermee', 'Doouala', 'Académique', '3 mois'),
(2, 2, 'developpeur frontend', 'êtes-vous capable', '2026-07-21', 'ouverte', 'Doouala', 'Académique', '3 mois');

-- --------------------------------------------------------

--
-- Structure de la table `rapport`
--

CREATE TABLE `rapport` (
  `id_rapport` int(11) NOT NULL,
  `id_stage` int(11) NOT NULL,
  `fichier_pdf` varchar(255) NOT NULL,
  `date_depot` datetime DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stage`
--

CREATE TABLE `stage` (
  `id_stage` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_entreprise` int(11) NOT NULL,
  `id_offre` int(11) NOT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `statut_stage` enum('a_venir','en_cours','termine','interrompu') DEFAULT 'a_venir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','sous_admin','entreprise','etudiant') NOT NULL,
  `nom_complet` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `email`, `password`, `role`, `nom_complet`, `telephone`, `adresse`, `created_at`) VALUES
(1, 'rose@gmail.com', '$2y$10$fuF/VLUGq65nW6ftKTccROajOkF/Tq./oAd2Yi.iFTKr5qb7GioHm', 'etudiant', 'Rose', '677889977', 'Rue 22', '2026-07-15 12:29:36'),
(2, 'azur@gmail.com', '$2y$10$x3JnrHGgICEF31KqCa9K8emRZoAF09YkH8xbeiWywGUxckE5ohiQO', 'entreprise', 'Azur', '688225533', 'Rue 23', '2026-07-15 12:30:51'),
(4, 'admin@gestionstages.com', '$2y$10$.tP9pbmZm8Q2nn/.osl4dew4Asy//DSxPsd0K7lyo4kHDaSMRCi62', 'admin', 'Administrateur Principal', '+237682336519', 'Douala, Cameroun', '2026-07-15 12:47:26');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `candidature`
--
ALTER TABLE `candidature`
  ADD PRIMARY KEY (`id_candidature`),
  ADD KEY `fk_cand_etudiant` (`id_etudiant`),
  ADD KEY `fk_cand_offre` (`id_offre`);

--
-- Index pour la table `configuration`
--
ALTER TABLE `configuration`
  ADD PRIMARY KEY (`cle`);

--
-- Index pour la table `convention`
--
ALTER TABLE `convention`
  ADD PRIMARY KEY (`id_convention`),
  ADD KEY `fk_convention_stage` (`id_stage`);

--
-- Index pour la table `documents_stage`
--
ALTER TABLE `documents_stage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_candidature` (`id_candidature`);

--
-- Index pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `fk_enseignant` (`id_enseignant`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_notif_candidature` (`id_candidature`),
  ADD KEY `fk_notif_stage` (`id_stage`);

--
-- Index pour la table `offre_stage`
--
ALTER TABLE `offre_stage`
  ADD PRIMARY KEY (`id_offre`),
  ADD KEY `fk_offre_entreprise` (`id_entreprise`);

--
-- Index pour la table `rapport`
--
ALTER TABLE `rapport`
  ADD PRIMARY KEY (`id_rapport`),
  ADD KEY `fk_rapport_stage` (`id_stage`);

--
-- Index pour la table `stage`
--
ALTER TABLE `stage`
  ADD PRIMARY KEY (`id_stage`),
  ADD KEY `fk_stage_etudiant` (`id_etudiant`),
  ADD KEY `fk_stage_entreprise` (`id_entreprise`),
  ADD KEY `fk_stage_offre` (`id_offre`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `candidature`
--
ALTER TABLE `candidature`
  MODIFY `id_candidature` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `convention`
--
ALTER TABLE `convention`
  MODIFY `id_convention` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents_stage`
--
ALTER TABLE `documents_stage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `offre_stage`
--
ALTER TABLE `offre_stage`
  MODIFY `id_offre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `rapport`
--
ALTER TABLE `rapport`
  MODIFY `id_rapport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stage`
--
ALTER TABLE `stage`
  MODIFY `id_stage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `candidature`
--
ALTER TABLE `candidature`
  ADD CONSTRAINT `fk_cand_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cand_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre_stage` (`id_offre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `convention`
--
ALTER TABLE `convention`
  ADD CONSTRAINT `fk_convention_stage` FOREIGN KEY (`id_stage`) REFERENCES `stage` (`id_stage`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_stage`
--
ALTER TABLE `documents_stage`
  ADD CONSTRAINT `documents_stage_ibfk_1` FOREIGN KEY (`id_candidature`) REFERENCES `candidature` (`id_candidature`) ON DELETE CASCADE;

--
-- Contraintes pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD CONSTRAINT `fk_entreprise_user` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD CONSTRAINT `fk_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `utilisateur` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_etudiant_user` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_candidature` FOREIGN KEY (`id_candidature`) REFERENCES `candidature` (`id_candidature`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_stage` FOREIGN KEY (`id_stage`) REFERENCES `stage` (`id_stage`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `offre_stage`
--
ALTER TABLE `offre_stage`
  ADD CONSTRAINT `fk_offre_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rapport`
--
ALTER TABLE `rapport`
  ADD CONSTRAINT `fk_rapport_stage` FOREIGN KEY (`id_stage`) REFERENCES `stage` (`id_stage`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stage`
--
ALTER TABLE `stage`
  ADD CONSTRAINT `fk_stage_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_user`),
  ADD CONSTRAINT `fk_stage_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiant` (`id_user`),
  ADD CONSTRAINT `fk_stage_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre_stage` (`id_offre`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
