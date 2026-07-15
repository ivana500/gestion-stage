<?php/*
// 1. Inclure ton fichier de connexion mis à jour avec le port 3307
include('../Auth/config_db.php'); 

// 2. Définir les infos de l'admin
$email = 'admin@gestionstages.com';
$password_clair = 'admin123'; // Change ce mot de passe ici
$nom_complet = 'Administrateur Principal';
$telephone = '+237682336519';
$adresse = 'Douala, Cameroun';

// 3. Hacher le mot de passe de manière sécurisée
$password_hache = password_hash($password_clair, PASSWORD_BCRYPT);

try {
    // 4. Préparer la requête d'insertion
    $sql = "INSERT INTO UTILISATEUR (email, password, role, nom_complet, telephone, adresse) 
            VALUES (:email, :password, 'admin', :nom, :tel, :adresse)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email'    => $email,
        ':password' => $password_hache,
        ':nom'      => $nom_complet,
        ':tel'      => $telephone,
        ':adresse'  => $adresse
    ]);

    echo "Administrateur créé avec succès !";
} catch (PDOException $e) {
    echo "Erreur lors de la création : " . $e->getMessage();
}*/
?>