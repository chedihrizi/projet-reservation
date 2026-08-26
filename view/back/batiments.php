<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/BatimentController.php';

$controller = new BatimentController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $controller->delete($_POST['id']);
    header('Location: batiments.php');
    exit();
}

$batiments = $controller->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Bâtiments</title>
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
        .content-section { background-color: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { color: var(--primary-color); font-size: 20px; }
        .btn { padding: 8px 16px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; border: none; text-decoration: none; }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--accent-color); }
        .btn-warning { background-color: #FFC107; color: #333; }
        .btn-danger { background-color: var(--danger-color); color: white; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th { background-color: var(--primary-color); color: white; padding: 12px 15px; text-align: left; font-weight: 500; }
        .data-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .data-table tr:hover { background-color: #f9f9f9; }
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
            <li class="active"><a href="batiments.php"><i class="fas fa-building"></i> <span>Bâtiments</span></a></li>
            <li><a href="salles.php"><i class="fas fa-door-open"></i> <span>Salles</span></a></li>
            <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> <span>Réservations</span></a></li>
            <li><a href="statistiques.php"><i class="fas fa-chart-bar"></i> <span>Statistiques</span></a></li>
            <li><a href="rapports.php"><i class="fas fa-file-alt"></i> <span>Rapports</span></a></li>
            <li><a href="../index.php?action=logout"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="top-nav">
            <div class="page-title"><h1>Gestion des Bâtiments</h1></div>
        </div>
        <div class="content-section">
            <div class="section-header">
                <h2>Liste des Bâtiments</h2>
                <a href="ajouter_batiment.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouveau Bâtiment</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Étages</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batiments as $b): ?>
                    <tr>
                        <td><?php echo $b['id']; ?></td>
                        <td><?php echo htmlspecialchars($b['nom']); ?></td>
                        <td><?php echo htmlspecialchars($b['adresse']); ?></td>
                        <td><?php echo $b['nombre_etages']; ?></td>
                        <td><?php echo htmlspecialchars(substr($b['description'], 0, 50)); ?>...</td>
                        <td>
                            <a href="modifier_batiment.php?id=<?php echo $b['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            <button onclick="confirmDelete(<?php echo $b['id']; ?>)" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>
    <script>
    function confirmDelete(id) {
        if (confirm('Êtes-vous sûr ? Cela supprimera aussi toutes les salles de ce bâtiment.')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
    </script>
</body>
</html>
