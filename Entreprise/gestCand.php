<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];

// --- GESTION DES ACTIONS ---
if (isset($_GET['action']) && isset($_GET['id_cand'])) {
    $id_cand = (int)$_GET['id_cand'];

    if ($_GET['action'] === 'accepter') {
        // Validation par l'entreprise : passage au statut intermédiaire
        $sql1 = "UPDATE CANDIDATURE c
                 JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
                 SET c.statut_candidature = 'valide_par_entreprise'
                 WHERE c.id_candidature = ? 
                   AND o.id_entreprise = ? 
                   AND c.statut_candidature = 'en_attente'";
        $pdo->prepare($sql1)->execute([$id_cand, $id_ent]);
    } elseif ($_GET['action'] === 'refuser') {
        $sql1 = "UPDATE CANDIDATURE c
                 JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
                 SET c.statut_candidature = 'refusee'
                 WHERE c.id_candidature = ? AND o.id_entreprise = ?";
        $pdo->prepare($sql1)->execute([$id_cand, $id_ent]);
    }
    header('Location: gestCand.php');
    exit();
}

// --- RÉCUPÉRATION DES CANDIDATURES ---
$sql = "SELECT c.*, u.nom_complet, u.email, u.telephone, u.adresse, u.created_at,
               e.ville, o.titre as titre_offre, o.description as description_offre
        FROM CANDIDATURE c
        JOIN UTILISATEUR u ON c.id_etudiant = u.id_user
        JOIN ETUDIANT e ON e.id_user = c.id_etudiant
        JOIN OFFRE_STAGE o ON c.id_offre = o.id_offre
        WHERE o.id_entreprise = ?
        ORDER BY c.date_postulation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_ent]);
$candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des candidatures | Espace Entreprise</title>

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
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #38bdf8;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
        }

        /* SIDEBAR (Harmonisée) */
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

        /* HEADER & FILTERS */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 35px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 20px;
        }

        .filter-group {
            background: var(--card-dark);
            padding: 5px 15px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
        }

        .filter-select {
            background: transparent;
            border: none;
            color: white;
            padding: 8px;
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
        }

        /* TABLE CARD */
        .card-table {
            background: var(--card-dark);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .table { color: #e2e8f0; vertical-align: middle; margin-bottom: 0; }
        .table thead th {
            background: rgba(0,0,0,0.2);
            color: var(--text-muted);
            border: none;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 15px;
        }

        .table tbody tr { transition: 0.3s; border-color: rgba(255,255,255,0.05); }
        .table tbody tr:hover { background: rgba(59, 130, 246, 0.04); }

        /* CANDIDATE INFO & AVATAR */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: 600;
            color: white;
            font-size: 0.85rem;
        }

        .candidate-name { font-weight: 600; font-size: 0.95rem; }
        .candidate-sub { font-size: 0.75rem; color: var(--text-muted); }

        /* STATUS BADGES */
        .status-pill {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            background: rgba(255,255,255,0.05);
        }

        .status-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
            flex-shrink: 0;
        }

        /* ACTION BUTTONS */
        .btn-action-group { display: flex; gap: 8px; }

        .btn-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: 0.3s;
            background: #2d3248;
            color: white;
        }

        .btn-view:hover { background: var(--accent-blue); transform: translateY(-2px); }
        .btn-accept:hover { background: var(--success); transform: translateY(-2px); }
        .btn-reject:hover { background: var(--danger); transform: translateY(-2px); }

        .btn-circle.disabled {
            opacity: 0.2;
            pointer-events: none;
        }

        /* PANNEAU LATÉRAL "VISUALISER" (offcanvas) */
        .offcanvas {
            width: 440px;
            background-color: var(--card-dark);
            color: white;
            border-left: 1px solid rgba(255,255,255,0.08);
        }
        .offcanvas-header {
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 20px 25px;
        }
        .offcanvas-title { font-weight: 600; }
        .offcanvas-body { padding: 25px; }
        .offcanvas .btn-close { filter: invert(1); opacity: 0.6; }

        .oc-avatar {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            font-size: 1.3rem;
            margin-right: 18px;
            flex-shrink: 0;
        }
        .oc-info-row {
            display: flex;
            align-items: flex-start;
            color: #cbd5e1;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .oc-info-row i { width: 20px; margin-right: 12px; color: var(--accent-blue); margin-top: 2px; }
        .oc-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 600;
            margin: 25px 0 12px;
        }
        .oc-offer-box {
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
            padding: 15px;
            font-size: 0.85rem;
            color: #cbd5e1;
            line-height: 1.5;
        }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-building me-2"></i> TECH SOLUTIONS
    </div>

    <nav>
         <a href="dashEnt.php" class="nav-link "><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="pubOffre.php" class="nav-link "><i class="fa-solid fa-plus-circle"></i> Publier une offre</a>
        <a href="offrePub.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link active"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
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
            <h2 class="fw-bold mb-1">Gestion des candidatures</h2>
            <p class="text-muted mb-0">Analysez et répondez aux postulants en temps réel.</p>
        </div>

        <div class="filter-group">
            <i class="fa-solid fa-magnifying-glass text-muted me-2" style="font-size: 0.8rem;"></i>
            <select class="filter-select">
                <option>Toutes les offres</option>
                <option>Développeur Web</option>
                <option>Data Analyst</option>
            </select>
        </div>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Candidat</th>
                        <th>Offre visée</th>
                        <th>Postulé le</th>
                        <th>Statut actuel</th>
                        <th class="text-end">Actions rapides</th>
                    </tr>
                </thead>
               <tbody>
    <?php if (empty($candidatures)): ?>
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">Aucune candidature reçue pour le moment.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($candidatures as $c):
            // Extraction des initiales (ex: Jean Paul -> JP)
            $mots = explode(" ", $c['nom_complet']);
            $initiales = strtoupper(substr($mots[0], 0, 1) . (isset($mots[1]) ? substr($mots[1], 0, 1) : ""));

            $deja_valide_entreprise = !empty($c['validation_entreprise']);

            // Détermination du statut affiché (3 états possibles maintenant)
            if ($c['statut_candidature'] === 'acceptee') {
                $status_color = 'var(--success)';
                $status_text  = 'Acceptée (validée admin)';
            } elseif ($c['statut_candidature'] === 'refusee') {
                $status_color = 'var(--danger)';
                $status_text  = 'Refusée';
            } elseif ($deja_valide_entreprise) {
                // en_attente MAIS déjà validée par l'entreprise -> en attente admin
                $status_color = 'var(--info)';
                $status_text  = 'Validée par vous — en attente admin';
            } else {
                $status_color = 'var(--warning)';
                $status_text  = 'En attente';
            }
        ?>
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar" style="<?= $c['statut_candidature'] == 'acceptee' ? 'background: linear-gradient(135deg, #10b981, #059669);' : ($deja_valide_entreprise ? 'background: linear-gradient(135deg, #38bdf8, #0284c7);' : '') ?>">
                        <?= $initiales ?>
                    </div>
                    <div>
                        <div class="candidate-name"><?= htmlspecialchars($c['nom_complet']) ?></div>
                        <div class="candidate-sub"><?= htmlspecialchars($c['email']) ?></div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-dark border border-secondary fw-normal"><?= htmlspecialchars($c['titre_offre']) ?></span>
            </td>
            <td>
    <div class="fw-500"><?= date('d/m/Y', strtotime($c['date_postulation'])) ?></div>
    <div class="candidate-sub">
        <?php
            $date = new DateTime($c['date_postulation']);
            $now = new DateTime();
            $diff = $now->diff($date);
            echo ($diff->days == 0) ? "Aujourd'hui" : "Il y a " . $diff->days . " jours";
        ?>
    </div>
</td>
            <td>
                <div class="status-pill" style="color: <?= $status_color ?>;">
                    <div class="dot" style="background: <?= $status_color ?>;"></div> <?= $status_text ?>
                </div>
            </td>
            <td>
                <div class="btn-action-group justify-content-end">
                    <button type="button" class="btn-circle btn-view" title="Consulter le profil"
                            data-bs-toggle="offcanvas" data-bs-target="#oc<?= $c['id_candidature'] ?>">
                        <i class="fa-solid fa-eye"></i>
                    </button>

                    <?php
                        // Le bouton "Accepter" se désactive si :
                        // - la candidature a déjà été refusée
                        // - la candidature a déjà été validée admin (acceptee)
                        // - l'entreprise a déjà validé (validation_entreprise existe)
                        $accept_disabled = $deja_valide_entreprise
                            || $c['statut_candidature'] === 'acceptee'
                            || $c['statut_candidature'] === 'refusee';
                    ?>
                    <a href="gestCand.php?action=accepter&id_cand=<?= $c['id_candidature'] ?>"
                       class="btn-circle btn-accept <?= $accept_disabled ? 'disabled' : '' ?>"
                       title="<?= $deja_valide_entreprise ? 'Déjà validée, en attente de l\'admin' : 'Valider (envoyer à l\'admin)' ?>">
                        <i class="fa-solid fa-check"></i>
                    </a>

                    <a href="gestCand.php?action=refuser&id_cand=<?= $c['id_candidature'] ?>"
                       class="btn-circle btn-reject <?= $c['statut_candidature'] == 'refusee' ? 'disabled' : '' ?>"
                       onclick="return confirm('Refuser cette candidature ?')"
                       title="Refuser">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 p-3 rounded-4 d-flex align-items-center" style="background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.2);">
        <i class="fa-solid fa-lightbulb text-primary me-3 fs-5"></i>
        <span class="text-muted small"><strong>Conseil :</strong> Un "check" bleu signifie que vous avez validé la candidature ; elle reste "en attente" côté étudiant jusqu'à la validation pédagogique de l'administrateur.</span>
    </div>

