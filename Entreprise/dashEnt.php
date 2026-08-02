<?php
session_start();
include('../Auth/config_db.php');

// Protection de la page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];

// Petit helper : reconstruit un chemin utilisable vers un fichier stocké en base.
// Hypothèse : si la valeur contient déjà un "/", elle est stockée relative à la racine du projet.
// Sinon, c'est juste un nom de fichier -> on le complète avec le dossier par défaut.
// ⚠️ À vérifier/ajuster selon ce que ton script d'upload écrit réellement en base.
function resolveFilePath($raw, $defaultFolder) {
    if (empty($raw)) return null;
    if (strpos($raw, '/') !== false) {
        return '../' . ltrim($raw, '/');
    }
    return $defaultFolder . $raw;
}

// --- 1. STATISTIQUES ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM OFFRE_STAGE WHERE id_entreprise = ?");
$stmt->execute([$id_ent]);
$nb_offres = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM CANDIDATURE c 
                       JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre 
                       WHERE o.id_entreprise = ?");
$stmt->execute([$id_ent]);
$nb_candidatures = $stmt->fetchColumn();

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

// --- 3. NOTIFICATIONS NON LUES (documents de candidature OU rapport) ---
// AVANT : la requête joignait n.id_user (= id de l'entreprise) à c.id_etudiant,
// ce qui ne pouvait jamais matcher correctement. On utilise maintenant les
// colonnes id_candidature / id_stage ajoutées par la migration.
$stmtNotifs = $pdo->prepare("SELECT id, type, message, lu, date_creation, id_candidature, id_stage
                              FROM notifications
                              WHERE id_user = ? AND lu = 0
                              ORDER BY date_creation DESC");
$stmtNotifs->execute([$id_ent]);
$notifications_brutes = $stmtNotifs->fetchAll(PDO::FETCH_ASSOC);

$notifications = [];
foreach ($notifications_brutes as $n) {

    if ($n['type'] === 'documents_candidature' && $n['id_candidature']) {
        // Sécurité : la candidature doit viser une offre de CETTE entreprise
        $stmtCheck = $pdo->prepare("SELECT u.nom_complet
                                     FROM CANDIDATURE c
                                     JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
                                     JOIN UTILISATEUR u ON c.id_etudiant = u.id_user
                                     WHERE c.id_candidature = ? AND o.id_entreprise = ?");
        $stmtCheck->execute([$n['id_candidature'], $id_ent]);
        $info = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$info) continue; // pas la bonne entreprise -> on ignore par sécurité

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
        // Sécurité : le stage doit bien être rattaché à CETTE entreprise
        $stmtCheck = $pdo->prepare("SELECT u.nom_complet
                                     FROM STAGE s
                                     JOIN UTILISATEUR u ON s.id_etudiant = u.id_user
                                     WHERE s.id_stage = ? AND s.id_entreprise = ?");
        $stmtCheck->execute([$n['id_stage'], $id_ent]);
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
    // types inconnus : ignorés par sécurité
}
$nb_notifs = count($notifications);
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
            position: relative;
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

        /* ---- NOTIFICATIONS ---- */
        .notif-bell-wrap { position: relative; }
        .notif-bell-btn {
            width: 46px; height: 46px; border-radius: 50%;
            background: var(--card-dark); border: 1px solid rgba(255,255,255,0.08);
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
            background: var(--card-dark); border: 1px solid rgba(255,255,255,0.08);
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

        /* ---- VUE DOCUMENT (CV / lettre / rapport) ---- */
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
        .pointer { cursor: pointer; }
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
        </a>
    </nav>
</div>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Bienvenue <?= htmlspecialchars($_SESSION['user_name'] ?? 'Entreprise') ?> 👋</h2>
            <p class="text-muted mb-0">Voici l'état actuel de vos recrutements de stagiaires.</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="notif-bell-wrap">
                <button class="notif-bell-btn" onclick="toggleNotifPanel()" type="button">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($nb_notifs > 0): ?>
                        <span class="notif-badge"><?= $nb_notifs > 9 ? '9+' : $nb_notifs ?></span>
                    <?php endif; ?>
                </button>

                <!-- PANNEAU NOTIFICATIONS -->
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

            <a href="publierOffre.php" class="btn btn-publish shadow text-white" style="text-decoration:none;">
                <i class="fa-solid fa-paper-plane me-2"></i> Publier une nouvelle offre
            </a>
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
                                <?php elseif($c['statut_candidature'] == 'refusee'): ?>
                                    <span class="badge badge-status bg-danger">Refusée</span>
                                <?php else: ?>
                                    <span class="badge badge-status bg-info text-dark"><?= htmlspecialchars($c['statut_candidature']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="gestCand.php" class="btn btn-sm btn-outline-info me-1"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleNotifPanel() {
        const panel = document.getElementById('notifPanel');
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
    }

    function showDoc(notifId) {
        // On cache le panneau de notifications ET tous les autres docs déjà ouverts
        document.getElementById('notifPanel').style.display = 'none';
        document.querySelectorAll('.doc-view').forEach(el => el.style.display = 'none');

        const view = document.getElementById('docView_' + notifId);
        if (view) {
            view.style.display = 'block';
            view.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Marque la notification comme lue en arrière-plan, sans quitter la page
        fetch('marquer_notif_lue.php?id=' + notifId).catch(() => {});
    }

    function backToNotifs() {
        document.querySelectorAll('.doc-view').forEach(el => el.style.display = 'none');
        document.getElementById('notifPanel').style.display = 'block';
    }

    // Ferme le panneau si on clique en dehors
    document.addEventListener('click', function (e) {
        const wrap = document.querySelector('.notif-bell-wrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('notifPanel').style.display = 'none';
        }
    });
</script>

</body>
</html>