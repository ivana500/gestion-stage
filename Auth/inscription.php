<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #74ebd5, #9face6);
        }

        .register-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .register-title {
            color: #333;
            font-weight: bold;
        }

        .form-control {
            border-radius: 10px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #74ebd5;
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
        }

        .btn-custom:hover {
            opacity: 0.9;
        }

        a {
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="col-md-5 register-card">

        <h2 class="text-center mb-4 register-title">Créer un compte ✨</h2>

        <form>

            <!-- Nom -->
            <div class="mb-3">
                <label class="form-label">Nom complet</label>
                <input type="text" class="form-control" placeholder="Entrez votre nom">
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" placeholder="Entrez votre email">
            </div>

            <!-- Mot de passe -->
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" class="form-control" placeholder="Créer un mot de passe">
            </div>

            <!-- Confirmer mot de passe -->
            <div class="mb-3">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" placeholder="Confirmez votre mot de passe">
            </div>

            <!-- Bouton -->
            <div class="d-grid">
                <button type="submit" class="btn btn-custom text-white">S'inscrire</button>
            </div>

        </form>

        <!-- Lien -->
        <div class="text-center mt-3">
            <small>Déjà un compte ? <a href="login.php">Se connecter</a></small>
        </div>

    </div>

</div>

</body>
</html>