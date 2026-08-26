<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';
require_once __DIR__ . '/../../controller/SalleController.php';
require_once __DIR__ . '/../../controller/BatimentController.php';
require_once __DIR__ . '/../../controller/UtilisateurController.php';

$resCtrl = new ReservationController();
$salleCtrl = new SalleController();
$batimentCtrl = new BatimentController();
$userCtrl = new UtilisateurController();

$totalRes = $resCtrl->countAll();
$enAttente = $resCtrl->countByStatut('en_attente');
$validees = $resCtrl->countByStatut('validee');
$refusees = $resCtrl->countByStatut('refusee');
$annulees = $resCtrl->countByStatut('annulee');
$totalSalles = $salleCtrl->countAll();
$sallesDispo = $salleCtrl->countDisponibles();
$totalUsers = $userCtrl->countAll();
$totalBatiments = $batimentCtrl->countAll();

// Reservations per month (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-{$i} month"));
    $monthLabel = date('M Y', strtotime("-{$i} month"));
    $start = $month . '-01';
    $end = date('Y-m-t', strtotime($start));
    $res = $resCtrl->getReservationsByDateRange($start, $end);
    $monthlyData[] = ['label' => $monthLabel, 'count' => count($res)];
}

// Reservations per room
$allRes = $resCtrl->getAll();
$roomStats = [];
foreach ($allRes as $r) {
    $key = $r['nom_salle'];
    if (!isset($roomStats[$key])) $roomStats[$key] = 0;
    $roomStats[$key]++;
}
arsort($roomStats);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .stat-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; }
        .stat-card .value { font-size: 32px; font-weight: 700; color: var(--primary-color); }
        .stat-card .label { font-size: 13px; color: #666; margin-top: 5px; }
        .content-section { background-color: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 20px; }
        .section-header h2 { color: var(--primary-color); font-size: 20px; margin-bottom: 15px; }
        .chart-bar { display: flex; align-items: flex-end; gap: 15px; height: 200px; padding: 20px 0; }
        .bar-item { flex: 1; display: flex; flex-direction: column; align-items: center; }
        .bar { background: var(--primary-color); border-radius: 4px 4px 0 0; width: 100%; min-width: 30px; transition: all 0.3s; position: relative; }
        .bar:hover { background: var(--accent-color); }
        .bar-label { font-size: 11px; margin-top: 5px; text-align: center; color: #666; }
        .bar-value { font-size: 12px; font-weight: 600; margin-bottom: 5px; color: var(--primary-color); }
        .statut-bar { display: flex; gap: 10px; margin-top: 15px; }
        .statut-item { flex: 1; text-align: center; padding: 15px; border-radius: 8px; }
        .statut-item .num { font-size: 24px; font-weight: 700; }
        .statut-item .lbl { font-size: 12px; margin-top: 5px; }
        .se-en_attente { background: #FFF3CD; color: #856404; }
        .se-validee { background: #D4EDDA; color: #155724; }
        .se-refusee { background: #F8D7DA; color: #721C24; }
        .se-annulee { background: #e2e3e5; color: #383d41; }
        .room-list { list-style: none; padding: 0; }
        .room-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .room-list li:last-child { border-bottom: none; }
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
            <li class="active"><a href="statistiques.php"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
            <li><a href="rapports.php"><i class="fas fa-file-alt"></i> <span>Rapports</span></a></li>
            <li><a href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="page-title"><h1>Statistiques d'utilisation</h1></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="value"><?php echo $totalRes; ?></div><div class="label">Total réservations</div></div>
            <div class="stat-card"><div class="value"><?php echo $totalSalles; ?></div><div class="label">Salles totales</div></div>
            <div class="stat-card"><div class="value"><?php echo $sallesDispo; ?></div><div class="label">Salles disponibles</div></div>
            <div class="stat-card"><div class="value"><?php echo $totalBatiments; ?></div><div class="label">Bâtiments</div></div>
            <div class="stat-card"><div class="value"><?php echo $totalUsers; ?></div><div class="label">Utilisateurs</div></div>
        </div>

        <div class="content-section">
            <div class="section-header"><h2>Répartition par statut</h2></div>
            <div class="statut-bar">
                <div class="statut-item se-en_attente"><div class="num"><?php echo $enAttente; ?></div><div class="lbl">En attente</div></div>
                <div class="statut-item se-validee"><div class="num"><?php echo $validees; ?></div><div class="lbl">Validées</div></div>
                <div class="statut-item se-refusee"><div class="num"><?php echo $refusees; ?></div><div class="lbl">Refusées</div></div>
                <div class="statut-item se-annulee"><div class="num"><?php echo $annulees; ?></div><div class="lbl">Annulées</div></div>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header"><h2>Réservations par mois (6 derniers mois)</h2></div>
            <div class="chart-bar">
                <?php
                $maxCount = max(array_column($monthlyData, 'count'));
                $maxHeight = 180;
                foreach ($monthlyData as $md):
                    $h = $maxCount > 0 ? ($md['count'] / $maxCount) * $maxHeight : 0;
                ?>
                <div class="bar-item">
                    <div class="bar-value"><?php echo $md['count']; ?></div>
                    <div class="bar" style="height: <?php echo max($h, 5); ?>px;"></div>
                    <div class="bar-label"><?php echo $md['label']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header"><h2>Salles les plus utilisées</h2></div>
            <ul class="room-list">
                <?php $i = 0; foreach (array_slice($roomStats, 0, 10, true) as $room => $count): ?>
                <li><span><?php echo htmlspecialchars($room); ?></span><strong><?php echo $count; ?> réservation(s)</strong></li>
                <?php if (++$i >= 10) break; endforeach; ?>
            </ul>
        </div>
    </main>
</body>
</html>
