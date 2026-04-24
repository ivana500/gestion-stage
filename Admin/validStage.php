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
        }
        .btn-validate { background: var(--accent-green); color: white; }
        .btn-view { background: rgba(59, 130, 246, 0.1); color: var(--primary); }
        
        .btn-validate:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); }
        .btn-view:hover { background: var(--primary); color: white; }

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
        <a href="validStage.php" class="nav-link active"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <a href="Config.php" class="nav-link"><i class="fa-solid fa-gears"></i> Configurations</a>
    </nav>
</div>

<div class="main">
    
    <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-down">
        <div>
            <h2 class="fw-800 mb-1">Gestion des Offres</h2>
            <p class="text-muted mb-0">Contrôle des publications, validations des stages et rapports.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 rounded-pill">Total: 248 offres</span>
        </div>
    </div>

    <div class="content-section shadow-lg" data-aos="fade-up">
        <div class="section-header">
            <h5 class="fw-700 mb-0"><i class="fa-solid fa-layer-group me-2 text-primary"></i> Offres publiées</h5>
            <div class="search-box">
                <input type="text" class="form-control form-control-sm bg-dark border-0 text-white" placeholder="Rechercher une offre...">
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
                    <tr>
                        <td class="fw-600">Développeur Web Fullstack</td>
                        <td>
                            <div class="company-tag">
                                <div class="company-logo text-primary">TS</div>
                                <span>Tech Solutions</span>
                            </div>
                        </td>
                        <td><i class="fa-solid fa-location-dot me-1 text-muted"></i> Douala</td>
                        <td class="text-muted">30 Mai 2026</td>
                        <td><span class="status-pill bg-active">Active</span></td>
                    </tr>
                    <tr>
                        <td class="fw-600">Data Analyst Junior</td>
                        <td>
                            <div class="company-tag">
                                <div class="company-logo text-success">DC</div>
                                <span>Data Corp</span>
                            </div>
                        </td>
                        <td><i class="fa-solid fa-location-dot me-1 text-muted"></i> Yaoundé</td>
                        <td class="text-muted">15 Mai 2026</td>
                        <td><span class="status-pill bg-expired">Expirée</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
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
                            <tr>
                                <td>
                                    <div class="fw-bold">Jean Paul</div>
                                    <div class="small text-muted">Génie Logiciel</div>
                                </td>
                                <td class="small">Tech Solutions</td>
                                <td class="text-end">
                                    <button class="action-btn btn-validate shadow-sm">
                                        <i class="fa fa-check"></i> Valider
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="content-section shadow-lg h-100" data-aos="fade-left" data-aos-delay="200">
                <div class="section-header">
                    <h5 class="fw-700 mb-0"><i class="fa-solid fa-file-export me-2 text-warning"></i> Derniers Rapports</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Auteur</th>
                                <th class="text-end">Fichier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-bold">Alice N.</div>
                                    <div class="small text-muted">Déposé le 10/06</div>
                                </td>
                                <td class="text-end">
                                    <button class="action-btn btn-view">
                                        <i class="fa fa-eye"></i> Consulter
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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