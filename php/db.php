<?php
$DB_HOST = 'localhost';
$DB_NAME = 'milegajo'; 
$DB_USER = 'root';
$DB_PASS = '123456';
$DB_CHAR = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHAR}",
        $DB_USER,
        $DB_PASS,
        $options
    );
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
