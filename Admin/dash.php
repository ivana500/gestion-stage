<?php
session_start();

// 1. Vérifier la connexion et l'existence des variables de session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// 2. Vérifier si l'utilisateur a le droit d'être ici (admin)
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// 3. Connexion à la Base de données (Port 3307) - UNIQUE ET MUTUALISÉE
include('../Auth/config_db.php');

// Petit helper : reconstruit un chemin utilisable vers un fichier stocké en base
// (même logique que dans Entreprise/dashEnt.php, à garder cohérente entre les deux)
function resolveFilePath($raw, $defaultFolder) {
    if (empty($raw)) return null;
    if (strpos($raw, '/') !== false) {
        return '../' . ltrim($raw, '/');
    }
    return $defaultFolder . $raw;
}

// 4. Nombre total d'étudiants (l'admin voit tout, aucun filtrage par rôle)
$totalEtudiants = (int) $pdo->query("SELECT COUNT(*) FROM ETUDIANT")->fetchColumn();

// ============================================================
// RÉCUPÉRATION DES STATISTIQUES GLOBALISÉES (KPI CARDS)
// ============================================================

// a. Total Utilisateurs (Comptes globaux de la plateforme)
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM UTILISATEUR")->fetchColumn();

// b. Note : $totalEtudiants est déjà calculé plus haut selon le rôle !

// c. Total Entreprises (Utilisateurs ayant le rôle d'entreprise)
$totalEntreprises = (int) $pdo->query("SELECT COUNT(*) FROM UTILISATEUR WHERE role = 'entreprise'")->fetchColumn();

// d. Total Offres de Stage publiées
$totalOffres = (int) $pdo->query("SELECT COUNT(*) FROM OFFRE_STAGE")->fetchColumn();

// e. Total Candidatures soumises
$totalCandidatures = (int) $pdo->query("SELECT COUNT(*) FROM CANDIDATURE")->fetchColumn();


// ============================================================
// FLUX TEMPS RÉEL (HISTORIQUE DES DERNIÈRES ACTIONS)
// ============================================================
$activities = [];

