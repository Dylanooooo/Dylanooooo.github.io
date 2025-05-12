-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 11:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Drop tables if exists (in correct order to respect foreign keys)
DROP TABLE IF EXISTS `taken`;
DROP TABLE IF EXISTS `rooster`;
DROP TABLE IF EXISTS `berichten`;
DROP TABLE IF EXISTS `gesprekken_gebruikers`;
DROP TABLE IF EXISTS `gesprekken`;
DROP TABLE IF EXISTS `project_gebruikers`;
DROP TABLE IF EXISTS `projecten`;
DROP TABLE IF EXISTS `gebruikers`;

-- Gebruikers tabel
CREATE TABLE `gebruikers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `wachtwoord` varchar(255) NOT NULL,
  `rol` enum('admin','stagiair') NOT NULL DEFAULT 'stagiair',
  `profile_image` varchar(255) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `opleiding` varchar(100) DEFAULT NULL,
  `uren_per_week` int(11) DEFAULT NULL,
  `startdatum` date DEFAULT NULL,
  `einddatum` date DEFAULT NULL,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Projecten tabel
CREATE TABLE `projecten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `start_datum` date NOT NULL,
  `eind_datum` date NOT NULL,
  `status` enum('aankomend','actief','afgerond') NOT NULL DEFAULT 'aankomend',
  `voortgang` int(11) NOT NULL DEFAULT 0,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Project gebruikers koppeltabel
CREATE TABLE `project_gebruikers` (
  `project_id` int(11) NOT NULL,
  `gebruiker_id` int(11) NOT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `datum_toegevoegd` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`project_id`,`gebruiker_id`),
  FOREIGN KEY (`project_id`) REFERENCES `projecten`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Taken tabel
CREATE TABLE `taken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `naam` varchar(255) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('open','in_uitvoering','afgerond') NOT NULL DEFAULT 'open',
  `prioriteit` enum('laag','medium','hoog') NOT NULL DEFAULT 'medium',
  `toegewezen_aan` int(11) DEFAULT NULL,
  `aangemaakt_door` int(11) DEFAULT NULL,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `projecten`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`toegewezen_aan`) REFERENCES `gebruikers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`aangemaakt_door`) REFERENCES `gebruikers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Berichten tabel
CREATE TABLE `berichten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afzender_id` int(11) NOT NULL,
  `ontvanger_id` int(11) NOT NULL,
  `inhoud` text NOT NULL,
  `gelezen` tinyint(1) NOT NULL DEFAULT 0,
  `datum_verzonden` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`afzender_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`ontvanger_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Gesprekken tabel
CREATE TABLE `gesprekken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) DEFAULT NULL,
  `type` enum('direct','groep') NOT NULL DEFAULT 'direct',
  `gebruiker1_id` int(11) DEFAULT NULL,
  `gebruiker2_id` int(11) DEFAULT NULL,
  `aangemaakt_door` int(11) NOT NULL,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
  `laatste_bericht` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`aangemaakt_door`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gebruiker1_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gebruiker2_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Gesprekken gebruikers koppeltabel
CREATE TABLE `gesprekken_gebruikers` (
  `gesprek_id` int(11) NOT NULL,
  `gebruiker_id` int(11) NOT NULL,
  `ongelezen_berichten` int(11) NOT NULL DEFAULT 0,
  `datum_toegevoegd` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`gesprek_id`,`gebruiker_id`),
  FOREIGN KEY (`gesprek_id`) REFERENCES `gesprekken`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Rooster tabel
CREATE TABLE `rooster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gebruiker_id` int(11) NOT NULL,
  `dag` date NOT NULL,
  `start_tijd` time NOT NULL,
  `eind_tijd` time NOT NULL,
  `locatie` varchar(100) DEFAULT NULL,
  `opmerkingen` text DEFAULT NULL,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `gebruiker_id` (`gebruiker_id`),
  CONSTRAINT `rooster_ibfk_1` FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Standaard gebruikers toevoegen
INSERT INTO `gebruikers` (`naam`, `email`, `wachtwoord`, `rol`) VALUES
('Admin Gebruiker', 'admin@flitz-events.nl', '$2y$10$JZ4KArPtkrGBfNHAQNNXkuM7xY4fKcDzcbW/RroYSa7FvOgwGiCbi', 'admin'),
('Stagiair Gebruiker', 'stagiair@flitz-events.nl', '$2y$10$Bc4.eqYKrJZQIzZQLM7O/.HD3KGZLCiZ28pKrEiB0n3nNaIQSdnF.', 'stagiair');

