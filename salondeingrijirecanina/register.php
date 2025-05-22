<?php
require 'includes/db.php';
$errors = [];

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $parola = $_POST['parola'];

    $stmt = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Emailul este deja înregistrat.";
    } else {
        $stmt = $conn->prepare("INSERT INTO stapani (nume, email, telefon) VALUES (?, ?, ?)");
        $nume_complet = $nume . ' ' . $prenume;
        $stmt->bind_param("sss", $nume_complet, $email, $telefon);
        $stmt->execute();
        $id_stapan = $stmt->insert_id;

        $hash = password_hash($parola, PASSWORD_BCRYPT);
        $rol = "client";
        $stmt = $conn->prepare("INSERT INTO utilizatori (email, parola_hash, rol, id_stapan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $email, $hash, $rol, $id_stapan);
        $stmt->execute();

        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Înregistrare - Pet Salon SRL</title>
  <link rel="stylesheet" href="register.css">
</head>
<body>
  <div class="container">
    <div class="form-wrapper">
      <div class="left">
        <img src="assets/img/pet-register.jpg" alt="Pet Grooming">
        <h2>Cont nou client</h2>
      </div>
      <div class="right">
        <form method="post">
          <h3>Înregistrare cont</h3>
          <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
          <input type="text" name="nume" placeholder="Nume" required>
          <input type="text" name="prenume" placeholder="Prenume" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="text" name="telefon" placeholder="Telefon" required>
          <input type="password" name="parola" placeholder="Parolă" required>
          <button type="submit">Creează cont</button>
        </form>
      </div>
    </div>
    <footer>
      &copy; 2025 Pet Salon SRL • <a href="login.php">Contul meu</a> • <a href="#">Termeni și condiții</a>
    </footer>
  </div>
</body>
</html>
