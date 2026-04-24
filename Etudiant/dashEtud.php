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

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-graduation-cap me-2 text-primary"></i> GESTION STAGES
    </div>
    
    <div class="px-4 mb-5 d-flex align-items-center">
        <div class="position-relative">
            <img src="https://ui-avatars.com/api/?name=Etudiant+User&background=3b82f6&color=fff" class="rounded-circle me-3" width="45">
            <span class="position-absolute bottom-0 end-0 badge border border-light rounded-circle bg-success p-1" style="transform: translate(-15px, 0);"><span class="visually-hidden">online</span></span>
        </div>
        <div>
            <div class="fw-bold" style="font-size: 0.85rem;">Espace Étudiant</div>
            <small class="text-muted" style="font-size: 0.7rem;">ID: 2026-042</small>
        </div>
    </div>

    <nav>
        <a href="dashEtud.php" class="nav-link active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a href="listeStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
        <a href="Candidature.php" class="nav-link"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
        <a href="MonStage.php" class="nav-link"><i class="fa-solid fa-laptop-code"></i> Mon stage</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="login.php" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5 fade-in">
        <div>
            <h2 class="fw-bold mb-1">Bienvenue 👋</h2>
            <p class="text-muted mb-0">Ravi de vous revoir ! Voici le point sur vos recherches.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark text-muted border border-secondary">Année académique 2025/2026</span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 fade-in">
            <div class="stat-card bg-blue shadow-sm">
                <small class="text-uppercase fw-600 opacity-75">Offres disponibles</small>
                <h2>15</h2>
                <i class="fa-solid fa-briefcase bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-arrow-up"></i> +4 cette semaine
                </div>
            </div>
        </div>

        <div class="col-md-4 fade-in delay-1">
            <div class="stat-card bg-green shadow-sm">
                <small class="text-uppercase fw-600 opacity-75">Mes candidatures</small>
                <h2>03</h2>
                <i class="fa-solid fa-paper-plane bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">
                    Dernière mise à jour : Hier
                </div>
            </div>
        </div>

        <div class="col-md-4 fade-in delay-2">
            <div class="stat-card bg-orange shadow-sm">
                <small class="text-uppercase fw-600 opacity-75">Stage en cours</small>
                <h2>01</h2>
                <i class="fa-solid fa-spinner bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">
                    Rapport dû dans 12 jours
                </div>
            </div>
        </div>
    </div>

    <div class="custom-table-card fade-in delay-2 shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Mes dernières candidatures</h5>
            <button class="btn btn-sm btn-outline-secondary">Voir tout</button>
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
                    <tr>
                        <td><span class="text-muted">#01</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded p-1 me-2" style="width: 30px; height: 30px;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c8/Orange_logo.svg" width="100%">
                                </div>
                                <span class="fw-bold">Orange Cameroun</span>
                            </div>
                        </td>
                        <td>Développeur Web</td>
                        <td>10 Avril 2026</td>
                        <td><span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass-half me-1"></i> En attente</span></td>
                        <td class="text-end"><a href="#" class="btn btn-sm btn-dark border-secondary"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>

                    <tr>
                        <td><span class="text-muted">#02</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-warning rounded p-1 me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <span class="text-dark fw-bold" style="font-size: 0.6rem;">MTN</span>
                                </div>
                                <span class="fw-bold">MTN Foundation</span>
                            </div>
                        </td>
                        <td>Ingénieur Réseau</td>
                        <td>08 Avril 2026</td>
                        <td><span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> Accepté</span></td>
                        <td class="text-end"><a href="#" class="btn btn-sm btn-dark border-secondary"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>

                    <tr>
                        <td><span class="text-muted">#03</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-info rounded p-1 me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <i class="fa-solid fa-building text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                <span class="fw-bold">Camtel</span>
                            </div>
                        </td>
                        <td>Technicien Maintenance</td>
                        <td>05 Avril 2026</td>
                        <td><span class="badge bg-danger"><i class="fa-solid fa-times-circle me-1"></i> Refusé</span></td>
                        <td class="text-end"><a href="#" class="btn btn-sm btn-dark border-secondary"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="mt-5 text-center text-muted" style="font-size: 0.75rem;">
        <p>&copy; 2026 Gestion des Stages - Université de Technologie. Tous droits réservés.</p>
    </footer>
</div>

</body>
</html>