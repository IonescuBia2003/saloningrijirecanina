<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Administrator</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>Bine ai revenit, admin!</h2>

    <div class="grid">
        <div class="card">
            <h3>👥 Angajați</h3>
            <p>Administrează lista angajaților salonului.</p>
            <a href="angajati.php">Vezi mai mult →</a>
        </div>
        <div class="card">
            <h3>🗓 Programări</h3>
            <p>Vizualizează și gestionează programările clienților.</p>
            <a href="programari_admin.php">Vezi mai mult →</a>
        </div>
        <div class="card">
            <h3>📦 Produse</h3>
            <p>Actualizează stocul și adaugă produse noi.</p>
            <a href="gestiune_produse.php">Vezi mai mult →</a>
        </div>
        <div class="card">
            <h3>📊 Rapoarte</h3>
            <p>Vezi rapoarte și statistici lunare.</p>
            <a href="rapoarte_statistici.php">Vezi mai mult →</a>
        </div>
    </div>
</div>

</body>
</html>