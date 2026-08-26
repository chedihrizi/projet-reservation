<?php
session_start();
require_once __DIR__ . '/../controller/UtilisateurController.php';

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'];
        if ($role === 'admin') { header('Location: back/dashboard.php'); }
        elseif ($role === 'gestionnaire') { header('Location: back/gestion_reservations.php'); }
        else { header('Location: front/front.php'); }
        exit();
    }
    $controller = new UtilisateurController();
    $controller->showLogin();

} elseif ($action === 'register') {
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'];
        if ($role === 'admin') { header('Location: back/dashboard.php'); }
        elseif ($role === 'gestionnaire') { header('Location: back/gestion_reservations.php'); }
        else { header('Location: front/front.php'); }
        exit();
    }
    $controller = new UtilisateurController();
    $controller->showRegister();

} elseif ($action === 'logout') {
    $controller = new UtilisateurController();
    $controller->handleLogout();

} else {
    require __DIR__ . '/front/landing.php';
}
?>
