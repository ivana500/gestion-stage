<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_etudiant = $_SESSION['user_id'];

// --- NOUVEAU : On récupère les infos de la candidature dès le début ---
$stmt_init = $pdo->prepare("SELECT * FROM CANDIDATURE WHERE id_etudiant = ? AND statut_candidature = 'acceptee' LIMIT 1");
$stmt_init->execute([$id_etudiant]);
$cand = $stmt_init->fetch(); // Maintenant $cand existe pour le HTML plus bas !

// --- TRAITEMENT DE L'UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['rapport'])) {
    header('Content-Type: application/json');
    
    if (!$cand) {
        echo json_encode(['status' => 'error', 'message' => 'Aucun stage accepté trouvé.']);
        exit;
    }

    $file = $_FILES['rapport'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($ext !== 'pdf') {
        echo json_encode(['status' => 'error', 'message' => 'Seul le format PDF est autorisé.']);
        exit;
    }

    $dest_folder = '../uploads/rapports/';
    if (!is_dir($dest_folder)) mkdir($dest_folder, 0777, true);

    $file_name = "rapport_" . $id_etudiant . "_" . time() . ".pdf";
    $dest_path = $dest_folder . $file_name;

    if (move_uploaded_file($file['tmp_name'], $dest_path)) {
        $update = $pdo->prepare("UPDATE CANDIDATURE SET rapport_pdf = ? WHERE id_candidature = ?");
        $update->execute([$file_name, $cand['id_candidature']]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors du déplacement du fichier.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt de Rapport | STAGES HELLO</title>

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

        /* SIDEBAR (Identique pour cohérence) */
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

        /* MAIN CONTENT */
        .main {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent);
        }

        /* ZONE D'UPLOAD INNOVANTE */
        .card-upload {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 40px;
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

        /* BARRE DE PROGRESSION SÉQUENTIELLE */
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
            transition: width 1s ease;
        }

        /* CONSEILS CARD */
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

        .progress-container, .success-animation { display: none; }
.drop-zone { cursor: pointer; transition: all 0.3s ease; border: 2px dashed rgba(255,255,255,0.1); padding: 40px; border-radius: 20px; }
.drop-zone:hover, .drop-zone.active { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
#fileInput { display: none; } /* On cache l'input moche */
    </style>
</head>
<body>

<div class="sidebar">
    <a href="#" class="sidebar-brand">
        <i class="fa-solid fa-rocket"></i> STAGES HELLO
    </a>
   <a href="dashEtud.php"><i class="fa-solid fa-grip-vertical"></i> Dashboard</a>
    <a href="listeStage.php" ><i class="fa-solid fa-briefcase"></i> Offres de stage</a>
    <a href="Candidature.php"><i class="fa-solid fa-paper-plane"></i> Mes candidatures</a>
    <a href="MonStage.php" class="active"><i class="fa-solid fa-file-arrow-up"></i> Dépôt de rapport</a>
</div>

<div class="main">
    
    <div class="mb-5" data-aos="fade-down">
        <h2 class="fw-800">Finalisez votre expérience</h2>
        <p class="text-muted">Félicitations pour la fin de votre stage ! Soumettez votre rapport pour validation finale.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7" data-aos="fade-right">
            <div class="card-upload">
                <form id="uploadForm">
                    <label class="drop-zone w-100" id="dropZone">
                        <div class="upload-icon">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <h5 class="fw-bold">Glissez votre rapport ici</h5>
                        <p class="text-muted small">Ou cliquez pour parcourir vos fichiers (Max 20Mo)</p>
                        <input type="file" id="fileInput" accept="application/pdf">
                        <div id="fileDisplay" class="mt-3 fw-bold text-primary"></div>
                    </label>
                    <?php if (!empty($cand['rapport_pdf'])): ?>
    <a href="telecharger_rapport.php?file=<?= $cand['rapport_pdf'] ?>" class="btn btn-outline-primary btn-sm rounded-pill">
        <i class="fa-solid fa-download me-2"></i> Télécharger le rapport
    </a>
<?php else: ?>
    <span class="text-muted small italic">Aucun rapport déposé</span>
<?php endif; ?>

                    <div class="progress-container" id="progressContainer">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Envoi en cours...</small>
                            <small class="fw-bold" id="percentText">0%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="success-animation" id="successArea">
                        <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <h4 class="fw-bold">Rapport Soumis !</h4>
                        <p class="text-muted">Votre encadreur recevra une notification immédiatement.</p>
                    </div>

                    <button type="submit" class="btn-submit-premium" id="submitBtn" disabled>
                        <i class="fa-solid fa-paper-plane me-2"></i> Envoyer mon rapport final
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5" data-aos="fade-left">
            <div class="tips-card mb-4">
                <h5 class="fw-bold mb-4 text-white">Checklist avant envoi</h5>
                <div class="tip-item">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Assurez-vous que le fichier est bien au format <strong>PDF</strong> uniquement.</span>
                </div>
                <div class="tip-item">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Vérifiez que vous avez inclus la page de garde officielle de votre établissement.</span>
                </div>
                <div class="tip-item">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Le fichier ne doit pas dépasser <strong>20 Mo</strong>.</span>
                </div>
            </div>

            <div class="p-4 rounded-4 border border-secondary border-opacity-25 bg-dark bg-opacity-20">
                <h6 class="fw-bold text-white mb-2">Besoin d'aide ?</h6>
                <p class="small text-muted mb-0">Si vous rencontrez des difficultés lors du dépôt, contactez le support technique via le chat en direct ou consultez notre FAQ.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800 });

    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const fileDisplay = document.getElementById('fileDisplay');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const percentText = document.getElementById('percentText');
    const successArea = document.getElementById('successArea');

    fileInput.addEventListener('change', function() {
        if (this.files[0]) {
            fileDisplay.innerText = "Fichier : " + this.files[0].name;
            submitBtn.disabled = false;
        }
    });

    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData();
        formData.append('rapport', fileInput.files[0]);

        let xhr = new XMLHttpRequest();
        progressContainer.style.display = 'block';
        submitBtn.style.display = 'none';

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
                } else {
                    alert(response.message);
                    submitBtn.style.display = 'block';
                    progressContainer.style.display = 'none';
                }
            } catch(e) {
                alert("Erreur de réponse du serveur.");
            }
        };

        xhr.open('POST', window.location.href, true);
        xhr.send(formData);
    });
</script>

</body>
</html>