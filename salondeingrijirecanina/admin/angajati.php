<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../autentificare.php");
    exit();
}

$actiune = $_GET['actiune'] ?? null;
$id_edit = $_GET['id'] ?? null;

// 🔄 Ștergere angajat
if ($actiune === 'sterge' && $id_edit) {
    $stmt = $conn->prepare("DELETE FROM angajati WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    header("Location: angajati.php");
    exit();
}

// 🔁 Editare
$angajat_edit = null;
if ($actiune === 'editeaza' && $id_edit) {
    $stmt = $conn->prepare("SELECT * FROM angajati WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $angajat_edit = $stmt->get_result()->fetch_assoc();
}

// 💾 Salvare
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = $_POST['nume'];
    $functie = $_POST['functie'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];

    if ($_POST['editare'] === '1') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE angajati SET nume=?, functie=?, telefon=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $nume, $functie, $telefon, $email, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO angajati (nume, functie, telefon, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nume, $functie, $telefon, $email);
    }

    $stmt->execute();
    header("Location: angajati.php");
    exit();
}

$angajati = $conn->query("SELECT * FROM angajati");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Angajați Salon</title>
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
    <h2>👥 Administrare Angajați</h2>

    <table>
        <thead>
            <tr>
                <th>Nume</th>
                <th>Funcție</th>
                <th>Telefon</th>
                <th>Email</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($a = $angajati->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($a['nume']) ?></td>
                <td><?= htmlspecialchars($a['functie']) ?></td>
                <td><?= htmlspecialchars($a['telefon'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td>
                    <a class="btn-edit" href="?actiune=editeaza&id=<?= $a['id'] ?>">✏️</a>
                    <a class="btn-del" href="?actiune=sterge&id=<?= $a['id'] ?>" onclick="return confirm('Ștergi angajatul?')">🗑️</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h3><?= $angajat_edit ? '✏️ Editare angajat' : '➕ Adaugă angajat nou' ?></h3>

    <form method="post">
        <input type="hidden" name="editare" value="<?= $angajat_edit ? '1' : '0' ?>">
        <input type="hidden" name="id" value="<?= $angajat_edit['id'] ?? '' ?>">

        <label>Nume complet:</label>
        <input type="text" name="nume" required value="<?= $angajat_edit['nume'] ?? '' ?>">

        <label>Funcție:</label>
        <input type="text" name="functie" required value="<?= $angajat_edit['functie'] ?? '' ?>">

        <label>Telefon:</label>
        <input type="text" name="telefon" value="<?= $angajat_edit['telefon'] ?? '' ?>">

        <label>Email:</label>
        <input type="email" name="email" value="<?= $angajat_edit['email'] ?? '' ?>">

        <br><br>
        <button type="submit"><?= $angajat_edit ? '💾 Salvează' : '➕ Adaugă angajat' ?></button>
    </form>
</div>

</body>
</html>
