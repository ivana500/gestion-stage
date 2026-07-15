<?php
session_start();
include('../Auth/config_db.php');

if (isset($_GET['file'])) {
    $fileName = basename($_GET['file']); // Sécurité : basename empêche de sortir du dossier
    $filePath = '../uploads/rapports/' . $fileName;

    if (file_exists($filePath)) {
        // Définition des headers pour forcer le téléchargement du PDF
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    } else {
        echo "Erreur : Le fichier n'existe pas sur le serveur.";
    }
}
?>