-- Nieuwe tabellen voor core functionaliteit

-- Projecten tabel
CREATE TABLE IF NOT EXISTS `projecten` (
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

-- Taken tabel
CREATE TABLE IF NOT EXISTS `taken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `naam` varchar(255) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('open','in_uitvoering','afgerond') NOT NULL DEFAULT 'open',
  `toegewezen_aan` int(11) DEFAULT NULL,
  `datum_aangemaakt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `toegewezen_aan` (`toegewezen_aan`),
  CONSTRAINT `taken_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projecten` (`id`) ON DELETE CASCADE,
  CONSTRAINT `taken_ibfk_2` FOREIGN KEY (`toegewezen_aan`) REFERENCES `gebruikers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Berichten tabel voor chat functionaliteit
CREATE TABLE IF NOT EXISTS `berichten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afzender_id` int(11) NOT NULL,
  `ontvanger_id` int(11) NOT NULL,
  `bericht` text NOT NULL,
  `gelezen` tinyint(1) NOT NULL DEFAULT 0,
  `datum_verzonden` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `afzender_id` (`afzender_id`),
  KEY `ontvanger_id` (`ontvanger_id`),
  CONSTRAINT `berichten_ibfk_1` FOREIGN KEY (`afzender_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `berichten_ibfk_2` FOREIGN KEY (`ontvanger_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Voorbeelddata voor projecten
INSERT INTO `projecten` (`naam`, `beschrijving`, `start_datum`, `eind_datum`, `status`, `voortgang`) VALUES
('Zomerfestival Noordwijk', 'Driedaags festival op het strand van Noordwijk', '2025-06-15', '2025-06-18', 'actief', 35),
('Bedrijfsevent Amstelveen', 'Eendaags bedrijfsevent voor 250 personen', '2025-07-05', '2025-07-05', 'aankomend', 10),
('Muziekfestival Groningen', 'Muziekfestival in centrum Groningen', '2025-07-22', '2025-07-24', 'aankomend', 5),
('Bruiloft Amsterdam', 'Luxe bruiloft voor 120 gasten', '2025-05-02', '2025-05-02', 'afgerond', 100),
('Bedrijfsjubileum Utrecht', 'Vijftigjarig jubileum met 300 gasten', '2025-04-12', '2025-04-12', 'afgerond', 100),
('Sporttoernooi Eindhoven', 'Driedaags sporttoernooi voor bedrijven', '2025-08-10', '2025-08-12', 'aankomend', 2);

-- Voorbeelddata voor taken
INSERT INTO `taken` (`project_id`, `naam`, `beschrijving`, `deadline`, `status`, `toegewezen_aan`) VALUES
(1, 'Leverancierslijst controleren', 'Controleer alle leveranciers en bevestig hun beschikbaarheid', '2025-03-29', 'open', 11),
(1, 'Voorbereiden beachvolleybaltoernooi', 'Plan het volledige toernooi uit en maak een schema', '2025-03-30', 'open', 11),
(1, 'Contact opnemen met DJ\'s', 'Bevestig alle DJ\'s en hun tijdsloten', '2025-03-25', 'afgerond', 11);
