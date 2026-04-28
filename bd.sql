-- ============================================================
-- 1. CRÉATION DE LA BASE DE DONNÉES
-- ============================================================
CREATE DATABASE IF NOT EXISTS gestion_stages CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_stages;

-- ============================================================
-- 2. TABLES DES UTILISATEURS (Gestion des comptes et profils)
-- ============================================================

-- Table parente : contient 90% des infos communes
CREATE TABLE UTILISATEUR (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'etudiant', 'entreprise') NOT NULL,
    nom_complet VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table fille : Étudiant (spécificité : ville)
CREATE TABLE ETUDIANT (
    id_user INT PRIMARY KEY,
    ville VARCHAR(100),
    CONSTRAINT fk_etudiant_user FOREIGN KEY (id_user) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table fille : Entreprise (spécificité : siège social)
CREATE TABLE ENTREPRISE (
    id_user INT PRIMARY KEY,
    siege_social VARCHAR(100),
    CONSTRAINT fk_entreprise_user FOREIGN KEY (id_user) REFERENCES UTILISATEUR(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. TABLES DU PROCESSUS DE STAGE (Offres et Candidatures)
-- ============================================================

-- Offres publiées par les entreprises
CREATE TABLE OFFRE_STAGE (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    id_entreprise INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    date_limite DATE,
    statut ENUM('ouverte', 'fermee') DEFAULT 'ouverte',
    CONSTRAINT fk_offre_entreprise FOREIGN KEY (id_entreprise) REFERENCES ENTREPRISE(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Candidatures des étudiants aux offres
CREATE TABLE CANDIDATURE (
    id_candidature INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT NOT NULL,
    id_offre INT NOT NULL,
    date_postulation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut_candidature ENUM('en_attente', 'acceptee', 'refusee') DEFAULT 'en_attente',
    CONSTRAINT fk_cand_etudiant FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_user) ON DELETE CASCADE,
    CONSTRAINT fk_cand_offre FOREIGN KEY (id_offre) REFERENCES OFFRE_STAGE(id_offre) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. TABLES DE SUIVI ET DOCUMENTS (Stages, Rapports, Conventions)
-- ============================================================

-- Le stage concret (créé après validation d'une candidature)
CREATE TABLE STAGE (
    id_stage INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT NOT NULL,
    id_entreprise INT NOT NULL,
    id_offre INT NOT NULL,
    date_debut DATE,
    date_fin DATE,
    statut_stage ENUM('a_venir', 'en_cours', 'termine', 'interrompu') DEFAULT 'a_venir',
    CONSTRAINT fk_stage_etudiant FOREIGN KEY (id_etudiant) REFERENCES ETUDIANT(id_user),
    CONSTRAINT fk_stage_entreprise FOREIGN KEY (id_entreprise) REFERENCES ENTREPRISE(id_user),
    CONSTRAINT fk_stage_offre FOREIGN KEY (id_offre) REFERENCES OFFRE_STAGE(id_offre)
) ENGINE=InnoDB;

-- Rapports de fin de stage
CREATE TABLE RAPPORT (
    id_rapport INT AUTO_INCREMENT PRIMARY KEY,
    id_stage INT NOT NULL,
    fichier_pdf VARCHAR(255) NOT NULL,
    date_depot DATETIME DEFAULT CURRENT_TIMESTAMP,
    commentaire TEXT,
    CONSTRAINT fk_rapport_stage FOREIGN KEY (id_stage) REFERENCES STAGE(id_stage) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Conventions de début de stage
CREATE TABLE CONVENTION (
    id_convention INT AUTO_INCREMENT PRIMARY KEY,
    id_stage INT NOT NULL,
    fichier_pdf VARCHAR(255) NOT NULL,
    date_signature DATE,
    CONSTRAINT fk_convention_stage FOREIGN KEY (id_stage) REFERENCES STAGE(id_stage) ON DELETE CASCADE
) ENGINE=InnoDB;