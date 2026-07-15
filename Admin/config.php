<?php
// 1. DÉMARRAGE DE LA SESSION (Indispensable pour lire $_SESSION !)
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Vérifier si l'utilisateur est STRICTEMENT l'administrateur principal
if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['erreur_access'] = "Accès refusé : vous devez être Administrateur Principal pour modifier les configurations.";
    header("Location: dash.php");
    exit();
}

// Génération du token CSRF pour sécuriser le formulaire POST
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// 1. CONNEXION À LA BASE DE DONNÉES (PORT 3307)
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion_stages;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";
$messageType = "";

// ============================================================
// 2. INITIALISATION OU RÉCUPÉRATION DES CONFIGURATIONS
// ============================================================
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS CONFIGURATION (
        cle VARCHAR(50) PRIMARY KEY,
        valeur TEXT
    )");

    // Insertion des valeurs par défaut si la table est vide
    $defaults = [
        'annee_scolaire' => '2025-2026',
        'limite_candidatures' => '5',
        'autoriser_inscriptions' => '1',
        'taille_max_pdf' => '5' // en Mo
    ];

    foreach ($defaults as $cle => $valeur) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM CONFIGURATION WHERE cle = ?");
        $stmtCheck->execute([$cle]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO CONFIGURATION (cle, valeur) VALUES (?, ?)");
            $stmtInsert->execute([$cle, $valeur]);
        }
    }
} catch (PDOException $e) {
    $message = "Erreur d'initialisation de la configuration : " . $e->getMessage();
    $messageType = "danger";
}

