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

// Petit helper : reconstruit un chemin utilisable vers un fichier stocké en base
// (même logique que dans Entreprise/dashEnt.php et Admin/dash.php)
function resolveFilePath($raw, $defaultFolder) {
    if (empty($raw)) return null;
    if (strpos($raw, '/') !== false) {
        return '../' . ltrim($raw, '/');
    }
    return $defaultFolder . $raw;
}

// ============================================================
// NOTIFICATIONS (toutes, avec état lu/non lu)
// ============================================================
$stmtNotifs = $pdo->prepare("
    SELECT id, type, message, lu, date_creation, id_stage
    FROM notifications
    WHERE id_user = ? AND type = 'convention_disponible'
    ORDER BY date_creation DESC
");
$stmtNotifs->execute([$id_etudiant]);
$notifications_brutes = $stmtNotifs->fetchAll(PDO::FETCH_ASSOC);

$notifications = [];
$nb_notifs = 0; // compteur uniquement des notifications NON lues

foreach ($notifications_brutes as $n) {
    if (!$n['id_stage']) continue;

    // Sécurité : le stage doit bien appartenir à cet étudiant
    $stmtCheck = $pdo->prepare("
        SELECT s.id_stage
        FROM STAGE s
        WHERE s.id_stage = ? AND s.id_etudiant = ?
    ");
    $stmtCheck->execute([$n['id_stage'], $id_etudiant]);
    if (!$stmtCheck->fetch()) continue;

    $stmtConv = $pdo->prepare("
        SELECT fichier_pdf
        FROM convention
        WHERE id_stage = ?
        ORDER BY id_convention DESC
        LIMIT 1
    ");
    $stmtConv->execute([$n['id_stage']]);
    $conv = $stmtConv->fetch(PDO::FETCH_ASSOC);

    $fichiers = [];
    if ($conv) {
        $fichiers[] = [
            'label' => 'Convention de stage',
            'url' => resolveFilePath($conv['fichier_pdf'], '../uploads/conventions/')
        ];
    }

    if ((int)$n['lu'] === 0) {
        $nb_notifs++;
    }

    $notifications[] = [
        'id' => (int)$n['id'],
        'texte' => 'Votre convention de stage est disponible',
        'date' => $n['date_creation'],
        'fichiers' => $fichiers,
        'lu' => (int)$n['lu'],
    ];
}

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
            width: 340px; max-height: 460px; overflow-y: auto;
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
            background: rgba(16,185,129,0.08);
        }
        .notif-item.notif-read {
            background: rgba(15, 23, 42, 0.75);
            opacity: 0.8;
        }
        .notif-item.notif-read:hover {
            background: rgba(15, 23, 42, 0.9);
        }
        .notif-item.notif-read .texte {
            color: #cbd5e1;
        }
        .notif-item.notif-read .date {
            color: #64748b;
        }
        .notif-item:hover { background: rgba(16,185,129,0.16); }
        .notif-item .texte { font-weight: 600; font-size: 0.85rem; }
        .notif-item .date { color: #5b5f70; font-size: 0.7rem; margin-top: 6px; }
        .notif-empty { padding: 30px 18px; text-align: center; color: var(--text-muted); font-size: 0.85rem; }

        .doc-view {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(4px);
            padding: 40px 20px;
            overflow-y: auto;
        }
        .doc-view-inner {
            background: var(--card-dark);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 25px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
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
        .doc-file-block a.doc-download { color: var(--accent-blue); font-size: 0.78rem; text-decoration: none; }

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
    <div class="d-flex justify-content-between align-items-center mb-5 fade-in" style="position: relative; z-index: 50;">
        <div>
            <h2 class="fw-bold mb-1">Bienvenue 👋</h2>
            <p class="text-muted mb-0">Ravi de vous revoir ! Voici le point sur vos recherches.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="notif-bell-wrap">
                <button class="notif-bell-btn" onclick="toggleNotifPanel()" type="button">
                    <i class="fa-solid fa-bell"></i>
                    <span id="notifBadge" class="notif-badge" style="display: <?= $nb_notifs > 0 ? 'flex' : 'none' ?>;">
                        <?= $nb_notifs > 9 ? '9+' : $nb_notifs ?>
                    </span>
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
                        <div class="notif-item <?= (int)$n['lu'] === 1 ? 'notif-read' : '' ?>" onclick="showDoc(<?= (int)$n['id'] ?>)">
                            <div class="texte"><?= htmlspecialchars($n['texte']) ?></div>
                            <div class="date"><?= date('d/m/Y à H:i', strtotime($n['date'])) ?></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <span class="badge bg-dark text-muted border border-secondary">Année académique 2025/2026</span>
        </div>
    </div>

    <!-- VUES DOCUMENTS (une par notification, cachées par défaut) -->
    <?php foreach ($notifications as $n): ?>
        <div id="docView_<?= (int)$n['id'] ?>" class="doc-view">
            <div class="doc-view-inner">
                <div class="doc-view-header">
                    <h4>Ma convention de stage</h4>
                    <button class="btn-back" onclick="backToNotifs()">
                        <i class="fa-solid fa-arrow-left me-2"></i>Retour aux notifications
                    </button>
                </div>
                <?php if (empty($n['fichiers'])): ?>
                    <p class="text-muted">Fichier introuvable pour le moment.</p>
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
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

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

<!-- Manquant avant : sans ce script, aucun composant Bootstrap interactif (dont la cloche de notifications) ne fonctionne -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let unreadNotifCount = <?= (int)$nb_notifs ?>;

    function updateNotifBadge() {
        const badge = document.getElementById('notifBadge');
        if (!badge) return;

        if (unreadNotifCount > 0) {
            badge.textContent = unreadNotifCount > 9 ? '9+' : unreadNotifCount;
            badge.style.display = 'flex';
        } else {
            badge.textContent = '';
            badge.style.display = 'none';
        }
    }

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

        fetch('marquer_notif_lue.php?id=' + notifId)
            .then(response => response.json())
            .then(data => {
                if (data && typeof data.remaining_unread !== 'undefined') {
                    unreadNotifCount = Number(data.remaining_unread) || 0;
                    updateNotifBadge();
                }
            })
            .catch(() => {});
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

    updateNotifBadge();
</script>

</body>
</html>