<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_etudiant = $_SESSION['user_id'];

// 1. Récupération des statistiques
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut_candidature = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
    SUM(CASE WHEN statut_candidature = 'acceptee' THEN 1 ELSE 0 END) as acceptees
    FROM CANDIDATURE WHERE id_etudiant = ?";
$stmt_stats = $pdo->prepare($sql_stats);
$stmt_stats->execute([$id_etudiant]);
$stats = $stmt_stats->fetch();

// 2. Liste des candidatures avec jointures
$sql_list = "SELECT c.*, o.titre, u.nom_complet as entreprise, e.siege_social 
             FROM CANDIDATURE c
             JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
             JOIN UTILISATEUR u ON o.id_entreprise = u.id_user
             LEFT JOIN ENTREPRISE e ON u.id_user = e.id_user
             WHERE c.id_etudiant = ?
             ORDER BY c.date_postulation DESC";
$stmt_list = $pdo->prepare($sql_list);
$stmt_list->execute([$id_etudiant]);
$candidatures = $stmt_list->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures | STAGES HELLO</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --sidebar-bg: #020617;
            --card-bg: rgba(30, 41, 59, 0.4);
            --primary: #3b82f6;
            --accent-green: #10b981;
            --accent-orange: #f59e0b;
            --accent-red: #ef4444;
            --text-muted: #94a3b8;
        }

        body {
            background: var(--bg);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }

        /* SIDEBAR UNIFIÉE */
        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            background: var(--sidebar-bg);
            padding: 30px 20px;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 1000;
        }

        .sidebar-brand {
            font-weight: 800;
            font-size: 1.25rem;
            background: linear-gradient(to right, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 40px;
            display: block;
            text-decoration: none;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: 0.3s;
            font-weight: 500;
        }

        .sidebar a i { margin-right: 15px; width: 20px; text-align: center; }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }

        .sidebar a.active { 
            background: var(--primary); 
            color: white; 
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2); 
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.03), transparent);
        }

        /* STATS CARDS QUICK VIEW */
        .stat-mini-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 15px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* CARD CANDIDATURE MODERNE */
        .card-candidature {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 15px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-candidature:hover {
            transform: translateY(-5px) scale(1.01);
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .company-logo-placeholder {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
        }

        /* BADGES DE STATUT */
        .badge-status {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-attente { background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); }
        .status-accepte { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .status-refuse { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }

        .status-attente i { animation: blink 1.5s infinite; }

        @keyframes blink { 50% { opacity: 0.5; } }

        .btn-view {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 18px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-view:hover {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="sidebar-brand">
        <i class="fa-solid fa-rocket"></i> STAGES HELLO
    </a>
    <a href="dashEtud.php"><i class="fa-solid fa-grip-vertical"></i> Dashboard</a>
    <a href="listeStage.php"><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
    <a href="Candidature.php" class="active"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
    <a href="MonStage.php"><i class="fa-solid fa-file-arrow-up"></i> Espace Documents</a>
    <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Déconnexion</span>
</a>
</div>

<div class="main">
    
    <div class="mb-5" data-aos="fade-down">
        <h2 class="fw-800">Suivi de mes candidatures</h2>
        <p class="text-muted">Gérez vos demandes et surveillez les retours des recruteurs.</p>
    </div>

    <div class="row g-3 mb-5" data-aos="fade-up">
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="text-primary"><i class="fa-solid fa-paper-plane fa-lg"></i></div>
                <div><h6 class="mb-0 fw-800"><?= sprintf("%02d", $stats['total']) ?></h6><small class="text-muted">Total</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="text-warning"><i class="fa-solid fa-clock fa-lg"></i></div>
                <div><h6 class="mb-0 fw-800"><?= sprintf("%02d", $stats['en_attente']) ?></h6><small class="text-muted">En attente</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="text-success"><i class="fa-solid fa-check-circle fa-lg"></i></div>
                <div><h6 class="mb-0 fw-800"><?= sprintf("%02d", $stats['acceptees']) ?></h6><small class="text-muted">Acceptées</small></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-10">
            <?php if (count($candidatures) > 0): ?>
                <?php foreach ($candidatures as $index => $cand): 
                    // Gestion des classes de couleur selon le statut
                    $status_class = [
                        'en_attente' => 'status-attente',
                        'acceptee'   => 'status-accepte',
                        'refusee'    => 'status-refuse'
                    ];
                    $dot_color = [
                        'en_attente' => 'text-warning',
                        'acceptee'   => 'text-success',
                        'refusee'    => 'text-danger'
                    ];
                    $initials = strtoupper(substr($cand['entreprise'], 0, 2));
                ?>
                
                <div class="card-candidature" data-aos="fade-right" data-aos-delay="<?= ($index + 1) * 100 ?>">
                    <div class="d-flex align-items-center flex-wrap gap-4">
                        <div class="company-logo-placeholder"><?= $initials ?></div>
                        
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($cand['titre']) ?></h5>
                            <p class="text-muted small mb-0">
                                <i class="fa-solid fa-location-dot me-1"></i> 
                                <?= htmlspecialchars($cand['entreprise']) ?> • <?= htmlspecialchars($cand['siege_social'] ?? 'Douala') ?>
                            </p>
                        </div>

                        <div>
                            <span class="badge-status <?= $status_class[$cand['statut_candidature']] ?>">
                                <i class="fa-solid fa-circle <?= $dot_color[$cand['statut_candidature']] ?> small"></i> 
                                <?= ucfirst(str_replace('_', ' ', $cand['statut_candidature'])) ?>
                            </span>
                        </div>

                        <div class="text-end" style="min-width: 120px;">
                            <small class="text-muted d-block">Postulé le</small>
                            <span class="fw-600"><?= date('d M Y', strtotime($cand['date_postulation'])) ?></span>
                        </div>

                        <button class="btn-view" 
        onclick="afficherDetails(<?= htmlspecialchars(json_encode($cand)) ?>)">
    <i class="fa-solid fa-eye me-2"></i>Détails
</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">Vous n'avez pas encore postulé à des offres.</p>
                    <a href="listeStage.php" class="btn btn-primary rounded-pill">Explorer les offres</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    function afficherDetails(data) {
    const modalBody = document.getElementById('modalContent');
    const modalTitre = document.getElementById('modalTitre');
    
    // On met à jour le titre
    modalTitre.innerText = data.titre;

    // On prépare le contenu HTML
    let html = `
        <div class="mb-3">
            <label class="text-muted small fw-bold uppercase">ENTREPRISE</label>
            <p class="mb-0 text-primary fw-bold">${data.entreprise}</p>
        </div>
        <div class="mb-3">
            <label class="text-muted small fw-bold uppercase">LIEU / SIÈGE</label>
            <p class="mb-0">${data.siege_social || 'Non spécifié'}</p>
        </div>
        <div class="mb-3">
            <label class="text-muted small fw-bold uppercase">DESCRIPTION DU POSTE</label>
            <p class="small" style="text-align: justify; color: #cbd5e1;">
                ${data.description ? data.description : 'Aucune description détaillée disponible.'}
            </p>
        </div>
        <hr class="border-secondary">
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <label class="text-muted small d-block">STATUT ACTUEL</label>
                <span class="badge rounded-pill bg-opacity-10 bg-info text-info p-2 px-3">
                    ${data.statut_candidature.replace('_', ' ')}
                </span>
            </div>
            <div class="text-end">
                <label class="text-muted small d-block">POSTULÉ LE</label>
                <span class="fw-bold">${new Date(data.date_postulation).toLocaleDateString('fr-FR')}</span>
            </div>
        </div>
    `;

    // On injecte et on affiche la modale
    modalBody.innerHTML = html;
    var myModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    myModal.show();
}
</script>

</body>

<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="modalTitre">Détails de l'offre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <div class="text-center p-4">
                    <i class="fa-solid fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

</html>