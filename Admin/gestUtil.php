<?php
// ============================================================
// 1. CONNEXION À LA BASE DE DONNÉES (PORT 3307)
// ============================================================
require_once '../Auth/config_db.php';

$message = "";
$messageType = "";

// ============================================================
// 2. SUPPRESSION D'UN UTILISATEUR
// ============================================================
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        // Grâce au "ON DELETE CASCADE" défini dans SQL,
        // Supprimer l'utilisateur supprimera aussi ses lignes correspondantes dans ETUDIANT ou ENTREPRISE.
        $stmt = $pdo->prepare("DELETE FROM UTILISATEUR WHERE id_user = ?");
        $stmt->execute([$delete_id]);
        
        $message = "Utilisateur supprimé avec succès !";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression : " . $e->getMessage();
        $messageType = "danger";
    }
}

// ============================================================
// 3. CRÉATION D'UN UTILISATEUR (Via Modal)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add'])) {
    $nom_complet = trim($_POST['nom_complet']);
    $email = trim($_POST['email']);
    $role = $_POST['role']; // admin (Enseignant), etudiant, entreprise

    if (!empty($nom_complet) && !empty($email)) {
        try {
            // Un mot de passe temporaire par défaut par sécurité : "Pass123"
            $password_temp = password_hash("Pass123", PASSWORD_BCRYPT);
            
            // Démarrer une transaction car on écrit potentiellement dans deux tables
            $pdo->beginTransaction();

            // Étape A : Insérer l'utilisateur de base
            $stmt = $pdo->prepare("INSERT INTO UTILISATEUR (email, password, role, nom_complet) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $password_temp, $role, $nom_complet]);
            
            // Récupérer l'ID généré
            $last_id = $pdo->lastInsertId();

            // Étape B : Insérer dans les tables de rôles correspondantes
            if ($role === 'etudiant') {
                $stmtEtud = $pdo->prepare("INSERT INTO ETUDIANT (id_user, ville) VALUES (?, 'Non spécifiée')");
                $stmtEtud->execute([$last_id]);
            } elseif ($role === 'entreprise') {
                $stmtEntr = $pdo->prepare("INSERT INTO ENTREPRISE (id_user, siege_social) VALUES (?, 'Non spécifié')");
                $stmtEntr->execute([$last_id]);
            }

            $pdo->commit();
            $message = "Nouvel utilisateur créé avec succès ! (Mot de passe temporaire : Pass123)";
            $messageType = "success";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "L'adresse email est déjà utilisée ou une erreur est survenue.";
            $messageType = "danger";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $messageType = "warning";
    }
}

// ============================================================
// 4. RÉCUPÉRATION DES STATISTIQUES DYNAMIQUES
// ============================================================
// Compter le nombre d'étudiants
$countEtud = $pdo->query("SELECT COUNT(*) FROM UTILISATEUR WHERE role = 'etudiant'")->fetchColumn();

// Compter le nombre d'entreprises
$countEntr = $pdo->query("SELECT COUNT(*) FROM UTILISATEUR WHERE role = 'entreprise'")->fetchColumn();

// Compter les candidatures en attente de traitement
$countPending = $pdo->query("SELECT COUNT(*) FROM CANDIDATURE WHERE statut_candidature = 'en_attente'")->fetchColumn();
// 4. MODIFICATION D'UN UTILISATEUR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_edit'])) {
    $stmt = $pdo->prepare("UPDATE UTILISATEUR SET email = ?, role = ?, nom_complet = ? WHERE id_user = ?");
    $stmt->execute([$_POST['email'], $_POST['role'], $_POST['nom_complet'], $_POST['id_user']]);
    $message = "Utilisateur mis à jour avec succès !";
    $messageType = "success";
}

