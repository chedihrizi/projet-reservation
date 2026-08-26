<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';

$controller = new ReservationController();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'valider':
                $controller->updateStatus($_POST['id'], 'validee');
                break;
            case 'refuser':
                $controller->updateStatus($_POST['id'], 'refusee', $_POST['motif_refus'] ?? '');
                break;
            case 'delete':
                $controller->delete($_POST['id']);
                break;
        }
        header('Location: reservations.php');
        exit();
    }
}

// Search and filter
$search = $_GET['search'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

if ($search) {
    $reservations = $controller->search($search, $statut_filter ?: null);
} elseif ($statut_filter) {
    $reservations = $controller->getByStatut($statut_filter);
} else {
    $reservations = $controller->getAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Gestion des Réservations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary-color: #602299; --secondary-color: #301934; --accent-color: #4a1a7a; --text-light: #ffffff; --text-dark: #381d51; --bg-light: #f5f5f5; --success-color: #4CAF50; --danger-color: #f44336; --info-color: #2196F3; --warning-color: #FFC107; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background-color: var(--bg-light); min-height: 100vh; color: var(--text-dark); }
        .sidebar { width: 250px; background-color: var(--secondary-color); color: var(--text-light); height: 100vh; position: fixed; left: 0; top: 0; overflow-y: auto; padding: 20px 0; z-index: 1000; }
        .sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-menu { list-style: none; padding: 0 10px; }
        .sidebar-menu li { padding: 12px 20px; cursor: pointer; transition: all 0.3s; font-size: 14px; border-radius: 4px; margin-bottom: 5px; display: flex; align-items: center; }
        .sidebar-menu li:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar-menu li.active { background-color: var(--primary-color); }
        .sidebar-menu li i { width: 24px; text-align: center; font-size: 16px; margin-right: 10px; }
        .sidebar-menu li a { color: inherit; text-decoration: none; display: block; width: 100%; }
        .main-content { margin-left: 250px; flex: 1; padding: 20px; width: calc(100% - 250px); }
        .top-nav { display: flex; justify-content: space-between; align-items: center; background-color: white; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .page-title h1 { font-size: 24px; color: var(--primary-color); }
        .search-bar { display: flex; gap: 10px; align-items: center; }
        .search-bar input, .search-bar select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .search-bar input:focus, .search-bar select:focus { outline: none; border-color: var(--primary-color); }
        .content-section { background-color: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { color: var(--primary-color); font-size: 20px; }
        .btn { padding: 8px 16px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; border: none; text-decoration: none; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--accent-color); }
        .btn-success { background-color: var(--success-color); color: white; }
        .btn-danger { background-color: var(--danger-color); color: white; }
        .btn-warning { background-color: var(--warning-color); color: #333; }
        .btn-info { background-color: var(--info-color); color: white; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        .data-table th { background-color: var(--primary-color); color: white; padding: 12px 15px; text-align: left; font-weight: 500; }
        .data-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .data-table tr:hover { background-color: #f9f9f9; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-en_attente { background-color: #FFF3CD; color: #856404; }
        .status-validee { background-color: #D4EDDA; color: #155724; }
        .status-refusee { background-color: #F8D7DA; color: #721C24; }
        .status-annulee { background-color: #e2e3e5; color: #383d41; }
        .table-actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .motif-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .motif-overlay.active { display: flex; }
        .motif-box { background: white; border-radius: 8px; padding: 25px; width: 400px; max-width: 90%; }
        .motif-box h3 { color: var(--primary-color); margin-bottom: 15px; }
        .motif-box textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; }
        .motif-box .form-buttons { display: flex; gap: 10px; justify-content: flex-end; }
        @media (max-width: 992px) { .sidebar { width: 70px; } .sidebar-header-text, .sidebar-menu li a span { display: none; } .sidebar-menu li i { margin-right: 0; } .main-content { margin-left: 70px; width: calc(100% - 70px); } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-header-text">
                <h1><i class="fas fa-building"></i> SallesPro</h1>
                <p>Panel Admin</p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="utilisateurs.php"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
            <li><a href="batiments.php"><i class="fas fa-building"></i> <span>Bâtiments</span></a></li>
            <li><a href="salles.php"><i class="fas fa-door-open"></i> <span>Salles</span></a></li>
            <li class="active"><a href="reservations.php"><i class="fas fa-calendar-check"></i> <span>Réservations</span></a></li>
            <li><a href="statistiques.php"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
            <li><a href="rapports.php"><i class="fas fa-file-alt"></i> <span>Rapports</span></a></li>
            <li><a href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="page-title"><h1>Gestion des Réservations</h1></div>
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?php if($statut_filter==='en_attente') echo 'selected'; ?>>En attente</option>
                    <option value="validee" <?php if($statut_filter==='validee') echo 'selected'; ?>>Validée</option>
                    <option value="refusee" <?php if($statut_filter==='refusee') echo 'selected'; ?>>Refusée</option>
                    <option value="annulee" <?php if($statut_filter==='annulee') echo 'selected'; ?>>Annulée</option>
                </select>
                <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="content-section">
            <div class="section-header">
                <h2>Liste des Réservations</h2>
                <a href="ajouter_reservation.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Réservation</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Salle</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Objet</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reservations): foreach ($reservations as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['prenom'] . ' ' . $r['nom']); ?></td>
                        <td><?php echo htmlspecialchars($r['nom_salle'] . ' - ' . $r['nom_batiment']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['date_reservation'])); ?></td>
                        <td><?php echo substr($r['heure_debut'],0,5) . ' - ' . substr($r['heure_fin'],0,5); ?></td>
                        <td><?php echo htmlspecialchars(substr($r['objet'], 0, 30)); ?></td>
                        <td><span class="status-badge status-<?php echo $r['statut']; ?>"><?php echo ucfirst(str_replace('_', ' ', $r['statut'])); ?></span></td>
                        <td class="table-actions">
                            <?php if ($r['statut'] === 'en_attente'): ?>
                            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="valider"><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button></form>
                            <button type="button" class="btn btn-danger btn-sm" onclick="openRefuse(<?php echo $r['id']; ?>)"><i class="fas fa-times"></i></button>
                            <?php endif; ?>
                            <a href="modifier_reservation.php?id=<?php echo $r['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" style="text-align:center; padding:20px;">Aucune réservation trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="motif-overlay" id="refuseModal">
        <div class="motif-box">
            <h3>Refuser la réservation</h3>
            <form method="POST">
                <input type="hidden" name="action" value="refuser">
                <input type="hidden" name="id" id="refuseId">
                <textarea name="motif_refus" rows="4" placeholder="Motif du refus..."></textarea>
                <div class="form-buttons">
                    <button type="button" class="btn btn-info btn-sm" onclick="closeRefuse()">Annuler</button>
                    <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openRefuse(id) {
        document.getElementById('refuseId').value = id;
        document.getElementById('refuseModal').classList.add('active');
    }
    function closeRefuse() {
        document.getElementById('refuseModal').classList.remove('active');
    }
    </script>
</body>
</html>
