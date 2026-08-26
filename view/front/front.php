<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';
require_once __DIR__ . '/../../controller/SalleController.php';

$resCtrl = new ReservationController();
$salleCtrl = new SalleController();

$rooms = $salleCtrl->getAllDisponibles();
$today = date('Y-m-d');
$calendarMonth = intval($_GET['month'] ?? date('m'));
$calendarYear = intval($_GET['year'] ?? date('Y'));
$calendarData = $resCtrl->getCalendarData($calendarYear, $calendarMonth);

$firstDay = mktime(0, 0, 0, $calendarMonth, 1, $calendarYear);
$daysInMonth = date('t', $firstDay);
$startDay = date('w', $firstDay);
$monthName = date('F Y', $firstDay);
$prevMonth = $calendarMonth - 1;
$prevYear = $calendarYear;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $calendarMonth + 1;
$nextYear = $calendarYear;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$eventsByDay = [];
foreach ($calendarData as $evt) {
    $day = intval(date('d', strtotime($evt['date_reservation'])));
    if (!isset($eventsByDay[$day])) $eventsByDay[$day] = [];
    $eventsByDay[$day][] = $evt;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Salles Disponibles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #99f6e4;
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

        /* Top Navbar */
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
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 20px;
        }
        .navbar-brand i { font-size: 24px; }
        .navbar-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-item {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.2); color: #fff; }
        .nav-item.btn-accent {
            background: rgba(255,255,255,0.95);
            color: var(--primary-dark);
            font-weight: 600;
        }
        .nav-item.btn-accent:hover { background: #fff; transform: translateY(-1px); }
        .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            font-size: 13px;
            margin-left: 16px;
            padding-left: 16px;
            border-left: 1px solid rgba(255,255,255,0.3);
        }
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        /* Main Content */
        .main { max-width: 1200px; margin: 0 auto; padding: 30px 40px; }

        /* Welcome */
        .welcome {
            background: linear-gradient(135deg, #0d9488, #06b6d4);
            border-radius: 16px;
            padding: 35px 40px;
            color: #fff;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .welcome::after {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }
        .welcome h1 { font-size: 26px; font-weight: 700; margin-bottom: 6px; }
        .welcome p { font-size: 15px; opacity: 0.85; }

        /* Section */
        .section {
            background: var(--card);
            border-radius: 14px;
            padding: 28px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid var(--border);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .section-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-header h2 i { color: var(--primary); }

        /* Calendar */
        .cal-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .cal-nav h3 { font-size: 17px; font-weight: 600; text-transform: capitalize; color: var(--secondary); }
        .cal-nav-btns { display: flex; gap: 8px; }
        .cal-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }
        .cal-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .calendar { width: 100%; border-collapse: collapse; }
        .calendar th {
            background: var(--secondary);
            color: #fff;
            padding: 10px 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .calendar th:first-child { border-radius: 8px 0 0 0; }
        .calendar th:last-child { border-radius: 0 8px 0 0; }
        .calendar td {
            border: 1px solid var(--border);
            padding: 8px;
            vertical-align: top;
            height: 90px;
            font-size: 12px;
            background: #fff;
        }
        .calendar td.empty { background: #f8fafc; }
        .calendar td.today { background: #f0fdfa; border-color: var(--primary); }
        .day-num { font-weight: 700; color: var(--secondary); margin-bottom: 5px; font-size: 14px; }
        .event {
            background: var(--primary);
            color: #fff;
            padding: 3px 7px;
            border-radius: 5px;
            margin-bottom: 3px;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .event.pending { background: var(--warning); color: #78350f; }
        .legend {
            display: flex;
            gap: 20px;
            margin-top: 16px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .legend span { display: flex; align-items: center; gap: 6px; }
        .dot { width: 12px; height: 12px; border-radius: 4px; display: inline-block; }
        .dot-confirmed { background: var(--primary); }
        .dot-pending { background: var(--warning); }

        /* Room Cards */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        .room-card {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all 0.3s;
        }
        .room-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .room-card-top {
            background: linear-gradient(135deg, #0d9488, #06b6d4);
            padding: 20px 22px;
            color: #fff;
        }
        .room-card-top h3 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .room-card-top .building { font-size: 13px; opacity: 0.85; }
        .room-card-body { padding: 20px 22px; }
        .room-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 14px;
        }
        .room-stat {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .room-stat i { color: var(--primary); font-size: 14px; }
        .equipments {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .equipments i { margin-right: 4px; }
        .btn-book {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-book:hover { background: var(--primary-dark); transform: translateY(-1px); }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .main { padding: 20px 16px; }
            .welcome { padding: 24px 20px; }
            .welcome h1 { font-size: 20px; }
            .rooms-grid { grid-template-columns: 1fr; }
            .calendar td { height: 60px; min-width: 50px; padding: 4px; }
            .navbar-links .nav-text { display: none; }
            .user-badge span { display: none; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="../index.php">
        <i class="fas fa-building"></i> SallesPro
    </a>
    <div class="navbar-links">
        <a class="nav-item active" href="front.php"><i class="fas fa-door-open"></i> <span class="nav-text">Salles</span></a>
        <a class="nav-item" href="ajouter_reservation.php"><i class="fas fa-plus-circle"></i> <span class="nav-text">Réserver</span></a>
        <a class="nav-item" href="mes_reservations.php"><i class="fas fa-calendar-check"></i> <span class="nav-text">Mes Réservations</span></a>
        <a class="nav-item btn-accent" href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span class="nav-text">Déconnexion</span></a>
        <div class="user-badge">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['prenom'],0,1) . substr($_SESSION['nom'],0,1)); ?></div>
            <span><?php echo htmlspecialchars($_SESSION['prenom']); ?></span>
        </div>
    </div>
</nav>

<div class="main">
    <div class="welcome">
        <h1>Bonjour, <?php echo htmlspecialchars($_SESSION['prenom']); ?></h1>
        <p>Consultez les salles disponibles et réservez votre prochain créneau</p>
    </div>

    <!-- Calendar -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-calendar-alt"></i> Calendrier des disponibilités</h2>
        </div>
        <div class="cal-nav">
            <a class="cal-btn" href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>"><i class="fas fa-chevron-left"></i></a>
            <h3><?php echo $monthName; ?></h3>
            <a class="cal-btn" href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>"><i class="fas fa-chevron-right"></i></a>
        </div>
        <table class="calendar">
            <thead>
                <tr><th>Dim</th><th>Lun</th><th>Mar</th><th>Mer</th><th>Jeu</th><th>Ven</th><th>Sam</th></tr>
            </thead>
            <tbody>
                <?php
                $day = 1;
                for ($row = 0; $row < 6; $row++) {
                    if ($day > $daysInMonth) break;
                    echo '<tr>';
                    for ($col = 0; $col < 7; $col++) {
                        if (($row === 0 && $col < $startDay) || $day > $daysInMonth) {
                            echo '<td class="empty"></td>';
                        } else {
                            $isToday = ($day == date('d') && $calendarMonth == date('m') && $calendarYear == date('Y'));
                            echo '<td' . ($isToday ? ' class="today"' : '') . '>';
                            echo '<div class="day-num">' . $day . '</div>';
                            if (isset($eventsByDay[$day])) {
                                foreach ($eventsByDay[$day] as $evt) {
                                    $cls = $evt['statut'] === 'validee' ? '' : ' pending';
                                    echo '<div class="event' . $cls . '" title="' . htmlspecialchars($evt['nom_salle'] . ' ' . substr($evt['heure_debut'],0,5) . '-' . substr($evt['heure_fin'],0,5)) . '">' . htmlspecialchars(substr($evt['heure_debut'],0,5) . ' ' . $evt['nom_salle']) . '</div>';
                                }
                            }
                            echo '</td>';
                            $day++;
                        }
                    }
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <div class="legend">
            <span><span class="dot dot-confirmed"></span> Confirmée</span>
            <span><span class="dot dot-pending"></span> En attente</span>
        </div>
    </div>

    <!-- Available Rooms -->
    <div class="section">
        <div class="section-header">
            <h2><i class="fas fa-door-open"></i> Salles disponibles</h2>
            <a href="ajouter_reservation.php" class="btn-book" style="width:auto;padding:10px 22px;border-radius:10px;"><i class="fas fa-plus"></i> Nouvelle réservation</a>
        </div>
        <div class="rooms-grid">
            <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <div class="room-card-top">
                    <h3><?php echo htmlspecialchars($room['nom']); ?></h3>
                    <div class="building"><i class="fas fa-building"></i> <?php echo htmlspecialchars($room['nom_batiment']); ?> — Étage <?php echo $room['etage']; ?></div>
                </div>
                <div class="room-card-body">
                    <div class="room-stats">
                        <div class="room-stat"><i class="fas fa-users"></i> <?php echo $room['capacite']; ?> personnes</div>
                        <div class="room-stat"><i class="fas fa-layer-group"></i> Étage <?php echo $room['etage']; ?></div>
                    </div>
                    <div class="equipments"><i class="fas fa-tools"></i> <?php echo htmlspecialchars($room['equipements']); ?></div>
                    <a href="ajouter_reservation.php?salle=<?php echo $room['id']; ?>" class="btn-book"><i class="fas fa-calendar-plus"></i> Réserver cette salle</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>