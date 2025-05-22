<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Panou Admin - Pet Salon SRL</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="admin-container">
    <h1>Bună, admin!</h1>

    <div class="dashboard-cards">
      <div class="card">
        <h3>Angajați</h3>
        <p><a href="angajati.php">Vezi mai mult</a></p>
      </div>
      <div class="card">
        <h3>Programări</h3>
        <p><a href="programari.php">Vezi mai mult</a></p>
      </div>
      <div class="card">
        <h3>Produse</h3>
        <p><a href="produse.php">Vezi mai mult</a></p>
      </div>
      <div class="card">
        <h3>Rapoarte</h3>
        <p><a href="rapoarte.php">Vezi mai mult</a></p>
      </div>
    </div>
  </div>

  <footer>
    Pet Salon &copy; 2025 Toate drepturile rezervate | <a href="#">Termeni și condiții</a>
  </footer>
</body>
</html>
