<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'client') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id_stapan FROM utilizatori WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($id_stapan);
$stmt->fetch();
$stmt->close();

$sql = "
SELECT 
    p.id,
    p.data,
    p.ora,
    c.nume AS caine,
    GROUP_CONCAT(s.denumire ORDER BY s.denumire SEPARATOR ', ') AS servicii,
    p.status
FROM programari p
JOIN caini c ON p.id_caine = c.id
JOIN programari_servicii ps ON ps.id_programare = p.id
JOIN servicii s ON ps.id_serviciu = s.id
WHERE c.id_stapan = ?
GROUP BY p.id, p.data, p.ora, c.nume, p.status
ORDER BY p.data DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_stapan);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Client</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <h2>Bine ai venit la Pet Salon SRL!</h2>
  <p>
    <a href="programare.php">+ Adaugă o programare</a> |
    <a href="logout.php">Deconectare</a>
  </p>

  <h3>Programările tale</h3>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>Data</th>
      <th>Ora</th>
      <th>Câine</th>
      <th>Servicii</th>
      <th>Status</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['data']) ?></td>
        <td><?= htmlspecialchars($row['ora']) ?></td>
        <td><?= htmlspecialchars($row['caine']) ?></td>
        <td><?= htmlspecialchars($row['servicii']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
