<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_etudiant = $_SESSION['user_id'];

// --- On récupère les infos de la candidature acceptée ---
$stmt_init = $pdo->prepare("SELECT * FROM CANDIDATURE WHERE id_etudiant = ? AND statut_candidature = 'acceptee' LIMIT 1");
$stmt_init->execute([$id_etudiant]);
$cand = $stmt_init->fetch();

// --- On vérifie s'il existe déjà une convention liée à ce stage (via la candidature) ---
$convention = null;
if ($cand) {
    $stmt_conv = $pdo->prepare("SELECT * FROM CONVENTION WHERE id_stage = ? LIMIT 1");
    $stmt_conv->execute([$cand['id_candidature']]); // Note: Ajuste ici si ta table CONVENTION lie id_stage à une autre table
    $convention = $stmt_conv->fetch();
}

// --- TRAITEMENT DES UPLOADS EN AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_FILES['rapport']) || isset($_FILES['convention']))) {
    header('Content-Type: application/json');
    
    if (!$cand) {
        echo json_encode(['status' => 'error', 'message' => 'Aucun stage accepté trouvé.']);
        exit;
    }

    $is_rapport = isset($_FILES['rapport']);
    $file = $is_rapport ? $_FILES['rapport'] : $_FILES['convention'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($ext !== 'pdf') {
        echo json_encode(['status' => 'error', 'message' => 'Seul le format PDF est autorisé.']);
        exit;
    }

    // Sélection du dossier de destination
    $dest_folder = $is_rapport ? '../uploads/rapports/' : '../uploads/conventions/';
    if (!is_dir($dest_folder)) {
        mkdir($dest_folder, 0777, true);
    }

    // Nommage unique du fichier
    $prefix = $is_rapport ? "rapport_" : "convention_";
    $file_name = $prefix . $id_etudiant . "_" . time() . ".pdf";
    $dest_path = $dest_folder . $file_name;

    if (move_uploaded_file($file['tmp_name'], $dest_path)) {
        if ($is_rapport) {
            // Sauvegarde du Rapport dans la table CANDIDATURE
            $update = $pdo->prepare("UPDATE CANDIDATURE SET rapport_pdf = ? WHERE id_candidature = ?");
            $update->execute([$file_name, $cand['id_candidature']]);
        } else {
            // Sauvegarde ou modification de la CONVENTION
            if ($convention) {
                $update_conv = $pdo->prepare("UPDATE CONVENTION SET fichier_pdf = ?, date_signature = NOW() WHERE id_stage = ?");
                $update_conv->execute([$file_name, $cand['id_candidature']]);
            } else {
                $insert_conv = $pdo->prepare("INSERT INTO CONVENTION (id_stage, fichier_pdf, date_signature) VALUES (?, ?, NOW())");
                $insert_conv->execute([$cand['id_candidature'], $file_name]);
            }
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors du déplacement du fichier sur le serveur.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi & Documents de Stage | STAGES HELLO</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --sidebar-bg: #020617;
            --card-bg: rgba(30, 41, 59, 0.4);
            --primary: #3b82f6;
            --accent-green: #10b981;
            --text-muted: #94a3b8;
        }

        body {
            background: var(--bg);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }

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
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
        }

        .sidebar a.active { background: var(--primary); color: white; }

        .main {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
        }

        .card-upload {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 40px;
        }

        /* STYLE DES ONGLETS INNOVANTS */
        .nav-pills .nav-link {
            color: var(--text-muted);
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            transition: 0.3s;
            border: 1px solid transparent;
        }
        .nav-pills .nav-link.active {
            background: rgba(59, 130, 246, 0.15) !important;
            color: var(--primary) !important;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .drop-zone {
            border: 2px dashed rgba(59, 130, 246, 0.3);
            border-radius: 20px;
            padding: 50px 20px;
            text-align: center;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(15, 23, 42, 0.2);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: block;
        }

        .drop-zone:hover, .drop-zone.drag-over {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.05);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.1);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .drop-zone:hover .upload-icon {
            transform: translateY(-10px);
        }

        .progress-container {
            display: none;
            margin-top: 30px;
        }

        .progress {
            height: 8px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(to right, var(--primary), var(--accent-green));
            transition: width 0.4s ease;
        }

        .tips-card {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 16px;
            padding: 20px;
        }

        .tip-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .tip-item i {
            color: var(--accent-green);
            margin-right: 12px;
            margin-top: 4px;
        }

        .btn-submit-premium {
            background: var(--primary);
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            width: 100%;
            margin-top: 25px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .btn-submit-premium:disabled {
            background: #334155;
            box-shadow: none;
            cursor: not-allowed;
        }

        .success-animation {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .success-icon {
            font-size: 4rem;
            color: var(--accent-green);
            margin-bottom: 15px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        input[type="file"] { display: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="sidebar-brand">
        <i class="fa-solid fa-rocket"></i> STAGES HELLO
    </a>
    <a href="dashEtud.php"><i class="fa-solid fa-grip-vertical"></i> Dashboard</a>
    <a href="listeStage.php"><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
    <a href="Candidature.php"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
    <a href="MonStage.php" class="active"><i class="fa-solid fa-file-arrow-up"></i> Espace Documents</a>
    <a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Déconnexion</span>
</a>
</div>

<div class="main">
    
    <div class="mb-4" data-aos="fade-down">
        <h2 class="fw-800">Gestion de votre stage</h2>
        <p class="text-muted">Déposez et suivez vos documents obligatoires (Convention et Rapport Final).</p>
    </div>

    <!-- Sélections d'onglets pour basculer proprement entre convention et rapport -->
    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist" data-aos="fade-down">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-convention-tab" data-bs-toggle="pill" data-bs-target="#pills-convention" type="button" role="tab"><i class="fa-solid fa-file-signature me-2"></i>1. Convention de Stage</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-rapport-tab" data-bs-toggle="pill" data-bs-target="#pills-rapport" type="button" role="tab"><i class="fa-solid fa-file-lines me-2"></i>2. Rapport Final</button>
        </li>
    </ul>

    <div class="row g-4">
        <div class="col-lg-7" data-aos="fade-right">
            <div class="card-upload">
                <div class="tab-content" id="pills-tabContent">
                    
                    <!-- ONGLET CONVENTION -->
                    <div class="tab-pane fade show active" id="pills-convention" role="tabpanel">
                        <form class="uploadForm" data-type="convention">
                            <label class="drop-zone w-100">
                                <div class="upload-icon"><i class="fa-solid fa-file-signature text-info"></i></div>
                                <h5 class="fw-bold">Glissez votre convention signée ici</h5>
                                <p class="text-muted small">Format PDF uniquement (Max 20Mo)</p>
                                <input type="file" class="fileInput" accept="application/pdf">
                                <div class="fileDisplay mt-3 fw-bold text-info"></div>
                            </label>
                            
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <?php if (!empty($convention['fichier_pdf'])): ?>
                                    <a href="../uploads/conventions/<?= $convention['fichier_pdf'] ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill">
                                        <i class="fa-solid fa-download me-2"></i> Visualiser ma convention
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic"><i class="fa-solid fa-clock me-1"></i> Aucune convention déposée</span>
                                <?php endif; ?>
                            </div>

                            <!-- Progress & Success unifiés par formulaire -->
                            <div class="progress-container">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Envoi de la convention...</small>
                                    <small class="fw-bold percentText">0%</small>
                                </div>
                                <div class="progress"><div class="progress-bar style-width: 0%"></div></div>
                            </div>
                            <div class="success-animation">
                                <div class="success-icon"><i class="fa-solid fa-circle-check text-info"></i></div>
                                <h4 class="fw-bold">Convention transmise !</h4>
                                <p class="text-muted">Le document est archivé et consultable par l'administration.</p>
                            </div>
                            <button type="submit" class="btn-submit-premium" disabled><i class="fa-solid fa-paper-plane me-2"></i>Soumettre la convention</button>
                        </form>
                    </div>

                    <!-- ONGLET RAPPORT -->
                    <div class="tab-pane fade" id="pills-rapport" role="tabpanel">
                        <form class="uploadForm" data-type="rapport">
                            <label class="drop-zone w-100">
                                <div class="upload-icon"><i class="fa-solid fa-file-pdf"></i></div>
                                <h5 class="fw-bold">Glissez votre rapport de fin de stage ici</h5>
                                <p class="text-muted small">Format PDF uniquement (Max 20Mo)</p>
                                <input type="file" class="fileInput" accept="application/pdf">
                                <div class="fileDisplay mt-3 fw-bold text-primary"></div>
                            </label>
                            
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <?php if (!empty($cand['rapport_pdf'])): ?>
                                    <a href="telecharger_rapport.php?file=<?= $cand['rapport_pdf'] ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="fa-solid fa-download me-2"></i> Télécharger le rapport
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic"><i class="fa-solid fa-clock me-1"></i> Aucun rapport déposé</span>
                                <?php endif; ?>
                            </div>

                            <div class="progress-container">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Envoi du rapport en cours...</small>
                                    <small class="fw-bold percentText">0%</small>
                                </div>
                                <div class="progress"><div class="progress-bar" style="width: 0%"></div></div>
                            </div>
                            <div class="success-animation">
                                <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
                                <h4 class="fw-bold">Rapport Soumis !</h4>
                                <p class="text-muted">Votre encadreur et l'administration ont été notifiés.</p>
                            </div>
                            <button type="submit" class="btn-submit-premium" disabled><i class="fa-solid fa-paper-plane me-2"></i>Envoyer mon rapport final</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-5" data-aos="fade-left">
            <div class="tips-card mb-4">
                <h5 class="fw-bold mb-4 text-white">Checklist administrative</h5>
                <div class="tip-item">
                    <i class="fa-solid fa-check-double"></i>
                    <span>La <strong>Convention</strong> doit comporter les signatures physiques ou numériques de l'établissement, de l'entreprise d'accueil et la vôtre.</span>
                </div>
                <div class="tip-item">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Le <strong>Rapport</strong> doit obligatoirement respecter la charte de présentation de votre filière.</span>
                </div>
                <div class="tip-item">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Tous les documents soumis doivent être convertis en <strong>PDF</strong> de moins de 20 Mo.</span>
                </div>
            </div>

            <div class="p-4 rounded-4 border border-secondary border-opacity-25 bg-dark bg-opacity-20">
                <h6 class="fw-bold text-white mb-2">Besoin d'aide ?</h6>
                <p class="small text-muted mb-0">Vos documents sont cryptés et transmis de manière sécurisée aux autorités académiques compétentes pour traitement.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800 });

    // Gestion unifiée de tous les formulaires d'upload (Convention et Rapport)
    document.querySelectorAll('.uploadForm').forEach(form => {
        const fileInput = form.querySelector('.fileInput');
        const fileDisplay = form.querySelector('.fileDisplay');
        const submitBtn = form.querySelector('.btn-submit-premium');
        const progressContainer = form.querySelector('.progress-container');
        const progressBar = form.querySelector('.progress-bar');
        const percentText = form.querySelector('.percentText');
        const successArea = form.querySelector('.success-animation');
        const dropZone = form.querySelector('.drop-zone');
        const uploadType = form.getAttribute('data-type');

        fileInput.addEventListener('change', function() {
            if (this.files[0]) {
                fileDisplay.innerText = "Fichier sélectionné : " + this.files[0].name;
                submitBtn.disabled = false;
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData();
            // On attribue la clé attendue par le traitement PHP supérieur ('rapport' ou 'convention')
            formData.append(uploadType, fileInput.files[0]);

            let xhr = new XMLHttpRequest();
            progressContainer.style.display = 'block';
            submitBtn.style.display = 'none';
            dropZone.style.display = 'none';

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    percentText.innerText = percent + '%';
                }
            });

            xhr.onload = function() {
                try {
                    let response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        progressContainer.style.display = 'none';
                        successArea.style.display = 'block';
                        setTimeout(() => { window.location.reload(); }, 2000); // Recharge proprement la page pour actualiser le bouton de téléchargement
                    } else {
                        alert(response.message);
                        submitBtn.style.display = 'block';
                        dropZone.style.display = 'block';
                        progressContainer.style.display = 'none';
                    }
                } catch(e) {
                    alert("Erreur de communication avec le serveur.");
                    submitBtn.style.display = 'block';
                    dropZone.style.display = 'block';
                    progressContainer.style.display = 'none';
                }
            };

            xhr.open('POST', window.location.href, true);
            xhr.send(formData);
        });
    });
</script>
</body>
</html>