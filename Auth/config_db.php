<?php
// config_db.php

$host = 'localhost';
$dbname = 'gestion_stages';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // gestion des erreurs
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // résultats en tableau associatif
            PDO::ATTR_EMULATE_PREPARES => false // sécurité
        ]
    );

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>