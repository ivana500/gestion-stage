<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures | STAGES HELLO</title>

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
            --accent-orange: #f59e0b;
            --accent-red: #ef4444;
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
        }

        .sidebar a {
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

        .sidebar a i { margin-right: 15px; width: 20px; text-align: center; }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }

        .sidebar a.active { 
            background: var(--primary); 
            color: white; 
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2); 
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.03), transparent);
        }

        /* STATS CARDS QUICK VIEW */
        .stat-mini-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 15px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* CARD CANDIDATURE MODERNE */
        .card-candidature {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 15px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-candidature:hover {
            transform: translateY(-5px) scale(1.01);
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .company-logo-placeholder {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
        }

        /* BADGES DE STATUT */
        .badge-status {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-attente { background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); }
        .status-accepte { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .status-refuse { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); }

        .status-attente i { animation: blink 1.5s infinite; }

        @keyframes blink { 50% { opacity: 0.5; } }

        .btn-view {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 18px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-view:hover {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="sidebar-brand">
        <i class="fa-solid fa-rocket"></i> STAGES HELLO
    </a>
    <a href="dashEtud.php"><i class="fa-solid fa-grip-vertical"></i> Dashboard</a>
    <a href="listeStage.php"><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
    <a href="Candidature.php" class="active"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
    <a href="MonStage.php"><i class="fa-solid fa-file-arrow-up"></i> Dépôt de rapport</a>
</div>

<div class="main">
    
    <div class="mb-5" data-aos="fade-down">
        <h2 class="fw-800">Suivi de mes candidatures</h2>
        <p class="text-muted">Gérez vos demandes et surveillez les retours des recruteurs.</p>
    </div>

    <div class="row g-3 mb-5" data-aos="fade-up">
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="text-primary"><i class="fa-solid fa-paper-plane fa-lg"></i></div>
                <div><h6 class="mb-0 fw-800">03</h6><small class="text-muted">Total</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-mini-card">
                <div class="text-warning"><i class="fa-solid fa-clock fa-lg"></i></div>
                <div><h6 class="mb-0 fw-800">01</h6><small class="text-muted">En attente</small></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-10">
            
            <div class="card-candidature" data-aos="fade-right" data-aos-delay="100">
                <div class="d-flex align-items-center flex-wrap gap-4">
                    <div class="company-logo-placeholder">TS</div>
                    
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Développeur Web Full Stack</h5>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i> Tech Solutions • Douala</p>
                    </div>

                    <div>
                        <span class="badge-status status-attente"><i class="fa-solid fa-circle text-warning small"></i> En attente</span>
                    </div>

                    <div class="text-end" style="min-width: 120px;">
                        <small class="text-muted d-block">Postulé le</small>
                        <span class="fw-600">20 Avril 2026</span>
                    </div>

                    <button class="btn-view"><i class="fa-solid fa-eye me-2"></i>Détails</button>
                </div>
            </div>

            <div class="card-candidature" data-aos="fade-right" data-aos-delay="200">
                <div class="d-flex align-items-center flex-wrap gap-4">
                    <div class="company-logo-placeholder" style="color:var(--accent-green)">DC</div>
                    
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Data Analyst</h5>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i> Data Corp • Yaoundé</p>
                    </div>

                    <div>
                        <span class="badge-status status-accepte"><i class="fa-solid fa-circle text-success small"></i> Acceptée</span>
                    </div>

                    <div class="text-end" style="min-width: 120px;">
                        <small class="text-muted d-block">Postulé le</small>
                        <span class="fw-600">18 Avril 2026</span>
                    </div>

                    <button class="btn-view"><i class="fa-solid fa-eye me-2"></i>Détails</button>
                </div>
            </div>

            <div class="card-candidature" data-aos="fade-right" data-aos-delay="300">
                <div class="d-flex align-items-center flex-wrap gap-4">
                    <div class="company-logo-placeholder" style="color:var(--accent-red)">CS</div>
                    
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Designer UI/UX</h5>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i> Creative Studio • Remote</p>
                    </div>

                    <div>
                        <span class="badge-status status-refuse"><i class="fa-solid fa-circle text-danger small"></i> Refusée</span>
                    </div>

                    <div class="text-end" style="min-width: 120px;">
                        <small class="text-muted d-block">Postulé le</small>
                        <span class="fw-600">15 Avril 2026</span>
                    </div>

                    <button class="btn-view"><i class="fa-solid fa-eye me-2"></i>Détails</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>

</body>
</html>