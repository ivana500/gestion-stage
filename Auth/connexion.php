<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #74ebd5, #9face6);
        }

        .login-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .login-title {
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

    <div class="col-md-4 login-card">

        <h2 class="text-center mb-4 login-title">Bienvenue 👋</h2>

        <form>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" placeholder="Entrez votre email">
            </div>

            <!-- Mot de passe -->
            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" class="form-control" placeholder="Entrez votre mot de passe">
            </div>

            <!-- Se souvenir -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox">
                <label class="form-check-label">Se souvenir de moi</label>
            </div>

            <!-- Bouton -->
            <div class="d-grid">
                <button type="submit" class="btn btn-custom text-white">Se connecter</button>
            </div>

        </form>

        <!-- Liens -->
        <div class="text-center mt-3">
            <a href="#" class="text-muted">Mot de passe oublié ?</a><br>
            <small>Pas de compte ? <a href="#">S'inscrire</a></small>
        </div>

    </div>

</div>

</body>
</html>