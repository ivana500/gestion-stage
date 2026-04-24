<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail de l'offre</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root{
            --bg:#1a1d2d;
            --card:#23273a;
            --muted:#8a8d9a;
            --blue:#3b82f6;
        }

        body{
            background:var(--bg);
            color:white;
            font-family:'Inter',sans-serif;
        }

        .sidebar{
            width:260px;
            height:100vh;
            position:fixed;
            background:#111422;
            padding:20px;
        }

        .sidebar a{
            display:block;
            padding:12px;
            color:var(--muted);
            text-decoration:none;
            border-radius:8px;
            margin-bottom:8px;
            transition:0.3s;
        }

        .sidebar a:hover{
            background:rgba(59,130,246,0.15);
            color:white;
        }

        .main{
            margin-left:270px;
            padding:30px;
        }

        .card-detail{
            background:var(--card);
            border-radius:15px;
            padding:30px;
            margin-bottom:20px;
        }

        .badge-custom{
            background:#111422;
            border:1px solid rgba(255,255,255,0.1);
            padding:6px 12px;
            border-radius:6px;
            font-size:0.75rem;
            margin-right:5px;
        }

        .btn-apply{
            background:var(--blue);
            border:none;
            padding:10px 20px;
            border-radius:8px;
            color:white;
            font-weight:600;
        }

        .btn-apply:hover{
            background:#2563eb;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4 class="text-primary mb-4">Étudiant</h4>

   <a href="dashEtud.php"><i class="fa-solid fa-grip-vertical"></i> Dashboard</a>
    <a href="listeStage.php" ><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
    <a href="Candidature.php"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
    <a href="MonStage.php" ><i class="fa-solid fa-file-arrow-up"></i> Dépôt de rapport</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="card-detail">

        <h2 class="fw-bold mb-2">Développeur Web Full Stack</h2>
        <p class="text-muted mb-3">Tech Solutions • Douala</p>

        <div class="mb-3">
            <span class="badge-custom">3 mois</span>
            <span class="badge-custom">Stage professionnel</span>
            <span class="badge-custom">Présentiel</span>
        </div>

        <hr style="border-color: rgba(255,255,255,0.1);">

        <h5 class="mt-4">Description du stage</h5>
        <p class="text-muted">
            Nous recherchons un stagiaire développeur web capable de participer à la conception 
            et au développement d’applications web modernes. Vous travaillerez avec une équipe dynamique 
            sur des projets innovants utilisant PHP, JavaScript et MySQL.
        </p>

        <h5 class="mt-4">Missions</h5>
        <ul class="text-muted">
            <li>Développement de fonctionnalités web</li>
            <li>Maintenance des applications existantes</li>
            <li>Participation aux réunions techniques</li>
        </ul>

        <h5 class="mt-4">Compétences requises</h5>
        <ul class="text-muted">
            <li>Bonne connaissance de HTML/CSS</li>
            <li>Notions en PHP et JavaScript</li>
            <li>Esprit d’équipe</li>
        </ul>

        <h5 class="mt-4">Informations supplémentaires</h5>
        <p class="text-muted">
            Date limite de candidature : <strong>30 Mai 2026</strong><br>
            Début du stage : Juin 2026
        </p>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <a href="#" class="text-decoration-none text-muted">
                <i class="fa fa-arrow-left me-1"></i> Retour aux offres
            </a>

            <button class="btn-apply">
                <i class="fa fa-paper-plane me-2"></i> Postuler
            </button>
        </div>

    </div>

</div>

</body>
</html>