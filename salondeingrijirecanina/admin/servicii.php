<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../autentificare.php");
    exit();
}

$actiune = $_GET['actiune'] ?? null;
$id_edit = $_GET['id'] ?? null;

// 🔄 Ștergere serviciu
if ($actiune === 'sterge' && $id_edit) {
    $stmt = $conn->prepare("DELETE FROM servicii WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    header("Location: servicii.php");
    exit();
}

// 🔁 Editare
$serviciu_edit = null;
if ($actiune === 'editeaza' && $id_edit) {
    $stmt = $conn->prepare("SELECT * FROM servicii WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $serviciu_edit = $stmt->get_result()->fetch_assoc();
}

// 📏 Salvare
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $denumire = $_POST['denumire'];
    $durata = $_POST['durata'];
    $pret = $_POST['pret'];

    if ($_POST['editare'] === '1') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE servicii SET denumire=?, durata=?, pret=? WHERE id=?");
        $stmt->bind_param("sidi", $denumire, $durata, $pret, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO servicii (denumire, durata, pret) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $denumire, $durata, $pret);
    }

    $stmt->execute();
    header("Location: servicii.php");
    exit();
}

$servicii = $conn->query("SELECT * FROM servicii");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Servicii Salon</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body { font-family: sans-serif; padding: 30px; background: #f4f6f8; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #eee; }
        form { margin-top: 30px; background: white; padding: 20px; border-radius: 8px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; }
        a.btn-del { color: red; text-decoration: none; font-weight: bold; }
        a.btn-edit { color: #2980b9; margin-right: 10px; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>💇 Administrare Servicii</h2>

    <div class="grid">
        <div class="card" style="width: 100%;">
            <table>
                <thead>
                    <tr>
                        <th>Denumire</th>
                        <th>Durată (minute)</th>
                        <th>Preț (RON)</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($s = $servicii->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['denumire']) ?></td>
                        <td><?= $s['durata'] ?> min</td>
                        <td><?= number_format($s['pret'], 2) ?> lei</td>
                        <td>
                            <a class="btn-edit" href="?actiune=editeaza&id=<?= $s['id'] ?>">✏️</a>
                            <a class="btn-del" href="?actiune=sterge&id=<?= $s['id'] ?>" onclick="return confirm('Țtețrgi serviciul?')">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="width: 100%;">
            <h3><?= $serviciu_edit ? '✏️ Editare serviciu' : '➕ Adaugă serviciu' ?></h3>

            <form method="post">
                <input type="hidden" name="editare" value="<?= $serviciu_edit ? '1' : '0' ?>">
                <input type="hidden" name="id" value="<?= $serviciu_edit['id'] ?? '' ?>">

                <label>Denumire:</label>
                <input type="text" name="denumire" required value="<?= $serviciu_edit['denumire'] ?? '' ?>">

                <label>Durată (minute):</label>
                <input type="number" name="durata" required value="<?= $serviciu_edit['durata'] ?? '' ?>">

                <label>Preț (lei):</label>
                <input type="number" step="0.01" name="pret" required value="<?= $serviciu_edit['pret'] ?? '' ?>">

                <br><br>
                <button type="submit"><?= $serviciu_edit ? '📏 Salvează' : '➕ Adaugă serviciu' ?></button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
