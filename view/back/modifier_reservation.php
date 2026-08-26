<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'gestionnaire'])) {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';
require_once __DIR__ . '/../../controller/SalleController.php';

$resCtrl = new ReservationController();
$salleCtrl = new SalleController();

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: ' . ($_SESSION['role'] === 'gestionnaire' ? 'gestion_reservations.php' : 'reservations.php')); exit; }
$reservation = $resCtrl->getById($id);
$salles = $salleCtrl->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $resCtrl->update($id, $_POST['id_salle'], $_POST['date_reservation'], $_POST['heure_debut'], $_POST['heure_fin'], $_POST['objet'], $_POST['statut']);
    if ($result === true) { header('Location: ' . ($_SESSION['role'] === 'gestionnaire' ? 'gestion_reservations.php' : 'reservations.php')); exit(); }
    elseif (is_array($result)) { $errors = $result; }
    elseif ($result === false) { $errors = ["Conflit détecté ! La salle est déjà réservée à ce créneau."]; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SallesPro — Modifier Réservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary-color: #602299; --secondary-color: #301934; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background-color: #f5f5f5; min-height: 100vh; }
        .sidebar { width: 250px; background-color: var(--secondary-color); color: white; height: 100vh; position: fixed; left: 0; top: 0; overflow-y: auto; padding: 20px 0; }
        .sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid #4a2d6b; margin-bottom: 20px; }
        .sidebar-menu { list-style: none; padding: 0 10px; }
        .sidebar-menu li { padding: 12px 20px; cursor: pointer; transition: background-color 0.3s; font-size: 14px; border-radius: 4px; margin-bottom: 5px; }
        .sidebar-menu li:hover { background-color: #4a2d6b; }
        .sidebar-menu li a { color: inherit; text-decoration: none; display: block; width: 100%; }
        .sidebar-menu li i { margin-right: 10px; width: 20px; text-align: center; font-size: 16px; }
        .main-content { margin-left: 250px; flex: 1; padding: 20px; width: calc(100% - 250px); }
        .top-nav { display: flex; justify-content: space-between; align-items: center; background-color: white; padding: 12px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; }
        .form-container { background-color: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #381d51; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #381d51; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        .form-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; color: white; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none; }
        .btn-primary { background-color: var(--primary-color); }
        .btn-danger { background-color: #dc3545; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header"><h1><i class="fas fa-building"></i> SallesPro</h1></div>
        <ul class="sidebar-menu">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="dashboard.php" style="color:inherit;text-decoration:none;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="utilisateurs.php" style="color:inherit;text-decoration:none;"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="batiments.php" style="color:inherit;text-decoration:none;"><i class="fas fa-building"></i> Bâtiments</a></li>
            <li><a href="salles.php" style="color:inherit;text-decoration:none;"><i class="fas fa-door-open"></i> Salles</a></li>
            <li style="background-color:#602299;"><a href="reservations.php" style="color:inherit;text-decoration:none;"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <?php else: ?>
            <li style="background-color:#602299;"><a href="gestion_reservations.php" style="color:inherit;text-decoration:none;"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <?php endif; ?>
            <li><a href="../index.php?action=logout" style="color:inherit;text-decoration:none;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <div class="top-nav">
            <h2 style="font-size:18px;color:#381d51;">Modifier la Réservation #<?php echo $id; ?></h2>
            <a href="reservations.php"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" onsubmit="return validateForm(event)">
                <div class="form-group">
                    <label>Salle</label>
                    <select name="id_salle" id="id_salle" class="form-control">
                        <?php foreach ($salles as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php if ($s['id'] == $reservation['id_salle']) echo 'selected'; ?>><?php echo htmlspecialchars($s['nom'] . ' - ' . $s['nom_batiment']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date de réservation</label>
                    <input type="date" name="date_reservation" id="date_reservation" class="form-control" value="<?php echo $reservation['date_reservation']; ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Heure de début</label>
                        <input type="time" name="heure_debut" id="heure_debut" class="form-control" value="<?php echo substr($reservation['heure_debut'],0,5); ?>">
                    </div>
                    <div class="form-group">
                        <label>Heure de fin</label>
                        <input type="time" name="heure_fin" id="heure_fin" class="form-control" value="<?php echo substr($reservation['heure_fin'],0,5); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Objet</label>
                    <input type="text" name="objet" id="objet" class="form-control" value="<?php echo htmlspecialchars($reservation['objet']); ?>">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" class="form-control">
                        <option value="en_attente" <?php if($reservation['statut']==='en_attente') echo 'selected'; ?>>En attente</option>
                        <option value="validee" <?php if($reservation['statut']==='validee') echo 'selected'; ?>>Validée</option>
                        <option value="refusee" <?php if($reservation['statut']==='refusee') echo 'selected'; ?>>Refusée</option>
                        <option value="annulee" <?php if($reservation['statut']==='annulee') echo 'selected'; ?>>Annulée</option>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="reservations.php" class="btn btn-danger"><i class="fas fa-times"></i> Annuler</a>
                </div>
            </form>
        </div>
    </main>
    <script>
    function validateForm(event) {
        var s = document.getElementById('id_salle').value;
        var d = document.getElementById('date_reservation').value;
        var hd = document.getElementById('heure_debut').value;
        var hf = document.getElementById('heure_fin').value;
        var o = document.getElementById('objet').value.trim();

        if (!s || !d || !hd || !hf || !o) {
            alert("Tous les champs sont obligatoires");
            event.preventDefault(); return false;
        }
        if (hd >= hf) {
            alert("L'heure de fin doit être après l'heure de début");
            event.preventDefault(); return false;
        }
        return true;
    }
    </script>
</body>
</html>
