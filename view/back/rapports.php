<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';

$controller = new ReservationController();
$results = null;
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');

if (isset($_GET['search'])) {
    $results = $controller->getReservationsByDateRange($date_debut, $date_fin);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Rapports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary-color: #602299; --secondary-color: #301934; --accent-color: #4a1a7a; --text-light: #ffffff; --text-dark: #381d51; --bg-light: #f5f5f5; --success-color: #4CAF50; --danger-color: #f44336; }
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
        .content-section { background-color: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 20px; }
        .section-header h2 { color: var(--primary-color); font-size: 20px; margin-bottom: 15px; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .filter-form .form-group { }
        .filter-form label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .filter-form input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .filter-form input:focus { outline: none; border-color: var(--primary-color); }
        .btn { padding: 8px 16px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; border: none; text-decoration: none; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-success { background-color: var(--success-color); color: white; }
        .btn-info { background-color: #2196F3; color: white; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        .data-table th { background-color: var(--primary-color); color: white; padding: 12px 15px; text-align: left; }
        .data-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .data-table tr:hover { background-color: #f9f9f9; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-en_attente { background-color: #FFF3CD; color: #856404; }
        .status-validee { background-color: #D4EDDA; color: #155724; }
        .status-refusee { background-color: #F8D7DA; color: #721C24; }
        .status-annulee { background-color: #e2e3e5; color: #383d41; }
        .summary { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .summary-item { background: var(--bg-light); padding: 10px 20px; border-radius: 8px; text-align: center; }
        .summary-item .num { font-size: 20px; font-weight: 700; color: var(--primary-color); }
        .summary-item .lbl { font-size: 12px; color: #666; }
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
            <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> <span>Réservations</span></a></li>
            <li><a href="statistiques.php"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
            <li class="active"><a href="rapports.php"><i class="fas fa-file-alt"></i> <span>Rapports</span></a></li>
            <li><a href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="page-title"><h1>Rapports de réservation</h1></div>
        </div>
        <div class="content-section">
            <div class="section-header"><h2>Filtrer par période</h2></div>
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Date début</label>
                    <input type="date" name="date_debut" value="<?php echo $date_debut; ?>">
                </div>
                <div class="form-group">
                    <label>Date fin</label>
                    <input type="date" name="date_fin" value="<?php echo $date_fin; ?>">
                </div>
                <button type="submit" name="search" class="btn btn-primary"><i class="fas fa-search"></i> Générer le rapport</button>
                <?php if ($results !== null): ?>
                <a href="export_rapport.php?date_debut=<?php echo $date_debut; ?>&date_fin=<?php echo $date_fin; ?>" target="_blank" class="btn btn-success"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($results !== null): ?>
        <?php
            $countTotal = count($results);
            $countValidees = 0;
            $countRefusees = 0;
            $countEnAttente = 0;
            foreach ($results as $r) {
                if ($r['statut'] === 'validee') $countValidees++;
                elseif ($r['statut'] === 'refusee') $countRefusees++;
                elseif ($r['statut'] === 'en_attente') $countEnAttente++;
            }
        ?>
        <div class="content-section">
            <div class="section-header"><h2>Résultats du rapport</h2></div>
            <div class="summary">
                <div class="summary-item"><div class="num"><?php echo $countTotal; ?></div><div class="lbl">Total</div></div>
                <div class="summary-item"><div class="num"><?php echo $countValidees; ?></div><div class="lbl">Validées</div></div>
                <div class="summary-item"><div class="num"><?php echo $countRefusees; ?></div><div class="lbl">Refusées</div></div>
                <div class="summary-item"><div class="num"><?php echo $countEnAttente; ?></div><div class="lbl">En attente</div></div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Salle</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Objet</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['prenom'] . ' ' . $r['nom']); ?></td>
                        <td><?php echo htmlspecialchars($r['nom_salle'] . ' - ' . $r['nom_batiment']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['date_reservation'])); ?></td>
                        <td><?php echo substr($r['heure_debut'],0,5) . ' - ' . substr($r['heure_fin'],0,5); ?></td>
                        <td><?php echo htmlspecialchars($r['objet']); ?></td>
                        <td><span class="status-badge status-<?php echo $r['statut']; ?>"><?php echo ucfirst(str_replace('_', ' ', $r['statut'])); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
