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

$action_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = intval($_POST['user_id'] ?? 0);
    $target_matricule = $_POST['target_matricule'] ?? '';
    $admin_id = $_SESSION['user_id'];

    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param('i', $target_id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_matricule) VALUES (?, 'delete', ?)");
            $stmt->bind_param('is', $admin_id, $target_matricule);
            $stmt->execute();
            $action_msg = "Utilisateur supprimé avec succès.";
        }
    } elseif (isset($_POST['change_role'])) {
        $new_role = ($_POST['new_role'] === 'admin') ? 'admin' : 'user';
        $res = $conn->query("SELECT role FROM users WHERE id=$target_id LIMIT 1");
        $old_role = $res->fetch_assoc()['role'] ?? '';
        $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->bind_param('si', $new_role, $target_id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_matricule, details) VALUES (?, 'update', ?, ?)");
            $details = "Role: $old_role -> $new_role";
            $stmt->bind_param('iss', $admin_id, $target_matricule, $details);
            $stmt->execute();
            $action_msg = "Rôle mis à jour avec succès.";
        }
    } elseif (isset($_POST['reset_password'])) {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $plain = '';
        for ($i=0;$i<12;$i++) $plain .= $chars[random_int(0, strlen($chars)-1)];
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param('si', $hash, $target_id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_matricule, details) VALUES (?, 'update', ?, ?)");
            $details = "Mot de passe réinitialisé";
            $stmt->bind_param('iss', $admin_id, $target_matricule, $details);
            $stmt->execute();
            $action_msg = "Mot de passe réinitialisé : <strong>$plain</strong> (à copier une seule fois)";
        }
    }
}

$result = $conn->query("SELECT id, matricule, role, created_at FROM users ORDER BY id ASC");
$users = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Gestion des utilisateurs</title>
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

  <h1>Gestion des utilisateurs</h1>
  <?php if($action_msg): ?><p class="ok"><?= $action_msg ?></p><?php endif; ?>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr><th>ID</th><th>Matricule</th><th>Rôle</th><th>Créé le</th><th>Actions</th></tr>
    <?php foreach($users as $u): ?>
    <tr>
      <td><?= htmlspecialchars($u['id']) ?></td>
      <td><?= htmlspecialchars($u['matricule']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td><?= htmlspecialchars($u['created_at']) ?></td>
      <td>
        <form method="post" style="display:inline-block;">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <input type="hidden" name="target_matricule" value="<?= $u['matricule'] ?>">
          <select name="new_role">
            <option value="user" <?= $u['role']==='user'?'selected':''?>>Utilisateur</option>
            <option value="admin" <?= $u['role']==='admin'?'selected':''?>>Administrateur</option>
          </select>
          <button type="submit" name="change_role">Modifier rôle</button>
          <button type="submit" name="reset_password">Réinitialiser mot de passe</button>
          <button type="submit" name="delete" onclick="return confirm('Confirmer suppression de <?= htmlspecialchars($u['matricule']) ?> ?')">Supprimer</button>
        </form>
      </td>
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
