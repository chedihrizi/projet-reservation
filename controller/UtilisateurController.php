<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../Model/Utilisateur.php';

class UtilisateurController {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $query = $this->db->prepare("SELECT * FROM utilisateur ORDER BY id DESC");
        $query->execute();
        return $query->fetchAll();
    }

    public function getById($id) {
        $query = $this->db->prepare("SELECT * FROM utilisateur WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch();
    }

    public function getByEmail($email) {
        $query = $this->db->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $query->execute([$email]);
        return $query->fetch();
    }

    public function add($nom, $prenom, $email, $mot_de_passe, $telephone, $role) {
        if ($this->emailExists($email)) return ['Cet email est déjà utilisé'];

        $mdp = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $query = $this->db->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role) VALUES (?, ?, ?, ?, ?, ?)");
        return $query->execute([$nom, $prenom, $email, $mdp, $telephone, $role]);
    }

    public function update($id, $nom, $prenom, $email, $telephone, $role) {
        $query = $this->db->prepare("UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, role = ? WHERE id = ?");
        return $query->execute([$nom, $prenom, $email, $telephone, $role, $id]);
    }

    public function updatePassword($id, $mot_de_passe) {
        $mdp = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $query = $this->db->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
        return $query->execute([$mdp, $id]);
    }

    public function delete($id) {
        try {
            $queryRes = $this->db->prepare("DELETE FROM reservation WHERE id_utilisateur = ?");
            $queryRes->execute([$id]);

            $query = $this->db->prepare("DELETE FROM utilisateur WHERE id = ?");
            return $query->execute([$id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function exists($id) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id = ?");
        $query->execute([$id]);
        return $query->fetchColumn() > 0;
    }

    public function emailExists($email) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
        $query->execute([$email]);
        return $query->fetchColumn() > 0;
    }

    public function getAllByRole($role) {
        $query = $this->db->prepare("SELECT * FROM utilisateur WHERE role = ? ORDER BY nom");
        $query->execute([$role]);
        return $query->fetchAll();
    }

    public function countAll() {
        $query = $this->db->query("SELECT COUNT(*) FROM utilisateur");
        return $query->fetchColumn();
    }

    public function authenticate($email, $mot_de_passe) {
        $user = $this->getByEmail($email);
        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }

    // === Login ===
    public function showLogin() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';

            $user = $this->authenticate($email, $mot_de_passe);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: back/dashboard.php');
                } elseif ($user['role'] === 'gestionnaire') {
                    header('Location: back/gestion_reservations.php');
                } else {
                    header('Location: front/front.php');
                }
                exit();
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        }
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — SallesPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #301934 0%, #602299 30%, #0d9488 70%, #06b6d4 100%);
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header .logo {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            background: linear-gradient(135deg, #602299, #0d9488);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 26px;
            color: #fff;
        }
        .login-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .login-header p { font-size: 14px; color: #64748b; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #1e293b; font-size: 13px; }
        .form-group input {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: #f8fafc;
        }
        .form-group input:focus { border-color: #602299; outline: none; box-shadow: 0 0 0 4px rgba(96,34,153,0.1); background: #fff; }
        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #602299, #0d9488);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(96,34,153,0.3); }
        .error {
            background-color: #fef2f2; color: #991b1b;
            padding: 12px; border-radius: 10px;
            margin-bottom: 18px; text-align: center; font-size: 13px;
            border: 1px solid #fecaca;
        }
        .links { text-align: center; margin-top: 24px; }
        .links a { color: #0d9488; text-decoration: none; font-size: 14px; font-weight: 500; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo"><i class="fas fa-building"></i></div>
            <h1>Bienvenue</h1>
            <p>Connectez-vous à votre compte SallesPro</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="text" id="email" name="email" placeholder="votre@email.com">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Votre mot de passe">
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="links">
            <a href="index.php?action=register">Créer un compte</a> &nbsp;·&nbsp;
            <a href="index.php">← Accueil</a>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        var email = document.getElementById('email').value.trim();
        var mdp = document.getElementById('mot_de_passe').value.trim();
        if (!email) { alert("Veuillez saisir votre email"); e.preventDefault(); return false; }
        if (!mdp) { alert("Veuillez saisir votre mot de passe"); e.preventDefault(); return false; }
    });
    </script>
</body>
</html>
        <?php
    }

    // === Register ===
    public function showRegister() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';

            $result = $this->add($nom, $prenom, $email, $mot_de_passe, $telephone, 'utilisateur');
            if (is_array($result)) {
                $error = $result[0];
            } else {
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            }
        }
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte — SallesPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #301934 0%, #602299 30%, #0d9488 70%, #06b6d4 100%);
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 16px;
            padding: 36px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        .register-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .register-header .logo {
            width: 60px; height: 60px;
            border-radius: 14px;
            background: linear-gradient(135deg, #602299, #0d9488);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 26px; color: #fff;
        }
        .register-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .register-header p { font-size: 14px; color: #64748b; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #1e293b; font-size: 13px; }
        .form-group input {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: all 0.2s; background: #f8fafc;
        }
        .form-group input:focus { border-color: #602299; outline: none; box-shadow: 0 0 0 4px rgba(96,34,153,0.1); background: #fff; }
        .btn-register {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #602299, #0d9488);
            color: white; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(96,34,153,0.3); }
        .error {
            background-color: #fef2f2; color: #991b1b;
            padding: 10px; border-radius: 10px; margin-bottom: 16px;
            text-align: center; font-size: 13px; border: 1px solid #fecaca;
        }
        .success {
            background-color: #d1fae5; color: #065f46;
            padding: 10px; border-radius: 10px; margin-bottom: 16px;
            text-align: center; font-size: 13px; border: 1px solid #a7f3d0;
        }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #0d9488; text-decoration: none; font-size: 14px; font-weight: 500; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <div class="logo"><i class="fas fa-user-plus"></i></div>
            <h1>Créer un compte</h1>
            <p>Rejoignez SallesPro en quelques secondes</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" placeholder="Votre nom">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" placeholder="Votre prénom">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="votre@email.com">
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" placeholder="Ex: 55000000">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Minimum 6 caractères">
            </div>
            <div class="form-group">
                <label for="confirm_mdp">Confirmer le mot de passe</label>
                <input type="password" id="confirm_mdp" name="confirm_mdp" placeholder="Retapez le mot de passe">
            </div>
            <button type="submit" class="btn-register">
                <i class="fas fa-check"></i> Créer mon compte
            </button>
        </form>

        <div class="links">
            <a href="index.php?action=login">Déjà un compte ? Se connecter</a> &nbsp;·&nbsp;
            <a href="index.php">← Accueil</a>
        </div>
    </div>

    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        var nom = document.getElementById('nom').value.trim();
        var prenom = document.getElementById('prenom').value.trim();
        var email = document.getElementById('email').value.trim();
        var telephone = document.getElementById('telephone').value.trim();
        var mdp = document.getElementById('mot_de_passe').value;
        var confirmMdp = document.getElementById('confirm_mdp').value;
        if (!nom) { alert("Le nom est obligatoire"); e.preventDefault(); return false; }
        if (nom.length < 2) { alert("Le nom doit contenir au moins 2 caractères"); e.preventDefault(); return false; }
        if (!prenom) { alert("Le prénom est obligatoire"); e.preventDefault(); return false; }
        if (prenom.length < 2) { alert("Le prénom doit contenir au moins 2 caractères"); e.preventDefault(); return false; }
        if (!email) { alert("L'email est obligatoire"); e.preventDefault(); return false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert("Format d'email invalide"); e.preventDefault(); return false; }
        if (!telephone) { alert("Le téléphone est obligatoire"); e.preventDefault(); return false; }
        if (!/^[0-9]{8}$/.test(telephone)) { alert("Le téléphone doit contenir exactement 8 chiffres"); e.preventDefault(); return false; }
        if (!mdp) { alert("Le mot de passe est obligatoire"); e.preventDefault(); return false; }
        if (mdp.length < 6) { alert("Le mot de passe doit contenir au moins 6 caractères"); e.preventDefault(); return false; }
        if (mdp !== confirmMdp) { alert("Les mots de passe ne correspondent pas"); e.preventDefault(); return false; }
        return true;
    });
    </script>
</body>
</html>
        <?php
    }

    // === Logout ===
    public function handleLogout() {
        session_destroy();
        header('Location: index.php?action=login');
        exit();
    }
}
