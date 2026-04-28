<?php
session_start();
include('../Auth/config_db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'entreprise') {
    header('Location: ../Auth/connexion.php');
    exit();
}

$id_ent = $_SESSION['user_id'];

// 1. AUTOMATISATION DES STATUTS
$pdo->query("UPDATE OFFRE_STAGE SET statut = 'fermee' WHERE date_limite < CURDATE() AND statut = 'ouverte'");

// 2. SUPPRESSION
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM OFFRE_STAGE WHERE id_offre = ? AND id_entreprise = ?");
    $stmt->execute([$_GET['delete_id'], $id_ent]);
    header('Location: offrePub.php');
    exit();
}

// 3. MODIFICATION
if (isset($_POST['update_offre'])) {
    $sql = "UPDATE OFFRE_STAGE SET titre=?, lieu=?, type_stage=?, duree=?, date_limite=?, description=? WHERE id_offre=? AND id_entreprise=?";
    $pdo->prepare($sql)->execute([
        $_POST['titre'], $_POST['lieu'], $_POST['type_stage'], 
        $_POST['duree'], $_POST['date_limite'], $_POST['description'], 
        $_POST['id_offre'], $id_ent
    ]);
    header('Location: offrePub.php');
    exit();
}

// 4. RÉCUPÉRATION UNIQUE (On utilise une seule variable propre)
$stmt = $pdo->prepare("SELECT * FROM OFFRE_STAGE WHERE id_entreprise = ? ORDER BY date_limite DESC");
$stmt->execute([$id_ent]);
$liste_offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_offres = count($liste_offres);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes offres publiées | Espace Entreprise</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #1a1d2d;
            --sidebar-dark: #111422;
            --card-dark: #23273a;
            --text-muted: #8a8d9a;
            --accent-blue: #3b82f6;
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
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0 25px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-blue);
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
            border-left: 4px solid var(--accent-blue);
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
        }

        /* HEADER BOX */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 20px;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--accent-blue), #2563eb);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* TABLE CARD */
        .card-table {
            background: var(--card-dark);
            border-radius: 18px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .table { color: #e2e8f0; vertical-align: middle; margin-bottom: 0; }
        .table thead th { 
            background: rgba(0,0,0,0.2); 
            color: var(--text-muted); 
            border: none; 
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 18px 15px;
        }

        .table tbody tr { transition: 0.2s; border-color: rgba(255,255,255,0.05); }
        .table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .table td { padding: 18px 15px; }

        /* BADGES & BUTTONS */
        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 8px;
            margin-left: 5px;
            transition: 0.3s;
            background: #2d3248;
            color: white;
        }

        .btn-view:hover { background: var(--accent-blue); }
        .btn-edit:hover { background: #f59e0b; color: black; }
        .btn-delete:hover { background: #ef4444; }

        .text-info-small { font-size: 0.8rem; color: var(--text-muted); }

       .row-highlight {
    background-color: #e0f2fe !important; /* Un bleu ciel très doux qui ressort sur le blanc */
    transition: all 0.3s ease-in-out;
    transform: scale(1.01); /* Légère prise de volume */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Ombre portée pour décoller la ligne du fond */
    position: relative;
    z-index: 5;
}

.row-highlight td {
    font-weight: 900 !important;   /* Texte très épais */
    color: #1e3a8a !important;     /* Bleu marine très foncé (presque noir) pour contraster avec le bleu ciel */
    font-size: 1.05rem;            /* On grossit un peu les lettres */
    border-bottom: 2px solid #3b82f6 !important; /* Soulignement bleu vif */
}

/* On fait ressortir l'icône de l'œil sur la ligne active */
.row-highlight .btn-view {
    background-color: #3b82f6 !important;
    color: white !important;
    transform: scale(1.1);
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
        <a href="pubOffre.php" class="nav-link "><i class="fa-solid fa-plus-circle"></i> Publier une offre</a>
        <a href="OffrePub.php" class="nav-link active"><i class="fa-solid fa-list-check"></i> Mes offres</a>
        <a href="gestCand.php" class="nav-link"><i class="fa-solid fa-users-rectangle"></i> Candidatures</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">

         <a href="paramEnt.php" class="nav-link"><i class="fa-solid fa-gear"></i> Paramètres</a>
        <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
<a href="../Auth/deconnexion.php" class="nav-link text-danger" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?')">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Déconnexion</span>
</a>    </nav>
</div>

<div class="main">

    <div class="header-box">
        <div>
            <h2 class="fw-bold mb-1">Mes offres publiées</h2>
            <p class="text-muted mb-0">Total :</p> <span class="text-white fw-bold">
        <?= $total_offres; ?> offre<?= ($total_offres > 1) ? 's' : ''; ?>
    </span> enregistrée<?= ($total_offres > 1) ? 's' : ''; ?>
        </div>

       <a href="pubOffre.php" class="btn-add text-decoration-none">
            <i class="fa-solid fa-plus me-2"></i> Nouvelle offre
        </a>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Offre & Lieu</th>
                        <th>Type</th>
                        <th>Durée</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
               <tbody>
    <?php if (empty($liste_offres)): ?>
        <tr>
            <td colspan="6" class="text-center py-4 text-muted">Aucune offre publiée pour le moment.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($liste_offres as $offre): 
            $date_limite = new DateTime($offre['date_limite']);
            $aujourdhui = new DateTime(date('Y-m-d'));
            $interval = $aujourdhui->diff($date_limite);
            $expiree = ($date_limite < $aujourdhui);
        ?>
        <tr>
            <td>
                <div class="fw-bold"><?= htmlspecialchars($offre['titre']) ?></div>
                <div class="text-info-small">
                    <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($offre['lieu']) ?>
                </div>
            </td>
            <td><span class="badge bg-dark border border-secondary"><?= htmlspecialchars($offre['type_stage']) ?></span></td>
            <td><?= htmlspecialchars($offre['duree']) ?></td>
            <td>
                <div class="fw-600"><?= date('d/m/Y', strtotime($offre['date_limite'])) ?></div>
                <div class="text-info-small <?= $expiree ? 'text-danger' : '' ?>">
                    <?= $expiree ? "Expiré" : "Dans " . $interval->days . " jours"; ?>
                </div>
            </td>
            <td>
                <?php if ($offre['statut'] == 'ouverte'): ?>
                    <span class="badge-status bg-success"><i class="fa-solid fa-check-circle me-1"></i> Active</span>
                <?php else: ?>
                    <span class="badge-status bg-danger text-white"><i class="fa-solid fa-clock me-1"></i> Terminée</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <button class="btn-action btn-view" onclick="illuminerLigne(this)" title="Visualiser">
    <i class="fa-solid fa-eye"></i>
</button>

                <button class="btn-action btn-edit" onclick='ouvrirModification(<?= json_encode($offre); ?>)' title="Modifier">
                    <i class="fa-solid fa-pen"></i>
                </button>

                <a href="offrePub.php?delete_id=<?= $offre['id_offre'] ?>" 
                   class="btn-action btn-delete" 
                   onclick="return confirm('Supprimer cette offre ?')" title="Supprimer">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>

    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            <li class="page-item disabled"><a class="page-link bg-dark border-secondary text-muted" href="#">Précédent</a></li>
            <li class="page-item active"><a class="page-link bg-primary border-primary" href="#">1</a></li>
            <li class="page-item"><a class="page-link bg-dark border-secondary text-white" href="#">2</a></li>
            <li class="page-item"><a class="page-link bg-dark border-secondary text-white" href="#">Suivant</a></li>
        </ul>
    </nav>

</div>

</body>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">Modifier l'offre</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
            <input type="hidden" name="id_offre" id="edit_id">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Titre</label>
                    <input type="text" name="titre" id="edit_titre" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Lieu</label>
                    <input type="text" name="lieu" id="edit_lieu" class="form-control bg-dark text-white border-secondary">
                </div>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" id="edit_desc" class="form-control bg-dark text-white border-secondary" rows="4"></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Durée</label>
                    <input type="text" name="duree" id="edit_duree" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Type</label>
                    <input type="text" name="type_stage" id="edit_type" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Date Limite</label>
                    <input type="date" name="date_limite" id="edit_date" class="form-control bg-dark text-white border-secondary">
                </div>
            </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" name="update_offre" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function ouvrirModification(offre) {
    // On remplit les champs du modal avec les données de l'offre
    document.getElementById('edit_id').value = offre.id_offre;
    document.getElementById('edit_titre').value = offre.titre;
    document.getElementById('edit_lieu').value = offre.lieu;
    document.getElementById('edit_desc').value = offre.description;
    document.getElementById('edit_duree').value = offre.duree;
    document.getElementById('edit_type').value = offre.type_stage;
    document.getElementById('edit_date').value = offre.date_limite;

    // On affiche le modal (Bootstrap)
    var myModal = new bootstrap.Modal(document.getElementById('editModal'));
    myModal.show();
}

function illuminerLigne(bouton) {
    // 1. On récupère toutes les lignes du tableau
    const toutesLesLignes = document.querySelectorAll('tbody tr');

    // 2. On retire la classe d'illumination de toutes les lignes
    toutesLesLignes.forEach(ligne => {
        ligne.classList.remove('row-highlight');
    });

    // 3. On remonte du bouton vers la ligne parente (tr) et on lui ajoute la classe
    const ligneActuelle = bouton.closest('tr');
    ligneActuelle.classList.add('row-highlight');
    
    // Optionnel : Un petit message dans la console pour déboguer
    console.log("Ligne sélectionnée avec succès !");
}

</script>
</html>