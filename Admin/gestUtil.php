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

        /* SIDEBAR UNIFIÉE (Identique au Dashboard) */
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
    </nav>
</div>

<div class="main">
    
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
                        <p class="text-muted small fw-bold text-uppercase mb-1">Étudiants</p>
                        <h3 class="fw-800 mb-0">1,284</h3>
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
                        <p class="text-muted small fw-bold text-uppercase mb-1">Entreprises</p>
                        <h3 class="fw-800 mb-0">156</h3>
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
                        <p class="text-muted small fw-bold text-uppercase mb-1">En attente</p>
                        <h3 class="fw-800 mb-0">12</h3>
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
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar">JP</div>
                                <div>
                                    <div class="fw-bold">Jean Paul</div>
                                    <div class="small text-muted">jean@mail.com</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-white bg-opacity-10 text-white border-0 px-2 small">Étudiant</span></td>
                        <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 small">Actif</span></td>
                        <td class="text-muted small">Il y a 2 heures</td>
                        <td class="text-end">
                            <button class="btn-action btn-edit"><i class="fa fa-pen"></i></button>
                            <button class="btn-action btn-delete"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar" style="background: var(--accent-orange);">EX</div>
                                <div>
                                    <div class="fw-bold">Entreprise X</div>
                                    <div class="small text-muted">contact@entreprise.com</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-white bg-opacity-10 text-white border-0 px-2 small">Entreprise</span></td>
                        <td><span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3 small">Inactif</span></td>
                        <td class="text-muted small">Il y a 5 jours</td>
                        <td class="text-end">
                            <button class="btn-action btn-edit"><i class="fa fa-pen"></i></button>
                            <button class="btn-action btn-delete"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-800">Ajouter un compte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Nom complet</label>
                    <input type="text" class="form-control" placeholder="ex: Marc Dupont">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Adresse Email</label>
                    <input type="email" class="form-control" placeholder="nom@exemple.com">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Type de compte</label>
                    <select class="form-select">
                        <option>Étudiant</option>
                        <option>Entreprise</option>
                        <option>Administrateur</option>
                    </select>
                </div>
                <button class="btn btn-primary-premium w-100 shadow">Créer l'utilisateur</button>
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