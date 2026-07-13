<?php
session_start();
include('../Auth/config_db.php');

// Vérification de la session et du rôle
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];
$message = "";
$message_type = "";

// Traitement du formulaire d'ajout
if (isset($_POST['ajouter_offre'])) {
    // Récupération et nettoyage des données de manière basique (PDO gère les injections)
    $titre = trim($_POST['titre']);
    $lieu = trim($_POST['lieu']);
    $type_stage = trim($_POST['type_stage']);
    $duree = trim($_POST['duree']);
    $date_limite = $_POST['date_limite'];
    $description = trim($_POST['description']);
    $statut = 'ouverte'; // Par défaut, l'offre est active à la publication

    if (!empty($titre) && !empty($lieu) && !empty($type_stage) && !empty($duree) && !empty($date_limite) && !empty($description)) {
        try {
            $sql = "INSERT INTO OFFRE_STAGE (titre, lieu, type_stage, duree, date_limite, description, statut, id_entreprise) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titre, $lieu, $type_stage, $duree, $date_limite, $description, $statut, $id_ent]);
            
            // Redirection vers la liste des offres après succès
            header('Location: OffrePub.php');
            exit();
        } catch (PDOException $e) {
            $message = "Une erreur est survenue lors de l'ajout de l'offre : " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une offre | Espace Entreprise</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #1a1d2d;
            --sidebar-dark: #111422;
            --card-dark: #23273a;
            --text-muted: #8a8d9a;
            --accent-blue: #3b82f6;
            --input-bg: #1f2335;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
        }

        /* SIDEBAR (Identique à OffrePub) */
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: var(--sidebar-dark);
            border-right: 1px solid #2d3142;
            position: fixed;
            padding: 20px 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0 25px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-blue);
            font-size: 1.1rem;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 12px 25px;
            display: flex;
            align-items: center;
            transition: 0.3s;
            text-decoration: none;
        }

        .nav-link i { margin-right: 15px; width: 20px; text-align: center; }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.1);
            color: white;
            border-left: 4px solid var(--accent-blue);
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
        }

        /* HEADER BOX */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 20px;
        }

        .btn-back {
            background: #2d3248;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #3d4363;
            color: white;
        }

        /* FORM CARD */
        .card-form {
            background: var(--card-dark);
            border-radius: 18px;
            padding: 35px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 900px;
            margin: 0 auto;
        }

        /* Form Controls personnalisés */
        .form-label {
            color: #e2e8f0;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            background-color: var(--input-bg) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px;
            padding: 12px 15px;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
            outline: none;
        }

        .form-control::placeholder {
            color: #5a5e73;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent-blue), #2563eb);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-building me-2"></i> TECH SOLUTIONS
    </div>
    
    <nav>
        <a href="dashEnt.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="pubOffre.php" class="nav-link active"><i class="fa-solid fa-plus-circle"></i> Publier une offre</a>
        <a href="OffrePub.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="paramEnt.php" class="nav-link"><i class="fa-solid fa-gear"></i> Paramètres</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>

<div class="main">

    <div class="header-box">
        <div>
            <h2 class="fw-bold mb-1">Publier une nouvelle offre</h2>
            <p class="text-muted mb-0">Remplissez les critères pour trouver votre futur stagiaire</p>
        </div>
        <a href="OffrePub.php" class="btn-back"><i class="fa-solid fa-arrow-left me-2"></i> Retour aux offres</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show border-0 max-width-900 mx-auto mb-4" role="alert" style="max-width: 900px; border-radius: 10px;">
            <i class="fa-solid <?php echo ($message_type == 'danger') ? 'fa-circle-xmark' : 'fa-circle-exclamation'; ?> me-2"></i>
            <?= $message; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card-form">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label"><i class="fa-solid fa-briefcase me-2 text-primary"></i>Titre de l'offre *</label>
                    <input type="text" name="titre" class="form-control" placeholder="Ex: Développeur PHP / Laravel H/F" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Lieu de stage *</label>
                    <input type="text" name="lieu" class="form-control" placeholder="Ex: Douala, Akwa" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label"><i class="fa-solid fa-layer-group me-2 text-primary"></i>Type de stage *</label>
                    <select name="type_stage" class="form-select" required>
                        <option value="" disabled selected>Sélectionner un type</option>
                        <option value="Académique">Académique</option>
                        <option value="Professionnel">Professionnel</option>
                        <option value="Pré-emploi">Pré-emploi</option>
                    </select>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="form-label"><i class="fa-solid fa-hourglass-half me-2 text-primary"></i>Durée *</label>
                    <input type="text" name="duree" class="form-control" placeholder="Ex: 2 mois, 6 mois" required>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="form-label"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Date limite de candidature *</label>
                    <input type="date" name="date_limite" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-align-left me-2 text-primary"></i>Description détaillée du profil & missions *</label>
                <textarea name="description" class="form-control" rows="6" placeholder="Décrivez les missions du stage, les technologies à utiliser, et le profil recherché (compétences, niveau d'études)..." required></textarea>
            </div>

            <div class="text-end mt-2">
                <button type="reset" class="btn btn-outline-secondary me-2 border-0 text-white py-3 px-4" style="border-radius: 10px;">Réinitialiser</button>
                <button type="submit" name="ajouter_offre" class="btn-submit">
                    <i class="fa-solid fa-paper-plane me-2"></i> Publier l'offre
                </button>
            </div>
        </form>
    </div>

</div>

</body>
</html>