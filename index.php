<?php
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$role = $_SESSION['role'] ?? 'user';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil - projet_brinks</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="center">
  <div class="card">
    <h1>Bienvenue</h1>
    <p>Vous êtes connecté.</p>
    <?php if ($role === 'admin'): ?>
      <p><a href="admin_create.php">Créer un nouvel utilisateur</a></p>
    <?php endif; ?>
    <p><a href="logout.php">Se déconnecter</a></p>
  </div>
</body>
</html>