</div>

<?php foreach ($candidatures as $c):
    $mots = explode(" ", $c['nom_complet']);
    $initiales = strtoupper(substr($mots[0], 0, 1) . (isset($mots[1]) ? substr($mots[1], 0, 1) : ""));

    $deja_valide_entreprise = !empty($c['validation_entreprise']);

    if ($c['statut_candidature'] === 'acceptee') {
        $status_color = 'var(--success)';
        $status_text  = 'Acceptée (validée admin)';
    } elseif ($c['statut_candidature'] === 'refusee') {
        $status_color = 'var(--danger)';
        $status_text  = 'Refusée';
    } elseif ($deja_valide_entreprise) {
        $status_color = 'var(--info)';
        $status_text  = 'Validée par vous — en attente admin';
    } else {
        $status_color = 'var(--warning)';
        $status_text  = 'En attente';
    }

    $accept_disabled = $deja_valide_entreprise
        || $c['statut_candidature'] === 'acceptee'
        || $c['statut_candidature'] === 'refusee';
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="oc<?= $c['id_candidature'] ?>">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Profil du candidat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">

        <div class="d-flex align-items-center mb-3">
            <div class="oc-avatar" style="<?= $c['statut_candidature'] == 'acceptee' ? 'background: linear-gradient(135deg, #10b981, #059669);' : ($deja_valide_entreprise ? 'background: linear-gradient(135deg, #38bdf8, #0284c7);' : '') ?>">
                <?= $initiales ?>
            </div>
            <div>
                <div class="fw-bold fs-5"><?= htmlspecialchars($c['nom_complet']) ?></div>
                <div class="status-pill mt-1" style="color: <?= $status_color ?>;">
                    <div class="dot" style="background: <?= $status_color ?>;"></div> <?= $status_text ?>
                </div>
            </div>
        </div>

        <div class="oc-info-row"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($c['email']) ?></div>
        <?php if (!empty($c['telephone'])): ?>
            <div class="oc-info-row"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($c['telephone']) ?></div>
        <?php endif; ?>
        <?php if (!empty($c['ville'])): ?>
            <div class="oc-info-row"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($c['ville']) ?></div>
        <?php endif; ?>
        <?php if (!empty($c['adresse'])): ?>
            <div class="oc-info-row"><i class="fa-solid fa-house"></i> <?= htmlspecialchars($c['adresse']) ?></div>
        <?php endif; ?>
        <?php if (!empty($c['created_at'])): ?>
            <div class="oc-info-row"><i class="fa-solid fa-calendar"></i> Inscrit(e) le <?= date('d/m/Y', strtotime($c['created_at'])) ?></div>
        <?php endif; ?>

        <div class="oc-section-title">Offre visée</div>
        <div class="oc-offer-box">
            <div class="fw-600 mb-2" style="color:white;"><?= htmlspecialchars($c['titre_offre']) ?></div>
            <?= nl2br(htmlspecialchars($c['description_offre'])) ?>
        </div>

        <div class="oc-section-title">Candidature</div>
        <div class="oc-info-row"><i class="fa-solid fa-calendar-check"></i> Postulé le <?= date('d/m/Y', strtotime($c['date_postulation'])) ?></div>

        <div class="btn-action-group mt-4">
            <a href="gestCand.php?action=accepter&id_cand=<?= $c['id_candidature'] ?>"
               class="btn btn-sm flex-fill <?= $accept_disabled ? 'btn-secondary disabled' : 'btn-success' ?>">
                <i class="fa-solid fa-check me-1"></i> Accepter
            </a>
            <a href="gestCand.php?action=refuser&id_cand=<?= $c['id_candidature'] ?>"
               class="btn btn-sm flex-fill <?= $c['statut_candidature'] == 'refusee' ? 'btn-secondary disabled' : 'btn-danger' ?>"
               onclick="return confirm('Refuser cette candidature ?')">
                <i class="fa-solid fa-xmark me-1"></i> Refuser
            </a>
        </div>

    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>