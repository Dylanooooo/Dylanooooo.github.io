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

--
-- Database: `flitz_events`
--

-- --------------------------------------------------------

--
-- Table structure for table `gebruikers`
--

CREATE TABLE `gebruikers` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `wachtwoord` varchar(255) NOT NULL,
  `rol` enum('stagiair','admin') DEFAULT 'stagiair',
  `school` varchar(255) NOT NULL,
  `opleiding` varchar(255) NOT NULL,
  `uren_per_week` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gebruikers`
--

INSERT INTO `gebruikers` (`id`, `naam`, `email`, `wachtwoord`, `rol`, `school`, `opleiding`, `uren_per_week`) VALUES
(10, 'Admin', 'admin@flitz.nl', '$2y$10$tp0wuUhopnRUcB8ai2RkP.6IaxN783Tr7b2nIfE.P81OGiIkAljJe', 'admin', '', '', 0),
(11, 'Pien', 'test@mail.com', '$2y$10$2Xh9.d/0PCXkowEKJuJoP./c9esTOSsRCe.xrm5kre2KUkKApv4NW', 'stagiair', '', '', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gebruikers`
--
ALTER TABLE `gebruikers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gebruikers`
--
ALTER TABLE `gebruikers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

-- --------------------------------------------------------

--
-- Table structure for table `projecten`
--

CREATE TABLE `projecten` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `start_datum` date NOT NULL,
  `eind_datum` date NOT NULL,
  `status` enum('aankomend','actief','afgerond') NOT NULL DEFAULT 'aankomend',
  `voortgang` int(11) NOT NULL DEFAULT 0,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projecten`
--

INSERT INTO `projecten` (`id`, `naam`, `beschrijving`, `start_datum`, `eind_datum`, `status`, `voortgang`, `datum_aangemaakt`) VALUES
(1, 'Zomerfestival Noordwijk', 'Driedaags festival op het strand van Noordwijk', '2025-06-15', '2025-06-18', 'actief', 35, '2025-03-01 12:00:00'),
(2, 'Bedrijfsevent Amstelveen', 'Eendaags bedrijfsevent voor 250 personen', '2025-07-05', '2025-07-05', 'aankomend', 10, '2025-03-05 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `taken`
--

CREATE TABLE `taken` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `naam` varchar(255) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('open','in_uitvoering','afgerond') NOT NULL DEFAULT 'open',
  `toegewezen_aan` int(11) DEFAULT NULL,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taken`
--

INSERT INTO `taken` (`id`, `project_id`, `naam`, `beschrijving`, `deadline`, `status`, `toegewezen_aan`, `datum_aangemaakt`) VALUES
(1, 1, 'Leverancierslijst controleren', 'Controleer alle leveranciers en bevestig hun beschikbaarheid', '2025-03-29', 'open', 11, '2025-03-15 10:00:00'),
(2, 1, 'Voorbereiden beachvolleybaltoernooi', 'Plan het volledige toernooi uit en maak een schema', '2025-03-30', 'open', 11, '2025-03-16 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `berichten`
--

CREATE TABLE `berichten` (
  `id` int(11) NOT NULL,
  `afzender_id` int(11) NOT NULL,
  `ontvanger_id` int(11) NOT NULL,
  `bericht` text NOT NULL,
  `gelezen` tinyint(1) NOT NULL DEFAULT 0,
  `datum_verzonden` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for additional tables
--

ALTER TABLE `projecten`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `taken`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `toegewezen_aan` (`toegewezen_aan`);

ALTER TABLE `berichten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `afzender_id` (`afzender_id`),
  ADD KEY `ontvanger_id` (`ontvanger_id`);

--
-- AUTO_INCREMENT for additional tables
--

ALTER TABLE `projecten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `taken`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `berichten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for additional tables
--

ALTER TABLE `taken`
  ADD CONSTRAINT `taken_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projecten` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `taken_ibfk_2` FOREIGN KEY (`toegewezen_aan`) REFERENCES `gebruikers` (`id`) ON DELETE SET NULL;

ALTER TABLE `berichten`
  ADD CONSTRAINT `berichten_ibfk_1` FOREIGN KEY (`afzender_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `berichten_ibfk_2` FOREIGN KEY (`ontvanger_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
