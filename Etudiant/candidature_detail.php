<?php
if ($_FILES['file_signe']['error'] == 0) {
    $dossier = 'uploads/';
    $nom_fichier = 'conv_signee_' . $_POST['id_cand'] . '.pdf';
    
    if (move_uploaded_file($_FILES['file_signe']['tmp_name'], $dossier . $nom_fichier)) {
        // Enregistre le chemin dans ta base
        $sql = "INSERT INTO DOCUMENTS_STAGE (id_candidature, type_document, chemin_fichier) VALUES (?, 'signee_etudiant', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['id_cand'], $dossier . $nom_fichier]);
        echo "Fichier envoyé avec succès !";
    }
}
?>

<form action="candidature_detail.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_cand" value="<?= $id_cand ?>">
    <label>Envoyer ma convention signée :</label>
    <input type="file" name="file_signe" accept="application/pdf" required>
    <button type="submit">Envoyer</button>
</form>