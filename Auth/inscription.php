<?php
include('config_db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Récupération des données
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $tel = htmlspecialchars($_POST['tel']);
    $adresse = htmlspecialchars($_POST['adresse']);
    $role = $_POST['identite']; // 'etudiant' ou 'entreprise'
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

if ($password !== $confirm) {
    echo "<script>alert('Les mots de passe ne correspondent pas');</script>";
    exit();
}!
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

    try {
        // 2. Insertion dans UTILISATEUR (La table parente)
        $sqlUser = "INSERT INTO UTILISATEUR (email, password, role, nom_complet, telephone, adresse) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlUser);
        $stmt->execute([$email, $password, $role, $nom, $tel, $adresse]);
        
        // On récupère l'ID qui vient d'être créé
        $lastId = $pdo->lastInsertId();

        // 3. Insertion spécifique selon le rôle
        if ($role == 'etudiant') {
            $ville = htmlspecialchars($_POST['ville']);
            $sqlSpec = "INSERT INTO ETUDIANT (id_user, ville) VALUES (?, ?)";
            $stmtSpec = $pdo->prepare($sqlSpec);
            $stmtSpec->execute([$lastId, $ville]);
        } else if ($role == 'entreprise') {
            $siege = htmlspecialchars($_POST['ville']);
            $sqlSpec = "INSERT INTO ENTREPRISE (id_user, siege_social) VALUES (?, ?)";
            $stmtSpec = $pdo->prepare($sqlSpec);
            $stmtSpec->execute([$lastId, $siege]);
        }

        echo "<script>alert('Compte créé avec succès !'); window.location='connexion.php';</script>";

    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Admin Portal</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-muted: #94a3b8;
        }

        body {
            background: var(--bg);
            background-image: 
                radial-gradient(circle at 90% 10%, rgba(59, 130, 246, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 10% 90%, rgba(96, 165, 250, 0.1) 0%, transparent 40%);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            overflow-x: hidden;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }

        .register-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), #60a5fa);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
        }

        .register-title {
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 5px;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 2px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            color: white;
        }

        .btn-register {
            background: var(--primary);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            transition: 0.3s;
            margin-top: 15px;
        }

        .btn-register:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        /* Séparateur visuel pour les champs côte à côte */
        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 576px) {
            .input-row { grid-template-columns: 1fr; }
        }
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2394a3b8'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        .form-select option { background: #1e293b; color: white; }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 10px 16px;
            color: white;
            transition: 0.3s;
        }
    </style>
</head>
<body>

<div class="register-container" data-aos="fade-up" data-aos-duration="1000">
    
    <div class="register-card">
        <div class="brand-logo">
            <i class="fa-solid fa-user-plus text-white"></i>
        </div>

        <h2 class="register-title">Rejoignez-nous ✨</h2>
        <p class="text-muted small mb-4">Créez votre compte en quelques secondes.</p>

        <form action="#" method="POST">
            <div class="mb-3">
                <label class="form-label" >Nom complet</label>
                <div class="input-group">
                    <input name="nom" type="text" class="form-control" placeholder="entrer votre nom" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" >Email</label>
                <input name="email" type="email" class="form-control" placeholder="entrer votre email" required>
            </div>
            
           <div class="mb-3">
    <label class="form-label">Vous êtes ?</label>
    <select  id="roleSelect" class="form-select" required name="identite">
        <option value="" selected disabled>Choisir un rôle</option>
        <option value="etudiant">Étudiant (cherche un stage)</option>
        <option value="entreprise">Entreprise (publie des offres)</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label" id="labelDynamique" >Ville</label>
    <input name="ville" type="text" name="localisation" id="inputDynamique" class="form-control" placeholder="entrer la ville" required>
</div>

 <div class="mb-3">
                <label class="form-label" >Telephone</label>
                <input name="tel" type="number" class="form-control" placeholder="entrer le numero de telephone" required>
            </div>
             <div class="mb-3">
                <label class="form-label" >Adresse</label>
                <input name="adresse" type="adresse" class="form-control" placeholder="entrer votre adresse" required>
            </div>
            <div class="input-row mb-4">
                <div>
                    <label class="form-label" >Mot de passe</label>
                    <input name="password" type="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div>
                    <label class="form-label" >Confirmation</label>
                    <input name="confirm" type="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label small text-muted" for="terms">
                    J'accepte les <a href="#" class="text-primary">Conditions d'utilisation</a>
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-register text-white" name="btn">
                    Créer mon compte <i class="fa-solid fa-sparkles ms-2"></i>
                </button>
            </div>
        </form>

        <div class="text-center mt-4 footer-links">
            <p class="text-muted small">Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<script>
    const roleSelect = document.getElementById('roleSelect');
    const labelDynamique = document.getElementById('labelDynamique');
    const inputDynamique = document.getElementById('inputDynamique');

    roleSelect.addEventListener('change', function() {
        if (this.value === 'entreprise') {
            labelDynamique.innerText = "Siège Social";
            inputDynamique.placeholder = "ex: Akwa, Douala";
        } else {
            labelDynamique.innerText = "Ville";
            inputDynamique.placeholder = "ex: Yaoundé";
        }
    });
</script>

</body>
</html>