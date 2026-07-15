<?php
session_start();
include('../Auth/config_db.php');

// Activer l'affichage des erreurs PDO pour déboguer
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_etudiant = $_SESSION['user_id'];

// 2. Récupération utilisateur
$stmt_user = $pdo->prepare("SELECT nom_complet FROM UTILISATEUR WHERE id_user = ?");
$stmt_user->execute([$id_etudiant]);
$user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);
$nom_complet = $user_info ? htmlspecialchars($user_info['nom_complet']) : "Étudiant";

// 3. Stats Offres
$total_offres_dispo = $pdo->query("SELECT COUNT(*) FROM OFFRE_STAGE WHERE statut = 'ouverte'")->fetchColumn();
$offres_cette_semaine = $pdo->query("SELECT COUNT(*) FROM OFFRE_STAGE WHERE statut = 'ouverte' AND date_limite >= CURDATE()")->fetchColumn();

// 4. STATISTIQUE 2 : Compteur de candidatures
$stmt_cand_total = $pdo->prepare("SELECT COUNT(*) FROM CANDIDATURE WHERE id_etudiant = ?");
$stmt_cand_total->execute([$id_etudiant]);
$total_mes_candidatures = $stmt_cand_total->fetchColumn();

// 5. STATISTIQUE 3 : Stage en cours
$stmt_stage_en_cours = $pdo->prepare("SELECT COUNT(*) FROM STAGE WHERE id_etudiant = ? AND statut_stage IN ('en_cours', 'a_venir')");
$stmt_stage_en_cours->execute([$id_etudiant]);
$stage_en_cours = $stmt_stage_en_cours->fetchColumn();

// 6. TABLEAU : Utilisation de LEFT JOIN pour éviter les blocages de jointure
$sql_dernieres_cand = "SELECT c.id_candidature, c.date_postulation AS date_depot, c.statut_candidature, 
                              o.titre, 
                              u.nom_complet AS nom_entreprise 
                       FROM CANDIDATURE c
                       LEFT JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
                       LEFT JOIN UTILISATEUR u ON o.id_entreprise = u.id_user
                       WHERE c.id_etudiant = ?
                       ORDER BY c.date_postulation DESC 
                       LIMIT 5";

$stmt_liste = $pdo->prepare($sql_dernieres_cand);
$stmt_liste->execute([$id_etudiant]);
$dernieres_candidatures = $stmt_liste->fetchAll(PDO::FETCH_ASSOC);

// DEBUG : Si le total est > 0 mais que le tableau est vide, décommentez la ligne ci-dessous :
// var_dump($dernieres_candidatures); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Étudiant | Gestion des Stages</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #1a1d2d;
            --sidebar-dark: #111422;
            --card-dark: #23273a;
            --text-muted: #8a8d9a;
            --accent-blue: #3b82f6;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            overflow-x: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            height: 100vh;
            background-color: var(--sidebar-dark);
            border-right: 1px solid #2d3142;
            position: fixed;
            width: 260px;
            padding: 20px 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0 25px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
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
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        /* CARDS STATS */
        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover { transform: translateY(-5px); }

        .stat-card h2 { font-weight: 700; margin: 10px 0; font-size: 2rem; }
        .stat-card i.bg-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.15;
        }

        .bg-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .bg-green { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }

        /* TABLE STYLING */
        .custom-table-card {
            background: var(--card-dark);
            border-radius: 15px;
            border: none;
            padding: 20px;
        }

        .table { color: #e2e8f0; margin-bottom: 0; }
        .table thead th { 
            background: rgba(0,0,0,0.2); 
            color: var(--text-muted); 
            border: none; 
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px;
        }
        .table tbody td { padding: 15px; border-color: rgba(255,255,255,0.05); vertical-align: middle; }

        /* ANIMATIONS */
        .fade-in {
            animation: fadeIn 0.8s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }

        .badge { border-radius: 6px; padding: 6px 12px; font-weight: 500; }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-graduation-cap me-2 text-primary"></i> GESTION STAGES
    </div>
    
    <div class="px-4 mb-5 d-flex align-items-center">
        <div class="position-relative">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nom_complet); ?>&background=3b82f6&color=fff" class="rounded-circle me-3" width="45">
            <span class="position-absolute bottom-0 end-0 badge border border-light rounded-circle bg-success p-1" style="transform: translate(-15px, 0);"><span class="visually-hidden">online</span></span>
        </div>
        <div>
            <div class="fw-bold" style="font-size: 0.85rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <?= $nom_complet; ?>
            </div>
            <small class="text-muted" style="font-size: 0.7rem;">ID: E-<?= str_pad($id_etudiant, 3, '0', STR_PAD_LEFT); ?></small>
        </div>
    </div>

    <nav>
        <a href="dashEtud.php" class="nav-link active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a href="listeStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
        <a href="Candidature.php" class="nav-link"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
        <a href="MonStage.php" class="nav-link"><i class="fa-solid fa-laptop-code"></i> Espace Documents</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
