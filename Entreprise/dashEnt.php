<?php
session_start();
include('../Auth/config_db.php');

// Protection de la page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];

// --- 1. STATISTIQUES ---
// Nombre d'offres publiées
$stmt = $pdo->prepare("SELECT COUNT(*) FROM OFFRE_STAGE WHERE id_entreprise = ?");
$stmt->execute([$id_ent]);
$nb_offres = $stmt->fetchColumn();

// Nombre de candidatures reçues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM CANDIDATURE c 
                       JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre 
                       WHERE o.id_entreprise = ?");
$stmt->execute([$id_ent]);
$nb_candidatures = $stmt->fetchColumn();

// Nombre de stages en cours (candidatures acceptées)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM CANDIDATURE c 
                       JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre 
                       WHERE o.id_entreprise = ? AND c.statut_candidature = 'acceptee'");
$stmt->execute([$id_ent]);
$nb_stages = $stmt->fetchColumn();

// --- 2. LES 5 DERNIÈRES CANDIDATURES ---
$sql = "SELECT c.*, u.nom_complet, o.titre as titre_offre 
        FROM CANDIDATURE c
        JOIN UTILISATEUR u ON c.id_etudiant = u.id_user
        JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
        WHERE o.id_entreprise = ?
        ORDER BY c.date_postulation DESC LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_ent]);
$dernieres_candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Entreprise | Gestion des Stages</title>

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
            margin: 0;
            display: flex;
        }

        /* SIDEBAR */
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
            color: #3b82f6;
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
        .main-content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h2 { font-weight: 700; margin: 10px 0; font-size: 2.2rem; }
        
        .bg-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.15;
        }

        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .bg-gradient-purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .bg-gradient-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }

        /* TABLE STYLING */
        .custom-table-card {
            background: var(--card-dark);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }

        .table { color: #e2e8f0; vertical-align: middle; }
        .table thead th { 
            background: rgba(0,0,0,0.2); 
            color: var(--text-muted); 
            border: none; 
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px;
        }
        .table tbody td { padding: 15px; border-color: rgba(255,255,255,0.05); }

        .btn-publish {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-publish:hover { background: #2563eb; color: white; transform: scale(1.02); }

        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-building me-2"></i> TECH SOLUTIONS
    </div>
    
    <div class="px-4 mb-5 d-flex align-items-center">
        <img src="https://ui-avatars.com/api/?name=Tech+Solutions&background=3b82f6&color=fff" class="rounded-circle me-3" width="45">
        <div>
            <div class="fw-bold" style="font-size: 0.85rem;">Espace Recruteur</div>
            <small class="text-success"><i class="fa-solid fa-circle fa-2xs"></i> Partenaire</small>
        </div>
    </div>

    <nav>
        <a href="dashEnt.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="pubOffre.php" class="nav-link "><i class="fa-solid fa-plus-circle"></i> Publier une offre</a>
        <a href="OffrePub.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
        <a href="paramEnt.php" class="nav-link"><i class="fa-solid fa-gear"></i> Paramètres</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
<a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Déconnexion</span>
</a>    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Bienvenue <?= htmlspecialchars($_SESSION['user_name'] ?? 'Entreprise') ?> 👋</h2>
            <p class="text-muted mb-0">Voici l'état actuel de vos recrutements de stagiaires.</p>
        </div>
        <a href="publierOffre.php" class="btn btn-publish shadow text-white" style="text-decoration:none;">
            <i class="fa-solid fa-paper-plane me-2"></i> Publier une nouvelle offre
        </a>
    </div>

    <div class="row g-4 mb-2">
        <div class="col-md-4">
            <div class="stat-card bg-gradient-blue shadow">
                <small class="text-uppercase fw-600 opacity-75">Offres publiées</small>
                <h2><?= sprintf("%02d", $nb_offres) ?></h2>
                <i class="fa-solid fa-briefcase bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">Gérez vos annonces actives</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card bg-gradient-purple shadow">
                <small class="text-uppercase fw-600 opacity-75">Candidatures reçues</small>
                <h2><?= sprintf("%02d", $nb_candidatures) ?></h2>
                <i class="fa-solid fa-user-tie bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">Consultez les nouveaux profils</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card bg-gradient-orange shadow">
                <small class="text-uppercase fw-600 opacity-75">Stages en cours</small>
                <h2><?= sprintf("%02d", $nb_stages) ?></h2>
                <i class="fa-solid fa-user-graduate bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">Suivi des stagiaires acceptés</div>
            </div>
        </div>
    </div>

    <div class="custom-table-card shadow mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold">Dernières candidatures reçues</h5>
            <a href="gestCand.php" class="btn btn-sm btn-dark border-secondary text-muted">Voir tout</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nom du candidat</th>
                        <th>Offre visée</th>
                        <th>Date de dépôt</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dernieres_candidatures)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Aucune candidature pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dernieres_candidatures as $c): 
                            // Initiales
                            $mots = explode(" ", $c['nom_complet']);
                            $init = strtoupper(substr($mots[0],0,1).(isset($mots[1])?substr($mots[1],0,1):""));
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        <?= $init ?>
                                    </div>
                                    <span class="fw-bold"><?= htmlspecialchars($c['nom_complet']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-dark border border-secondary text-white"><?= htmlspecialchars($c['titre_offre']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($c['date_postulation'])) ?></td>
                            <td>
                                <?php if($c['statut_candidature'] == 'en_attente'): ?>
                                    <span class="badge badge-status bg-warning text-dark">En attente</span>
                                <?php elseif($c['statut_candidature'] == 'acceptee'): ?>
                                    <span class="badge badge-status bg-success">Acceptée</span>
                                <?php else: ?>
                                    <span class="badge badge-status bg-danger">Refusée</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="profilEtudiant.php?id=<?= $c['id_etudiant'] ?>" class="btn btn-sm btn-outline-info me-1"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

</body>
</html>