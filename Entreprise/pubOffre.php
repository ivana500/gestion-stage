<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une offre | Espace Entreprise</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #1a1d2d;
            --sidebar-dark: #111422;
            --card-dark: #23273a;
            --input-bg: #1f2333;
            --text-muted: #8a8d9a;
            --accent: #3b82f6;
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
        }

        .sidebar-brand {
            padding: 0 25px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
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
            border-left: 4px solid var(--accent);
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
        }

        .header-section {
            margin-bottom: 35px;
            border-left: 5px solid var(--accent);
            padding-left: 20px;
        }

        /* FORM CARD */
        .card-form {
            background: var(--card-dark);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .form-label {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .form-control, .form-select {
            background: var(--input-bg);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            padding: 12px 15px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            background: #252a3d;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            color: white;
        }

        .form-control::placeholder {
            color: #555a70;
        }

        /* BUTTONS */
        .btn-publish {
            background: linear-gradient(135deg, var(--accent), #2563eb);
            color: white;
            padding: 12px 35px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-publish:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .btn-cancel {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 12px 25px;
            border-radius: 10px;
            margin-right: 10px;
            transition: 0.3s;
        }

        .btn-cancel:hover {
            background: rgba(255,0,0,0.1);
            color: #ff4d4d;
            border-color: #ff4d4d;
        }

        /* PROGRESS ICON */
        .step-icon {
            width: 40px;
            height: 40px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-building me-2"></i> TECH SOLUTIONS
    </div>
    
    <nav>
        <a href="dashEnt.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="pubOffre.php" class="nav-link active"><i class="fa-solid fa-plus-circle"></i> Publier une offre</a>
        <a href="OffrePub.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        <a href="#" class="nav-link text-danger"><i class="fa-solid fa-power-off"></i> Déconnexion</a>
    </nav>
</div>

<div class="main">

    <div class="header-section">
        <h2 class="fw-bold mb-1">Publier une nouvelle offre</h2>
        <p class="text-muted">Remplissez les informations ci-dessous pour attirer les meilleurs talents.</p>
    </div>

    <div class="card-form">
        <div class="step-icon">
            <i class="fa-solid fa-file-signature"></i>
        </div>

        <form method="POST" action="save_offre.php">

            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-heading me-2"></i>Titre de l’offre</label>
                <input type="text" name="titre" class="form-control" placeholder="Ex: Développeur Web Full Stack" required>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-align-left me-2"></i>Description du poste</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Décrivez les missions, l'environnement de travail..." required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label"><i class="fa-solid fa-tags me-2"></i>Compétences requises</label>
                    <input type="text" name="competences" class="form-control" placeholder="ex: PHP, React, UI/UX Design">
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label"><i class="fa-solid fa-location-dot me-2"></i>Lieu du stage</label>
                    <input type="text" name="lieu" class="form-control" placeholder="ex: Douala, Akwa">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label"><i class="fa-solid fa-briefcase me-2"></i>Type de stage</label>
                    <select name="type_stage" class="form-select">
                        <option>Stage académique</option>
                        <option>Stage professionnel</option>
                        <option>Stage pré-emploi</option>
                    </select>
                </div>

                <div class="col-md-4 mb-4">
                    <label class="form-label"><i class="fa-solid fa-calendar-day me-2"></i>Durée</label>
                    <input type="text" name="duree" class="form-control" placeholder="ex: 3 à 6 mois">
                </div>

                <div class="col-md-4 mb-4">
                    <label class="form-label"><i class="fa-solid fa-clock me-2"></i>Date limite de dépôt</label>
                    <input type="date" name="date_limite" class="form-control">
                </div>
            </div>

            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

            <div class="d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-cancel">Annuler</button>
                <button type="submit" class="btn-publish">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i> Mettre en ligne l'offre
                </button>
            </div>

        </form>
    </div>

    <div class="text-center mt-4">
        <small class="text-muted">Besoin d'aide ? Consultez notre <a href="#" class="text-primary text-decoration-none">guide de rédaction</a></small>
    </div>

</div>

</body>
</html>