-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 14 oct. 2026 à 11:10
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sat_comp`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidats`
--

CREATE TABLE `candidats` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(20) NOT NULL COMMENT 'Ex : CAND-2026-0001',
  `type_participation` enum('individuel','equipe') NOT NULL DEFAULT 'individuel',
  `nom_equipe` varchar(150) DEFAULT NULL,
  `categorie` enum('professionnel','etudiant') NOT NULL DEFAULT 'professionnel',
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `organisation` varchar(190) DEFAULT NULL COMMENT 'Cabinet, université ou Ordre des architectes',
  `numero_ordre` varchar(50) DEFAULT NULL COMMENT 'N° inscription Ordre / matricule étudiant',
  `mot_de_passe` varchar(255) NOT NULL COMMENT 'password_hash()',
  `email_verifie_le` datetime DEFAULT NULL,
  `statut` enum('en_attente','valide','rejete','suspendu') NOT NULL DEFAULT 'en_attente',
  `motif_rejet` text DEFAULT NULL,
  `decide_par` int(10) UNSIGNED DEFAULT NULL COMMENT 'id administrateur',
  `decide_le` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `candidats`
--

INSERT INTO `candidats` (`id`, `reference`, `type_participation`, `nom_equipe`, `categorie`, `nom`, `prenom`, `email`, `telephone`, `organisation`, `numero_ordre`, `mot_de_passe`, `email_verifie_le`, `statut`, `motif_rejet`, `decide_par`, `decide_le`, `created_at`, `updated_at`) VALUES
(1, 'CAND-2026-0001', 'individuel', NULL, 'professionnel', 'NDAYISHIMIYE', 'Claude', 'claude.ndayi@example.com', '+257 79 000 001', 'Cabinet Archi+ Bujumbura', 'OAB-0142', '$2y$10$FfSBWjOIY4/0JPXBTiLHHu9f/w3NezmKcCTHsAcVzoBq5mXOB/Dyi', '2026-10-10 12:19:59', 'en_attente', NULL, NULL, NULL, '2026-10-10 12:19:59', NULL),
(2, 'CAND-2026-0002', 'equipe', 'Atelier Ubuntu', 'etudiant', 'IRAKOZE', 'Divine', 'divine.irakoze@example.com', '+257 68 000 002', 'Université du Burundi', 'UB-2023-118', '$2y$10$FfSBWjOIY4/0JPXBTiLHHu9f/w3NezmKcCTHsAcVzoBq5mXOB/Dyi', '2026-10-12 16:19:59', 'en_attente', NULL, NULL, NULL, '2026-10-12 12:19:59', NULL),
(3, 'CAND-2026-0003', 'individuel', NULL, 'etudiant', 'HAKIZIMANA', 'Eric', 'eric.hakiz@example.com', '+257 71 000 003', 'Université Lumière de Bujumbura', 'ULBu-7781', '$2y$10$FfSBWjOIY4/0JPXBTiLHHu9f/w3NezmKcCTHsAcVzoBq5mXOB/Dyi', NULL, 'en_attente', NULL, NULL, NULL, '2026-10-13 10:19:59', NULL),
(4, 'CAND-2026-0004', 'equipe', 'Studio Ingoma', 'professionnel', 'NIYONZIMA', 'Aline', 'aline.niyo@example.com', '+257 62 000 004', 'Ingoma Design Studio', 'OAB-0217', '$2y$10$FfSBWjOIY4/0JPXBTiLHHu9f/w3NezmKcCTHsAcVzoBq5mXOB/Dyi', '2026-10-13 07:19:59', 'en_attente', NULL, NULL, NULL, '2026-10-13 06:19:59', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `candidatures`
--

CREATE TABLE `candidatures` (
  `id` int(10) UNSIGNED NOT NULL,
  `concours_id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED NOT NULL COMMENT 'Candidat individuel ou chef d''équipe',
  `type` enum('individuelle','equipe') NOT NULL DEFAULT 'individuelle',
  `nom_equipe` varchar(150) DEFAULT NULL,
  `code_anonyme` char(10) NOT NULL COMMENT 'Identifiant montré au jury à la place du nom',
  `statut` enum('inscrit','dossier_complet','projet_soumis','rejete','retenu') NOT NULL DEFAULT 'inscrit',
  `motif_rejet` varchar(255) DEFAULT NULL,
  `traite_par` int(10) UNSIGNED DEFAULT NULL,
  `traite_le` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `candidat_decisions`
--

CREATE TABLE `candidat_decisions` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidat_id` int(10) UNSIGNED NOT NULL,
  `decision` enum('valide','rejete','suspendu','reouvert') NOT NULL,
  `motif` text DEFAULT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `candidat_membres`
--

CREATE TABLE `candidat_membres` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidat_id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL COMMENT 'Architecte, designer, ingénieur…',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `candidat_membres`
--

INSERT INTO `candidat_membres` (`id`, `candidat_id`, `nom`, `prenom`, `email`, `telephone`, `role`, `created_at`) VALUES
(1, 2, 'NSHIMIRIMANA', 'Kevin', 'kevin.n@example.com', NULL, 'Architecte', '2026-10-13 12:19:59'),
(2, 2, 'KANEZA', 'Grâce', 'grace.k@example.com', NULL, 'Designer d\'intérieur', '2026-10-13 12:19:59'),
(3, 4, 'BIZIMANA', 'Jean', 'jean.b@example.com', NULL, 'Ingénieur structure', '2026-10-13 12:19:59');

-- --------------------------------------------------------

--
-- Structure de la table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `concours`
--

CREATE TABLE `concours` (
  `id` int(10) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `edition` varchar(20) DEFAULT NULL,
  `sous_titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `objectifs` text DEFAULT NULL,
  `criteres_eligibilite` text DEFAULT NULL,
  `public_cible` enum('professionnels','etudiants','mixte') NOT NULL DEFAULT 'mixte',
  `reglement_pdf` varchar(255) DEFAULT NULL COMMENT 'Chemin du règlement téléchargeable',
  `guide_pdf` varchar(255) DEFAULT NULL COMMENT 'Guide de participation',
  `ouverture_inscriptions` datetime DEFAULT NULL,
  `cloture_inscriptions` datetime DEFAULT NULL,
  `date_limite_soumission` datetime DEFAULT NULL,
  `date_resultats` datetime DEFAULT NULL,
  `taille_equipe_max` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `taille_max_fichier_mo` smallint(5) UNSIGNED NOT NULL DEFAULT 50,
  `extensions_autorisees` varchar(100) NOT NULL DEFAULT 'pdf,zip,jpg,jpeg,png',
  `mode_validation_compte` enum('email','manuelle','les_deux') NOT NULL DEFAULT 'manuelle',
  `statut` enum('brouillon','publie','cloture','archive') NOT NULL DEFAULT 'brouillon',
  `est_actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `mode_participation` enum('individuel','equipe','les_deux') NOT NULL DEFAULT 'les_deux',
  `taille_equipe_min` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `nb_max_fichiers` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `modification_avant_cloture` tinyint(1) NOT NULL DEFAULT 1,
  `anonymisation_jury` tinyint(1) NOT NULL DEFAULT 1,
  `logo` varchar(255) DEFAULT NULL,
  `banniere` varchar(255) DEFAULT NULL,
  `email_contact` varchar(150) DEFAULT NULL,
  `telephone_contact` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `concours`
--

INSERT INTO `concours` (`id`, `titre`, `edition`, `sous_titre`, `description`, `objectifs`, `criteres_eligibilite`, `public_cible`, `reglement_pdf`, `guide_pdf`, `ouverture_inscriptions`, `cloture_inscriptions`, `date_limite_soumission`, `date_resultats`, `taille_equipe_max`, `taille_max_fichier_mo`, `extensions_autorisees`, `mode_validation_compte`, `statut`, `est_actif`, `created_by`, `updated_by`, `created_at`, `updated_at`, `mode_participation`, `taille_equipe_min`, `nb_max_fichiers`, `modification_avant_cloture`, `anonymisation_jury`, `logo`, `banniere`, `email_contact`, `telephone_contact`) VALUES
(1, 'Interiot Design', '2026', 'Bonjour a tout Nous avons le plaisir de vous accuiellier au concour de Interio Design', 'Le concour est destion au etudiant des universite', 'Voir les etuadiant passion de metier', 'avoir minimum 60%', 'etudiants', 'uploads/concours/959975582cd710188155e48b6a3ab4d2.pdf', 'uploads/concours/373922eb22456fbd1f61421d7f58cc97.pdf', '2026-10-14 08:00:00', '2026-10-20 23:59:00', '2026-10-21 00:00:00', '2026-10-23 12:00:00', 5, 30, 'pdf,zip,jpg,jpeg,png', 'manuelle', 'publie', 1, 1, 1, '2026-10-13 07:59:13', '2026-10-13 08:59:15', 'individuel', 2, 5, 1, 1, 'uploads/concours/7b48bc7aab1532352af36dadef893da4.png', 'uploads/concours/926a6a474b91fa5377e3211ddce5b6cc.jpg', 'info@satracoconstruction.com', '+257633333');

-- --------------------------------------------------------

--
-- Structure de la table `criteres_evaluation`
--

CREATE TABLE `criteres_evaluation` (
  `id` int(10) UNSIGNED NOT NULL,
  `concours_id` int(10) UNSIGNED NOT NULL,
  `libelle` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `note_max` decimal(5,2) NOT NULL DEFAULT 20.00,
  `coefficient` decimal(4,2) NOT NULL DEFAULT 1.00,
  `ordre` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `criteres_notation`
--

CREATE TABLE `criteres_notation` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(120) NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Ce que le jury doit apprécier',
  `poids` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Pondération en % (somme des actifs = 100)',
  `note_max` tinyint(3) UNSIGNED NOT NULL DEFAULT 20 COMMENT 'Barème : note de 0 à note_max',
  `ordre` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `criteres_notation`
--

INSERT INTO `criteres_notation` (`id`, `nom`, `description`, `poids`, `note_max`, `ordre`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Cotation des murs', NULL, 10, 100, 1, 1, '2026-10-13 15:24:26', '2026-10-13 15:24:43');

-- --------------------------------------------------------

--
-- Structure de la table `dates_cles`
--

CREATE TABLE `dates_cles` (
  `id` int(10) UNSIGNED NOT NULL,
  `concours_id` int(10) UNSIGNED NOT NULL,
  `libelle` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `icone` varchar(50) NOT NULL DEFAULT 'far fa-calendar',
  `est_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dates_cles`
--

INSERT INTO `dates_cles` (`id`, `concours_id`, `libelle`, `description`, `date_debut`, `date_fin`, `icone`, `est_public`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Formation des Candidat', 'Formation sur Olivier', '2026-10-13 09:00:00', '2026-10-16 09:00:00', 'fas fa-chalkboard-teacher', 1, 1, '2026-10-13 09:01:16', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidature_id` int(10) UNSIGNED NOT NULL,
  `jury_id` int(10) UNSIGNED NOT NULL,
  `critere_id` int(10) UNSIGNED NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation_notes`
--

CREATE TABLE `evaluation_notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `soumission_id` int(10) UNSIGNED NOT NULL,
  `jury_id` int(10) UNSIGNED NOT NULL COMMENT 'id du membre du jury',
  `critere_id` int(10) UNSIGNED NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `evaluation_notes`
--

INSERT INTO `evaluation_notes` (`id`, `soumission_id`, `jury_id`, `critere_id`, `note`, `commentaire`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 91.50, 'Concept clair et bien argumenté.', '2026-10-13 15:47:53', NULL),
(2, 2, 1, 1, 54.50, 'Concept clair et bien argumenté.', '2026-10-13 15:47:53', NULL),
(3, 1, 2, 1, 54.00, NULL, '2026-10-13 15:47:53', NULL),
(4, 2, 2, 1, 67.50, NULL, '2026-10-13 15:47:53', NULL),
(5, 1, 3, 1, 66.50, NULL, '2026-10-13 15:47:53', NULL),
(6, 2, 3, 1, 80.00, NULL, '2026-10-13 15:47:53', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `evaluation_parametres`
--

CREATE TABLE `evaluation_parametres` (
  `cle` varchar(50) NOT NULL,
  `valeur` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `evaluation_parametres`
--

INSERT INTO `evaluation_parametres` (`cle`, `valeur`, `updated_at`) VALUES
('grille_verrouillee', '1', '2026-10-13 15:47:53');

-- --------------------------------------------------------

--
-- Structure de la table `exports_journal`
--

CREATE TABLE `exports_journal` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL COMMENT 'candidats, participants, projets, journal, archive',
  `format` varchar(10) NOT NULL COMMENT 'xlsx, csv, zip',
  `filtres` varchar(500) DEFAULT NULL COMMENT 'Filtres appliqués (JSON)',
  `nb_lignes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `admin_libelle` varchar(150) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `exports_journal`
--

INSERT INTO `exports_journal` (`id`, `type`, `format`, `filtres`, `nb_lignes`, `admin_id`, `admin_libelle`, `ip`, `created_at`) VALUES
(1, 'candidats', 'xlsx', NULL, 4, 1, 'Administrateur', '::1', '2026-10-13 14:46:39');

-- --------------------------------------------------------

--
-- Structure de la table `fichiers_projet`
--

CREATE TABLE `fichiers_projet` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `categorie` enum('plan','coupe','elevation','rendu_3d','note','autre') NOT NULL,
  `nom_original` varchar(255) NOT NULL,
  `nom_stocke` varchar(255) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `taille` bigint(20) UNSIGNED NOT NULL COMMENT 'Octets',
  `hash_sha256` char(64) NOT NULL COMMENT 'Contrôle d''intégrité',
  `est_actif` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = remplacé par une version plus récente',
  `remplace_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Fichier remplacé par celui-ci',
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_soumissions`
--

CREATE TABLE `historique_soumissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED NOT NULL,
  `action` enum('creation','modification_fiche','ajout_fichier','remplacement_fichier','suppression_fichier','soumission_finale') NOT NULL,
  `fichier_id` int(10) UNSIGNED DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `journal_activites`
--

CREATE TABLE `journal_activites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'Ex. connexion, echec_connexion, validation_compte, export',
  `cible_type` varchar(50) DEFAULT NULL,
  `cible_id` int(10) UNSIGNED DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `journal_activites`
--

INSERT INTO `journal_activites` (`id`, `utilisateur_id`, `action`, `cible_type`, `cible_id`, `details`, `ip`, `user_agent`, `created_at`) VALUES
(1, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:18:08'),
(2, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:28:45'),
(3, 1, 'demande_reinitialisation', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:31:50'),
(4, 1, 'demande_reinitialisation', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:40:38'),
(5, 1, 'demande_reinitialisation', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:42:54'),
(6, 1, 'reinitialisation_mot_de_passe', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:43:35'),
(7, 1, 'demande_reinitialisation', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:44:07'),
(8, 1, 'reinitialisation_mot_de_passe', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:44:25'),
(9, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:44:34'),
(10, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-12 16:46:54'),
(11, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-13 06:38:55'),
(12, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-13 07:11:05'),
(13, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-13 07:11:09'),
(14, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-13 07:12:08'),
(15, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-13 07:12:12'),
(16, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 18:34:13'),
(17, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 19:11:16'),
(18, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 20:07:45'),
(19, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 20:13:15'),
(20, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 20:14:35'),
(21, 1, 'connexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 20:14:43'),
(22, 1, 'deconnexion', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-13 20:14:49');

-- --------------------------------------------------------

--
-- Structure de la table `membres_equipe`
--

CREATE TABLE `membres_equipe` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidature_id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `organisation` varchar(150) DEFAULT NULL,
  `role_equipe` varchar(80) DEFAULT NULL COMMENT 'Ex. architecte, designer, ingénieur',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants`
--

CREATE TABLE `participants` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `universite` varchar(190) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `statut` enum('actif','suspendu') NOT NULL DEFAULT 'actif',
  `derniere_connexion` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `utilisateur_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL COMMENT 'hash(''sha256'', jeton) – le jeton brut n''est jamais stocké',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `utilisateur_id`, `token_hash`, `expires_at`, `used_at`, `ip`, `created_at`) VALUES
(1, 1, '1904220c940f415c1003e4a6f62f36dd07ddb143256ce32e16bfcbf9b6fe2582', '2026-10-12 17:31:50', '2026-10-12 16:40:38', '::1', '2026-10-12 16:31:50'),
(2, 1, '06136ab93c0d48572f64011957e23d3757ff551ef21540ff9f3ffac814a2ac01', '2026-10-12 17:40:38', '2026-10-12 16:42:54', '::1', '2026-10-12 16:40:38'),
(3, 1, '8b221689c4b35f8f72665c4d2ef0b1aaf03696bb59db1a8914fe723d8a04032d', '2026-10-12 17:42:54', '2026-10-12 16:43:35', '::1', '2026-10-12 16:42:54'),
(4, 1, '2c46675e026b31a5ff5931f9c1bb9e578b4d537add3441a0cfb49b0adfc40d75', '2026-10-12 17:44:07', '2026-10-12 16:44:25', '::1', '2026-10-12 16:44:07');

-- --------------------------------------------------------

--
-- Structure de la table `prix`
--

CREATE TABLE `prix` (
  `id` int(10) UNSIGNED NOT NULL,
  `concours_id` int(10) UNSIGNED NOT NULL,
  `categorie` enum('principal','special','mention') NOT NULL DEFAULT 'principal',
  `rang` tinyint(3) UNSIGNED DEFAULT NULL,
  `libelle` varchar(150) NOT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `devise` char(3) NOT NULL DEFAULT 'BIF',
  `nombre_laureats` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `est_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `prix`
--

INSERT INTO `prix` (`id`, `concours_id`, `categorie`, `rang`, `libelle`, `montant`, `devise`, `nombre_laureats`, `description`, `image`, `est_public`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'principal', 1, '1 er Prix', 1000000.00, 'BIF', 1, '# mois de Stage paye', 'uploads/concours/bf2bd8a731affa1eeb66fd896d09946e.jpg', 1, 1, '2026-10-13 09:17:39', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `projets`
--

CREATE TABLE `projets` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidature_id` int(10) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `note_conceptuelle` text NOT NULL,
  `premiere_soumission` datetime DEFAULT NULL,
  `derniere_soumission` datetime DEFAULT NULL COMMENT 'Horodatage serveur de la dernière modification',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ressources`
--

CREATE TABLE `ressources` (
  `id` int(10) UNSIGNED NOT NULL,
  `concours_id` int(10) UNSIGNED NOT NULL,
  `categorie` enum('brief','plans','photos','gabarits','autre') NOT NULL DEFAULT 'autre',
  `titre` varchar(150) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `fichier_chemin` varchar(100) NOT NULL,
  `fichier_nom_original` varchar(255) NOT NULL,
  `fichier_taille` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `fichier_mime` varchar(100) DEFAULT NULL,
  `fichier_extension` varchar(10) NOT NULL,
  `hash_sha256` char(64) DEFAULT NULL,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `visibilite` enum('public','inscrits','valides') NOT NULL DEFAULT 'valides',
  `est_publie` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `nb_telechargements` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ressources`
--

INSERT INTO `ressources` (`id`, `concours_id`, `categorie`, `titre`, `description`, `fichier_chemin`, `fichier_nom_original`, `fichier_taille`, `fichier_mime`, `fichier_extension`, `hash_sha256`, `version`, `visibilite`, `est_publie`, `ordre`, `nb_telechargements`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'photos', 'Interiot Design', 'Le contenu des fichier', '3efedfae9f7e73675d014d3dbc895b28.jpeg', 'WhatsApp Image 2026-10-10 at 16.06.22.jpeg', 290039, 'image/jpeg', 'jpeg', '9224f12742a0fc06ad93df71ad8bf6220baf25ad109f8a9624ba58344971977c', 1, 'valides', 1, 1, 0, 1, '2026-10-13 09:36:18', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `code`, `libelle`) VALUES
(1, 'admin', 'Administrateur SATRACO'),
(2, 'jury', 'Membre du jury'),
(3, 'candidat', 'Candidat');

-- --------------------------------------------------------

--
-- Structure de la table `soumissions`
--

CREATE TABLE `soumissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidat_id` int(10) UNSIGNED NOT NULL,
  `code_anonyme` varchar(12) NOT NULL COMMENT 'Identifiant présenté au jury (ex : PRJ-7K3QX)',
  `titre` varchar(200) NOT NULL,
  `note_descriptive` text DEFAULT NULL COMMENT 'Note conceptuelle du projet',
  `statut` enum('brouillon','soumis') NOT NULL DEFAULT 'brouillon',
  `conformite` enum('a_verifier','conforme','non_conforme') NOT NULL DEFAULT 'a_verifier',
  `motif_conformite` text DEFAULT NULL,
  `controle_par` int(10) UNSIGNED DEFAULT NULL COMMENT 'id administrateur',
  `controle_le` datetime DEFAULT NULL,
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 1 COMMENT '+1 à chaque nouvelle soumission',
  `premiere_soumission_le` datetime DEFAULT NULL,
  `derniere_soumission_le` datetime DEFAULT NULL COMMENT 'Horodatage serveur de la dernière soumission',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `soumissions`
--

INSERT INTO `soumissions` (`id`, `candidat_id`, `code_anonyme`, `titre`, `note_descriptive`, `statut`, `conformite`, `motif_conformite`, `controle_par`, `controle_le`, `version`, `premiere_soumission_le`, `derniere_soumission_le`, `created_at`, `updated_at`) VALUES
(1, 1, 'PRJ-7K3QX', 'Pavillon Ubuntu — habitat évolutif', 'Un module d\'habitat en briques de terre compressée, extensible par travées, organisé autour d\'une cour ventilée.', 'soumis', 'conforme', NULL, NULL, NULL, 2, '2026-10-11 14:12:30', '2026-10-13 09:12:30', '2026-10-10 14:12:30', '2026-10-13 15:47:53'),
(2, 4, 'PRJ-M2V8D', 'Ingoma Hub — centre culturel', 'Toiture en voûtes légères évoquant le tambour, galerie publique traversante et amphithéâtre extérieur.', 'soumis', 'conforme', NULL, NULL, NULL, 1, '2026-10-12 14:12:30', '2026-10-12 14:12:30', '2026-10-11 14:12:30', '2026-10-13 15:47:53'),
(3, 2, 'PRJ-T9H4N', 'Atelier Ubuntu (brouillon)', NULL, 'brouillon', 'a_verifier', NULL, NULL, NULL, 1, NULL, NULL, '2026-10-13 04:12:30', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `soumission_fichiers`
--

CREATE TABLE `soumission_fichiers` (
  `id` int(10) UNSIGNED NOT NULL,
  `soumission_id` int(10) UNSIGNED NOT NULL,
  `categorie` enum('plan','coupe','elevation','rendu_3d','note','autre') NOT NULL DEFAULT 'autre',
  `nom_original` varchar(255) NOT NULL,
  `chemin` varchar(255) NOT NULL COMMENT 'Relatif au dossier de stockage, jamais exposé publiquement',
  `extension` varchar(10) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `taille_octets` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `empreinte` char(64) DEFAULT NULL COMMENT 'SHA-256 (intégrité / preuve de dépôt)',
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `remplace_le` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `soumission_fichiers`
--

INSERT INTO `soumission_fichiers` (`id`, `soumission_id`, `categorie`, `nom_original`, `chemin`, `extension`, `mime`, `taille_octets`, `empreinte`, `version`, `actif`, `remplace_le`, `created_at`) VALUES
(1, 1, 'rendu_3d', 'perspective-cour.jpg', '1/perspective-cour.jpg', 'jpg', 'image/jpeg', 14680064, NULL, 1, 1, NULL, '2026-10-13 14:12:30'),
(2, 1, 'elevation', 'facades.pdf', '1/facades.pdf', 'pdf', 'application/pdf', 6291456, NULL, 1, 1, NULL, '2026-10-13 14:12:30'),
(3, 1, 'coupe', 'coupes-AA-BB.pdf', '1/coupes-AA-BB.pdf', 'pdf', 'application/pdf', 5242880, NULL, 1, 1, NULL, '2026-10-13 14:12:30'),
(4, 1, 'plan', 'plan-masse-v2.pdf', '1/plan-masse-v2.pdf', 'pdf', 'application/pdf', 9102336, NULL, 2, 1, NULL, '2026-10-13 14:12:30'),
(5, 1, 'plan', 'plan-masse.pdf', '1/plan-masse.pdf', 'pdf', 'application/pdf', 8421376, NULL, 1, 0, '2026-10-13 09:12:30', '2026-10-13 14:12:30'),
(6, 2, 'rendu_3d', 'rendus.zip', '2/rendus.zip', 'zip', 'application/zip', 47185920, NULL, 1, 1, NULL, '2026-10-13 14:12:30'),
(7, 2, 'plan', 'plans-niveaux.pdf', '2/plans-niveaux.pdf', 'pdf', 'application/pdf', 11534336, NULL, 1, 1, NULL, '2026-10-13 14:12:30');

-- --------------------------------------------------------

--
-- Structure de la table `soumission_historique`
--

CREATE TABLE `soumission_historique` (
  `id` int(10) UNSIGNED NOT NULL,
  `soumission_id` int(10) UNSIGNED NOT NULL,
  `action` enum('creation','depot_fichier','remplacement_fichier','soumission','conforme','non_conforme','a_verifier') NOT NULL,
  `detail` text DEFAULT NULL,
  `auteur_type` enum('candidat','admin') NOT NULL,
  `auteur_id` int(10) UNSIGNED DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `soumission_historique`
--

INSERT INTO `soumission_historique` (`id`, `soumission_id`, `action`, `detail`, `auteur_type`, `auteur_id`, `ip`, `created_at`) VALUES
(1, 1, 'soumission', 'Version 1 soumise', 'candidat', NULL, NULL, '2026-10-11 14:12:30'),
(2, 2, 'soumission', 'Version 1 soumise', 'candidat', NULL, NULL, '2026-10-12 14:12:30'),
(4, 1, 'remplacement_fichier', 'plan-masse.pdf → plan-masse-v2.pdf', 'candidat', NULL, NULL, '2026-10-13 09:12:30'),
(5, 1, 'soumission', 'Version 2 soumise', 'candidat', NULL, NULL, '2026-10-13 09:12:30');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `organisation` varchar(150) DEFAULT NULL COMMENT 'Université, cabinet ou ordre des architectes',
  `mot_de_passe` varchar(255) NOT NULL COMMENT 'password_hash() – bcrypt',
  `statut` enum('en_attente','actif','suspendu') NOT NULL DEFAULT 'en_attente',
  `email_verifie_le` datetime DEFAULT NULL,
  `valide_par` int(10) UNSIGNED DEFAULT NULL COMMENT 'Administrateur ayant validé le compte',
  `valide_le` datetime DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `tentatives_echouees` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `bloque_jusqua` datetime DEFAULT NULL COMMENT 'Blocage temporaire après trop d''échecs',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `role_id`, `nom`, `prenom`, `email`, `telephone`, `organisation`, `mot_de_passe`, `statut`, `email_verifie_le`, `valide_par`, `valide_le`, `derniere_connexion`, `tentatives_echouees`, `bloque_jusqua`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrateur', 'SATRACO', 'admin@satraco.bi', NULL, 'SATRACO Construction', '$2y$10$hP/kL1FJ6C1aZBeaLXDND.7Vz7FQj.SN6THns86UrriYm/v8/d9z.', 'actif', '2026-10-12 15:52:08', NULL, '2026-10-12 15:52:08', '2026-10-13 20:14:42', 0, NULL, '2026-10-12 15:52:08', '2026-10-13 20:14:42'),
(2, 1, 'Gestionnaire', 'Concours', 'concours@satraco.bi', NULL, 'SATRACO Construction', '$2y$12$bObvRwq8u1lh/PDCBoi7au4IU7ePGvEv1Sm1HOObYCOUVqe6izPVG', 'actif', '2026-10-12 15:52:08', 1, '2026-10-12 15:52:08', NULL, 0, NULL, '2026-10-12 15:52:08', NULL),
(3, 2, 'Jury', 'Membre 1', 'jury1@satraco.bi', NULL, 'Jury du concours', '$2y$12$I0vGIVuaZCgmbAnsqTjuLuPmQqYVwmSzFwPoVpGbEqPdd.soog5Xy', 'actif', '2026-10-12 15:52:08', 1, '2026-10-12 15:52:08', NULL, 0, NULL, '2026-10-12 15:52:08', NULL),
(4, 3, 'Candidat', 'Test', 'candidat.test@satraco.bi', '+257 79 000 001', 'Université du Burundi', '$2y$12$79p1zRBINeAKhDXZQ5RLy./rUCeVEHCEkv08Ze4OVdpVl2xp.ygem', 'actif', '2026-10-12 15:52:08', 1, '2026-10-12 15:52:08', NULL, 0, NULL, '2026-10-12 15:52:08', NULL),
(5, 3, 'Candidat', 'En attente', 'candidat.attente@satraco.bi', '+257 79 000 002', 'Université Lumière de Bujumbura', '$2y$12$DQ2dWoKpsJ3iBoxJAMPGLep2JG0wsE3EcpNZddpJrelWHXO86Xhkq', 'en_attente', NULL, NULL, NULL, NULL, 0, NULL, '2026-10-12 15:52:08', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `candidats`
--
ALTER TABLE `candidats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_candidats_email` (`email`),
  ADD UNIQUE KEY `uk_candidats_reference` (`reference`),
  ADD KEY `idx_candidats_statut_date` (`statut`,`created_at`);

--
-- Index pour la table `candidatures`
--
ALTER TABLE `candidatures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_candidatures_user_concours` (`concours_id`,`utilisateur_id`),
  ADD UNIQUE KEY `uk_candidatures_code` (`code_anonyme`),
  ADD KEY `idx_candidatures_statut` (`statut`),
  ADD KEY `fk_candidatures_user` (`utilisateur_id`),
  ADD KEY `fk_candidatures_traite_par` (`traite_par`);

--
-- Index pour la table `candidat_decisions`
--
ALTER TABLE `candidat_decisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_decisions_candidat` (`candidat_id`);

--
-- Index pour la table `candidat_membres`
--
ALTER TABLE `candidat_membres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_membres_candidat` (`candidat_id`);

--
-- Index pour la table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Index pour la table `concours`
--
ALTER TABLE `concours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_concours_statut` (`statut`),
  ADD KEY `fk_concours_created_by` (`created_by`),
  ADD KEY `idx_concours_actif` (`est_actif`);

--
-- Index pour la table `criteres_evaluation`
--
ALTER TABLE `criteres_evaluation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_criteres_concours` (`concours_id`);

--
-- Index pour la table `criteres_notation`
--
ALTER TABLE `criteres_notation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_criteres_ordre` (`actif`,`ordre`);

--
-- Index pour la table `dates_cles`
--
ALTER TABLE `dates_cles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dates_cles_concours_date` (`concours_id`,`date_debut`);

--
-- Index pour la table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_email_verif_token` (`token_hash`),
  ADD KEY `fk_email_verif_user` (`utilisateur_id`);

--
-- Index pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_evaluations` (`candidature_id`,`jury_id`,`critere_id`),
  ADD KEY `idx_evaluations_jury` (`jury_id`),
  ADD KEY `fk_evaluations_critere` (`critere_id`);

--
-- Index pour la table `evaluation_notes`
--
ALTER TABLE `evaluation_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_note` (`soumission_id`,`jury_id`,`critere_id`),
  ADD KEY `idx_notes_critere` (`critere_id`);

--
-- Index pour la table `evaluation_parametres`
--
ALTER TABLE `evaluation_parametres`
  ADD PRIMARY KEY (`cle`);

--
-- Index pour la table `exports_journal`
--
ALTER TABLE `exports_journal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exports_date` (`created_at`);

--
-- Index pour la table `fichiers_projet`
--
ALTER TABLE `fichiers_projet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_fichiers_nom_stocke` (`nom_stocke`),
  ADD KEY `idx_fichiers_projet` (`projet_id`,`est_actif`),
  ADD KEY `fk_fichiers_remplace` (`remplace_id`);

--
-- Index pour la table `historique_soumissions`
--
ALTER TABLE `historique_soumissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_historique_projet` (`projet_id`,`created_at`),
  ADD KEY `fk_historique_user` (`utilisateur_id`),
  ADD KEY `fk_historique_fichier` (`fichier_id`);

--
-- Index pour la table `journal_activites`
--
ALTER TABLE `journal_activites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_journal_user` (`utilisateur_id`,`created_at`),
  ADD KEY `idx_journal_action` (`action`);

--
-- Index pour la table `membres_equipe`
--
ALTER TABLE `membres_equipe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_membres_candidature` (`candidature_id`);

--
-- Index pour la table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_participants_email` (`email`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_password_resets_token` (`token_hash`),
  ADD KEY `idx_password_resets_user` (`utilisateur_id`);

--
-- Index pour la table `prix`
--
ALTER TABLE `prix`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prix_concours` (`concours_id`,`categorie`,`rang`);

--
-- Index pour la table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_projets_candidature` (`candidature_id`);

--
-- Index pour la table `ressources`
--
ALTER TABLE `ressources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ressources_concours` (`concours_id`,`categorie`,`ordre`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_roles_code` (`code`);

--
-- Index pour la table `soumissions`
--
ALTER TABLE `soumissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_soumissions_candidat` (`candidat_id`),
  ADD UNIQUE KEY `uk_soumissions_code` (`code_anonyme`),
  ADD KEY `idx_soumissions_statut` (`statut`,`conformite`);

--
-- Index pour la table `soumission_fichiers`
--
ALTER TABLE `soumission_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fichiers_soumission` (`soumission_id`,`actif`);

--
-- Index pour la table `soumission_historique`
--
ALTER TABLE `soumission_historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hist_soumission` (`soumission_id`,`created_at`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_utilisateurs_email` (`email`),
  ADD KEY `idx_utilisateurs_role` (`role_id`),
  ADD KEY `idx_utilisateurs_statut` (`statut`),
  ADD KEY `fk_utilisateurs_valide_par` (`valide_par`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `candidats`
--
ALTER TABLE `candidats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `candidatures`
--
ALTER TABLE `candidatures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `candidat_decisions`
--
ALTER TABLE `candidat_decisions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `candidat_membres`
--
ALTER TABLE `candidat_membres`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `concours`
--
ALTER TABLE `concours`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `criteres_evaluation`
--
ALTER TABLE `criteres_evaluation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `criteres_notation`
--
ALTER TABLE `criteres_notation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `dates_cles`
--
ALTER TABLE `dates_cles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evaluation_notes`
--
ALTER TABLE `evaluation_notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `exports_journal`
--
ALTER TABLE `exports_journal`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `fichiers_projet`
--
ALTER TABLE `fichiers_projet`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `historique_soumissions`
--
ALTER TABLE `historique_soumissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `journal_activites`
--
ALTER TABLE `journal_activites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `membres_equipe`
--
ALTER TABLE `membres_equipe`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `prix`
--
ALTER TABLE `prix`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `projets`
--
ALTER TABLE `projets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ressources`
--
ALTER TABLE `ressources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `soumissions`
--
ALTER TABLE `soumissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `soumission_fichiers`
--
ALTER TABLE `soumission_fichiers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `soumission_historique`
--
ALTER TABLE `soumission_historique`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `candidatures`
--
ALTER TABLE `candidatures`
  ADD CONSTRAINT `fk_candidatures_concours` FOREIGN KEY (`concours_id`) REFERENCES `concours` (`id`),
  ADD CONSTRAINT `fk_candidatures_traite_par` FOREIGN KEY (`traite_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_candidatures_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `candidat_decisions`
--
ALTER TABLE `candidat_decisions`
  ADD CONSTRAINT `fk_decisions_candidat` FOREIGN KEY (`candidat_id`) REFERENCES `candidats` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `candidat_membres`
--
ALTER TABLE `candidat_membres`
  ADD CONSTRAINT `fk_membres_candidat` FOREIGN KEY (`candidat_id`) REFERENCES `candidats` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `concours`
--
ALTER TABLE `concours`
  ADD CONSTRAINT `fk_concours_created_by` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `criteres_evaluation`
--
ALTER TABLE `criteres_evaluation`
  ADD CONSTRAINT `fk_criteres_concours` FOREIGN KEY (`concours_id`) REFERENCES `concours` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dates_cles`
--
ALTER TABLE `dates_cles`
  ADD CONSTRAINT `fk_dates_cles_concours` FOREIGN KEY (`concours_id`) REFERENCES `concours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_email_verif_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `fk_evaluations_candidature` FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evaluations_critere` FOREIGN KEY (`critere_id`) REFERENCES `criteres_evaluation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evaluations_jury` FOREIGN KEY (`jury_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `evaluation_notes`
--
ALTER TABLE `evaluation_notes`
  ADD CONSTRAINT `fk_notes_critere` FOREIGN KEY (`critere_id`) REFERENCES `criteres_notation` (`id`),
  ADD CONSTRAINT `fk_notes_soumission` FOREIGN KEY (`soumission_id`) REFERENCES `soumissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fichiers_projet`
--
ALTER TABLE `fichiers_projet`
  ADD CONSTRAINT `fk_fichiers_projet` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fichiers_remplace` FOREIGN KEY (`remplace_id`) REFERENCES `fichiers_projet` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `historique_soumissions`
--
ALTER TABLE `historique_soumissions`
  ADD CONSTRAINT `fk_historique_fichier` FOREIGN KEY (`fichier_id`) REFERENCES `fichiers_projet` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_historique_projet` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historique_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `journal_activites`
--
ALTER TABLE `journal_activites`
  ADD CONSTRAINT `fk_journal_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `membres_equipe`
--
ALTER TABLE `membres_equipe`
  ADD CONSTRAINT `fk_membres_candidature` FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `prix`
--
ALTER TABLE `prix`
  ADD CONSTRAINT `fk_prix_concours` FOREIGN KEY (`concours_id`) REFERENCES `concours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `fk_projets_candidature` FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ressources`
--
ALTER TABLE `ressources`
  ADD CONSTRAINT `fk_ressources_concours` FOREIGN KEY (`concours_id`) REFERENCES `concours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `soumissions`
--
ALTER TABLE `soumissions`
  ADD CONSTRAINT `fk_soumissions_candidat` FOREIGN KEY (`candidat_id`) REFERENCES `candidats` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `soumission_fichiers`
--
ALTER TABLE `soumission_fichiers`
  ADD CONSTRAINT `fk_fichiers_soumission` FOREIGN KEY (`soumission_id`) REFERENCES `soumissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `soumission_historique`
--
ALTER TABLE `soumission_historique`
  ADD CONSTRAINT `fk_hist_soumission` FOREIGN KEY (`soumission_id`) REFERENCES `soumissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `fk_utilisateurs_valide_par` FOREIGN KEY (`valide_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
