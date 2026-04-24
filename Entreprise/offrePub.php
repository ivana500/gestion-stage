<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes offres publiées | Espace Entreprise</title>

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

        /* HEADER BOX */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 20px;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--accent-blue), #2563eb);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* TABLE CARD */
        .card-table {
            background: var(--card-dark);
            border-radius: 18px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .table { color: #e2e8f0; vertical-align: middle; margin-bottom: 0; }
        .table thead th { 
            background: rgba(0,0,0,0.2); 
            color: var(--text-muted); 
            border: none; 
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 18px 15px;
        }

        .table tbody tr { transition: 0.2s; border-color: rgba(255,255,255,0.05); }
        .table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .table td { padding: 18px 15px; }

        /* BADGES & BUTTONS */
        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 8px;
            margin-left: 5px;
            transition: 0.3s;
            background: #2d3248;
            color: white;
        }

        .btn-view:hover { background: var(--accent-blue); }
        .btn-edit:hover { background: #f59e0b; color: black; }
        .btn-delete:hover { background: #ef4444; }

        .text-info-small { font-size: 0.8rem; color: var(--text-muted); }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-building me-2"></i> TECH SOLUTIONS
    </div>
    
    <nav>
         <a href="dashEnt.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="pubOffre.php" class="nav-link "><i class="fa-solid fa-plus-circle"></i> Publier une offre</a>
        <a href="OffrePub.php" class="nav-link active"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="#" class="nav-link text-danger"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    </nav>
</div>

<div class="main">

    <div class="header-box">
        <div>
            <h2 class="fw-bold mb-1">Mes offres publiées</h2>
            <p class="text-muted mb-0">Total : <span class="text-white fw-bold">12 offres</span> enregistrées</p>
        </div>

        <button class="btn-add">
            <i class="fa-solid fa-plus me-2"></i> Nouvelle offre
        </button>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Offre & Lieu</th>
                        <th>Type</th>
                        <th>Durée</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td>
                            <div class="fw-bold">Développeur Web Full Stack</div>
                            <div class="text-info-small"><i class="fa-solid fa-location-dot me-1"></i> Douala, Akwa</div>
                        </td>
                        <td><span class="badge bg-dark border border-secondary">Stage pro</span></td>
                        <td>3 mois</td>
                        <td>
                            <div class="fw-600">30/05/2026</div>
                            <div class="text-info-small">Dans 1 mois</div>
                        </td>
                        <td><span class="badge-status bg-success"><i class="fa-solid fa-check-circle me-1"></i> Active</span></td>
                        <td class="text-end">
                            <button class="btn-action btn-view" title="Voir les candidats"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action btn-edit" title="Modifier"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action btn-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="fw-bold">Data Analyst Junior</div>
                            <div class="text-info-small"><i class="fa-solid fa-location-dot me-1"></i> Yaoundé, Centre</div>
                        </td>
                        <td><span class="badge bg-dark border border-secondary">Stage académique</span></td>
                        <td>2 mois</td>
                        <td>
                            <div class="fw-600">15/05/2026</div>
                            <div class="text-info-small text-danger">Expiré</div>
                        </td>
                        <td><span class="badge-status bg-danger text-white"><i class="fa-solid fa-clock me-1"></i> Terminée</span></td>
                        <td class="text-end">
                            <button class="btn-action btn-view" title="Voir les candidats"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action btn-edit" title="Modifier"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action btn-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="fw-bold">Assistant RH</div>
                            <div class="text-info-small"><i class="fa-solid fa-location-dot me-1"></i> Douala, Bonanjo</div>
                        </td>
                        <td><span class="badge bg-dark border border-secondary">Stage pro</span></td>
                        <td>6 mois</td>
                        <td>
                            <div class="fw-600">10/06/2026</div>
                            <div class="text-info-small">Bientôt</div>
                        </td>
                        <td><span class="badge-status bg-warning text-dark"><i class="fa-solid fa-pause-circle me-1"></i> Brouillon</span></td>
                        <td class="text-end">
                            <button class="btn-action btn-view" title="Voir les candidats"><i class="fa-solid fa-eye"></i></button>
                            <button class="btn-action btn-edit" title="Modifier"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn-action btn-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            <li class="page-item disabled"><a class="page-link bg-dark border-secondary text-muted" href="#">Précédent</a></li>
            <li class="page-item active"><a class="page-link bg-primary border-primary" href="#">1</a></li>
            <li class="page-item"><a class="page-link bg-dark border-secondary text-white" href="#">2</a></li>
            <li class="page-item"><a class="page-link bg-dark border-secondary text-white" href="#">Suivant</a></li>
        </ul>
    </nav>

</div>

</body>
</html>