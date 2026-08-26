<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}

require_once __DIR__ . '/../../controller/UtilisateurController.php';
require_once __DIR__ . '/../../controller/BatimentController.php';
require_once __DIR__ . '/../../controller/SalleController.php';
require_once __DIR__ . '/../../controller/ReservationController.php';

$utilisateurCtrl = new UtilisateurController();
$batimentCtrl = new BatimentController();
$salleCtrl = new SalleController();
$reservationCtrl = new ReservationController();

$totalUtilisateurs = $utilisateurCtrl->countAll();
$totalBatiments = $batimentCtrl->countAll();
$totalSalles = $salleCtrl->countAll();
$sallesDisponibles = $salleCtrl->countDisponibles();
$totalReservations = $reservationCtrl->countAll();
$reservationsEnAttente = $reservationCtrl->countByStatut('en_attente');
$reservationsValidees = $reservationCtrl->countByStatut('validee');
$reservationsRefusees = $reservationCtrl->countByStatut('refusee');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SallesPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #602299;
            --secondary-color: #301934;
            --accent-color: #4a1a7a;
            --text-light: #ffffff;
            --text-dark: #381d51;
            --bg-light: #f5f5f5;
            --success-color: #4CAF50;
            --warning-color: #FFC107;
            --danger-color: #f44336;
            --info-color: #2196F3;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; flex-direction: column; background-color: var(--bg-light); min-height: 100vh; color: var(--text-dark); }
        .sidebar { width: 250px; background-color: var(--secondary-color); color: var(--text-light); height: 100vh; position: fixed; left: 0; top: 0; overflow-y: auto; padding: 20px 0; z-index: 1000; }
        .sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 15px; }
        .sidebar-header-text { display: flex; flex-direction: column; }
        .sidebar-header h1 { font-size: 18px; }
        .sidebar-header p { font-size: 12px; opacity: 0.7; }
        .sidebar-menu { list-style: none; padding: 0 10px; }
        .sidebar-menu li { padding: 12px 20px; cursor: pointer; transition: all 0.3s; font-size: 14px; border-radius: 4px; margin-bottom: 5px; display: flex; align-items: center; }
        .sidebar-menu li:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar-menu li.active { background-color: var(--primary-color); }
        .sidebar-menu li i { width: 24px; text-align: center; font-size: 16px; margin-right: 10px; }
        .sidebar-menu li a { color: inherit; text-decoration: none; display: block; width: 100%; }
        .main-content { margin-left: 250px; flex: 1; padding: 20px; width: calc(100% - 250px); }
        .top-nav { display: flex; justify-content: space-between; align-items: center; background-color: white; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .page-title h1 { font-size: 24px; color: var(--primary-color); }
        .page-title p { font-size: 14px; color: #666; }
        .user-profile { display: flex; align-items: center; gap: 10px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .stat-card .icon { position: absolute; right: 20px; top: 20px; font-size: 40px; opacity: 0.2; color: var(--primary-color); }
        .stat-card h3 { font-size: 14px; color: var(--text-dark); margin-bottom: 10px; font-weight: 500; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: var(--primary-color); margin-bottom: 5px; }
        .stat-card .label { font-size: 12px; color: #666; }
        .content-section { background-color: white; border-radius: 8px; padding: 25px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { color: var(--primary-color); font-size: 20px; }
        .action-buttons { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; border: none; text-decoration: none; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--accent-color); }
        .btn-success { background-color: var(--success-color); color: white; }
        .btn-info { background-color: var(--info-color); color: white; }
        .btn-warning { background-color: var(--warning-color); color: #333; }
        .btn-danger { background-color: var(--danger-color); color: white; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th { background-color: var(--primary-color); color: white; padding: 12px 15px; text-align: left; font-weight: 500; }
        .data-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .data-table tr:hover { background-color: #f9f9f9; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-en_attente { background-color: #FFF3CD; color: #856404; }
        .status-validee { background-color: #D4EDDA; color: #155724; }
        .status-refusee { background-color: #F8D7DA; color: #721C24; }
        .status-annulee { background-color: #e2e3e5; color: #383d41; }
        @media (max-width: 992px) { .sidebar { width: 70px; } .sidebar-header-text { display: none; } .sidebar-menu li a span { display: none; } .sidebar-menu li i { margin-right: 0; } .main-content { margin-left: 70px; width: calc(100% - 70px); } }
        @media (max-width: 768px) { .top-nav { flex-direction: column; gap: 15px; } .stats-grid { grid-template-columns: 1fr; } }
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
            <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="utilisateurs.php"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
            <li><a href="batiments.php"><i class="fas fa-building"></i> <span>Bâtiments</span></a></li>
            <li><a href="salles.php"><i class="fas fa-door-open"></i> <span>Salles</span></a></li>
            <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> <span>Réservations</span></a></li>
            <li><a href="statistiques.php"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
            <li><a href="rapports.php"><i class="fas fa-file-alt"></i> <span>Rapports</span></a></li>
            <li><a href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="page-title">
                <h1>Tableau de bord</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></p>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['prenom'],0,1) . substr($_SESSION['nom'],0,1)); ?></div>
                <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <h3>Utilisateurs</h3>
                <div class="value"><?php echo $totalUtilisateurs; ?></div>
                <div class="label">Total inscrits</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-building"></i></div>
                <h3>Bâtiments</h3>
                <div class="value"><?php echo $totalBatiments; ?></div>
                <div class="label">Enregistrés</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-door-open"></i></div>
                <h3>Salles</h3>
                <div class="value"><?php echo $totalSalles; ?></div>
                <div class="label"><?php echo $sallesDisponibles; ?> disponibles</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Réservations</h3>
                <div class="value"><?php echo $totalReservations; ?></div>
                <div class="label"><?php echo $reservationsEnAttente; ?> en attente</div>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2>Réservations récentes</h2>
                <div class="action-buttons">
                    <a href="reservations.php" class="btn btn-primary"><i class="fas fa-eye"></i> Voir tout</a>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Salle</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $reservations = $reservationCtrl->getAll();
                    $recent = array_slice($reservations, 0, 5);
                    if ($recent):
                        foreach ($recent as $res):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($res['prenom'] . ' ' . $res['nom']); ?></td>
                        <td><?php echo htmlspecialchars($res['nom_salle'] . ' - ' . $res['nom_batiment']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($res['date_reservation'])); ?></td>
                        <td><?php echo substr($res['heure_debut'],0,5) . ' - ' . substr($res['heure_fin'],0,5); ?></td>
                        <td><span class="status-badge status-<?php echo $res['statut']; ?>"><?php echo ucfirst($res['statut']); ?></span></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:20px;">Aucune réservation</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
