<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/SalleController.php';
require_once __DIR__ . '/../../controller/BatimentController.php';

$batimentCtrl = new BatimentController();
$salleCtrl = new SalleController();
$batiments = $batimentCtrl->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $salleCtrl->add(
        $_POST['nom'], $_POST['id_batiment'], $_POST['etage'], $_POST['capacite'],
        $_POST['equipements'], $_POST['description'],
        isset($_POST['disponible']) ? 1 : 0,
        isset($_POST['statut_maintenance']) ? 1 : 0
    );
    if ($result === true) { header('Location: salles.php'); exit(); }
    elseif (is_array($result)) { $errors = $result; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Salle</title>
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
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
        .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; }
        .form-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; color: white; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none; }
        .btn-primary { background-color: var(--primary-color); }
        .btn-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header"><h1><i class="fas fa-building"></i> SallesPro</h1></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" style="color:inherit;text-decoration:none;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="utilisateurs.php" style="color:inherit;text-decoration:none;"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="batiments.php" style="color:inherit;text-decoration:none;"><i class="fas fa-building"></i> Bâtiments</a></li>
            <li style="background-color:#602299;"><a href="salles.php" style="color:inherit;text-decoration:none;"><i class="fas fa-door-open"></i> Salles</a></li>
            <li><a href="reservations.php" style="color:inherit;text-decoration:none;"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <li><a href="../index.php?action=logout" style="color:inherit;text-decoration:none;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <div class="top-nav">
            <h2 style="font-size:18px;color:#381d51;">Ajouter une Salle</h2>
            <a href="salles.php"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div style="color:red;background:#f8d7da;padding:10px;border-radius:4px;margin-bottom:15px;">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" onsubmit="return validateForm(event)">
                <div class="form-group">
                    <label>Nom de la salle</label>
                    <input type="text" name="nom" id="nom" class="form-control">
                </div>
                <div class="form-group">
                    <label>Bâtiment</label>
                    <select name="id_batiment" id="id_batiment" class="form-control">
                        <option value="">Sélectionnez un bâtiment</option>
                        <?php foreach ($batiments as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Étage</label>
                        <input type="number" name="etage" id="etage" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Capacité (personnes)</label>
                        <input type="number" name="capacite" id="capacite" class="form-control" value="10">
                    </div>
                </div>
                <div class="form-group">
                    <label>Équipements</label>
                    <input type="text" name="equipements" id="equipements" class="form-control" placeholder="Ex: Vidéoprojecteur, tableau blanc, Wi-Fi">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Options</label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="disponible" id="disponible" checked>
                        <label for="disponible">Disponible</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" name="statut_maintenance" id="statut_maintenance">
                        <label for="statut_maintenance">En maintenance</label>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="salles.php" class="btn btn-danger"><i class="fas fa-times"></i> Annuler</a>
                </div>
            </form>
        </div>
    </main>
    <script>
    function validateForm(event) {
        var nom = document.getElementById('nom').value.trim();
        var batiment = document.getElementById('id_batiment').value;
        var capacite = document.getElementById('capacite').value;

        if (!nom) { alert("Le nom est obligatoire"); event.preventDefault(); return false; }
        if (!batiment) { alert("Veuillez sélectionner un bâtiment"); event.preventDefault(); return false; }
        if (!capacite || capacite < 1) { alert("La capacité doit être d'au moins 1"); event.preventDefault(); return false; }
        return true;
    }
    </script>
</body>
</html>
