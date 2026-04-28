<?php
session_start();
include('../Auth/config_db.php');

// 1. Protection de l'accès
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];
$status_msg = "";

// 2. Traitement des formulaires (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // MISE À JOUR DU PROFIL
    if (isset($_POST['save_info'])) {
        $nom = htmlspecialchars($_POST['nom_complet']);
        $email = htmlspecialchars($_POST['email']);
        $tel = htmlspecialchars($_POST['telephone']);
        $adr = htmlspecialchars($_POST['adresse']);
        $siege = htmlspecialchars($_POST['siege_social']);
        
        $sql = "UPDATE UTILISATEUR SET nom_complet = ?, email = ?, telephone = ?, adresse = ?, siege_social = ? WHERE id_user = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nom, $email, $tel, $adr, $siege, $id_ent])) {
            $_SESSION['user_name'] = $nom;
            $status_msg = "<div class='alert alert-success border-0 shadow-sm animate__animated animate__fadeIn'>
                            <i class='fa-solid fa-circle-check me-2'></i>Profil mis à jour avec succès !
                          </div>";
        } else {
            $status_msg = "<div class='alert alert-danger border-0 shadow-sm'>Une erreur est survenue lors de la mise à jour.</div>";
        }
    }

    // MISE À JOUR DU MOT DE PASSE
    if (isset($_POST['save_password'])) {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        if ($new_pass !== $confirm_pass) {
            $status_msg = "<div class='alert alert-warning border-0 shadow-sm'>Les nouveaux mots de passe ne correspondent pas.</div>";
        } else {
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM UTILISATEUR WHERE id_user = ?");
            $stmt->execute([$id_ent]);
            $user = $stmt->fetch();

            if (password_verify($old_pass, $user['mot_de_passe'])) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE UTILISATEUR SET mot_de_passe = ? WHERE id_user = ?");
                $update->execute([$hashed, $id_ent]);
                $status_msg = "<div class='alert alert-success border-0 shadow-sm'>Mot de passe modifié avec succès.</div>";
            } else {
                $status_msg = "<div class='alert alert-danger border-0 shadow-sm'>L'ancien mot de passe est incorrect.</div>";
            }
        }
    }
}

// 3. Récupération des données fraîches
$stmt = $pdo->prepare("SELECT * FROM UTILISATEUR WHERE id_user = ?");
$stmt->execute([$id_ent]);
$ent = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Entreprise - <?= htmlspecialchars($ent['nom_complet']) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-bg: #1e1e2d;
            --input-focus: #2b2b40;
        }
        
        body { 
            background-color: #0f172a; 
            color: #e2e8f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content { padding: 50px 20px; }

        .custom-table-card {
            background: var(--secondary-bg);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            border-left: 5px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 30px;
        }

        .form-control {
            background-color: #161625;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: var(--input-focus);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
            color: white;
        }

        .nav-pills .nav-link {
            color: #94a3b8;
            font-weight: 600;
            border-radius: 10px;
            margin-right: 10px;
            transition: 0.3s;
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-save {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .danger-zone {
            border: 1px dashed #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }
    </style>
</head>
<body>

<div class="container main-content">
    <div class="row">
        <div class="col-lg-10 mx-auto text-center mb-5">
            <h2 class="fw-bold">Configuration du Compte</h2>
            <p class="text-muted">Personnalisez votre présence sur la plateforme et sécurisez vos accès.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <?= $status_msg ?>

            <div class="custom-table-card mt-3">
                <ul class="nav nav-pills mb-5 justify-content-center" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-profile"><i class="fa-solid fa-id-card me-2"></i>Profil Entreprise</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-security"><i class="fa-solid fa-shield-halved me-2"></i>Sécurité</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    
                    <div class="tab-pane fade show active" id="pills-profile">
                        <h5 class="section-title">Informations Générales</h5>
                        <form method="POST">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">NOM DE L'ÉTABLISSEMENT</label>
                                    <input type="text" name="nom_complet" class="form-control" value="<?= htmlspecialchars($ent['nom_complet']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">EMAIL PROFESSIONNEL</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($ent['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">TÉLÉPHONE</label>
                                    <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($ent['telephone'] ?? '') ?>" placeholder="+237 ...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">SIÈGE SOCIAL (VILLE)</label>
                                    <input type="text" name="siege_social" class="form-control" value="<?= htmlspecialchars($ent['siege_social'] ?? '') ?>" placeholder="Ex: Douala, Cameroun">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-bold">ADRESSE GÉOGRAPHIQUE</label>
                                    <textarea name="adresse" class="form-control" rows="3" placeholder="Quartier, Rue, Immeuble..."><?= htmlspecialchars($ent['adresse'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" name="save_info" class="btn btn-save text-white">
                                        <i class="fa-solid fa-check me-2"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="pills-security">
                        <h5 class="section-title text-warning border-warning">Changer le mot de passe</h5>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold">MOT DE PASSE ACTUEL</label>
                                <input type="password" name="old_pass" class="form-control" required>
                            </div>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">NOUVEAU MOT DE PASSE</label>
                                    <input type="password" name="new_pass" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">CONFIRMATION</label>
                                    <input type="password" name="confirm_pass" class="form-control" required>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="save_password" class="btn btn-outline-warning rounded-pill px-5">
                                    <i class="fa-solid fa-lock-open me-2"></i>Mettre à jour
                                </button>
                            </div>
                        </form>

                        <div class="danger-zone p-4 rounded-3 mt-5">
                            <h6 class="text-danger fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i> Zone de danger</h6>
                            <p class="text-muted small mb-3">La suppression de votre compte entraînera la perte définitive de toutes vos offres publiées et des candidatures associées.</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous absolument sûr ? Cette action est irréversible.')">
                                Supprimer mon compte entreprise
                            </button>
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