// ============================================================
// 3. LOGIQUE DE MISE À JOUR DES CONFIGURATIONS (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    // Vérification stricte du jeton CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Erreur de sécurité : Jeton CSRF invalide.";
        $messageType = "danger";
    } else {
        try {
            $annee = trim($_POST['annee_scolaire']);
            $limite = intval($_POST['limite_candidatures']);
            $inscriptions = isset($_POST['autoriser_inscriptions']) ? '1' : '0';
            $tailleMax = intval($_POST['taille_max_pdf']);

            $stmtUpdate = $pdo->prepare("UPDATE CONFIGURATION SET valeur = ? WHERE cle = ?");
            
            $stmtUpdate->execute([$annee, 'annee_scolaire']);
            $stmtUpdate->execute([$limite, 'limite_candidatures']);
            $stmtUpdate->execute([$inscriptions, 'autoriser_inscriptions']);
            $stmtUpdate->execute([$tailleMax, 'taille_max_pdf']);

            $message = "Les configurations système ont été mises à jour avec succès !";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// ============================================================
// 4. LECTURE DES CONFIGURATIONS DEPUIS LA BASE
// ============================================================
$config = [];
try {
    $stmt = $pdo->query("SELECT * FROM CONFIGURATION");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $config[$row['cle']] = $row['valeur'];
    }
} catch (PDOException $e) {
    // Fallback si erreur de lecture
    $config = [
        'annee_scolaire' => '2025-2026',
        'limite_candidatures' => '5',
        'autoriser_inscriptions' => '1',
        'taille_max_pdf' => '5'
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Système | Admin Panel</title>

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
            font-size: 0.9rem;
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
            padding: 30px;
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
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /* FORMULAIRES COHÉRENTS */
        .form-label {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 12px 16px;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(15, 23, 42, 0.8);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            color: white;
        }

        /* SWITCH BOOTSTRAP PERSONNALISÉ */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: background-position .15s ease-in-out, background-color .15s;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--accent-green);
            border-color: var(--accent-green);
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            border: none;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            background: #2563eb;
        }

        .icon-container {
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
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
            <div class="fw-bold small">Admin Principal</div>
            <small class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-circle fa-2xs me-1"></i> Session Active</small>
        </div>
    </div>

    <nav>
        <a href="dash.php" class="nav-link"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="gestUtil.php" class="nav-link"><i class="fa-solid fa-users-gears"></i> Utilisateurs</a>
        <a href="validStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <a href="Config.php" class="nav-link active"><i class="fa-solid fa-gears"></i> Configurations</a>
        <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>

<div class="main">
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show border-0 shadow-lg mb-4" role="alert" style="border-radius: 15px; background: rgba(30, 41, 59, 0.8); color: white;">
            <?php if($messageType === 'success'): ?>
                <i class="fa-solid fa-circle-check text-success me-2"></i>
            <?php else: ?>
                <i class="fa-solid fa-circle-exclamation text-danger me-2"></i>
            <?php endif; ?>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-1">Configuration Système</h2>
            <p class="text-muted mb-0">Modifiez les variables globales et les paramètres de sécurité de l'application.</p>
        </div>
        <div class="text-end pb-1">
            <span class="badge bg-white bg-opacity-10 px-3 py-2 rounded-pill"><?php echo date('F Y'); ?></span>
        </div>
    </div>

    <div class="content-section shadow-lg" data-aos="fade-up">
        <div class="section-header">
            <h5 class="fw-700 mb-0 d-flex align-items-center gap-3">
                <div class="icon-container">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                Paramètres globaux de la plateforme
            </h5>
        </div>

        <form action="Config.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="row g-4">
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="annee_scolaire">Année Universitaire en cours</label>
                        <input type="text" class="form-control" id="annee_scolaire" name="annee_scolaire" 
                               value="<?php echo htmlspecialchars($config['annee_scolaire']); ?>" required placeholder="Ex: 2025-2026">
                        <small class="text-muted">Définit la session académique par défaut pour les stages et conventions.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="limite_candidatures">Nombre max de candidatures en cours par étudiant</label>
                        <input type="number" class="form-control" id="limite_candidatures" name="limite_candidatures" 
                               value="<?php echo htmlspecialchars($config['limite_candidatures']); ?>" min="1" max="20" required>
                        <small class="text-muted">Limite le spam d'offres par un seul candidat.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="taille_max_pdf">Taille limite des fichiers téléversés (Mo)</label>
                        <select class="form-select" id="taille_max_pdf" name="taille_max_pdf">
                            <option value="2" <?php echo ($config['taille_max_pdf'] == '2') ? 'selected' : ''; ?>>2 Mo</option>
                            <option value="5" <?php echo ($config['taille_max_pdf'] == '5') ? 'selected' : ''; ?>>5 Mo (Recommandé)</option>
                            <option value="10" <?php echo ($config['taille_max_pdf'] == '10') ? 'selected' : ''; ?>>10 Mo</option>
                            <option value="20" <?php echo ($config['taille_max_pdf'] == '20') ? 'selected' : ''; ?>>20 Mo</option>
                        </select>
                        <small class="text-muted">S'applique aux conventions, CV et rapports de stage.</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3 h-100 d-flex flex-column justify-content-center">
                        <div class="form-check form-switch d-flex align-items-center gap-3 ps-0">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="autoriser_inscriptions" 
                                   name="autoriser_inscriptions" <?php echo ($config['autoriser_inscriptions'] == '1') ? 'checked' : ''; ?>>
                            <div>
                                <label class="form-check-label fw-600 text-white" for="autoriser_inscriptions">Inscriptions publiques ouvertes</label>
                                <div class="small text-muted" style="margin-top: 2px;">Désactivez pour geler la création de nouveaux comptes étudiants/entreprises.</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-5 text-end">
                <button type="submit" name="save_config" class="btn-submit">
                    <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <div class="content-section border-danger border-opacity-10 shadow-lg" data-aos="fade-up" data-aos-delay="100">
        <div class="section-header border-bottom border-danger border-opacity-10 mb-4 pb-2">
            <h5 class="fw-700 text-danger mb-0 d-flex align-items-center gap-3">
                <div class="icon-container" style="background: rgba(239, 68, 68, 0.1); color: var(--accent-red);">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                Zone de maintenance (Actions irréversibles)
            </h5>
        </div>
        <p class="text-muted">Ces options touchent directement à l'intégrité de la base de données applicative.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
            <button class="btn btn-outline-danger btn-sm p-3 rounded-4 px-4 fw-600" onclick="alert('Fonctionnalité en cours de déploiement (sécurisée).');">
                <i class="fa-solid fa-broom me-2"></i> Nettoyer le cache des rapports et PDFs
            </button>
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