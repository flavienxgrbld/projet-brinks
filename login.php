<?php
session_start();

$servername = "localhost";
$dbuser = "root";
$dbpass = ""; // ton mot de passe MySQL si nécessaire
$dbname = "brinks";

$conn = new mysqli($servername, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE matricule = ?");
    $stmt->bind_param("s", $matricule);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($pass, $hashed_password)) {
        // Regénérer la session id pour éviter fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        header("Location: index.php");
        exit();
    } else {
        $error = "Matricule ou mot de passe incorrect.";
    }
    $stmt->close();
}
$conn->close();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Connexion</title></head>
<body>
<?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST" autocomplete="off">
    <label>Matricule: <input type="text" name="matricule" required></label><br>
    <label>Mot de passe: <input type="password" name="password" required></label><br>
    <button type="submit">Se connecter</button>
</form>
</body>
</html>
