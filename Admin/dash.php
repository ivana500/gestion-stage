<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stages - Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        /* SIDEBAR UNIFIÉE */
        .sidebar {
            height: 100vh;
            background-color: var(--sidebar-dark);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            position: fixed;
            width: 280px;
            padding: 30px 20px;
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
            color: var(--text-muted);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: 0.3s;
            font-weight: 500;
        }

        .nav-link i { margin-right: 15px; width: 20px; text-align: center; }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
        }

        .nav-link.active {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            padding: 40px;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
        }

        /* CARDS STATS */
        .stat-card {
            border: none;
            border-radius: 20px;
            padding: 25px;
            transition: transform 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h2 { font-weight: 800; margin: 10px 0; letter-spacing: -1px; }
        .stat-card .icon-box { font-size: 1.8rem; opacity: 0.3; }
        
        .footer-link { 
            font-size: 0.75rem; 
            text-decoration: none; 
            color: rgba(255,255,255,0.8); 
            display: flex; 
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 12px;
            font-weight: 600;
        }

        /* CHART CARDS */
        .chart-container {
            background: var(--card-dark);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 25px;
            height: 100%;
        }

        /* RECENT ACTIVITIES */
        .activity-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            align-items: center;
        }
        .activity-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .bg-custom-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .bg-custom-green { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-custom-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .bg-custom-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .bg-custom-red { background: linear-gradient(135deg, #ef4444, #dc2626); }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-shield-halved me-2"></i> ADMIN PANEL
    </div>
    
    <div class="px-3 mb-5 d-flex align-items-center">
        <div class="bg-primary rounded-3 p-2 me-3">
            <i class="fa-solid fa-user-tie text-white"></i>
        </div>
        <div>
            <div class="fw-bold small">Admin Principal</div>
            <small class="text-success" style="font-size: 0.7rem;"><i class="fa-solid fa-circle fa-2xs me-1"></i> Session Active</small>
        </div>
    </div>

    <nav>
        <a href="dash.php" class="nav-link active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="gestUtil.php" class="nav-link"><i class="fa-solid fa-users-gears"></i> Utilisateurs</a>
        <a href="validStage.php" class="nav-link"><i class="fa-solid fa-briefcase"></i> Toutes les offres</a>
        <a href="Config.php" class="nav-link"><i class="fa-solid fa-gears"></i> Configurations</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="fw-800 mb-1">Vue d'ensemble</h2>
            <p class="text-muted mb-0">Statistiques globales de la plateforme de stages.</p>
        </div>
        <div class="text-end pb-1">
            <span class="badge bg-white bg-opacity-10 px-3 py-2 rounded-pill">Avril 2026</span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md">
            <div class="stat-card bg-custom-blue text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">UTILISATEURS</small><h2>156</h2></div>
                    <i class="fa-solid fa-users icon-box"></i>
                </div>
                <a href="#" class="footer-link">Gérer les comptes <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-green text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">ÉTUDIANTS</small><h2>87</h2></div>
                    <i class="fa-solid fa-user-graduate icon-box"></i>
                </div>
                <a href="#" class="footer-link">Liste complète <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-purple text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">ENTREPRISES</small><h2>42</h2></div>
                    <i class="fa-solid fa-building icon-box"></i>
                </div>
                <a href="#" class="footer-link">Partenariats <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-orange text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">OFFRES</small><h2>64</h2></div>
                    <i class="fa-solid fa-briefcase icon-box"></i>
                </div>
                <a href="#" class="footer-link">Modérer les offres <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="col-md">
            <div class="stat-card bg-custom-red text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div><small class="fw-bold opacity-75">CANDIDATURES</small><h2>123</h2></div>
                    <i class="fa-solid fa-file-lines icon-box"></i>
                </div>
                <a href="#" class="footer-link">Suivi des dossiers <i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="chart-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-line me-2 text-primary"></i> Analyse des flux</h6>
                    <select class="form-select form-select-sm bg-dark text-white border-0 opacity-75" style="width:130px">
                        <option>Année 2026</option>
                    </select>
                </div>
                <canvas id="evolutionChart" height="280"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container shadow-sm">
                <h6 class="fw-bold mb-4"><i class="fa-solid fa-bolt-lightning me-2 text-warning"></i> Flux en temps réel</h6>
                <div class="mt-2">
                    <div class="activity-item">
                        <div class="activity-icon bg-custom-blue text-white"><i class="fa-solid fa-plus fa-xs"></i></div>
                        <div>
                            <div class="fw-bold">Offre TechSoft</div>
                            <small class="text-muted">Nouvelle publication • 5 min</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-custom-purple text-white"><i class="fa-solid fa-paper-plane fa-xs"></i></div>
                        <div>
                            <div class="fw-bold">Jean Dupont</div>
                            <small class="text-muted">Candidature envoyée • 15 min</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon bg-custom-green text-white"><i class="fa-solid fa-check fa-xs"></i></div>
                        <div>
                            <div class="fw-bold">Dossier Validé</div>
                            <small class="text-muted">Marie Claire • 1 h</small>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-sm w-100 mt-4 border-opacity-25 rounded-3">Historique complet</button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4"><div class="chart-container"><h6>Statuts des Stages</h6><canvas id="statDonut"></canvas></div></div>
        <div class="col-md-4"><div class="chart-container"><h6>Candidatures / Mois</h6><canvas id="barChart"></canvas></div></div>
        <div class="col-md-4"><div class="chart-container"><h6>Population Active</h6><canvas id="typeDonut"></canvas></div></div>
    </div>
</div>

<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
Chart.defaults.font.family = 'Plus Jakarta Sans';

// Graphique Évolution
new Chart(document.getElementById('evolutionChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [
            { label: 'Offres', data: [15, 12, 25, 20, 28, 22, 30, 25, 35, 45, 40, 42], borderColor: '#3b82f6', tension: 0.4, fill: true, backgroundColor: 'rgba(59, 130, 246, 0.05)' },
            { label: 'Candidatures', data: [10, 25, 20, 35, 30, 40, 35, 30, 40, 50, 45, 48], borderColor: '#10b981', tension: 0.4 }
        ]
    },
    options: { plugins: { legend: { display: true, position: 'bottom' } } }
});

// Donut Statut
new Chart(document.getElementById('statDonut'), {
    type: 'doughnut',
    data: {
        labels: ['En cours', 'Terminés', 'Attente'],
        datasets: [{ data: [45, 30, 25], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'], borderWidth: 0 }]
    },
    options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } }
});

// Bar Chart
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin'],
        datasets: [{ label: 'Demandes', data: [15, 22, 18, 25, 20, 23], backgroundColor: '#3b82f6', borderRadius: 6 }]
    }
});

// Répartition
new Chart(document.getElementById('typeDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Étudiants', 'Entreprises', 'Encadreurs'],
        datasets: [{ data: [60, 25, 15], backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6'], borderWidth: 0 }]
    },
    options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } }
});
</script>

</body>
</html>