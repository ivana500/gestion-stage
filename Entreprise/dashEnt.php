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
        <a href="#" class="nav-link"><i class="fa-solid fa-gear"></i> Paramètres</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="#" class="nav-link text-danger"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Bienvenue Entreprise 👋</h2>
            <p class="text-muted mb-0">Voici l'état actuel de vos recrutements de stagiaires.</p>
        </div>
        <button class="btn-publish shadow">
            <i class="fa-solid fa-paper-plane me-2"></i> Publier une nouvelle offre
        </button>
    </div>

    <div class="row g-4 mb-2">
        <div class="col-md-4">
            <div class="stat-card bg-gradient-blue shadow">
                <small class="text-uppercase fw-600 opacity-75">Offres publiées</small>
                <h2>12</h2>
                <i class="fa-solid fa-briefcase bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">3 offres actives cette semaine</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card bg-gradient-purple shadow">
                <small class="text-uppercase fw-600 opacity-75">Candidatures reçues</small>
                <h2>34</h2>
                <i class="fa-solid fa-user-tie bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">+12 nouveaux profils à consulter</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card bg-gradient-orange shadow">
                <small class="text-uppercase fw-600 opacity-75">Stages en cours</small>
                <h2>05</h2>
                <i class="fa-solid fa-user-graduate bg-icon"></i>
                <div class="mt-2" style="font-size: 0.8rem;">Suivi de l'évolution des stagiaires</div>
            </div>
        </div>
    </div>

    <div class="custom-table-card shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold">Dernières candidatures reçues</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-dark border-secondary text-muted" type="button">Filtrer par offre</button>
            </div>
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
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.8rem;">JP</div>
                                <span class="fw-bold">Jean Paul</span>
                            </div>
                        </td>
                        <td><span class="badge bg-dark border border-secondary text-white">Développeur Web</span></td>
                        <td>20/04/2026</td>
                        <td><span class="badge badge-status bg-warning text-dark">En attente</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info me-1"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.8rem;">AN</div>
                                <span class="fw-bold">Alice N.</span>
                            </div>
                        </td>
                        <td><span class="badge bg-dark border border-secondary text-white">Data Analyst</span></td>
                        <td>21/04/2026</td>
                        <td><span class="badge badge-status bg-success">Acceptée</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info me-1"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn btn-sm btn-outline-secondary" disabled><i class="fa-solid fa-check"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>