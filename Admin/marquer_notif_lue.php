<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('refuse');
}

$id_notif = (int)($_GET['id'] ?? 0);
$id_etudiant = $_SESSION['user_id'];

// Sécurité : on ne marque comme lue QUE si la notification appartient bien à cet étudiant
$pdo->prepare("UPDATE notifications SET lu = 1 WHERE id = ? AND id_user = ?")
    ->execute([$id_notif, $id_etudiant]);

echo 'ok';
