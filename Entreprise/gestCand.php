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
        
        .btn-circle:disabled { opacity: 0.2; transform: none; }

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
        <a href="OffrePub.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link active"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="#" class="nav-link text-danger"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
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

                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar">JP</div>
                                <div>
                                    <div class="candidate-name">Jean Paul</div>
                                    <div class="candidate-sub">j.paul@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-dark border border-secondary fw-normal">Développeur Web</span>
                        </td>
                        <td>
                            <div class="fw-500">20/04/2026</div>
                            <div class="candidate-sub">Il y a 4 jours</div>
                        </td>
                        <td>
                            <div class="status-pill" style="color: var(--warning);">
                                <div class="dot" style="background: var(--warning);"></div> En attente
                            </div>
                        </td>
                        <td>
                            <div class="btn-action-group justify-content-end">
                                <button class="btn-circle btn-view" title="Consulter le profil"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn-circle btn-accept" title="Accepter"><i class="fa-solid fa-check"></i></button>
                                <button class="btn-circle btn-reject" title="Refuser"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar" style="background: linear-gradient(135deg, #10b981, #059669);">AN</div>
                                <div>
                                    <div class="candidate-name">Alice N.</div>
                                    <div class="candidate-sub">a.ngono@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-dark border border-secondary fw-normal">Data Analyst</span>
                        </td>
                        <td>
                            <div class="fw-500">21/04/2026</div>
                            <div class="candidate-sub">Il y a 3 jours</div>
                        </td>
                        <td>
                            <div class="status-pill" style="color: var(--success);">
                                <div class="dot" style="background: var(--success);"></div> Acceptée
                            </div>
                        </td>
                        <td>
                            <div class="btn-action-group justify-content-end">
                                <button class="btn-circle btn-view" title="Consulter le profil"><i class="fa-solid fa-eye"></i></button>
                                <button class="btn-circle btn-accept" disabled><i class="fa-solid fa-check"></i></button>
                                <button class="btn-circle btn-reject" title="Refuser"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 p-3 rounded-4 d-flex align-items-center" style="background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.2);">
        <i class="fa-solid fa-lightbulb text-primary me-3 fs-5"></i>
        <span class="text-muted small"><strong>Conseil :</strong> Les candidatures en attente depuis plus de 7 jours sont marquées d'une alerte pour garantir une bonne expérience candidat.</span>
    </div>

</div>

</body>
</html>