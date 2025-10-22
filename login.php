<?php
date_default_timezone_set('Europe/Paris');
require_once __DIR__.'/config.php';

function db_connect(){
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        // message lisible pour admin ; en prod envisager log + message générique
        die("Erreur connexion BDD: " . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($matricule === '' || $password === '') {
        $error = "Matricule et mot de passe requis.";
    } else {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE matricule = ? LIMIT 1");
        $stmt->bind_param('s', $matricule);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hash, $role);
        if ($stmt->num_rows === 1) {
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = $role;
                header('Location: index.php');
                exit();
            } else {
                $error = "Matricule ou mot de passe incorrect.";
            }
        } else {
            $error = "Matricule ou mot de passe incorrect.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion - projet_brinks</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="center theme-dark">
  <div class="card">
    <h1>Connexion</h1>
    <?php if($error): ?><p class="err"><?=htmlspecialchars($error)?></p><?php endif; ?>
    <form method="post" autocomplete="off">
      <label>Matricule<br><input type="text" name="matricule" required></label><br>
      <label>Mot de passe<br><input type="password" name="password" required></label><br>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</body>
</html>