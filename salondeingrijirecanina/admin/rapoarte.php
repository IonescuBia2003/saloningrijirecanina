<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../autentificare.php");
    exit();
}


$totalCaini = 125;
$programariSaptamana = 18;
$serviciuPopular = "Tuns profesional";
$serviciuPopularCount = 42;

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Rapoarte</title>
    <link rel="stylesheet" href="admin.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f2f6f9;
            margin: 0;
            padding: 20px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0 5px;
            font-size: 1.1rem;
        }

        .card strong {
            font-size: 1.5rem;
            color: #333;
        }

        .card.total-caini { background-color: #2CBBC3; color: white; }
        .card.programari { background-color: #47A8F5; color: white; }
        .card.popular { background-color: #FF9934; color: white; }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<h2 style="text-align:center; color:#2c3e50;">📊 Rapoarte Salon Canin</h2>

<div class="dashboard">
    <div class="card total-caini">
        <i class="fas fa-dog"></i>
        <h3>Total câini înregistrați</h3>
        <strong><?= $totalCaini ?></strong>
    </div>

    <div class="card programari">
        <i class="fas fa-calendar-alt"></i>
        <h3>Programări săptămâna curentă</h3>
        <strong><?= $programariSaptamana ?></strong>
    </div>

    <div class="card popular">
        <i class="fas fa-cut"></i>
        <h3>Serviciu cel mai solicitat</h3>
        <strong><?= $serviciuPopular ?></strong>
        <div><?= $serviciuPopularCount ?> programări</div>
    </div>
</div>

</body>
</html>
