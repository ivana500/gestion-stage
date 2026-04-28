<?php
session_start();
include('../Auth/config_db.php');

// Récupération des offres actives (on joint l'utilisateur pour avoir le nom de l'entreprise)
$sql = "SELECT o.*, u.nom_complet as entreprise_nom, e.siege_social 
        FROM OFFRE_STAGE o
        JOIN UTILISATEUR u ON o.id_entreprise = u.id_user 
        LEFT JOIN ENTREPRISE e ON u.id_user = e.id_user 
        WHERE o.statut = 'ouverte' 
        AND (o.date_limite >= CURDATE() OR o.date_limite IS NULL)
        ORDER BY o.id_offre DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$offres = $stmt->fetchAll();

// Traitement AJAX de la postulation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_postuler'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
        echo json_encode(['status' => 'error', 'message' => 'Connectez-vous en tant qu\'étudiant.']);
        exit;
    }

    $id_offre = intval($_POST['id_offre']);
    $id_etudiant = $_SESSION['user_id'];

    try {
        // Vérifier si déjà postulé
        $check = $pdo->prepare("SELECT id_candidature FROM CANDIDATURE WHERE id_offre = ? AND id_etudiant = ?");
        $check->execute([$id_offre, $id_etudiant]);

        if ($check->rowCount() > 0) {
            echo json_encode(['status' => 'info', 'message' => 'Vous avez déjà postulé à cette offre.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO CANDIDATURE (id_etudiant, id_offre) VALUES (?, ?)");
            $stmt->execute([$id_etudiant, $id_offre]);
            echo json_encode(['status' => 'success', 'message' => 'Candidature envoyée !']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur technique.']);
    }
    exit; // Très important : on arrête le script ici pour ne pas envoyer le HTML
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres de Stage | STAGES HELLO</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --sidebar-bg: #020617;
            --card-bg: rgba(30, 41, 59, 0.5);
            --primary: #3b82f6;
            --accent: #10b981;
            --text-muted: #94a3b8;
        }

        body {
            background: var(--bg);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }

        /* SIDEBAR PREMIUM */
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
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        .sidebar a i {
            margin-right: 15px;
            font-size: 1.1rem;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: var(--primary);
            color: white;
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
        }

        /* SEARCH BAR */
        .search-container {
            position: relative;
            width: 350px;
        }

        .search-box {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 20px 12px 45px;
            border-radius: 12px;
            color: white;
            width: 100%;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* OFFER CARDS */
        .card-offre {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 25px;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-offre:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--primary);
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .company-logo {
            width: 45px;
            height: 45px;
            background: var(--sidebar-bg);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--primary);
            font-weight: bold;
        }

        .badge-custom {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 8px;
        }

        .salary-tag {
            color: var(--accent);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .btn-apply {
            background: var(--primary);
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: 0.3s;
            margin-top: auto;
        }

        .btn-apply:hover {
            background: #2563eb;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="sidebar-brand">
        <i class="fa-solid fa-rocket"></i> STAGES HELLO
    </a>

   <a href="dashEtud.php"><i class="fa-solid fa-grip-vertical"></i> Dashboard</a>
    <a href="listeStage.php" class="active"><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
    <a href="Candidature.php"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
    <a href="MonStage.php" ><i class="fa-solid fa-file-arrow-up"></i> Dépôt de rapport</a>
    
    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
        <a href="#" class="text-danger"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
    </div>
</div>

<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-1">Exploration</h2>
            <p class="text-muted mb-0">Trouvez le stage qui correspond à vos ambitions.</p>
        </div>

        <form action="" method="GET" class="search-container d-none d-lg-block">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" class="search-box" placeholder="Poste, entreprise, ville...">
        </form>
    </div>

    <div class="row g-4">
        <?php if (count($offres) > 0): ?>
           <?php foreach ($offres as $offre): 
    // On prépare les initiales pour le logo (ex: AZUR -> AZ)
    $initials = strtoupper(substr($offre['entreprise_nom'], 0, 2));
?>
<div class="col-xl-4 col-md-6" data-aos="fade-up">
    <div class="card-offre">
        <div class="d-flex justify-content-between align-items-start">
            <div class="company-logo"><?= $initials ?></div>
            <span class="salary-tag">Stage</span>
        </div>
        
        <h4 class="fw-bold mb-1"><?= htmlspecialchars($offre['titre']) ?></h4>
        
        <p class="text-muted small mb-3">
            <?= htmlspecialchars($offre['entreprise_nom']) ?> • <?= htmlspecialchars($offre['siege_social'] ?? 'Douala') ?>
        </p>

        <div class="mb-4">
            <span class="badge-custom"><i class="fa-solid fa-clock me-1"></i> Offre <?= htmlspecialchars($offre['statut']) ?></span>
            <span class="badge-custom"><i class="fa-solid fa-location-dot me-1"></i> Présentiel</span>
        </div>

        <p class="small text-muted mb-4">
            <?= nl2br(htmlspecialchars(substr($offre['description'], 0, 100))) ?>...
        </p>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted">
                <i class="fa-regular fa-calendar me-1"></i> 
                Limite : <?= date('d/m/Y', strtotime($offre['date_limite'])) ?>
            </small>
        </div>

       <button type="button" 
        class="btn-apply text-center d-block w-100 border-0" 
        onclick="postulerAjax(<?= $offre['id_offre'] ?>, this)">
    Postuler maintenant
</button>
    </div>
</div>
<?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune offre disponible pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    function postulerAjax(idOffre, btn) {
    if (!confirm('Voulez-vous postuler à cette offre ?')) return;

    // On prépare les données
    let formData = new FormData();
    formData.append('ajax_postuler', '1');
    formData.append('id_offre', idOffre);

    // Désactiver le bouton pendant l'envoi
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Envoi...';

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            btn.classList.replace('btn-apply', 'btn-success');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Postulé !';
        } else if (data.status === 'info') {
            alert(data.message);
            btn.innerHTML = 'Déjà postulé';
            btn.classList.add('btn-secondary');
        } else {
            alert(data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>


</body>
</html>