// 1. Dernières offres publiées
$queryOffresRecentes = $pdo->query("
    SELECT o.titre AS action_titre, 
           CONCAT('Nouvelle offre par ', u.nom_complet) AS action_details,
           o.date_limite AS date_ref, 
           'offre' AS type_act
    FROM OFFRE_STAGE o
    JOIN UTILISATEUR u ON o.id_entreprise = u.id_user
    ORDER BY o.id_offre DESC LIMIT 3
");
while ($row = $queryOffresRecentes->fetch(PDO::FETCH_ASSOC)) {
    $activities[] = $row;
}

// 2. Dernières candidatures postulées
$queryCandRecentes = $pdo->query("
    SELECT o.titre AS action_titre, 
           CONCAT(u.nom_complet, ' a postulé') AS action_details,
           c.date_postulation AS date_ref,
           'candidature' AS type_act
    FROM CANDIDATURE c
    JOIN UTILISATEUR u ON c.id_etudiant = u.id_user
    JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
    ORDER BY c.date_postulation DESC LIMIT 3
");
while ($row = $queryCandRecentes->fetch(PDO::FETCH_ASSOC)) {
    $activities[] = $row;
}

// Tri des activités combinées (plus récentes en premier)
usort($activities, function($a, $b) {
    return strcmp($b['date_ref'], $a['date_ref']);
});
$activities = array_slice($activities, 0, 4);


// ============================================================
// DONNÉES DYNAMIQUES POUR LES GRAPHIQUES CHART.JS
// ============================================================

// A. Statuts des Stages (Doughnut 1)
$stagesEnCours = (int) $pdo->query("SELECT COUNT(*) FROM CANDIDATURE WHERE statut_candidature = 'acceptee'")->fetchColumn();
$stagesAttente = (int) $pdo->query("SELECT COUNT(*) FROM CANDIDATURE WHERE statut_candidature = 'en_attente'")->fetchColumn();
$stagesRefuses = (int) $pdo->query("SELECT COUNT(*) FROM CANDIDATURE WHERE statut_candidature = 'refusee'")->fetchColumn();

// B. Candidatures par mois (Bar Chart - 6 derniers mois)
$moisLabels = [];
$candidaturesMoisData = [];
for ($i = 5; $i >= 0; $i--) {
    $moisLabels[] = date('M', strtotime("-$i months"));
    $dateStart = date('Y-m-01', strtotime("-$i months"));
    $dateEnd = date('Y-m-t', strtotime("-$i months"));
    
    $stmtMois = $pdo->prepare("SELECT COUNT(*) FROM CANDIDATURE WHERE date_postulation BETWEEN ? AND ?");
    $stmtMois->execute([$dateStart, $dateEnd]);
    $candidaturesMoisData[] = (int) $stmtMois->fetchColumn();
}

// C. Population Active (Doughnut 2)
$roleEtudiants = $totalEtudiants; // Filtre par rôle respecté
$roleEntreprises = $roleEntreprises ?? $totalEntreprises;
$roleAdmins = (int) $pdo->query("SELECT COUNT(*) FROM UTILISATEUR WHERE role = 'admin'")->fetchColumn();


// ============================================================
// NOTIFICATIONS NON LUES (documents de candidature OU rapport)
// Même logique que Entreprise/dashEnt.php : un seul rôle admin,
// donc aucun filtrage supplémentaire par droits/assignation.
// ============================================================
$stmtNotifs = $pdo->prepare("SELECT id, type, message, lu, date_creation, id_candidature, id_stage
                              FROM notifications
                              WHERE id_user = ? AND lu = 0
                              ORDER BY date_creation DESC");
$stmtNotifs->execute([$user_id]);
$notifications_brutes = $stmtNotifs->fetchAll(PDO::FETCH_ASSOC);

$notifications = [];
foreach ($notifications_brutes as $n) {

    if ($n['type'] === 'documents_candidature' && $n['id_candidature']) {
        $stmtCheck = $pdo->prepare("SELECT u.nom_complet
                                     FROM CANDIDATURE c
                                     JOIN UTILISATEUR u ON c.id_etudiant = u.id_user
                                     WHERE c.id_candidature = ?");
        $stmtCheck->execute([$n['id_candidature']]);
        $info = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$info) continue;

        $stmtDocs = $pdo->prepare("SELECT type_document, chemin_fichier FROM documents_stage WHERE id_candidature = ?");
        $stmtDocs->execute([$n['id_candidature']]);
        $fichiers = [];
        foreach ($stmtDocs->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $label = match (strtolower($d['type_document'])) {
                'cv' => 'CV',
                'lettre_motivation', 'lettre' => 'Lettre de motivation',
                'cv_lettre' => 'CV + Lettre de motivation',
                default => ucfirst($d['type_document']),
            };
            $fichiers[] = ['label' => $label, 'url' => resolveFilePath($d['chemin_fichier'], '../uploads/documents/')];
        }

        $notifications[] = [
            'id' => $n['id'],
            'nom_etudiant' => $info['nom_complet'],
            'texte' => 'a déjà envoyé sa lettre de motivation et son CV',
            'date' => $n['date_creation'],
            'fichiers' => $fichiers,
        ];

    } elseif ($n['type'] === 'rapport' && $n['id_stage']) {
        $stmtCheck = $pdo->prepare("SELECT u.nom_complet
                                     FROM STAGE s
                                     JOIN UTILISATEUR u ON s.id_etudiant = u.id_user
                                     WHERE s.id_stage = ?");
        $stmtCheck->execute([$n['id_stage']]);
        $info = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$info) continue;

        $stmtRap = $pdo->prepare("SELECT fichier_pdf FROM rapport WHERE id_stage = ?");
        $stmtRap->execute([$n['id_stage']]);
        $rap = $stmtRap->fetch(PDO::FETCH_ASSOC);

        $fichiers = [];
        if ($rap) {
            $fichiers[] = ['label' => 'Rapport de stage', 'url' => resolveFilePath($rap['fichier_pdf'], '../uploads/rapports/')];
        }

        $notifications[] = [
            'id' => $n['id'],
            'nom_etudiant' => $info['nom_complet'],
            'texte' => 'a déjà envoyé son rapport de stage',
            'date' => $n['date_creation'],
            'fichiers' => $fichiers,
        ];
    }
}
$nb_notifs = count($notifications);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stages - Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-dark: #0f172a;
            --sidebar-dark: #020617;
            --card-dark: rgba(30, 41, 59, 0.4);
            --text-muted: #94a3b8;
            --accent-blue: #3b82f6;
        }

        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9rem;
        }

        /* SIDEBAR */
        .sidebar {
            height: 100vh;
            background-color: var(--sidebar-dark);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            width: 280px;
            padding: 30px 20px;
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
            text-align: center;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: 0.3s;
            font-weight: 500;
        }

        .nav-link i { margin-right: 15px; width: 20px; text-align: center; }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
        }

        .nav-link.active {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            padding: 40px;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
            position: relative;
        }

        /* CARDS STATS */
        .stat-card {
            border: none;
            border-radius: 20px;
            padding: 25px;
            transition: transform 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h2 { font-weight: 800; margin: 10px 0; letter-spacing: -1px; }
        .stat-card .icon-box { font-size: 1.8rem; opacity: 0.3; }
        
        .footer-link { 
            font-size: 0.75rem; 
            text-decoration: none; 
            color: rgba(255,255,255,0.8); 
            display: flex; 
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 12px;
            font-weight: 600;
        }

        /* CHART CARDS */
        .chart-container {
            background: var(--card-dark);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 25px;
            height: 100%;
        }

        /* RECENT ACTIVITIES */
        .activity-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            align-items: center;
        }
        .activity-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .bg-custom-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .bg-custom-green { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-custom-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .bg-custom-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .bg-custom-red { background: linear-gradient(135deg, #ef4444, #dc2626); }

        /* ---- NOTIFICATIONS (identique à Entreprise/dashEnt.php) ---- */
        .notif-bell-wrap { position: relative; }
        .notif-bell-btn {
            width: 46px; height: 46px; border-radius: 50%;
            background: var(--sidebar-dark); border: 1px solid rgba(255,255,255,0.08);
            color: white; display: flex; align-items: center; justify-content: center;
            cursor: pointer; position: relative;
        }
        .notif-badge {
            position: absolute; top: -4px; right: -4px;
            background: #ef4444; color: white; font-size: 0.65rem; font-weight: 700;
            border-radius: 50%; width: 20px; height: 20px;
            display: flex; align-items: center; justify-content: center;
        }
        .notif-panel {
            display: none;
            position: absolute; top: 60px; right: 0;
            width: 380px; max-height: 460px; overflow-y: auto;
            background: #1e293b; border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px; box-shadow: 0 15px 40px rgba(0,0,0,0.5);
            z-index: 1000; padding: 0;
        }
        .notif-panel-header {
            padding: 16px 18px; border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; justify-content: space-between; align-items: center;
        }
        .notif-panel-header h5 { margin: 0; font-size: 0.95rem; font-weight: 700; }
        .notif-item {
            display: block; padding: 14px 18px; cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            background: rgba(59,130,246,0.06);
        }
        .notif-item:hover { background: rgba(59,130,246,0.14); }
        .notif-item .nom { font-weight: 600; font-size: 0.85rem; }
        .notif-item .texte { color: var(--text-muted); font-size: 0.8rem; margin-top: 2px; }
        .notif-item .date { color: #5b5f70; font-size: 0.7rem; margin-top: 6px; }
        .notif-empty { padding: 30px 18px; text-align: center; color: var(--text-muted); font-size: 0.85rem; }

        .doc-view {
            display: none;
            background: var(--card-dark);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .doc-view-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; padding-bottom: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .doc-view-header h4 { margin: 0; font-size: 1.1rem; }
        .btn-back {
            background: transparent; border: 1px solid rgba(255,255,255,0.15);
            color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-back:hover { background: rgba(255,255,255,0.05); }
        .doc-file-block { margin-bottom: 22px; }
        .doc-file-block .doc-label {
            font-weight: 600; font-size: 0.85rem; margin-bottom: 8px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .doc-file-block iframe {
            width: 100%; height: 420px; border: none; border-radius: 10px; background: white;
        }
        .doc-file-block a.doc-download {
            color: var(--accent-blue); font-size: 0.78rem; text-decoration: none;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-shield-halved me-2"></i> ADMIN PANEL
    </div>
    
    <div class="px-3 mb-5 d-flex align-items-center">
        <div class="bg-primary rounded-3 p-2 me-3">
            <i class="fa-solid fa-user-tie text-white"></i>
        </div>
        <div>
            <div class="fw-bold small"><?php echo ($user_role === 'admin') ? 'Admin Principal' : 'Sous-Admin'; ?></div>
            <small class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-circle fa-2xs me-1"></i> Session Active</small>
        </div>
    </div>

    <nav>
        <a href="dash.php" class="nav-link active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="gestUtil.php" class="nav-link"><i class="fa-solid fa-users-gears"></i> Utilisateurs</a>
        <a href="validStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <a href="Config.php" class="nav-link"><i class="fa-solid fa-gears"></i> Configurations</a>
        <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="fw-800 mb-1">Vue d'ensemble</h2>
            <p class="text-muted mb-0">Statistiques globales de la plateforme de stages.</p>
        </div>
        <div class="d-flex align-items-center gap-3 pb-1">
            <span class="badge bg-white bg-opacity-10 px-3 py-2 rounded-pill"><?php echo date('F Y'); ?></span>

            <div class="notif-bell-wrap">
                <button class="notif-bell-btn" onclick="toggleNotifPanel()" type="button">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($nb_notifs > 0): ?>
                        <span class="notif-badge"><?= $nb_notifs > 9 ? '9+' : $nb_notifs ?></span>
                    <?php endif; ?>
                </button>

                <div id="notifPanel" class="notif-panel">
                    <div class="notif-panel-header">
                        <h5>Notifications</h5>
                        <button class="btn-back" style="padding:4px 10px;" onclick="toggleNotifPanel()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <?php if (empty($notifications)): ?>
                        <div class="notif-empty">Aucune notification pour le moment.</div>
                    <?php else: foreach ($notifications as $n): ?>
                        <div class="notif-item" onclick="showDoc(<?= (int)$n['id'] ?>)">
                            <div class="nom"><?= htmlspecialchars($n['nom_etudiant']) ?></div>
                            <div class="texte">L'étudiant <?= htmlspecialchars($n['texte']) ?></div>
                            <div class="date"><?= date('d/m/Y à H:i', strtotime($n['date'])) ?></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- VUES DOCUMENTS (une par notification, cachées par défaut) -->
    <?php foreach ($notifications as $n): ?>
        <div id="docView_<?= (int)$n['id'] ?>" class="doc-view">
            <div class="doc-view-header">
                <h4>Documents de <?= htmlspecialchars($n['nom_etudiant']) ?></h4>
                <button class="btn-back" onclick="backToNotifs()">
                    <i class="fa-solid fa-arrow-left me-2"></i>Retour aux notifications
                </button>
            </div>
            <?php if (empty($n['fichiers'])): ?>
                <p class="text-muted">Aucun fichier trouvé pour cette notification.</p>
            <?php else: foreach ($n['fichiers'] as $f): ?>
                <div class="doc-file-block">
                    <div class="doc-label">
                        <span><?= htmlspecialchars($f['label']) ?></span>
                        <?php if ($f['url']): ?>
                            <a class="doc-download" href="<?= htmlspecialchars($f['url']) ?>" target="_blank">
                                <i class="fa-solid fa-up-right-from-square me-1"></i> Ouvrir dans un nouvel onglet
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if ($f['url']): ?>
                        <iframe src="<?= htmlspecialchars($f['url']) ?>"></iframe>
                    <?php else: ?>
                        <p class="text-muted">Fichier introuvable.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="row g-4 mb-5">
        <div class="col-md">
            <div class="stat-card bg-custom-blue text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">UTILISATEURS</small><h2><?php echo $totalUsers; ?></h2></div>
                    <i class="fa-solid fa-users icon-box"></i>
                </div>
                <a href="gestUtil.php" class="footer-link">Gérer les comptes <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-green text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">ÉTUDIANTS</small><h2><?php echo $totalEtudiants; ?></h2></div>
                    <i class="fa-solid fa-user-graduate icon-box"></i>
                </div>
                <a href="gestUtil.php?role=etudiant" class="footer-link">Liste complète <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-purple text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">ENTREPRISES</small><h2><?php echo $totalEntreprises; ?></h2></div>
                    <i class="fa-solid fa-building icon-box"></i>
                </div>
                <a href="gestUtil.php?role=entreprise" class="footer-link">Partenariats <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-orange text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">OFFRES</small><h2><?php echo $totalOffres; ?></h2></div>
                    <i class="fa-solid fa-briefcase icon-box"></i>
                </div>
                <a href="validStage.php" class="footer-link">Modérer les offres <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-red text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">CANDIDATURES</small><h2><?php echo $totalCandidatures; ?></h2></div>
                    <i class="fa-solid fa-file-lines icon-box"></i>
                </div>
                <a href="validStage.php" class="footer-link">Suivi des dossiers <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="chart-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-line me-2 text-primary"></i> Analyse des flux</h6>
                    <select class="form-select form-select-sm bg-dark text-white border-0 opacity-75" style="width:130px">
                        <option>Année <?php echo date('Y'); ?></option>
                    </select>
                </div>
                <canvas id="evolutionChart" height="280"></canvas>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="chart-container shadow-sm">
                <h6 class="fw-bold mb-4"><i class="fa-solid fa-bolt-lightning me-2 text-warning"></i> Flux en temps réel</h6>
                <div class="mt-2">
                    <?php if (count($activities) > 0): ?>
                        <?php foreach($activities as $act): ?>
                            <div class="activity-item">
                                <?php if ($act['type_act'] == 'offre'): ?>
                                    <div class="activity-icon bg-custom-blue text-white"><i class="fa-solid fa-plus fa-xs"></i></div>
                                <?php else: ?>
                                    <div class="activity-icon bg-custom-purple text-white"><i class="fa-solid fa-paper-plane fa-xs"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($act['action_titre']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($act['action_details']); ?> • <?php echo date('d/m/Y H:i', strtotime($act['date_ref'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">Aucune activité récente.</div>
                    <?php endif; ?>
                </div>
                <a href="validStage.php" class="btn btn-outline-primary btn-sm w-100 mt-4 border-opacity-25 rounded-3 text-decoration-none">Historique complet</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="chart-container">
                <h6>Statuts des Stages</h6>
                <canvas id="statDonut"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container">
                <h6>Candidatures / Mois</h6>
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container">
                <h6>Population Active</h6>
                <canvas id="typeDonut"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
Chart.defaults.font.family = 'Plus Jakarta Sans';

// Graphique Évolution
new Chart(document.getElementById('evolutionChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [
            { 
                label: 'Offres', 
                data: [15, 12, 25, 20, 28, 22, 30, 25, 35, 45, 40, <?php echo (int) $totalOffres; ?>], 
                borderColor: '#3b82f6', 
                tension: 0.4, 
                fill: true, 
                backgroundColor: 'rgba(59, 130, 246, 0.05)' 
            },
            { 
                label: 'Candidatures', 
                data: [10, 25, 20, 35, 30, 40, 35, 30, 40, 50, 45, <?php echo (int) $totalCandidatures; ?>], 
                borderColor: '#10b981', 
                tension: 0.4 
            }
        ]
    },
    options: { plugins: { legend: { display: true, position: 'bottom' } } }
});

// Donut Statut
new Chart(document.getElementById('statDonut'), {
    type: 'doughnut',
    data: {
        labels: ['En cours', 'Acceptés', 'Refusés'],
        datasets: [{ 
            data: [<?php echo (int)$stagesAttente; ?>, <?php echo (int)$stagesEnCours; ?>, <?php echo (int)$stagesRefuses; ?>], 
            backgroundColor: ['#f59e0b', '#10b981', '#ef4444'], 
            borderWidth: 0 
        }]
    },
    options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } }
});

// Bar Chart
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($moisLabels); ?>,
        datasets: [{ 
            label: 'Demandes', 
            data: <?php echo json_encode($candidaturesMoisData); ?>, 
            backgroundColor: '#3b82f6', 
            borderRadius: 6 
        }]
    }
});

// Répartition Active
new Chart(document.getElementById('typeDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Étudiants', 'Entreprises', 'Administrateurs'],
        datasets: [{ 
            data: [<?php echo (int)$roleEtudiants; ?>, <?php echo (int)$roleEntreprises; ?>, <?php echo (int)$roleAdmins; ?>], 
            backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6'], 
            borderWidth: 0 
        }]
    },
    options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } }
});
</script>

<script>
    function toggleNotifPanel() {
        const panel = document.getElementById('notifPanel');
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
    }

    function showDoc(notifId) {
        document.getElementById('notifPanel').style.display = 'none';
        document.querySelectorAll('.doc-view').forEach(el => el.style.display = 'none');

        const view = document.getElementById('docView_' + notifId);
        if (view) {
            view.style.display = 'block';
            view.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        fetch('marquer_notif_lue.php?id=' + notifId).catch(() => {});
    }

    function backToNotifs() {
        document.querySelectorAll('.doc-view').forEach(el => el.style.display = 'none');
        document.getElementById('notifPanel').style.display = 'block';
    }

    document.addEventListener('click', function (e) {
        const wrap = document.querySelector('.notif-bell-wrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('notifPanel').style.display = 'none';
        }
    });
</script>

</body>
</html>