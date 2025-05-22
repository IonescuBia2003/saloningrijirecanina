<?php
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $parola = $_POST['parola'];
    $rol_selectat = $_POST['rol'];

    $stmt = $conn->prepare("SELECT id, parola_hash, rol FROM utilizatori WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $parola_din_baza, $rol_din_baza);
        $stmt->fetch();

        if ($rol_din_baza !== $rol_selectat) {
            $errors[] = "Rolul selectat nu corespunde contului.";
        } elseif ($parola === $parola_din_baza) {
            $_SESSION['user_id'] = $id;
            $_SESSION['rol'] = $rol_din_baza;

            // Redirecționare în funcție de rol
            if ($rol_din_baza === 'client') {
                header("Location: client_dashboard.php");
            } else {
                header("Location: admin/dashboard.php");
            }
            exit();
        } else {
            $errors[] = "Parolă incorectă.";
        }
    } else {
        $errors[] = "Cont inexistent.";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Autentificare - Pet Salon SRL</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-box">
      <h2>Autentificare cont</h2>
      <p>Intră în contul tău și programează-ți patrupedul cu un click 🐾</p>

      <?php foreach ($errors as $error): ?>
        <p class="error"><?= $error ?></p>
      <?php endforeach; ?>

      <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="parola" placeholder="Parolă" required>
        <select name="rol" required>
          <option value="">-- Selectează rol --</option>
          <option value="client">Client</option>
          <option value="angajat">Angajat</option>
          <option value="admin">Administrator</option>
        </select>
        <button type="submit">Autentificare</button>
      </form>
    </div>
  </div>
</body>
</html>
