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
$salles = $salleCtrl->getAllDisponibles();
$selectedSalle = $_GET['salle'] ?? '';
$today = date('Y-m-d');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $resCtrl->add($_SESSION['user_id'], $_POST['id_salle'], $_POST['date_reservation'], $_POST['heure_debut'], $_POST['heure_fin'], $_POST['objet']);
    if ($result === true) { header('Location: mes_reservations.php'); exit(); }
    elseif (is_array($result)) { $errors = $result; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Nouvelle Réservation</title>
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
            --danger: #ef4444;
            --warning-bg: #fef3c7;
            --warning-border: #fcd34d;
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

        .main { max-width: 720px; margin: 0 auto; padding: 30px 40px; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .page-header h1 { font-size: 24px; font-weight: 700; color: var(--secondary); }
        .btn-back {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            background: var(--card);
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back:hover { color: var(--primary); border-color: var(--primary); }

        .info-banner {
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: var(--primary-dark);
        }
        .info-banner i { font-size: 20px; color: var(--primary); }

        .form-card {
            background: var(--card);
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid var(--border);
        }
        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 14px;
        }
        .form-group label i { color: var(--primary); margin-right: 6px; }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: #fafbfc;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(13,148,136,0.1); background: #fff; }
        select.form-control { cursor: pointer; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(13,148,136,0.3); }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 20px;
            color: #991b1b;
            font-size: 13px;
        }
        .error-box div { margin-bottom: 4px; }
        .error-box div:last-child { margin-bottom: 0; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .main { padding: 20px 16px; }
            .form-row { flex-direction: column; gap: 0; }
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
        <a class="nav-item active" href="ajouter_reservation.php"><i class="fas fa-plus-circle"></i> <span class="nav-text">Réserver</span></a>
        <a class="nav-item" href="mes_reservations.php"><i class="fas fa-calendar-check"></i> <span class="nav-text">Mes Réservations</span></a>
        <a class="nav-item btn-accent" href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span class="nav-text">Déconnexion</span></a>
        <div class="user-badge">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['prenom'],0,1) . substr($_SESSION['nom'],0,1)); ?></div>
            <span><?php echo htmlspecialchars($_SESSION['prenom']); ?></span>
        </div>
    </div>
</nav>

<div class="main">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle" style="color:var(--primary);"></i> Nouvelle Réservation</h1>
        <a href="front.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="info-banner">
        <i class="fas fa-info-circle"></i>
        Votre demande sera envoyée au gestionnaire pour validation. Vous recevrez une confirmation par email.
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $err): ?>
                <div>⚠ <?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" onsubmit="return validateForm(event)">
            <div class="form-group">
                <label><i class="fas fa-door-open"></i> Salle</label>
                <select name="id_salle" id="id_salle" class="form-control">
                    <option value="">— Sélectionnez une salle —</option>
                    <?php foreach ($salles as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php if ($s['id'] == $selectedSalle) echo 'selected'; ?>><?php echo htmlspecialchars($s['nom'] . ' — ' . $s['nom_batiment'] . ' (Cap: ' . $s['capacite'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Date</label>
                <input type="date" name="date_reservation" id="date_reservation" class="form-control" value="<?php echo $today; ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Heure de début</label>
                    <input type="time" name="heure_debut" id="heure_debut" class="form-control" value="08:00">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Heure de fin</label>
                    <input type="time" name="heure_fin" id="heure_fin" class="form-control" value="09:00">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-comment"></i> Objet de la réunion</label>
                <input type="text" name="objet" id="objet" class="form-control" placeholder="Ex: Réunion d'équipe, Présentation, Formation...">
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Envoyer la demande</button>
        </form>
    </div>
</div>

<script>
function validateForm(event) {
    var s = document.getElementById('id_salle').value;
    var d = document.getElementById('date_reservation').value;
    var hd = document.getElementById('heure_debut').value;
    var hf = document.getElementById('heure_fin').value;
    var o = document.getElementById('objet').value.trim();

    if (!s) { alert("Veuillez sélectionner une salle"); event.preventDefault(); return false; }
    if (!d) { alert("Veuillez choisir une date"); event.preventDefault(); return false; }
    if (d < '<?php echo $today; ?>') { alert("La date ne peut pas être dans le passé"); event.preventDefault(); return false; }
    if (!hd || !hf) { alert("Veuillez remplir les heures"); event.preventDefault(); return false; }
    if (!o) { alert("L'objet est obligatoire"); event.preventDefault(); return false; }
    if (o.length > 300) { alert("L'objet ne doit pas dépasser 300 caractères"); event.preventDefault(); return false; }
    if (hd >= hf) { alert("L'heure de fin doit être après l'heure de début"); event.preventDefault(); return false; }
    return true;
}
</script>

</body>
</html>