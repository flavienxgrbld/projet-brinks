<?php
date_default_timezone_set('Europe/Paris');
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
    <!-- Sélecteur de thème -->
    <div style="text-align: right; margin-bottom: 1rem;">
      <button type="button" onclick="setTheme('')">Clair</button>
      <button type="button" onclick="setTheme('theme-dark')">Sombre</button>
      <button type="button" onclick="setTheme('theme-modern')">Moderne</button>
    </div>

    <h1>Bienvenue</h1>
    <p>Vous êtes connecté.</p>

    <?php if ($role === 'admin'): ?>
      <p><a href="admin_create.php">Créer un nouvel utilisateur</a></p>
      <p><a href="admin_users.php">Gérer utilisateurs</a></p>
      <p><a href="admin_logs.php">Voir journal admin</a></p>
      <p><a href="admin_temps.php">Gestion du temps</a></p>
    <?php endif; ?>

    <p><a href="mes_heures.php">Mes heures</a></p>
    <p><a href="logout.php">Se déconnecter</a></p>
  </div>

  <script>
    // Appliquer le thème stocké au chargement
    document.addEventListener('DOMContentLoaded', function() {
      const theme = localStorage.getItem('theme') || '';
      if (theme) document.body.classList.add(theme);
    });

    // Fonction pour changer le thème
    function setTheme(themeClass) {
      document.body.classList.remove('theme-dark', 'theme-modern');
      if (themeClass) document.body.classList.add(themeClass);
      localStorage.setItem('theme', themeClass);
    }
  </script>
</body>
</html>

