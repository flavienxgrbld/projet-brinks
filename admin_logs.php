<?php
date_default_timezone_set('Europe/Paris');
require_once __DIR__.'/config.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php'); 
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Erreur BDD: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

$result = $conn->query("
SELECT a.id, u.matricule AS admin_matricule, a.action, a.target_matricule, a.details, a.created_at
FROM admin_logs a
JOIN users u ON a.admin_id = u.id
ORDER BY a.created_at DESC
");
$logs = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Journalisation actions admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="center">
<div class="card">
  <!-- Sélecteur de thème -->
  <div style="text-align: right; margin-bottom: 1rem;">
    <button type="button" onclick="setTheme('')">Clair</button>
    <button type="button" onclick="setTheme('theme-dark')">Sombre</button>
    <button type="button" onclick="setTheme('theme-modern')">Moderne</button>
  </div>

  <h1>Journalisation des actions admin</h1>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Admin</th>
      <th>Action</th>
      <th>Cible</th>
      <th>Détails</th>
      <th>Date</th>
    </tr>
    <?php foreach($logs as $l): ?>
    <tr>
      <td><?= htmlspecialchars($l['id']) ?></td>
      <td><?= htmlspecialchars($l['admin_matricule']) ?></td>
      <td><?= htmlspecialchars($l['action']) ?></td>
      <td><?= htmlspecialchars($l['target_matricule']) ?></td>
      <td><?= htmlspecialchars($l['details']) ?></td>
      <td><?= htmlspecialchars($l['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="index.php">Retour</a></p>
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