// ============================================================
// 5. RÉCUPÉRATION DE TOUS LES UTILISATEURS
// ============================================================
$queryUsers = $pdo->query("SELECT id_user, email, role, nom_complet, created_at FROM UTILISATEUR WHERE email != 'admin@gestionstages.com' ORDER BY id_user DESC");
$utilisateurs = $queryUsers->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs | Admin Panel</title>

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

        /* PROFIL SECTION DANS SIDEBAR */
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

        /* STAT CARDS */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 20px;
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--primary); }

        /* TABLE STYLING */
        .card-table {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 20px;
            margin-top: 30px;
        }

        .table { color: #f8fafc; vertical-align: middle; margin-bottom: 0; }
        .table thead th {
            background: transparent;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.02); transition: 0.2s; }
        .table tbody tr:hover { background: rgba(255,255,255,0.02); }

        /* AVATAR */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            margin-right: 15px;
        }

        /* ACTIONS */
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: 0.3s;
            margin-left: 5px;
            text-decoration: none;
        }
        .btn-edit { background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }
        .btn-edit:hover { background: var(--accent-orange); color: white; }
        .btn-delete:hover { background: var(--accent-red); color: white; }

        /* MODAL PREMIUM */
        .modal-content {
            background: #111827;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            color: white;
        }
        .form-control, .form-select {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 12px;
            padding: 12px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.05);
            color: white;
            border-color: var(--primary);
            box-shadow: none;
        }

        .btn-primary-premium {
            background: var(--primary);
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 700;
            transition: 0.3s;
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
        <a href="gestUtil.php" class="nav-link active"><i class="fa-solid fa-users-gears"></i> Utilisateurs</a>
        <a href="validStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <a href="Config.php" class="nav-link"><i class="fa-solid fa-gears"></i> Configurations</a>
        <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>

<div class="main">
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show border-0 shadow-lg mb-4" role="alert" style="border-radius: 15px; background: rgba(30, 41, 59, 0.8); color: white;">
            <i class="fa-solid <?php echo ($messageType === 'success') ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-1">Gestion des Utilisateurs</h2>
            <p class="text-muted mb-0">Supervisez, éditez et gérez les accès de la plateforme.</p>
        </div>
        <button class="btn btn-primary-premium shadow-lg" data-bs-toggle="modal" data-bs-target="#addUser">
            <i class="fa fa-user-plus me-2"></i> Nouvel Utilisateur
        </button>
    </div>

    <div class="row g-4 mb-4" data-aos="fade-up">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-white small fw-bold text-uppercase mb-1">Étudiants</p>
                        <h3 class="fw-800 mb-0"><?php echo number_format($countEtud); ?> </h3>
                    </div>
                    <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-white small fw-bold text-uppercase mb-1">Entreprises</p>
                        <h3 class="fw-800 mb-0"><?php echo number_format($countEntr); ?></h3>
                    </div>
                    <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-white small fw-bold text-uppercase mb-1">Candidatures en attente</p>
                        <h3 class="fw-800 mb-0"><?php echo number_format($countPending); ?></h3>
                    </div>
                    <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-table shadow-lg" data-aos="fade-up" data-aos-delay="100">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Créé le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($utilisateurs) > 0): ?>
                        <?php foreach ($utilisateurs as $user): 
                            // Générer des initiales pour l'avatar
                            $words = explode(" ", $user['nom_complet']);
                            $initials = "";
                            foreach ($words as $w) {
                                $initials .= mb_substr($w, 0, 1);
                            }
                            $initials = strtoupper(mb_substr($initials, 0, 2));
                            
                            // Définir la couleur de fond de l'avatar selon le rôle
                            $avatarBg = "var(--primary)";
                            if ($user['role'] === 'entreprise') { $avatarBg = "var(--accent-orange)"; }
                            if ($user['role'] === 'admin') { $avatarBg = "var(--accent-green)"; }
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar" style="background: <?php echo $avatarBg; ?>;">
                                            <?php echo htmlspecialchars($initials); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['nom_complet']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-white bg-opacity-10 text-white border-0 px-2 small text-capitalize">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn-action btn-edit btn-edit-trigger" 
        data-id="<?php echo $user['id_user']; ?>" 
        data-nom="<?php echo htmlspecialchars($user['nom_complet']); ?>" 
        data-email="<?php echo htmlspecialchars($user['email']); ?>" 
        data-role="<?php echo $user['role']; ?>"
        title="Modifier">
    <i class="fa fa-pen"></i>
</button>
                                    <a href="gestUtil.php?delete_id=<?php echo $user['id_user']; ?>" 
                                       class="btn-action btn-delete" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');"
                                       title="Supprimer">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun utilisateur trouvé dans la base de données.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <form action="gestUtil.php" method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-800">Ajouter un compte</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action_add" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-white">Nom complet</label>
                        <input type="text" name="nom_complet" class="form-control" placeholder="ex: Marc Dupont" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-white">Adresse Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nom@exemple.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Type de compte (Rôle)</label>
                        <select name="role" class="form-select" style="color: white !important; background-color: #1e293b;" required>
    <option value="" disabled selected style="color: #94a3b8;">-- Choisir un rôle --</option>
    <option value="etudiant" style="color: white; background-color: #111827;">Étudiant</option>
    <option value="entreprise" style="color: white; background-color: #111827;">Entreprise</option>
    <option value="admin" style="color: white; background-color: #111827;">Enseignant</option>
</select>
                    </div>
                    <button type="submit" class="btn btn-primary-premium w-100 shadow">Créer l'utilisateur</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="gestUtil.php" method="POST">
                <input type="hidden" name="action_edit" value="1">
                <input type="hidden" name="id_user" id="edit_id">
                <div class="modal-header border-0"><h5 class="fw-800">Modifier utilisateur</h5></div>
                <div class="modal-body p-4">
                    <input type="text" name="nom_complet" id="edit_nom" class="form-control mb-3" required>
                    <input type="email" name="email" id="edit_email" class="form-control mb-3" required>
                    <select name="role" id="edit_role" class="form-select mb-3" style="color:white; background:#1e293b;">
                        <option value="etudiant">Étudiant</option>
                        <option value="entreprise">Entreprise</option>
                        <option value="admin">Enseignant</option>
                    </select>
                    <button type="submit" class="btn btn-primary-premium w-100">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script pour ouvrir le modal et remplir les champs
    document.querySelectorAll('.btn-edit-trigger').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('edit_id').value = button.dataset.id;
            document.getElementById('edit_nom').value = button.dataset.nom;
            document.getElementById('edit_email').value = button.dataset.email;
            document.getElementById('edit_role').value = button.dataset.role;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>

</body>
</html>