-- Voorbeeld projecten toevoegen
INSERT INTO `projecten` (`naam`, `beschrijving`, `start_datum`, `eind_datum`, `status`, `voortgang`) VALUES
('Zomerfestival Noordwijk', 'Driedaags strandfestival met verschillende artiesten en activiteiten', DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY), 'actief', 35),
('Bedrijfsevent Amstelveen', 'Eendaags bedrijfsevent met workshops en netwerkborrel', DATE_ADD(CURDATE(), INTERVAL 40 DAY), DATE_ADD(CURDATE(), INTERVAL 40 DAY), 'aankomend', 10),
('Muziekfestival Groningen', 'Tweedaags muziekfestival in Stadspark Groningen', DATE_ADD(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 62 DAY), 'aankomend', 5),
('Bruiloft Amsterdam', 'Exclusieve bruiloft op locatie in Amsterdam', DATE_ADD(CURDATE(), INTERVAL -30 DAY), DATE_ADD(CURDATE(), INTERVAL -30 DAY), 'afgerond', 100),
('Bedrijfsjubileum Utrecht', 'Jubileumfeest voor 25-jarig bestaan tech bedrijf', DATE_ADD(CURDATE(), INTERVAL -45 DAY), DATE_ADD(CURDATE(), INTERVAL -45 DAY), 'afgerond', 100),
('Sporttoernooi Eindhoven', 'Driedaags sporttoernooi met verschillende sporten', DATE_ADD(CURDATE(), INTERVAL 90 DAY), DATE_ADD(CURDATE(), INTERVAL 92 DAY), 'aankomend', 2);

-- Project gebruikers koppelingen
INSERT INTO `project_gebruikers` (`project_id`, `gebruiker_id`, `rol`) VALUES
(1, 1, 'Manager'),
(1, 2, 'Assistent'),
(2, 1, 'Manager'),
(3, 2, 'Stagiair');

-- Voorbeeld taken
INSERT INTO `taken` (`project_id`, `naam`, `beschrijving`, `deadline`, `status`, `prioriteit`, `toegewezen_aan`, `aangemaakt_door`) VALUES
(1, 'Locatie bezoeken', 'Voorbezoek aan de locatie om logistiek te plannen', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'open', 'hoog', 2, 1),
(1, 'Artiesten boeken', 'Contact leggen met artiesten en contracten afronden', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'in_uitvoering', 'hoog', 1, 1),
(1, 'Promotie materiaal ontwerpen', 'Posters en flyers ontwerpen voor promotie', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'open', 'medium', 2, 1),
(2, 'Cateringbedrijf selecteren', 'Verschillende cateraars vergelijken en kiezen', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'open', 'medium', 1, 1),
(3, 'Vergunningen aanvragen', 'Alle benodigde vergunningen aanvragen bij gemeente', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'open', 'hoog', 1, 1);

-- Voorbeeld berichten
INSERT INTO `berichten` (`afzender_id`, `ontvanger_id`, `inhoud`, `gelezen`) VALUES
(1, 2, 'Hoi, kun je de planning voor het zomerfestival doorsturen?', 1),
(2, 1, 'Zeker, ik stuur het vanmiddag door!', 0),
(1, 2, 'Dank je, vergeet ook niet de contactgegevens van de leveranciers toe te voegen.', 0);

-- Voorbeeld gesprekken
INSERT INTO `gesprekken` (`naam`, `type`, `gebruiker1_id`, `gebruiker2_id`, `aangemaakt_door`, `laatste_bericht`) VALUES
(NULL, 'direct', 1, 2, 1, NOW()),
('Projectteam Noordwijk', 'groep', NULL, NULL, 1, NOW());

-- Gesprekken gebruikers
INSERT INTO `gesprekken_gebruikers` (`gesprek_id`, `gebruiker_id`, `ongelezen_berichten`) VALUES
(1, 1, 0),
(1, 2, 2),
(2, 1, 0),
(2, 2, 1);

-- Rooster voorbeeld data
INSERT INTO `rooster` (`gebruiker_id`, `dag`, `start_tijd`, `eind_tijd`, `locatie`, `opmerkingen`) VALUES
(1, CURDATE(), '09:00:00', '17:00:00', 'Kantoor Amsterdam', 'Projectoverleg om 10:00'),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '17:00:00', 'Kantoor Amsterdam', NULL),
(2, CURDATE(), '12:00:00', '20:00:00', 'Evenementlocatie Rotterdam', 'Festival setup'),
(2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '17:00:00', 'Kantoor Amsterdam', NULL),
(1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00', 'Kantoor Amsterdam', 'Teambespreking'),
(2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00', 'Kantoor Amsterdam', 'Teambespreking'),
(1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00:00', '16:00:00', 'Evenementlocatie Rotterdam', 'Locatiebezoek'),
(2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00:00', '16:00:00', 'Evenementlocatie Rotterdam', 'Locatiebezoek');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
