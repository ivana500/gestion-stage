<?php
session_start(); // Indispensable pour garder l'utilisateur connecté
include('config_db.php');

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // 1. On cherche l'utilisateur dans la table centrale
    $sql = "SELECT * FROM UTILISATEUR WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 2. Vérification du mot de passe
    if ($user && password_verify($password, $user['password'])) {
        
        // On stocke les infos importantes en session
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_nom'] = $user['nom_complet'];
        $_SESSION['user_role'] = $user['role'];

        // 3. Redirection dynamique selon le rôle
        if ($user['role'] === 'admin') {
            header('Location: ../Admin/dash.php');
        } elseif ($user['role'] === 'etudiant') {
            header('Location: ../Etudiant/dashEtud.php');
        } elseif ($user['role'] === 'entreprise') {
            header('Location: ../Entreprise/dashEnt.php');
        }
        exit();
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Admin Portal</title>

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
                radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(96, 165, 250, 0.1) 0%, transparent 40%);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 1.5rem;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 15px 30px rgba(59, 130, 246, 0.5); }
            100% { transform: scale(1); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3); }
        }

        .login-title {
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-left: 5px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 14px 18px;
            color: white;
            transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            color: white;
        }

        .form-control::placeholder {
            color: #4b5563;
        }

        .btn-login {
            background: var(--primary);
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .footer-links a:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .glass-dot {
            position: absolute;
            width: 100px;
            height: 100px;
            background: var(--primary);
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
        }
    </style>
</head>
<body>

<div class="login-container" data-aos="zoom-in" data-aos-duration="800">
    
    <div class="login-card">
        <div class="brand-logo">
            <i class="fa-solid fa-shield-halved text-white"></i>
        </div>

        <div class="text-center mb-4">
            <h2 class="login-title">Content de vous revoir !</h2>
            <p class="text-muted small">Veuillez entrer vos accès pour continuer.</p>
        </div>

        <?php if(!empty($erreur)): ?>
    <div class="alert alert-danger py-2" style="border-radius: 10px; font-size: 0.8rem;">
        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $erreur ?>
    </div>
<?php endif; ?>

<form action="" method="POST">
    <div class="input-group-custom">
        <label class="form-label">Adresse e-mail</label>
        <input type="email" name="email" class="form-control" placeholder="nom@exemple.com" required>
    </div>

    <div class="input-group-custom">
        <div class="d-flex justify-content-between">
            <label class="form-label">Mot de passe</label>
            <a href="#" class="small text-primary text-decoration-none fw-600">Oublié ?</a>
        </div>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-login text-white">
            Se connecter <i class="fa-solid fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>

        <div class="text-center mt-4 footer-links">
            <p class="text-muted small">Vous n'avez pas de compte ? <a href="inscription.php">Créer un compte</a></p>
        </div>
    </div>

    <div class="text-center mt-4">
        <p class="text-muted" style="font-size: 0.75rem;">&copy; 2026 Admin Portal System. Version 2.4.0</p>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>

</body>
</html>