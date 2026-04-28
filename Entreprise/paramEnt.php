<?php
session_start();
include('../Auth/config_db.php');

// 1. Protection de l'accès : Vérifie si l'utilisateur est connecté et est une entreprise
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];
$status_msg = "";

// 2. Traitement des formulaires (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- MISE À JOUR DU PROFIL (Gère les deux tables UTILISATEUR et ENTREPRISE) ---
    if (isset($_POST['save_info'])) {
        $nom   = htmlspecialchars($_POST['nom_complet']);
        $email = htmlspecialchars($_POST['email']);
        $tel   = htmlspecialchars($_POST['telephone']);
        $adr   = htmlspecialchars($_POST['adresse']);
        $siege = htmlspecialchars($_POST['siege_social']);
        
        try {
            // Début de la transaction pour garantir que les deux tables sont mises à jour ensemble
            $pdo->beginTransaction();

            // A. Mise à jour de la table UTILISATEUR (Infos communes)
            $sqlU = "UPDATE UTILISATEUR SET nom_complet = ?, email = ?, telephone = ?, adresse = ? WHERE id_user = ?";
            $stmtU = $pdo->prepare($sqlU);
            $stmtU->execute([$nom, $email, $tel, $adr, $id_ent]);

            // B. Mise à jour ou Insertion dans la table ENTREPRISE (Siège social)
            // ON DUPLICATE KEY UPDATE permet de créer la ligne si elle n'existe pas encore pour cet ID
            $sqlE = "INSERT INTO ENTREPRISE (id_user, siege_social) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE siege_social = VALUES(siege_social)";
            $stmtE = $pdo->prepare($sqlE);
            $stmtE->execute([$id_ent, $siege]);

            $pdo->commit();
            
            // Mise à jour du nom en session pour l'affichage immédiat
            $_SESSION['user_name'] = $nom;
            
            $status_msg = "<div class='alert alert-success border-0 shadow-sm animate__animated animate__fadeIn'>
                            <i class='fa-solid fa-circle-check me-2'></i>Profil et siège social enregistrés avec succès !
                          </div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $status_msg = "<div class='alert alert-danger border-0 shadow-sm'>
                            <i class='fa-solid fa-triangle-exclamation me-2'></i>Erreur lors de la mise à jour : " . $e->getMessage() . "
                          </div>";
        }
    }

    // --- MISE À JOUR DU MOT DE PASSE (Cible la colonne 'password' de ton SQL) ---
    if (isset($_POST['save_password'])) {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        if ($new_pass !== $confirm_pass) {
            $status_msg = "<div class='alert alert-warning border-0 shadow-sm'>Les nouveaux mots de passe ne correspondent pas.</div>";
        } else {
            // On vérifie d'abord l'ancien mot de passe
            $stmt = $pdo->prepare("SELECT password FROM UTILISATEUR WHERE id_user = ?");
            $stmt->execute([$id_ent]);
            $user = $stmt->fetch();

            if ($user && password_verify($old_pass, $user['password'])) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE UTILISATEUR SET password = ? WHERE id_user = ?");
                $update->execute([$hashed, $id_ent]);
                $status_msg = "<div class='alert alert-success border-0 shadow-sm'>Votre mot de passe a été modifié.</div>";
            } else {
                $status_msg = "<div class='alert alert-danger border-0 shadow-sm'>L'ancien mot de passe est incorrect.</div>";
            }
        }
    }
}

// 3. Récupération des données pour l'affichage (Jointure entre UTILISATEUR et ENTREPRISE)
$sql = "SELECT u.*, e.siege_social 
        FROM UTILISATEUR u 
        LEFT JOIN ENTREPRISE e ON u.id_user = e.id_user 
        WHERE u.id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_ent]);
$ent = $stmt->fetch();

// Si aucune donnée n'est trouvée (sécurité supplémentaire)
if (!$ent) {
    echo "Erreur : Impossible de charger les données du profil.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres - <?= htmlspecialchars($ent['nom_complet']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --dark-card: #1e1e2d;
            --input-bg: #2b2b40;
        }
        body { background-color: #0f172a; color: white; }
        
        .main-content { padding: 40px; }
        
        .custom-table-card {
            background: var(--dark-card);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            padding: 12px;
        }

        .form-control:focus {
            background-color: var(--input-bg);
            color: white;
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .btn-save {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
        }

        .section-title {
            font-size: 1.1rem;
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 25px;
        }

        .nav-tabs-custom .nav-link {
            color: #94a3b8;
            border: none;
            padding: 10px 20px;
        }

        .nav-tabs-custom .nav-link.active {
            background: none;
            color: white;
            border-bottom: 2px solid var(--primary-color);
        }
    </style>
</head>
<body>

<div class="main-content">

    <div class="mb-4">
        <h2 class="fw-bold">Paramètres</h2>
        <p class="text-muted">Gérez vos informations et la sécurité de votre compte entreprise.</p>
    </div>
    <a class="btn btn-outline-primary rounded-pill px-4" href="dashEnt.php">Retour</a>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <?= $status_msg ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8 mx-auto">
            <div class="custom-table-card shadow">
                
                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="configTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-panel" type="button">Profil</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-panel" type="button">Sécurité</button>
                    </li>
                </ul>

                <div class="tab-content" id="configTabsContent">
                    
                    <div class="tab-pane fade show active" id="info-panel" role="tabpanel">
                        <h5 class="section-title">Informations de l'entreprise</h5>
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-12 text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 1.5rem;">
                                            <?= strtoupper(substr($ent['nom_complet'], 0, 1)) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase opacity-50">Nom de la structure</label>
                                    <input type="text" name="nom_complet" class="form-control" value="<?= htmlspecialchars($ent['nom_complet']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-uppercase opacity-50">Email de contact</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($ent['email']) ?>" required>
                                </div>
                                 <div class="col-md-6">
                                    <label class="form-label small text-uppercase opacity-50">Telephone</label>
                                          <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($ent['telephone'] ?? '') ?>">     
                                 </div>

                                    <div class="col-md-6">
                                    <label class="form-label small text-uppercase opacity-50">Siege social</label>
                                    <input type="text" name="siege_social" class="form-control" value="<?= htmlspecialchars($ent['siege_social'] ?? '') ?>">    
                                   </div>
                                   <div class="col-md-6">
                                    <label class="form-label small text-uppercase opacity-50">Adresse</label>
                                        <textarea name="adresse" class="form-control"><?= htmlspecialchars($ent['adresse'] ?? '') ?></textarea>
                                   </div>

                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" name="save_info" class="btn btn-save text-white rounded-pill">
                                        <i class="fa-solid fa-floppy-disk me-2"></i> Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="security-panel" role="tabpanel">
                        <h5 class="section-title text-warning">Changer le mot de passe</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small text-uppercase opacity-50">Ancien mot de passe</label>
                                <input type="password" name="old_pass" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small text-uppercase opacity-50">Nouveau mot de passe</label>
                                    <input type="password" name="new_pass" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small text-uppercase opacity-50">Confirmer le mot de passe</label>
                                    <input type="password" name="confirm_pass" class="form-control" required>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" name="save_password" class="btn btn-outline-warning rounded-pill px-4">
                                    Mettre à jour le mot de passe
                                </button>
                            </div>
                        </form>

                        <div class="mt-5 pt-4 border-top border-secondary">
                            <h6 class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> Zone critique</h6>
                            <p class="text-muted small">Une fois votre compte supprimé, toutes vos offres et candidatures seront définitivement effacées.</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Attention ! Voulez-vous vraiment supprimer votre compte ?')">Supprimer le compte entreprise</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>