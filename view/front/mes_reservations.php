<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';

$resCtrl = new ReservationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'annuler') {
    $resCtrl->cancelByUser($_POST['id'], $_SESSION['user_id']);
    header('Location: mes_reservations.php');
    exit();
}

$reservations = $resCtrl->getByUtilisateur($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Mes Réservations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --secondary: #1e293b;
            --accent: #06b6d4;
            --bg: #f0fdfa;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        .navbar {
            background: linear-gradient(135deg, #0d9488, #06b6d4);
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 20px rgba(13,148,136,0.3);
        }
        .navbar-brand { display: flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; font-weight: 700; font-size: 20px; }
        .navbar-brand i { font-size: 24px; }
        .navbar-links { display: flex; align-items: center; gap: 8px; }
        .nav-item { color: rgba(255,255,255,0.85); text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 500; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.2); color: #fff; }
        .nav-item.btn-accent { background: rgba(255,255,255,0.95); color: var(--primary-dark); font-weight: 600; }
        .nav-item.btn-accent:hover { background: #fff; }
        .user-badge { display: flex; align-items: center; gap: 8px; color: #fff; font-size: 13px; margin-left: 16px; padding-left: 16px; border-left: 1px solid rgba(255,255,255,0.3); }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }

        .main { max-width: 1100px; margin: 0 auto; padding: 30px 40px; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .page-header h1 { font-size: 24px; font-weight: 700; color: var(--secondary); }
        .btn-new {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-new:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(13,148,136,0.3); }

        /* Stats Row */
        .stats-row {
            display: flex;
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            flex: 1;
            background: var(--card);
            border-radius: 12px;
            padding: 18px 22px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .stat-icon.pending { background: #fef3c7; color: #b45309; }
        .stat-icon.approved { background: #d1fae5; color: #065f46; }
        .stat-icon.rejected { background: #fee2e2; color: #991b1b; }
        .stat-icon.total { background: #e0f2fe; color: #075985; }
        .stat-info h4 { font-size: 22px; font-weight: 700; color: var(--secondary); }
        .stat-info p { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        /* Table Card */
        .table-card {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            background: var(--secondary);
            color: #fff;
            padding: 14px 18px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table tbody td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }
        .data-table tbody tr:hover { background: #f0fdfa; }
        .data-table tbody tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-cancelled { background: #e2e8f0; color: #475569; }

        .btn-cancel {
            padding: 6px 14px;
            background: var(--danger);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-cancel:hover { background: #dc2626; }

        .motif {
            font-size: 11px;
            color: var(--text-muted);
            font-style: italic;
            margin-top: 4px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i { font-size: 56px; color: #cbd5e1; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; color: var(--secondary); margin-bottom: 8px; }
        .empty-state p { font-size: 14px; color: var(--text-muted); margin-bottom: 20px; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .main { padding: 20px 16px; }
            .stats-row { flex-direction: column; }
            .data-table { font-size: 12px; }
            .data-table thead th, .data-table tbody td { padding: 10px 12px; }
            .navbar-links .nav-text { display: none; }
            .user-badge span { display: none; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="../index.php"><i class="fas fa-building"></i> SallesPro</a>
    <div class="navbar-links">
        <a class="nav-item" href="front.php"><i class="fas fa-door-open"></i> <span class="nav-text">Salles</span></a>
        <a class="nav-item" href="ajouter_reservation.php"><i class="fas fa-plus-circle"></i> <span class="nav-text">Réserver</span></a>
        <a class="nav-item active" href="mes_reservations.php"><i class="fas fa-calendar-check"></i> <span class="nav-text">Mes Réservations</span></a>
        <a class="nav-item btn-accent" href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span class="nav-text">Déconnexion</span></a>
        <div class="user-badge">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['prenom'],0,1) . substr($_SESSION['nom'],0,1)); ?></div>
            <span><?php echo htmlspecialchars($_SESSION['prenom']); ?></span>
        </div>
    </div>
</nav>

<div class="main">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check" style="color:var(--primary);"></i> Mes Réservations</h1>
        <a href="ajouter_reservation.php" class="btn-new"><i class="fas fa-plus"></i> Nouvelle réservation</a>
    </div>

    <?php
    $counts = ['en_attente' => 0, 'validee' => 0, 'refusee' => 0];
    foreach ($reservations as $r) {
        if (isset($counts[$r['statut']])) $counts[$r['statut']]++;
    }
    ?>
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-info"><h4><?php echo $counts['en_attente']; ?></h4><p>En attente</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon approved"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info"><h4><?php echo $counts['validee']; ?></h4><p>Validées</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rejected"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info"><h4><?php echo $counts['refusee']; ?></h4><p>Refusées</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-list"></i></div>
            <div class="stat-info"><h4><?php echo count($reservations); ?></h4><p>Total</p></div>
        </div>
    </div>

    <?php if ($reservations): ?>
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Bâtiment</th>
                    <th>Date</th>
                    <th>Horaires</th>
                    <th>Objet</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['nom_salle']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['nom_batiment']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($r['date_reservation'])); ?></td>
                    <td><?php echo substr($r['heure_debut'],0,5) . ' — ' . substr($r['heure_fin'],0,5); ?></td>
                    <td><?php echo htmlspecialchars($r['objet']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $r['statut'] === 'en_attente' ? 'pending' : ($r['statut'] === 'validee' ? 'approved' : ($r['statut'] === 'refusee' ? 'rejected' : 'cancelled')); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $r['statut'])); ?>
                        </span>
                        <?php if ($r['motif_refus']): ?>
                            <div class="motif">Motif : <?php echo htmlspecialchars($r['motif_refus']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (in_array($r['statut'], ['en_attente', 'validee'])): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                            <input type="hidden" name="action" value="annuler">
                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn-cancel"><i class="fas fa-times"></i> Annuler</button>
                        </form>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="table-card">
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>Aucune réservation</h3>
            <p>Vous n'avez pas encore fait de réservation.</p>
            <a href="ajouter_reservation.php" class="btn-new"><i class="fas fa-plus"></i> Réserver maintenant</a>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>