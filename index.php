<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STAGES HELLO | L'avenir de vos stages</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #3b82f6;
            --dark: #0f172a;
            --accent: #10b981;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--dark);
            color: #f8fafc;
            overflow-x: hidden;
        }

        /* NAVBAR MODERNE */
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(15, 23, 42, 0.8) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -1px;
            font-size: 1.5rem;
            background: linear-gradient(to right, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* CAROUSEL FULLSCREEN */
        #carouselAccueil, .carousel-item {
            height: 100vh;
        }

        .carousel-img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            filter: brightness(0.4) saturate(1.2);
            transition: transform 10s ease-in-out;
        }

        .carousel-item.active .carousel-img {
            transform: scale(1.1);
        }

        /* CAPTION STYLE */
        .carousel-caption-custom {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            z-index: 10;
        }

        .hero-title {
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(30px);
        }

        .hero-text {
            font-size: 1.25rem;
            color: #cbd5e1;
            margin-bottom: 35px;
            opacity: 0;
            transform: translateY(30px);
        }

        .carousel-item.active .hero-title,
        .carousel-item.active .hero-text,
        .carousel-item.active .hero-btn {
            animation: fadeInUp 0.8s forwards ease-out;
        }

        .hero-text { animation-delay: 0.2s !important; }
        .hero-btn { animation-delay: 0.4s !important; }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* BOUTONS */
        .btn-premium {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            border: none;
        }

        .btn-glow-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }

        .btn-glow-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.6);
            color: white;
        }

        /* SECTION FEATURES */
        .feature-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 40px;
            transition: 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            background: rgba(30, 41, 59, 0.8);
            transform: translateY(-10px);
            border-color: var(--primary);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }

        /* FOOTER */
        footer {
            background: #020617 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        html {
    scroll-behavior: smooth;
}

