<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit();
}

$id_notif = (int)($_GET['id'] ?? 0);
$id_etudiant = $_SESSION['user_id'];

// Sécurité : on ne marque comme lue QUE si la notification appartient bien à cet étudiant
$pdo->prepare("UPDATE notifications SET lu = 1 WHERE id = ? AND id_user = ?")
    ->execute([$id_notif, $id_etudiant]);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE id_user = ? AND lu = 0");
$stmt->execute([$id_etudiant]);
$remainingUnread = (int)$stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'remaining_unread' => $remainingUnread]);