<a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Déconnexion</span>
</a>    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5 fade-in">
        <div>
            <h2 class="fw-bold mb-1">Bienvenue 👋</h2>
            <p class="text-muted mb-0">Ravi de vous revoir ! Voici le point sur vos recherches.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark text-muted border border-secondary">Année académique 2025/2026</span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 fade-in">
            <div class="stat-card bg-blue shadow-sm">
                <small class="text-uppercase fw-600 opacity-75">Offres disponibles</small>
                <h2><?= str_pad($total_offres_dispo, 2, '0', STR_PAD_LEFT); ?></h2>
                <i class="fa-solid fa-briefcase bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-arrow-up"></i> +<?= $offres_cette_semaine ?> cette semaine
                </div>
            </div>
        </div>

        <div class="col-md-4 fade-in delay-1">
            <div class="stat-card bg-green shadow-sm">
                <small class="text-uppercase fw-600 opacity-75">Mes candidatures</small>
                <h2><?= str_pad($total_mes_candidatures, 2, '0', STR_PAD_LEFT); ?></h2>
                <i class="fa-solid fa-paper-plane bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">
                    Suivi de vos demandes en temps réel
                </div>
            </div>
        </div>

        <div class="col-md-4 fade-in delay-2">
            <div class="stat-card bg-orange shadow-sm">
                <small class="text-uppercase fw-600 opacity-75">Stage validé</small>
                <h2><?= str_pad($stage_en_cours, 2, '0', STR_PAD_LEFT); ?></h2>
                <i class="fa-solid fa-spinner bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">
                    <?= $stage_en_cours > 0 ? "Aperçu disponible sous 'Mon Stage'" : "Aucun stage actif pour l'instant"; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="custom-table-card fade-in delay-2 shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Mes dernières candidatures</h5>
            <a href="Candidature.php" class="btn btn-sm btn-outline-secondary text-decoration-none text-white">Voir tout</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Entreprise</th>
                        <th>Poste</th>
                        <th>Date de dépôt</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dernieres_candidatures)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Vous n'avez pas encore déposé de candidature.</td>
                        </tr>
                    <?php else: 
                        $compteur = 1;
                        foreach ($dernieres_candidatures as $cand): 
                            // Gestion graphique dynamique des badges de statut
                            $badge_class = "bg-warning text-dark";
                            $icon_class = "fa-hourglass-half";
                            $statut_texte = "En attente";

                            if ($cand['statut_candidature'] === 'acceptee') {
                                $badge_class = "bg-success text-white";
                                $icon_class = "fa-check-circle";
                                $statut_texte = "Accepté";
                            } elseif ($cand['statut_candidature'] === 'refusee') {
                                $badge_class = "bg-danger text-white";
                                $icon_class = "fa-times-circle";
                                $statut_texte = "Refusé";
                            }
                    ?>
                        <tr>
                            <td><span class="text-muted">#<?= str_pad($compteur++, 2, '0', STR_PAD_LEFT); ?></span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary rounded p-1 me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                        <i class="fa-solid fa-building text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-bold"><?= htmlspecialchars($cand['nom_entreprise']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($cand['titre']) ?></td>
                            <td><?= date('d M Y', strtotime($cand['date_depot'])) ?></td>
                            <td>
                                <span class="badge <?= $badge_class ?>">
                                    <i class="fa-solid <?= $icon_class ?> me-1"></i> <?= $statut_texte ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="Candidature.php" class="btn btn-sm btn-dark border-secondary"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="mt-5 text-center text-muted" style="font-size: 0.75rem;">
        <p>&copy; 2026 Gestion des Stages - Université de Technologie. Tous droits réservés.</p>
    </footer>
</div>

</body>
</html>