/* Style optionnel pour l'article nos offres */
.section-offres {
    padding: 100px 0;
    background: linear-gradient(to bottom, #0f172a, #1e293b);
}

.article-offres {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 30px;
    padding: 50px;
}

/* Container de l'article */
.article-offres {
    background: rgba(30, 41, 59, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 32px;
    backdrop-filter: blur(8px);
}

/* Effet Glow derrière la vidéo */
.video-glow-effect {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 110%;
    height: 110%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
    z-index: 0;
    pointer-events: none;
}

/* Style fenêtre macOS */
.video-window-container {
    position: relative;
    z-index: 2;
    background: #0f172a;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
}

.window-header {
    background: #1e293b;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.window-dots span {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
.dot-red { background: #ff5f56; }
.dot-yellow { background: #ffbd2e; }
.dot-green { background: #27c93f; }

.window-title {
    margin-left: auto;
    margin-right: auto;
    font-size: 0.75rem;
    color: #94a3b8;
    font-family: monospace;
}

/* Animation Pulse pour le badge En Direct */
.animate-pulse {
    animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

/* Correction icônes */
.icon-box {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.1);
    color: var(--primary);
}

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="fa-solid fa-rocket me-2"></i> STAGES HELLO
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link px-3" href="#">Accueil</a></li>
<li class="nav-item"><a class="nav-link px-3" href="#offres">Nos Offres</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#demo">Demo</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-outline-primary rounded-pill px-4" href="#">Connexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div id="carouselAccueil" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-inner">

        <div class="carousel-item active">
            <img src="images/acc1.png" class="carousel-img" alt="Collaboration">
            <div class="carousel-caption-custom">
                <h1 class="hero-title text-uppercase">Propulsez votre <br><span class="text-primary">Carrière</span></h1>
                <p class="hero-text mx-auto" style="max-width: 600px;">La plateforme n°1 pour connecter les talents de demain avec les entreprises les plus innovantes.</p>
                <div class="hero-btn">
                    <a href="#" class="btn btn-premium btn-glow-primary me-3">Trouver un stage</a>
                    <a href="#" class="btn btn-premium btn-outline-light">Recruter</a>
                </div>
            </div>
        </div>

        <div class="carousel-item">
            <img src="images/acc2.png" class="carousel-img" alt="Technologie">
            <div class="carousel-caption-custom">
                <h1 class="hero-title">L'Intelligence au <br>service du <span class="text-success">Recrutement</span></h1>
                <p class="hero-text mx-auto" style="max-width: 600px;">Gérez vos candidatures et vos offres avec une simplicité déconcertante grâce à nos outils automatisés.</p>
                <div class="hero-btn">
                    <a href="#" class="btn btn-premium btn-success text-white">Découvrir les outils</a>
                </div>
            </div>
        </div>

    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#carouselAccueil" data-bs-slide="prev">
        <span class="carousel-control-prev-icon p-4 rounded-circle bg-dark"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselAccueil" data-bs-slide="next">
        <span class="carousel-control-next-icon p-4 rounded-circle bg-dark"></span>
    </button>
</div>

<section class="py-5 mt-5">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-primary text-uppercase fw-bold">Pourquoi nous ?</h6>
            <h2 class="display-4 fw-bold">Une expérience réinventée</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card text-center">
                    <div class="icon-box mx-auto"><i class="fa-solid fa-bolt"></i></div>
                    <h4>Rapidité</h4>
                    <p class="text-muted">Postulez en un clic et recevez des réponses en moins de 48h.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card text-center">
                    <div class="icon-box mx-auto" style="color: var(--accent); background: rgba(16,185,129,0.1);"><i class="fa-solid fa-shield-halved"></i></div>
                    <h4>Sécurité</h4>
                    <p class="text-muted">Toutes les offres sont vérifiées manuellement par nos experts.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card text-center">
                    <div class="icon-box mx-auto" style="color: #f59e0b; background: rgba(245,158,11,0.1);"><i class="fa-solid fa-chart-pie"></i></div>
                    <h4>Suivi</h4>
                    <p class="text-muted">Suivez l'état de vos candidatures en temps réel via votre dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="offres" class="section-offres">
    <div class="container">
        <div class="article-offres" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h6 class="text-primary fw-bold text-uppercase mb-3">Opportunités</h6>
                    <h2 class="display-5 fw-bold mb-4">Découvrez nos offres exclusives</h2>
                    <p class="text-muted fs-5">
                        Chez <strong>STAGES HELLO</strong>, nous sélectionnons rigoureusement des offres qui correspondent à vos ambitions. Que vous soyez en quête d'un stage de fin d'études ou d'une première immersion professionnelle, notre catalogue couvre :
                    </p>
                    <ul class="list-unstyled mt-4">
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Développement Web & Mobile</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Design UI/UX & Création Graphique</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Marketing Digital & Communication</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i> Finance & Gestion d'entreprise</li>
                    </ul>
                    <a href="#" class="btn btn-primary rounded-pill px-5 py-3 mt-4 fw-bold">Consulter le catalogue complet</a>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-4 rounded-4 bg-primary bg-opacity-10 text-center">
                                <h3 class="fw-bold mb-0">+500</h3>
                                <small class="text-muted">Offres actives</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 rounded-4 bg-success bg-opacity-10 text-center">
                                <h3 class="fw-bold mb-0">120</h3>
                                <small class="text-muted">Partenaires</small>
                            </div>
                        </div>
                    </div>
                    <img src="images/offres-preview.png" alt="Offres" class="img-fluid mt-4 rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>
<article class="article-offres p-4 p-lg-5" data-aos="fade-up" id="demo">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill">Nouveauté 2026</span>
            <h2 class="display-5 fw-bold mb-4">Une nouvelle ère pour vos <span class="text-primary">recherches de stages</span></h2>
            
            <p class="lead text-light opacity-75">
                Trouver un stage ne devrait pas être un parcours du combattant. <strong>STAGES HELLO</strong> simplifie la mise en relation entre les étudiants et les entreprises leaders au Cameroun.
            </p>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="icon-box me-3" style="min-width: 50px; height: 50px;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Diversité</h5>
                            <p class="small text-muted">Accédez à des secteurs variés en un clic.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="icon-box me-3" style="min-width: 50px; height: 50px; color: var(--accent); background: rgba(16,185,129,0.1);">
                            <i class="fa-solid fa-handshake"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Direct</h5>
                            <p class="small text-muted">Échanges simplifiés avec les recruteurs.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-4 mt-5 pt-4 border-top border-secondary border-opacity-25">
                <div>
                    <h4 class="fw-bold text-primary mb-0">98%</h4>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Satisfaction</small>
                </div>
                <div class="vr opacity-25"></div>
                <div>
                    <h4 class="fw-bold text-white mb-0">2.5k</h4>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Étudiants</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="video-wrapper position-relative">
                <div class="video-glow-effect"></div>
                
                <div class="video-window-container">
                    <div class="window-header">
                        <div class="window-dots">
                            <span class="dot-red"></span>
                            <span class="dot-yellow"></span>
                            <span class="dot-green"></span>
                        </div>
                        <div class="window-title">Démo Plateforme - Stages Hello</div>
                    </div>
                    
                    <div class="ratio ratio-16x9 shadow-lg">
                        <video controls poster="images/poster-video.png" class="bg-black">
                            <source src="videos/votre-video-demo.mp4" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>
                    </div>
                </div>

                <div class="position-absolute top-0 end-0 mt-4 me-n2 translate-middle-x">
                    <span class="badge bg-danger px-3 py-2 shadow-lg animate-pulse">
                        <i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i> EN DIRECT
                    </span>
                </div>
            </div>
        </div>
    </div>
</article>

<footer class="text-light py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-4">STAGES HELLO</h5>
                <p class="text-muted">Nous transformons la recherche de stage en une expérience fluide, sécurisée et valorisante pour tous les étudiants du Cameroun.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light fs-5"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" class="text-light fs-5"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="text-light fs-5"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 offset-lg-1">
                <h6 class="fw-bold mb-4">Navigation</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted text-decoration-none mb-2 d-block">Parcourir les offres</a></li>
                    <li><a href="#" class="text-muted text-decoration-none mb-2 d-block">Espace Entreprise</a></li>
                    <li><a href="#" class="text-muted text-decoration-none mb-2 d-block">Aide & Support</a></li>
                </ul>
            </div>
            <div class="col-lg-5">
                <h6 class="fw-bold mb-4">Restez informé</h6>
                <div class="input-group mb-3">
                    <input type="text" class="form-control bg-dark border-secondary text-white" placeholder="Votre email...">
                    <button class="btn btn-primary px-4">S'abonner</button>
                </div>
            </div>
        </div>
        <hr class="my-5 border-secondary">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <small class="text-muted">&copy; 2026 STAGES HELLO. Tous droits réservés.</small>
            <small class="text-muted">Design by Ivana Fotsing</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true });
</script>

</body>
</html>