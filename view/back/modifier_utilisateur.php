<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/UtilisateurController.php';

$controller = new UtilisateurController();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: utilisateurs.php'); exit; }
$user = $controller->getById($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->update($id, $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'], $_POST['role']);
    if ($result === true) { header('Location: utilisateurs.php'); exit(); }
    elseif (is_array($result)) { $errors = $result; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Utilisateur</title>
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
        .form-control:focus { outline: none; border-color: #381d51; box-shadow: 0 0 0 2px rgba(56,29,81,0.2); }
        .form-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; color: white; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none; transition: background-color 0.3s; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header"><h1><i class="fas fa-building"></i> SallesPro</h1></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" style="color:inherit;text-decoration:none;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li style="background-color:#602299;"><a href="utilisateurs.php" style="color:inherit;text-decoration:none;"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="batiments.php" style="color:inherit;text-decoration:none;"><i class="fas fa-building"></i> Bâtiments</a></li>
            <li><a href="salles.php" style="color:inherit;text-decoration:none;"><i class="fas fa-door-open"></i> Salles</a></li>
            <li><a href="reservations.php" style="color:inherit;text-decoration:none;"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <li><a href="../index.php?action=logout" style="color:inherit;text-decoration:none;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <div class="top-nav">
            <h2 style="font-size:18px;color:#381d51;">Modifier l'Utilisateur</h2>
            <a href="utilisateurs.php"><i class="fas fa-arrow-left"></i> Retour</a>
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
                    <label>Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" value="<?php echo htmlspecialchars($user['nom']); ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo htmlspecialchars($user['prenom']); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" value="<?php echo htmlspecialchars($user['telephone']); ?>">
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <select name="role" id="role" class="form-control">
                        <option value="utilisateur" <?php if($user['role']==='utilisateur') echo 'selected'; ?>>Utilisateur</option>
                        <option value="gestionnaire" <?php if($user['role']==='gestionnaire') echo 'selected'; ?>>Gestionnaire</option>
                        <option value="admin" <?php if($user['role']==='admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="utilisateurs.php" class="btn btn-danger"><i class="fas fa-times"></i> Annuler</a>
                </div>
            </form>
        </div>
    </main>
    <script>
    function validateForm(event) {
        var nom = document.getElementById('nom').value.trim();
        var prenom = document.getElementById('prenom').value.trim();
        var email = document.getElementById('email').value.trim();
        var telephone = document.getElementById('telephone').value.trim();

        if (!nom || !prenom || !email || !telephone) {
            alert("Tous les champs sont obligatoires");
            event.preventDefault();
            return false;
        }
        if (!email.includes('@') || !email.includes('.')) {
            alert("Format d'email invalide");
            event.preventDefault();
            return false;
        }
        if (!/^[0-9]{8}$/.test(telephone)) {
            alert("Le téléphone doit contenir exactement 8 chiffres");
            event.preventDefault();
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
