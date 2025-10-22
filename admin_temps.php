<?php
date_default_timezone_set('Europe/Paris');
require_once __DIR__ . '/config.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("Erreur BDD: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

// employés actuellement en service
$in_service = $conn->query("
    SELECT u.matricule, w.start_time
    FROM work_time w
    JOIN users u ON w.user_id = u.id
    WHERE w.end_time IS NULL
    ORDER BY w.start_time ASC
")->fetch_all(MYSQLI_ASSOC);

// total heures de la semaine
$weekly = $conn->query("
    SELECT u.matricule, 
           ROUND(SUM(w.duration_minutes)/60,2) AS total_heures
    FROM work_time w
    JOIN users u ON w.user_id = u.id
    WHERE YEARWEEK(w.start_time, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY u.matricule
    ORDER BY total_heures DESC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Gestion du temps</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="center">
<div class="card">
<h1>Gestion du temps de travail</h1>

<h2>Employés actuellement en service</h2>
<table border="1" cellpadding="6">
<tr><th>Matricule</th><th>Début de service</th><th>Durée actuelle</th></tr>
<?php foreach($in_service as $s): 
$start = strtotime($s['start_time']);
$duration = floor((time() - $start)/60);
?>
<tr>
<td><?= htmlspecialchars($s['matricule']) ?></td>
<td><?= date('H:i', $start) ?></td>
<td><?= floor($duration/60).'h '.($duration%60).'m' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>Heures totales par utilisateur (semaine en cours)</h2>
<table border="1" cellpadding="6">
<tr><th>Matricule</th><th>Total heures</th></tr>
<?php foreach($weekly as $w): ?>
<tr>
<td><?= htmlspecialchars($w['matricule']) ?></td>
<td><?= $w['total_heures'] ?? 0 ?></td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="index.php">Retour</a></p>
</div>
</body>
</html>
