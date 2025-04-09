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
  `datum_tijd` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `afzender_id` (`afzender_id`),
  KEY `ontvanger_id` (`ontvanger_id`),
  CONSTRAINT `berichten_ibfk_1` FOREIGN KEY (`afzender_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `berichten_ibfk_2` FOREIGN KEY (`ontvanger_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Chat gesprekken tabel (houdt bij welke gesprekken actief zijn)
CREATE TABLE IF NOT EXISTS `gesprekken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gebruiker1_id` int(11) NOT NULL,
  `gebruiker2_id` int(11) NOT NULL,
  `laatste_bericht_id` int(11) DEFAULT NULL,
  `laatste_activiteit` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unieke_gesprekken` (`gebruiker1_id`,`gebruiker2_id`),
  KEY `gebruiker2_id` (`gebruiker2_id`),
  KEY `laatste_bericht_id` (`laatste_bericht_id`),
  CONSTRAINT `gesprekken_ibfk_1` FOREIGN KEY (`gebruiker1_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gesprekken_ibfk_2` FOREIGN KEY (`gebruiker2_id`) REFERENCES `gebruikers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gesprekken_ibfk_3` FOREIGN KEY (`laatste_bericht_id`) REFERENCES `berichten` (`id`) ON DELETE SET NULL
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

-- Voorbeelddata voor berichten
INSERT INTO `berichten` (`afzender_id`, `ontvanger_id`, `bericht`, `gelezen`, `datum_tijd`) VALUES
(11, 12, 'Hallo! Hoe gaat het met jouw taken voor het Noordwijk project?', 1, NOW() - INTERVAL 2 HOUR),
(12, 11, 'Hey! Het gaat goed, ik ben bezig met de voorbereidingen voor het event.', 1, NOW() - INTERVAL 1 HOUR - INTERVAL 58 MINUTE),
(12, 11, 'Ik heb de locatie al bezocht en foto''s gemaakt.', 1, NOW() - INTERVAL 1 HOUR - INTERVAL 58 MINUTE),
(11, 12, 'Perfect! Kun je die foto''s delen in het projectkanaal?', 1, NOW() - INTERVAL 1 HOUR - INTERVAL 55 MINUTE),
(12, 11, 'Zeker, ik zal ze vanmiddag uploaden!', 1, NOW() - INTERVAL 1 HOUR - INTERVAL 54 MINUTE),
(11, 12, 'Super, bedankt! Heb je nog vragen over je taken?', 0, NOW() - INTERVAL 1 HOUR - INTERVAL 52 MINUTE);

-- Voorbeelddata voor gesprekken
INSERT INTO `gesprekken` (`gebruiker1_id`, `gebruiker2_id`, `laatste_bericht_id`, `laatste_activiteit`) VALUES
(11, 12, 6, NOW() - INTERVAL 1 HOUR - INTERVAL 52 MINUTE);
