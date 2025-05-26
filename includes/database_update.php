<?php
// This file contains functions to check for and create missing database columns and tables
// Include this in config.php to automatically maintain database structure

/**
 * Check if a table exists in the database
 * @param PDO $pdo Database connection
 * @param string $table Table name
 * @return bool True if table exists
 */
function tableExists($pdo, $table) {
    try {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $pdo->query($sql);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error checking for table: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a column exists in a table
 * @param PDO $pdo Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @return bool True if column exists
 */
function columnExists($pdo, $table, $column) {
    try {
        $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
        $stmt = $pdo->query($sql);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error checking for column: " . $e->getMessage());
        return false;
    }
}

/**
 * Add column to table if it doesn't exist
 * @param PDO $pdo Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @param string $definition Column definition (e.g., "VARCHAR(100) DEFAULT NULL")
 * @return bool True if column was added or already exists
 */
function addColumnIfNotExists($pdo, $table, $column, $definition) {
    if (!tableExists($pdo, $table)) {
        error_log("Table '$table' does not exist, cannot add column '$column'");
        return false;
    }
    
    if (!columnExists($pdo, $table, $column)) {
        try {
            $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
            $pdo->exec($sql);
            error_log("Added column '$column' to table '$table'");
            return true;
        } catch (PDOException $e) {
            error_log("Error adding column '$column' to table '$table': " . $e->getMessage());
            return false;
        }
    }
    return true; // Column already exists
}

/**
 * Create table if it doesn't exist
 * @param PDO $pdo Database connection
 * @param string $table Table name
 * @param string $sql CREATE TABLE SQL statement
 * @return bool True if table was created or already exists
 */
function createTableIfNotExists($pdo, $table, $sql) {
    if (!tableExists($pdo, $table)) {
        try {
            $pdo->exec($sql);
            error_log("Created table '$table'");
            return true;
        } catch (PDOException $e) {
            error_log("Error creating table '$table': " . $e->getMessage());
            return false;
        }
    }
    return true; // Table already exists
}

/**
 * Initialize core database tables
 * @param PDO $pdo Database connection
 */
function initializeCoreTables($pdo) {
    // Users table
    $gebruikers_sql = "CREATE TABLE IF NOT EXISTS `gebruikers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `naam` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL UNIQUE,
        `wachtwoord` varchar(255) NOT NULL,
        `rol` enum('admin','stagiair','medewerker') NOT NULL DEFAULT 'stagiair',
        `school` varchar(100) DEFAULT NULL,
        `opleiding` varchar(100) DEFAULT NULL,
        `uren_per_week` int(11) DEFAULT NULL,
        `profile_image` varchar(255) DEFAULT NULL,
        `startdatum` date DEFAULT NULL,
        `einddatum` date DEFAULT NULL,
        `actief` tinyint(1) NOT NULL DEFAULT 1,
        `datum_aangemaakt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `laatst_ingelogd` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    createTableIfNotExists($pdo, 'gebruikers', $gebruikers_sql);

    // Projects table
    $projecten_sql = "CREATE TABLE IF NOT EXISTS `projecten` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `naam` varchar(200) NOT NULL,
        `beschrijving` text DEFAULT NULL,
        `start_datum` date NOT NULL,
        `eind_datum` date NOT NULL,
        `status` enum('aankomend','actief','afgerond','geannuleerd') NOT NULL DEFAULT 'aankomend',
        `voortgang` int(11) NOT NULL DEFAULT 0,
        `budget` decimal(10,2) DEFAULT NULL,
        `project_manager_id` int(11) DEFAULT NULL,
        `datum_aangemaakt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `laatst_bijgewerkt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    createTableIfNotExists($pdo, 'projecten', $projecten_sql);

    // Tasks table
    $taken_sql = "CREATE TABLE IF NOT EXISTS `taken` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `project_id` int(11) NOT NULL,
        `naam` varchar(200) NOT NULL,
        `beschrijving` text DEFAULT NULL,
        `status` enum('open','in_uitvoering','afgerond','geannuleerd') NOT NULL DEFAULT 'open',
        `prioriteit` enum('laag','gemiddeld','hoog','kritiek') NOT NULL DEFAULT 'gemiddeld',
        `toegewezen_aan` int(11) DEFAULT NULL,
        `deadline` date DEFAULT NULL,
        `geschatte_uren` decimal(5,2) DEFAULT NULL,
        `werkelijke_uren` decimal(5,2) DEFAULT NULL,
        `datum_aangemaakt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `datum_voltooid` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    createTableIfNotExists($pdo, 'taken', $taken_sql);

    // Messages table - Fixed to use 'bericht' as primary column
    $berichten_sql = "CREATE TABLE IF NOT EXISTS `berichten` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `afzender_id` int(11) NOT NULL,
        `ontvanger_id` int(11) NOT NULL,
        `bericht` text NOT NULL,
        `inhoud` text GENERATED ALWAYS AS (`bericht`) VIRTUAL,
        `gelezen` tinyint(1) NOT NULL DEFAULT 0,
        `datum_verzonden` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `timestamp` timestamp GENERATED ALWAYS AS (`datum_verzonden`) VIRTUAL,
        PRIMARY KEY (`id`),
        INDEX `idx_ontvanger_gelezen` (`ontvanger_id`, `gelezen`),
        INDEX `idx_afzender_ontvanger` (`afzender_id`, `ontvanger_id`),
        INDEX `idx_datum_verzonden` (`datum_verzonden`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    createTableIfNotExists($pdo, 'berichten', $berichten_sql);

    // Schedule/Roster table
    $rooster_sql = "CREATE TABLE IF NOT EXISTS `rooster` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `gebruiker_id` int(11) NOT NULL,
        `dag` date NOT NULL,
        `start_tijd` time NOT NULL,
        `eind_tijd` time NOT NULL,
        `locatie` varchar(200) DEFAULT NULL,
        `opmerkingen` text DEFAULT NULL,
        `type` enum('werk','afspraak','training','evenement') NOT NULL DEFAULT 'werk',
        `aangemaakt_door` int(11) NOT NULL,
        `datum_aangemaakt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    createTableIfNotExists($pdo, 'rooster', $rooster_sql);
}

/**
 * Check and update the database structure
 * @param PDO $pdo Database connection
 */
function updateDatabaseStructure($pdo) {
    // Initialize core tables first
    initializeCoreTables($pdo);
    
    // Add missing columns to existing tables
    addColumnIfNotExists($pdo, 'gebruikers', 'school', 'VARCHAR(100) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'opleiding', 'VARCHAR(100) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'uren_per_week', 'INT(11) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'profile_image', 'VARCHAR(255) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'startdatum', 'DATE DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'einddatum', 'DATE DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'laatst_ingelogd', 'TIMESTAMP NULL DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'actief', 'TINYINT(1) NOT NULL DEFAULT 1');
    
    // Add missing columns to projecten table
    addColumnIfNotExists($pdo, 'projecten', 'budget', 'DECIMAL(10,2) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'projecten', 'project_manager_id', 'INT(11) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'projecten', 'laatst_bijgewerkt', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    
    // Add missing columns to taken table
    addColumnIfNotExists($pdo, 'taken', 'prioriteit', "ENUM('laag','medium','hoog','kritiek') NOT NULL DEFAULT 'medium'");
    addColumnIfNotExists($pdo, 'taken', 'geschatte_uren', 'DECIMAL(5,2) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'taken', 'werkelijke_uren', 'DECIMAL(5,2) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'taken', 'datum_voltooid', 'TIMESTAMP NULL DEFAULT NULL');
    
    // Ensure berichten table has correct structure
    addColumnIfNotExists($pdo, 'berichten', 'bericht', 'TEXT NOT NULL');
    addColumnIfNotExists($pdo, 'berichten', 'timestamp', 'TIMESTAMP GENERATED ALWAYS AS (`datum_verzonden`) VIRTUAL');
    
    // Update rol enum to include 'medewerker'
    try {
        $pdo->exec("ALTER TABLE `gebruikers` MODIFY `rol` ENUM('admin','stagiair','medewerker') NOT NULL DEFAULT 'stagiair'");
    } catch (PDOException $e) {
        // Column might already have the correct enum values
        error_log("Note: Could not update rol enum (might already be correct): " . $e->getMessage());
    }
    
    // Add missing columns to rooster table
    addColumnIfNotExists($pdo, 'rooster', 'type', "ENUM('werk','afspraak','training','evenement') NOT NULL DEFAULT 'werk'");
    addColumnIfNotExists($pdo, 'rooster', 'aangemaakt_door', 'INT(11) NOT NULL DEFAULT 1');
    
    // Add indexes for better performance
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS `idx_berichten_ontvanger_gelezen` ON `berichten` (`ontvanger_id`, `gelezen`)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS `idx_berichten_afzender_ontvanger` ON `berichten` (`afzender_id`, `ontvanger_id`)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS `idx_berichten_datum` ON `berichten` (`datum_verzonden`)");
    } catch (PDOException $e) {
        // Indexes might already exist
        error_log("Note: Could not create indexes (might already exist): " . $e->getMessage());
    }
    
    error_log("Database structure update completed successfully");
}

/**
 * Fix message table structure for existing databases
 * @param PDO $pdo Database connection
 */
function fixMessageTableStructure($pdo) {
    try {
        // Check if we need to migrate from 'inhoud' to 'bericht'
        if (columnExists($pdo, 'berichten', 'inhoud') && !columnExists($pdo, 'berichten', 'bericht')) {
            // Rename 'inhoud' column to 'bericht'
            $pdo->exec("ALTER TABLE `berichten` CHANGE `inhoud` `bericht` TEXT NOT NULL");
            error_log("Migrated 'inhoud' column to 'bericht' in berichten table");
        } elseif (columnExists($pdo, 'berichten', 'inhoud') && columnExists($pdo, 'berichten', 'bericht')) {
            // Copy data from 'inhoud' to 'bericht' if both exist but 'bericht' is empty
            $pdo->exec("UPDATE `berichten` SET `bericht` = `inhoud` WHERE `bericht` = '' OR `bericht` IS NULL");
            error_log("Copied data from 'inhoud' to 'bericht' column");
        }
        
        // Ensure 'timestamp' virtual column exists
        if (!columnExists($pdo, 'berichten', 'timestamp')) {
            $pdo->exec("ALTER TABLE `berichten` ADD COLUMN `timestamp` TIMESTAMP GENERATED ALWAYS AS (`datum_verzonden`) VIRTUAL");
            error_log("Added 'timestamp' virtual column to berichten table");
        }
        
    } catch (PDOException $e) {
        error_log("Error fixing message table structure: " . $e->getMessage());
    }
}
