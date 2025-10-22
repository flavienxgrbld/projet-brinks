<?php
date_default_timezone_set('Europe/Paris');
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Erreur BDD: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

$user_id = $_SESSION['user_id'];
$message = "";

// Vérifier si l'utilisateur est actuellement en service
$stmt = $conn->prepare("SELECT id, start_time FROM work_time WHERE user_id=? AND end_time IS NULL ORDER BY start_time DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start'])) {
        if (!$current) {
            $stmt = $conn->prepare("INSERT INTO work_time (user_id, start_time) VALUES (?, NOW())");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $message = "Pointage d'entrée enregistré à " . date('H:i:s');

            // Actualiser l’état actuel
            $stmt = $conn->prepare("SELECT id, start_time FROM work_time WHERE user_id=? AND end_time IS NULL ORDER BY start_time DESC LIMIT 1");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $current = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "⚠️ Vous êtes déjà en service depuis " . date('H:i', strtotime($current['start_time']));
        }
    }
    if (isset($_POST['stop'])) {
        if ($current) {
            $stmt = $conn->prepare("UPDATE work_time SET end_time=NOW() WHERE id=?");
            $stmt->bind_param('i', $current['id']);
            $stmt->execute();
            $message = "Pointage de sortie enregistré à " . date('H:i:s');

            // Actualiser l’état après la sortie
            $stmt = $conn->prepare("SELECT id, start_time FROM work_time WHERE user_id=? AND end_time IS NULL ORDER BY start_time DESC LIMIT 1");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $current = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "⚠️ Vous n'êtes pas actuellement en service.";
        }
    }
}

// Historique de la semaine
$res = $conn->query("
    SELECT start_time, end_time, duration_minutes
    FROM work_time
    WHERE user_id = $user_id AND YEARWEEK(start_time, 1) = YEARWEEK(CURDATE(), 1)
    ORDER BY start_time DESC
");
$entries = $res->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Mes heures de travail</title>
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

  <h1>Mes heures de travail</h1>
  <p><?= htmlspecialchars($message) ?></p>

  <form method="post">
  <?php if (!$current): ?>
      <button type="submit" name="start">▶️ Pointer entrée</button>
  <?php else: ?>
      <button type="submit" name="stop">⏹️ Pointer sortie</button>
  <?php endif; ?>
  </form>

  <h2>Heures de la semaine</h2>
  <table border="1" cellpadding="6">
  <tr><th>Date</th><th>Début</th><th>Fin</th><th>Durée (heures)</th></tr>
  <?php
  $total = 0;
  foreach ($entries as $e):
      $dur = $e['duration_minutes'] ? round($e['duration_minutes']/60,2) : '-';
      if ($e['duration_minutes']) $total += $e['duration_minutes'];
  ?>
  <tr>
  <td><?= date('d/m/Y', strtotime($e['start_time'])) ?></td>
  <td><?= date('H:i', strtotime($e['start_time'])) ?></td>
  <td><?= $e['end_time'] ? date('H:i', strtotime($e['end_time'])) : 'En service' ?></td>
  <td><?= $dur ?></td>
  </tr>
  <?php endforeach; ?>
  </table>
  <p><strong>Total semaine : <?= round($total/60, 2) ?> h</strong></p>

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
