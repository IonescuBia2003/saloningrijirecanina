<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../autentificare.php");
    exit();
}

$actiune = $_GET['actiune'] ?? null;
$id_edit = $_GET['id'] ?? null;

// 🔄 Ștergere produs
if ($actiune === 'sterge' && $id_edit) {
    $stmt = $conn->prepare("DELETE FROM produse WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    header("Location: gestiune_produse.php");
    exit();
}

// 🔁 Preluare furnizori
$furnizori = $conn->query("SELECT id, nume FROM furnizori");

// 🔁 Editare produs
$produs_edit = null;
if ($actiune === 'editeaza' && $id_edit) {
    $stmt = $conn->prepare("SELECT * FROM produse WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $produs_edit = $stmt->get_result()->fetch_assoc();
}

// 💾 Salvare produs
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $denumire = $_POST['denumire'];
    $cantitate = $_POST['cantitate'];
    $furnizor = $_POST['id_furnizor'];

    if ($_POST['editare'] === '1') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE produse SET denumire=?, cantitate=?, id_furnizor=? WHERE id=?");
        $stmt->bind_param("siii", $denumire, $cantitate, $furnizor, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO produse (denumire, cantitate, id_furnizor) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $denumire, $cantitate, $furnizor);
    }

    $stmt->execute();
    header("Location: gestiune_produse.php");
    exit();
}

// 🔍 Listare produse
$produse = $conn->query("
    SELECT p.id, p.denumire, p.cantitate, f.nume AS furnizor
    FROM produse p
    LEFT JOIN furnizori f ON p.id_furnizor = f.id
");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Produse</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body { font-family: sans-serif; background: #f4f6f8; padding: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #eee; }
        form { background: white; padding: 20px; margin-top: 30px; border-radius: 8px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; }
        a.btn-del { color: red; text-decoration: none; font-weight: bold; }
        a.btn-edit { color: #2980b9; text-decoration: none; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>📦 Gestiune Produse</h2>

    <table>
        <thead>
            <tr>
                <th>Denumire</th>
                <th>Cantitate</th>
                <th>Furnizor</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($prod = $produse->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($prod['denumire']) ?></td>
                <td><?= $prod['cantitate'] ?> buc.</td>
                <td><?= $prod['furnizor'] ?></td>
                <td>
                    <a class="btn-edit" href="?actiune=editeaza&id=<?= $prod['id'] ?>">✏️</a>
                    <a class="btn-del" href="?actiune=sterge&id=<?= $prod['id'] ?>" onclick="return confirm('Ștergi produsul?')">🗑️</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h3><?= $produs_edit ? '✏️ Editare produs' : '➕ Adaugă produs nou' ?></h3>

    <form method="post">
        <input type="hidden" name="editare" value="<?= $produs_edit ? '1' : '0' ?>">
        <input type="hidden" name="id" value="<?= $produs_edit['id'] ?? '' ?>">

        <label>Denumire produs:</label>
        <input type="text" name="denumire" required value="<?= $produs_edit['denumire'] ?? '' ?>">

        <label>Cantitate (bucăți):</label>
        <input type="number" name="cantitate" required value="<?= $produs_edit['cantitate'] ?? '' ?>">

        <label>Furnizor:</label>
        <select name="id_furnizor" required>
            <?php foreach ($furnizori as $f): ?>
                <option value="<?= $f['id'] ?>" <?= ($produs_edit && $produs_edit['id_furnizor'] == $f['id']) ? 'selected' : '' ?>>
                    <?= $f['nume'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <button type="submit"><?= $produs_edit ? '💾 Salvează modificările' : '➕ Adaugă produs' ?></button>
    </form>
</div>

</body>
</html>
