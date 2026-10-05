<?php
/**
 * Database Connection: Suchinta's Guardian Angel
 * 
 * Lightweight PDO connection handler configured with utf8mb4 charset
 * and exception-based error reporting.
 */

$host    = 'localhost';
$dbname  = 'guardian_angel_db';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Re-throw with a clean message while preserving original code
    throw new PDOException("Database connection error: " . $e->getMessage(), (int)$e->getCode());
}

// Return the connection instance for versatile script inclusion
return $pdo;
