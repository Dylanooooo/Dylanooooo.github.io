<?php
// This file contains functions to check for and create missing database columns
// Include this in config.php to automatically maintain database structure

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
    if (!columnExists($pdo, $table, $column)) {
        try {
            $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
            $pdo->exec($sql);
            error_log("Added column '$column' to table '$table'");
            return true;
        } catch (PDOException $e) {
            error_log("Error adding column: " . $e->getMessage());
            return false;
        }
    }
    return true; // Column already exists
}

/**
 * Check and update the database structure
 * @param PDO $pdo Database connection
 */
function updateDatabaseStructure($pdo) {
    // Add missing columns to gebruikers table
    addColumnIfNotExists($pdo, 'gebruikers', 'school', 'VARCHAR(100) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'opleiding', 'VARCHAR(100) DEFAULT NULL');
    addColumnIfNotExists($pdo, 'gebruikers', 'uren_per_week', 'INT(11) DEFAULT NULL');
}
