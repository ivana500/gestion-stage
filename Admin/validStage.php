<?php
session_start();

// ============================================================
// 1. VÉRIFICATION DES ACCÈS & RÔLES
// ============================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Seuls l'admin principal et le sous_admin (enseignant) peuvent voir cette page
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'sous_admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// ============================================================
// 2. CONNEXION À LA BASE DE DONNÉES (PORT 3307) - Une seule fois !
// ============================================================
include('../Auth/config_db.php');

$message = "";
$messageType = "";

// Récupération de l'erreur d'accès redirigée si existante
if (isset($_SESSION['erreur_access'])) {
    $message = $_SESSION['erreur_access'];
    $messageType = "danger";
    unset($_SESSION['erreur_access']);
}

// ============================================================
// 3. LOGIQUE DE VALIDATION D'UNE CANDIDATURE / STAGE
// ============================================================
if (isset($_GET['validate_candidature_id'])) {
    $id_cand = intval($_GET['validate_candidature_id']);
    try {
        // Optionnel : Sécurité supplémentaire pour s'assurer que le sous-admin a le droit de valider cette candidature précise
        if ($user_role === 'sous_admin') {
            $check = $pdo->prepare("SELECT COUNT(*) FROM CANDIDATURE c 
                                    JOIN ETUDIANT e ON c.id_etudiant = e.id_user 
                                    WHERE c.id_candidature = ? AND e.id_enseignant = ?");
            $check->execute([$id_cand, $user_id]);
            $is_allowed = $check->fetchColumn();
        } else {
            $is_allowed = true; // L'admin principal peut tout valider
        }

        if ($is_allowed) {
            $stmt = $pdo->prepare("UPDATE CANDIDATURE SET statut_candidature = 'acceptee' WHERE id_candidature = ?");
            $stmt->execute([$id_cand]);

            // Création du stage associé à la candidature acceptée, si aucun stage n'existe déjà.
            $stageInfo = $pdo->prepare("
                SELECT c.id_etudiant, c.id_offre, o.id_entreprise
                FROM CANDIDATURE c
                JOIN OFFRE_STAGE o ON o.id_offre = c.id_offre
                WHERE c.id_candidature = ?
            ");
            $stageInfo->execute([$id_cand]);
            $stageData = $stageInfo->fetch(PDO::FETCH_ASSOC);

            if ($stageData) {
                $checkStage = $pdo->prepare("SELECT id_stage FROM STAGE WHERE id_etudiant = ? AND id_offre = ? LIMIT 1");
                $checkStage->execute([$stageData['id_etudiant'], $stageData['id_offre']]);
                $existingStage = $checkStage->fetch(PDO::FETCH_ASSOC);

                if (!$existingStage) {
                    $insertStage = $pdo->prepare("
                        INSERT INTO STAGE (id_etudiant, id_entreprise, id_offre, date_debut, date_fin, statut_stage)
                        VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'a_venir')
                    ");
                    $insertStage->execute([
                        $stageData['id_etudiant'],
                        $stageData['id_entreprise'],
                        $stageData['id_offre']
                    ]);
                }
            }

            $message = "Le stage a été validé avec succès !";
            $messageType = "success";
        } else {
            $message = "Action refusée : Cet étudiant ne vous est pas assigné.";
            $messageType = "danger";
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la validation : " . $e->getMessage();
        $messageType = "danger";
    }
}

// ============================================================
// 4. FILTRAGE DES STATISTIQUES GLOBALES ET RECHERCHES
// ============================================================
// Total des offres reste global pour tout le monde
$totalOffres = $pdo->query("SELECT COUNT(*) FROM OFFRE_STAGE")->fetchColumn();

// Nombre d'étudiants (Global pour Admin, Restreint pour l'enseignant)
if ($user_role === 'admin') {
    $totalEtudiants = $pdo->query("SELECT COUNT(*) FROM ETUDIANT")->fetchColumn();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ETUDIANT WHERE id_enseignant = ?");
    $stmt->execute([$user_id]);
    $totalEtudiants = $stmt->fetchColumn();
}

// Récupération des offres de stage (Visible par tous)
$search = "";
$sqlSearch = "SELECT o.*, u.nom_complet AS nom_entreprise 
              FROM OFFRE_STAGE o 
              JOIN UTILISATEUR u ON o.id_entreprise = u.id_user";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $sqlSearch .= " WHERE o.titre LIKE :search OR u.nom_complet LIKE :search";
}
$sqlSearch .= " ORDER BY o.date_limite DESC";

$stmtOffres = $pdo->prepare($sqlSearch);
if (!empty($search)) {
    $stmtOffres->execute([':search' => '%' . $search . '%']);
} else {
    $stmtOffres->execute();
}
$offres = $stmtOffres->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// 5. RÉCUPÉRATION DES CANDIDATURES EN ATTENTE DE VALIDATION (FILTRÉES)
// ============================================================
if ($user_role === 'admin') {
    // L'Admin voit toutes les candidatures en attente
    $queryCandidatures = $pdo->query("
        SELECT c.id_candidature, 
               u_etud.nom_complet AS nom_etudiant, 
               u_entr.nom_complet AS nom_entreprise
        FROM CANDIDATURE c
        JOIN ETUDIANT etud ON c.id_etudiant = etud.id_user
        JOIN UTILISATEUR u_etud ON etud.id_user = u_etud.id_user
        JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
        JOIN UTILISATEUR u_entr ON o.id_entreprise = u_entr.id_user
        WHERE c.statut_candidature = 'en_attente'
        ORDER BY c.date_postulation DESC
    ");
} else {
    // Le Sous-Admin (Enseignant) ne voit que les candidatures de ses étudiants assignés
    $queryCandidatures = $pdo->prepare("
        SELECT c.id_candidature, 
               u_etud.nom_complet AS nom_etudiant, 
               u_entr.nom_complet AS nom_entreprise
        FROM CANDIDATURE c
        JOIN ETUDIANT etud ON c.id_etudiant = etud.id_user
        JOIN UTILISATEUR u_etud ON etud.id_user = u_etud.id_user
        JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
        JOIN UTILISATEUR u_entr ON o.id_entreprise = u_entr.id_user
        WHERE c.statut_candidature = 'en_attente' AND etud.id_enseignant = ?
        ORDER BY c.date_postulation DESC
    ");
    $queryCandidatures->execute([$user_id]);
}
$candidatures = $queryCandidatures->fetchAll(PDO::FETCH_ASSOC);

// ============================================================
// 6. RÉCUPÉRATION DES DOCUMENTS ENVOYÉS (CV / LETTRE)
// ============================================================
try {
    if ($user_role === 'admin') {
        $queryDocuments = $pdo->query("
            SELECT ds.id,
                   ds.type_document,
                   ds.chemin_fichier,
                   ds.date_upload,
                   c.id_candidature,
                   u_etud.nom_complet AS nom_etudiant,
                   s.id_stage
            FROM documents_stage ds
            JOIN CANDIDATURE c ON ds.id_candidature = c.id_candidature
            JOIN ETUDIANT etud ON c.id_etudiant = etud.id_user
            JOIN UTILISATEUR u_etud ON c.id_etudiant = u_etud.id_user
            LEFT JOIN (
                SELECT MAX(id_stage) AS id_stage, id_etudiant, id_offre
                FROM STAGE
                GROUP BY id_etudiant, id_offre
            ) s ON s.id_etudiant = c.id_etudiant AND s.id_offre = c.id_offre
            WHERE c.statut_candidature = 'acceptee'
            ORDER BY ds.date_upload DESC
            LIMIT 10
        ");
    } else {
        $queryDocuments = $pdo->prepare("
            SELECT ds.id,
                   ds.type_document,
                   ds.chemin_fichier,
                   ds.date_upload,
                   c.id_candidature,
                   u_etud.nom_complet AS nom_etudiant,
                   s.id_stage
            FROM documents_stage ds
            JOIN CANDIDATURE c ON ds.id_candidature = c.id_candidature
            JOIN ETUDIANT etud ON c.id_etudiant = etud.id_user
            JOIN UTILISATEUR u_etud ON c.id_etudiant = u_etud.id_user
            LEFT JOIN (
                SELECT MAX(id_stage) AS id_stage, id_etudiant, id_offre
                FROM STAGE
                GROUP BY id_etudiant, id_offre
            ) s ON s.id_etudiant = c.id_etudiant AND s.id_offre = c.id_offre
            WHERE c.statut_candidature = 'acceptee' AND etud.id_enseignant = ?
            ORDER BY ds.date_upload DESC
            LIMIT 10
        ");
        $queryDocuments->execute([$user_id]);
    }
    $documents = $queryDocuments->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $documents = [];
}

// ============================================================
// 7. RÉCUPÉRATION DES DERNIERS RAPPORTS (FILTRÉS)
// ============================================================
try {
    if ($user_role === 'admin') {
        $queryRapports = $pdo->query("
            SELECT r.id_rapport,
                   r.fichier_pdf AS rapport_fichier,
                   r.date_depot AS date_rapport_depot,
                   u_etud.nom_complet AS nom_etudiant
            FROM RAPPORT r
            JOIN STAGE s ON r.id_stage = s.id_stage
            JOIN UTILISATEUR u_etud ON s.id_etudiant = u_etud.id_user
            ORDER BY r.date_depot DESC
            LIMIT 10
        ");
    } else {
        $queryRapports = $pdo->prepare("
            SELECT r.id_rapport,
                   r.fichier_pdf AS rapport_fichier,
                   r.date_depot AS date_rapport_depot,
                   u_etud.nom_complet AS nom_etudiant
            FROM RAPPORT r
            JOIN STAGE s ON r.id_stage = s.id_stage
            JOIN ETUDIANT etud ON s.id_etudiant = etud.id_user
            JOIN UTILISATEUR u_etud ON s.id_etudiant = u_etud.id_user
            WHERE etud.id_enseignant = ?
            ORDER BY r.date_depot DESC
            LIMIT 10
        ");
        $queryRapports->execute([$user_id]);
    }
    $rapports = $queryRapports->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rapports = [];
}

// ============================================================
// 8. RÉCUPÉRATION DES CONVENTIONS GÉNÉRÉES (FILTRÉES)
// ============================================================
try {
    if ($user_role === 'admin') {
        $queryConventions = $pdo->query("
            SELECT c.id_convention,
                   c.fichier_pdf,
                   c.id_stage,
                   u_etud.nom_complet AS nom_etudiant,
                   u_entr.nom_complet AS nom_entreprise,
                   s.date_debut,
                   s.date_fin
            FROM convention c
            JOIN stage s ON c.id_stage = s.id_stage
            JOIN utilisateur u_etud ON s.id_etudiant = u_etud.id_user
            JOIN utilisateur u_entr ON s.id_entreprise = u_entr.id_user
            ORDER BY c.id_convention DESC
        ");
    } else {
        $queryConventions = $pdo->prepare("
            SELECT c.id_convention,
                   c.fichier_pdf,
                   c.id_stage,
                   u_etud.nom_complet AS nom_etudiant,
                   u_entr.nom_complet AS nom_entreprise,
                   s.date_debut,
                   s.date_fin
            FROM convention c
            JOIN stage s ON c.id_stage = s.id_stage
            JOIN etudiant etud ON s.id_etudiant = etud.id_user
            JOIN utilisateur u_etud ON s.id_etudiant = u_etud.id_user
            JOIN utilisateur u_entr ON s.id_entreprise = u_entr.id_user
            WHERE etud.id_enseignant = ?
            ORDER BY c.id_convention DESC
        ");
        $queryConventions->execute([$user_id]);
    }
    $conventions = $queryConventions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conventions = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Offres | Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
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
            text-align: center;
        }

        .nav-link {
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

        .nav-link i { margin-right: 15px; width: 20px; text-align: center; }

        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }

        .nav-link.active { 
            background: var(--primary); 
            color: white; 
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2); 
        }

        .sidebar-profile {
            padding: 0 10px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
        }
        .profile-icon {
            background: var(--primary);
            padding: 8px;
            border-radius: 10px;
            margin-right: 12px;
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
        }

        /* SECTION CARDS */
        .content-section {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 35px;
            transition: 0.3s;
        }
        .content-section:hover {
            border-color: rgba(59, 130, 246, 0.2);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /* TABLES */
        .table { color: #f8fafc; vertical-align: middle; margin-bottom: 0; }
        .table thead th {
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
        }

        .table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.02); transition: 0.2s; }
        .table tbody tr:hover { background: rgba(255,255,255,0.02); }

        /* BADGES & BUTTONS */
        .status-pill {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .bg-active { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .bg-expired { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }

        .action-btn {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-validate { background: var(--accent-green); color: white; }
        .btn-view { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
        .btn-view-orange { background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); }
        
        .btn-validate:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); color: white; }
        .btn-view:hover { background: var(--primary); color: white; }
        .btn-view-orange:hover { background: var(--accent-orange); color: white; }

        .company-tag {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .company-logo {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.7rem;
        }

        /* STYLE DES NAV-TABS PILLES */
        .nav-pills-admin .nav-link {
            color: var(--text-muted);
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 8px 16px;
            transition: 0.3s;
        }
        .nav-pills-admin .nav-link.active {
            background: rgba(59, 130, 246, 0.15) !important;
            color: var(--primary) !important;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="sidebar-brand">
        <i class="fa-solid fa-shield-halved me-2"></i> ADMIN PANEL
    </a>
    
    <div class="sidebar-profile">
        <div class="profile-icon">
            <i class="fa-solid fa-user-tie text-white"></i>
        </div>
        <div>
            <div class="fw-bold small"><?php echo ($user_role === 'admin') ? 'Admin Principal' : 'Enseignant'; ?></div>
            <small class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-circle fa-2xs me-1"></i> Session Active</small>
        </div>
    </div>

    <nav>
        <a href="dash.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <?php if ($user_role === 'admin'): ?>
            <a href="gestUtil.php" class="nav-link"><i class="fa-solid fa-users-gears"></i> Utilisateurs</a>
        <?php endif; ?>
        <a href="validStage.php" class="nav-link active"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <?php if ($user_role === 'admin'): ?>
            <a href="config.php" class="nav-link"><i class="fa-solid fa-gears"></i> Configurations</a>
        <?php endif; ?>
        <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>

<div class="main">
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show border-0 shadow-lg mb-4" role="alert" style="border-radius: 15px; background: rgba(30, 41, 59, 0.8); color: white;">
            <i class="fa-solid fa-circle-check text-success me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-1">Gestion des Offres</h2>
            <p class="text-muted mb-0">Contrôle des publications, validations des candidatures et documents.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 rounded-pill">Total: <?php echo $totalOffres; ?> offres</span>
            <span class="badge bg-info bg-opacity-10 text-info p-2 px-3 rounded-pill">
                <?php echo ($user_role === 'admin') ? "Total élèves: $totalEtudiants" : "Mes étudiants: $totalEtudiants"; ?>
            </span>
        </div>
    </div>

    <div class="content-section shadow-lg" data-aos="fade-up">
        <div class="section-header">
            <h5 class="fw-700 mb-0"><i class="fa-solid fa-layer-group me-2 text-primary"></i> Offres publiées</h5>
            <div class="search-box">
                <form action="validStage.php" method="GET" class="d-flex gap-1">
                    <input type="text" name="search" class="form-control form-control-sm bg-dark border-0 text-white" 
                           placeholder="Rechercher une offre..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php if(!empty($search)): ?>
                        <a href="validStage.php" class="btn btn-sm btn-secondary"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Intitulé du poste</th>
                        <th>Entreprise</th>
                        <th>Localisation</th>
                        <th>Date Limite</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($offres) > 0): ?>
                        <?php foreach ($offres as $offre): 
                            $words = explode(" ", $offre['nom_entreprise']);
                            $logoText = mb_strtoupper(mb_substr($words[0], 0, 1) . (isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
                            $estActive = ($offre['statut'] === 'ouverte');
                        ?>
                            <tr>
                                <td class="fw-600"><?php echo htmlspecialchars($offre['titre']); ?></td>
                                <td>
                                    <div class="company-tag">
                                        <div class="company-logo text-primary">
                                            <?php echo htmlspecialchars($logoText); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($offre['nom_entreprise']); ?></span>
                                    </div>
                                </td>
                                <td><i class="fa-solid fa-location-dot me-1 text-muted"></i> Douala</td>
                                <td class="text-muted">
                                    <?php echo date('d M Y', strtotime($offre['date_limite'])); ?>
                                </td>
                                <td>
                                    <?php if ($estActive): ?>
                                        <span class="status-pill bg-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-pill bg-expired">Expirée</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune offre de stage trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="content-section shadow-lg h-100" data-aos="fade-right" data-aos-delay="100">
                <div class="section-header">
                    <h5 class="fw-700 mb-0"><i class="fa-solid fa-user-check me-2 text-success"></i> Validation de Stages</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Entreprise</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($candidatures) > 0): ?>
                                <?php foreach ($candidatures as $cand): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($cand['nom_etudiant']); ?></div>
                                            <div class="small text-muted">Étudiant inscrit</div>
                                        </td>
                                        <td class="small"><?php echo htmlspecialchars($cand['nom_entreprise']); ?></td>
                                        <td class="text-end">
                                            <a href="validStage.php?validate_candidature_id=<?php echo $cand['id_candidature']; ?>" 
                                               class="action-btn btn-validate shadow-sm"
                                               onclick="return confirm('Voulez-vous vraiment valider ce stage pour <?php echo htmlspecialchars($cand['nom_etudiant']); ?> ?');">
                                                <i class="fa fa-check"></i> Valider
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Aucune demande de validation en attente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-section shadow-lg h-100" data-aos="fade-left" data-aos-delay="200">
                
                <div class="section-header d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                    <h5 class="fw-700 mb-0"><i class="fa-solid fa-folder-open me-2 text-warning"></i> Documents Déposés</h5>
                    <ul class="nav nav-pills nav-pills-admin ms-sm-auto" id="docTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-doc-btn" data-bs-toggle="pill" data-bs-target="#tab-doc" type="button" role="tab">Documents</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-rap-btn" data-bs-toggle="pill" data-bs-target="#tab-rap" type="button" role="tab">Rapports</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-conv-btn" data-bs-toggle="pill" data-bs-target="#tab-conv" type="button" role="tab">Conventions</button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="docTabsContent">
                    
                    <div class="tab-pane fade show active" id="tab-doc" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Type</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($documents) > 0): ?>
                                        <?php foreach ($documents as $doc): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($doc['nom_etudiant']); ?></div>
                                                    <div class="small text-muted">
                                                        Déposé le <?php echo $doc['date_upload'] ? date('d/m/Y', strtotime($doc['date_upload'])) : 'N/A'; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                                        <?php echo htmlspecialchars(str_replace('_', ' ', $doc['type_document'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="../uploads/documents/<?php echo urlencode($doc['chemin_fichier']); ?>"
                                                       target="_blank"
                                                       class="action-btn btn-view-orange me-2">
                                                        <i class="fa fa-eye"></i> Consulter
                                                    </a>
                                                    <?php if (!empty($doc['id_stage'])): ?>
                                                        <a href="generate_convention.php?id_stage=<?php echo (int) $doc['id_stage']; ?>&id_candidature=<?php echo (int) $doc['id_candidature']; ?>"
                                                           target="_blank"
                                                           class="action-btn btn-validate">
                                                            <i class="fa fa-file-signature"></i> Générer la convention
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="generate_convention.php?id_candidature=<?php echo (int) $doc['id_candidature']; ?>"
                                                           target="_blank"
                                                           class="action-btn btn-validate">
                                                            <i class="fa fa-file-signature"></i> Générer la convention
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Aucun document déposé pour le moment.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-rap" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Auteur</th>
                                        <th class="text-end">Fichier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($rapports) > 0): ?>
                                        <?php foreach ($rapports as $rap): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($rap['nom_etudiant']); ?></div>
                                                    <div class="small text-muted">
                                                        Déposé le <?php echo isset($rap['date_rapport_depot']) && $rap['date_rapport_depot'] ? date('d/m/Y', strtotime($rap['date_rapport_depot'])) : 'N/A'; ?>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="../uploads/rapports/<?php echo urlencode($rap['rapport_fichier']); ?>" 
                                                       target="_blank" 
                                                       class="action-btn btn-view">
                                                        <i class="fa fa-eye"></i> Consulter
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Aucun rapport déposé pour le moment.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-conv" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Entreprise</th>
                                        <th class="text-end">Fichier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($conventions) > 0): ?>
                                        <?php foreach ($conventions as $convention): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($convention['nom_etudiant']); ?></div>
                                                    <div class="small text-muted">
                                                        Période :
                                                        <?php echo $convention['date_debut'] ? date('d/m/Y', strtotime($convention['date_debut'])) : 'N/A'; ?>
                                                        →
                                                        <?php echo $convention['date_fin'] ? date('d/m/Y', strtotime($convention['date_fin'])) : 'N/A'; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                                        <?php echo htmlspecialchars($convention['nom_entreprise']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="../uploads/conventions/<?php echo urlencode($convention['fichier_pdf']); ?>"
                                                       target="_blank"
                                                       class="action-btn btn-view">
                                                        <i class="fa fa-eye"></i> Consulter
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Aucune convention générée pour le moment.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>

</body>
</html>