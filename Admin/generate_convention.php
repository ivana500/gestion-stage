<?php
session_start();
include('../Auth/config_db.php');

// L'autoload manuel doit être chargé avant toute référence à Dompdf\Dompdf,
// et les instructions "use" doivent être au niveau du fichier (PHP interdit
// "use" à l'intérieur d'un bloc if/else — c'était une erreur fatale sinon).
// ⚠️ Adapte ce chemin si ton dossier lib/ n'est pas à la racine du projet
require_once '../lib/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'sous_admin'])) {
    header('Location: ../Auth/connexion.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// ============================================================
// GÉNÉRATION DU PDF (déclenchée si ?id_stage=... est présent dans l'URL)
// ============================================================
if (isset($_GET['id_stage'])) {

    $id_stage = (int) $_GET['id_stage'];

    // Sécurité : le stage doit exister, et pour un sous-admin, l'étudiant doit lui être assigné
    $sql = "SELECT s.*,
                   u_etud.nom_complet AS nom_etudiant, u_etud.email AS email_etudiant,
                   u_etud.telephone AS tel_etudiant,
                   u_ent.nom_complet AS nom_entreprise, u_ent.adresse AS adresse_entreprise,
                   o.titre AS titre_offre, o.description AS description_offre, o.lieu, o.duree,
                   et.id_enseignant
            FROM stage s
            JOIN utilisateur u_etud ON s.id_etudiant = u_etud.id_user
            JOIN utilisateur u_ent ON s.id_entreprise = u_ent.id_user
            JOIN offre_stage o ON s.id_offre = o.id_offre
            JOIN etudiant et ON s.id_etudiant = et.id_user
            WHERE s.id_stage = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_stage]);
    $stage = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stage) {
        die("Stage introuvable.");
    }
    if ($user_role === 'sous_admin' && $stage['id_enseignant'] != $user_id) {
        die("Accès refusé : cet étudiant ne vous est pas assigné.");
    }

    // 2. Construction du contenu HTML de la convention
    $date_jour = date('d/m/Y');
    $date_debut = $stage['date_debut'] ? date('d/m/Y', strtotime($stage['date_debut'])) : 'À définir';
    $date_fin   = $stage['date_fin'] ? date('d/m/Y', strtotime($stage['date_fin'])) : 'À définir';

    $html = '
    <html>
    <head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        .bandeau { background: #3b82f6; color: white; padding: 14px 20px; margin: -20px -20px 25px -20px; }
        .bandeau .brand { font-size: 14px; font-weight: bold; letter-spacing: 1px; }
        h1 { text-align: center; font-size: 18px; text-transform: uppercase; margin-bottom: 5px; }
        .sous-titre { text-align: center; color: #555; margin-bottom: 30px; font-size: 11px; }
        .section { margin-bottom: 18px; }
        .section-title { font-weight: bold; font-size: 13px; border-bottom: 1px solid #3b82f6; padding-bottom: 4px; margin-bottom: 8px; color: #1d4ed8; }
        table.infos { width: 100%; margin-bottom: 10px; }
        table.infos td { padding: 3px 0; vertical-align: top; }
        table.infos td.label { width: 160px; color: #444; }
        .signatures { margin-top: 60px; width: 100%; }
        .signatures td { width: 50%; text-align: center; padding-top: 40px; border-top: 1px solid #999; }
        .footer { margin-top: 40px; font-size: 9px; color: #888; text-align: center; }
    </style>
    </head>
    <body>
        <div class="bandeau"><span class="brand">StageApp — Plateforme de Gestion des Stages</span></div>

        <h1>Convention de Stage</h1>
        <div class="sous-titre">Générée le ' . $date_jour . '</div>

        <div class="section">
            <div class="section-title">Entre les soussignés</div>
            <table class="infos">
                <tr><td class="label">Entreprise :</td><td>' . htmlspecialchars($stage['nom_entreprise']) . '</td></tr>
                <tr><td class="label">Adresse :</td><td>' . htmlspecialchars($stage['adresse_entreprise'] ?? 'Non renseignée') . '</td></tr>
            </table>
            <table class="infos">
                <tr><td class="label">Stagiaire :</td><td>' . htmlspecialchars($stage['nom_etudiant']) . '</td></tr>
                <tr><td class="label">Email :</td><td>' . htmlspecialchars($stage['email_etudiant']) . '</td></tr>
                <tr><td class="label">Téléphone :</td><td>' . htmlspecialchars($stage['tel_etudiant'] ?? 'Non renseigné') . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Objet du stage</div>
            <table class="infos">
                <tr><td class="label">Poste / Offre :</td><td>' . htmlspecialchars($stage['titre_offre']) . '</td></tr>
                <tr><td class="label">Lieu :</td><td>' . htmlspecialchars($stage['lieu'] ?? 'Non précisé') . '</td></tr>
                <tr><td class="label">Durée :</td><td>' . htmlspecialchars($stage['duree'] ?? 'Non précisée') . '</td></tr>
                <tr><td class="label">Description :</td><td>' . nl2br(htmlspecialchars($stage['description_offre'])) . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Durée du stage</div>
            <table class="infos">
                <tr><td class="label">Date de début :</td><td>' . $date_debut . '</td></tr>
                <tr><td class="label">Date de fin :</td><td>' . $date_fin . '</td></tr>
            </table>
        </div>

        <table class="signatures">
            <tr>
                <td>Signature de l\'entreprise</td>
                <td>Signature du stagiaire</td>
            </tr>
        </table>

        <div class="footer">Document généré automatiquement via StageApp.</div>
    </body>
    </html>';

    // 3. Génération du PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 4. Sauvegarde sur le disque
    $dossier = '../uploads/conventions/';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }
    $nom_fichier = 'convention_stage' . $id_stage . '_' . time() . '.pdf';
    file_put_contents($dossier . $nom_fichier, $dompdf->output());

    // 5. Enregistrement en base + notification à l'étudiant
    try {
        $pdo->prepare("INSERT INTO convention (id_stage, fichier_pdf) VALUES (?, ?)")
            ->execute([$id_stage, $nom_fichier]);

        $pdo->prepare("INSERT INTO notifications (id_user, type, id_stage, message)
                        VALUES (?, 'convention_disponible', ?, 'Votre convention de stage est disponible.')")
            ->execute([$stage['id_etudiant'], $id_stage]);
    } catch (PDOException $e) {
        // On n'interrompt pas l'affichage du PDF si la base échoue, mais on log l'erreur
        error_log('Erreur enregistrement convention : ' . $e->getMessage());
    }

    // 6. Affichage direct dans le navigateur (Attachment=false => s'ouvre inline,
    //    l'admin peut utiliser le bouton Imprimer natif de la visionneuse PDF du navigateur)
    $dompdf->stream($nom_fichier, ["Attachment" => false]);
    exit();
}

// ============================================================
// LISTE DES STAGES ÉLIGIBLES (page normale, si pas de ?id_stage)
// ============================================================
if ($user_role === 'admin') {
    $stmtStages = $pdo->query("
        SELECT s.id_stage, s.date_debut, s.date_fin, s.statut_stage,
               u_etud.nom_complet AS nom_etudiant,
               u_ent.nom_complet AS nom_entreprise,
               o.titre AS titre_offre,
               conv.id_convention
        FROM stage s
        JOIN utilisateur u_etud ON s.id_etudiant = u_etud.id_user
        JOIN utilisateur u_ent ON s.id_entreprise = u_ent.id_user
        JOIN offre_stage o ON s.id_offre = o.id_offre
        LEFT JOIN convention conv ON conv.id_stage = s.id_stage
        ORDER BY s.id_stage DESC
    ");
    $stages = $stmtStages->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtStages = $pdo->prepare("
        SELECT s.id_stage, s.date_debut, s.date_fin, s.statut_stage,
               u_etud.nom_complet AS nom_etudiant,
               u_ent.nom_complet AS nom_entreprise,
               o.titre AS titre_offre,
               conv.id_convention
        FROM stage s
        JOIN utilisateur u_etud ON s.id_etudiant = u_etud.id_user
        JOIN utilisateur u_ent ON s.id_entreprise = u_ent.id_user
        JOIN offre_stage o ON s.id_offre = o.id_offre
        JOIN etudiant et ON s.id_etudiant = et.id_user
        LEFT JOIN convention conv ON conv.id_stage = s.id_stage
        WHERE et.id_enseignant = ?
        ORDER BY s.id_stage DESC
    ");
    $stmtStages->execute([$user_id]);
    $stages = $stmtStages->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conventions de Stage | Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
        .sidebar {
            height: 100vh; background-color: var(--sidebar-dark);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed; width: 280px; padding: 30px 20px; z-index: 1000;
        }
        .sidebar-brand {
            font-weight: 800; font-size: 1.25rem;
            background: linear-gradient(to right, #3b82f6, #60a5fa);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 40px; display: block; text-decoration: none; text-align: center;
        }
        .nav-link {
            color: var(--text-muted); padding: 14px 18px; display: flex; align-items: center;
            text-decoration: none; border-radius: 12px; margin-bottom: 10px; transition: 0.3s; font-weight: 500;
        }
        .nav-link i { margin-right: 15px; width: 20px; text-align: center; }
        .nav-link:hover, .nav-link.active { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); }
        .nav-link.active { background: var(--accent-blue); color: white; box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2); }

        .main-content {
            margin-left: 280px; padding: 40px;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
        }

        .table-card {
            background: var(--card-dark); backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px; padding: 25px;
        }
        .table { color: #e2e8f0; vertical-align: middle; }
        .table thead th {
            background: rgba(0,0,0,0.2); color: var(--text-muted); border: none;
            text-transform: uppercase; font-size: 0.72rem; letter-spacing: 1px; padding: 14px;
        }
        .table tbody td { padding: 14px; border-color: rgba(255,255,255,0.05); }

        .btn-generate {
            background: var(--accent-blue); color: white; border: none;
            padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.8rem;
            text-decoration: none; display: inline-flex; align-items: center;
        }
        .btn-generate:hover { background: #2563eb; color: white; }
        .btn-view {
            background: transparent; border: 1px solid rgba(16,185,129,0.4); color: #10b981;
            padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.8rem;
            text-decoration: none; display: inline-flex; align-items: center;
        }
        .btn-view:hover { background: rgba(16,185,129,0.1); color: #10b981; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand"><i class="fa-solid fa-shield-halved me-2"></i> ADMIN PANEL</div>
    <nav>
        <a href="dash.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="gestUtil.php" class="nav-link"><i class="fa-solid fa-users-gears"></i> Utilisateurs</a>
        <a href="validStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <a href="generate_convention.php" class="nav-link active"><i class="fa-solid fa-file-signature"></i> Conventions</a>
        <a href="Config.php" class="nav-link"><i class="fa-solid fa-gears"></i> Configurations</a>
        <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
            <i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span>
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="mb-5">
        <h2 class="fw-800 mb-1">Conventions de Stage</h2>
        <p class="text-muted mb-0">Génère et imprime la convention pour chaque stage validé.</p>
    </div>

    <div class="table-card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Entreprise</th>
                        <th>Offre</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stages)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun stage validé pour le moment.</td></tr>
                    <?php else: foreach ($stages as $s): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($s['nom_etudiant']) ?></td>
                            <td><?= htmlspecialchars($s['nom_entreprise']) ?></td>
                            <td><span class="badge bg-dark border border-secondary"><?= htmlspecialchars($s['titre_offre']) ?></span></td>
                            <td class="text-muted small">
                                <?= $s['date_debut'] ? date('d/m/Y', strtotime($s['date_debut'])) : '—' ?>
                                →
                                <?= $s['date_fin'] ? date('d/m/Y', strtotime($s['date_fin'])) : '—' ?>
                            </td>
                            <td><span class="badge bg-primary bg-opacity-25 text-info"><?= htmlspecialchars($s['statut_stage']) ?></span></td>
                            <td class="text-end">
                                <?php if ($s['id_convention']): ?>
                                    <a href="generate_convention.php?id_stage=<?= $s['id_stage'] ?>" target="_blank" class="btn-view">
                                        <i class="fa-solid fa-eye me-2"></i> Revoir / Réimprimer
                                    </a>
                                <?php else: ?>
                                    <a href="generate_convention.php?id_stage=<?= $s['id_stage'] ?>" target="_blank" class="btn-generate">
                                        <i class="fa-solid fa-file-pdf me-2"></i> Générer & Imprimer
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>