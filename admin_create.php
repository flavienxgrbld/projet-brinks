<?php
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

function db_connect(){
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) { die("Erreur BDD: ".$conn->connect_error); }
    $conn->set_charset('utf8mb4');
    return $conn;
}

$success = $error = $revealed_password = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule'] ?? '');
    $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';

    if ($matricule === '') {
        $error = "Matricule requis.";
    } else {
        // génère un mot de passe lisible aléatoire (12 chars)
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $plain = '';
        for ($i=0;$i<12;$i++) $plain .= $chars[random_int(0, strlen($chars)-1)];
        $hash = password_hash($plain, PASSWORD_BCRYPT);

        $conn = db_connect();
        $stmt = $conn->prepare("INSERT INTO users (matricule, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $matricule, $hash, $role);
        if ($stmt->execute()) {
            $success = "Utilisateur créé avec succès.";
            $revealed_password = $plain; // afficher UNE SEULE FOIS
        } else {
            $error = "Erreur : " . $stmt->error;
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
  <title>Créer utilisateur - projet_brinks</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="center">
  <div class="card">
    <h1>Créer un utilisateur</h1>
    <?php if($success): ?><p class="ok"><?=htmlspecialchars($success)?></p><?php endif; ?>
    <?php if($error): ?><p class="err"><?=htmlspecialchars($error)?></p><?php endif; ?>

    <?php if($revealed_password): ?>
      <p><strong>Mot de passe généré (copier UNE SEULE FOIS) :</strong></p>
      <div class="reveal"><?=htmlspecialchars($revealed_password)?></div>
      <p class="small">Ce mot de passe n'est **pas** stocké en clair. Demandez à l'utilisateur de le changer dès la première connexion.</p>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <label>Matricule<br><input type="text" name="matricule" required></label><br>
      <label>Rôle<br>
        <select name="role">
          <option value="user">Utilisateur</option>
          <option value="admin">Administrateur</option>
        </select>
      </label><br>
      <button type="submit">Créer</button>
    </form>
    <p><a href="index.php">Retour</a></p>
  </div>
</body>
</html>
