<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../autentificare.php");
    exit();
}

$actiune = $_GET['actiune'] ?? null;
$id_edit = $_GET['id'] ?? null;

// 🔄 Ștergere
if ($actiune === 'sterge' && $id_edit) {
    $stmt = $conn->prepare("DELETE FROM furnizori WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    header("Location: gestiune_furnizori.php");
    exit();
}

// 🔁 Editare
$furnizor_edit = null;
if ($actiune === 'editeaza' && $id_edit) {
    $stmt = $conn->prepare("SELECT * FROM furnizori WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $furnizor_edit = $stmt->get_result()->fetch_assoc();
}

// 💾 Salvare
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = $_POST['nume'];
    $persoana = $_POST['persoana_contact'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];

    if ($_POST['editare'] === '1') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE furnizori SET nume=?, persoana_contact=?, telefon=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $nume, $persoana, $telefon, $email, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO furnizori (nume, persoana_contact, telefon, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nume, $persoana, $telefon, $email);
    }

    $stmt->execute();
    header("Location: gestiune_furnizori.php");
    exit();
}

$furnizori = $conn->query("SELECT * FROM furnizori");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestiune Furnizori</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2>🚚 Gestiune Furnizori</h2>

    <div class="grid">
        <div class="card" style="width: 100%;">
            <table>
                <thead>
                    <tr>
                        <th>Nume firmă</th>
                        <th>Persoană contact</th>
                        <th>Telefon</th>
                        <th>Email</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($f = $furnizori->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['nume']) ?></td>
                            <td><?= htmlspecialchars($f['persoana_contact']) ?></td>
                            <td><?= htmlspecialchars($f['telefon']) ?></td>
                            <td><?= htmlspecialchars($f['email']) ?></td>
                            <td>
                                <a class="btn-edit" href="?actiune=editeaza&id=<?= $f['id'] ?>">✏️</a>
                                <a class="btn-del" href="?actiune=sterge&id=<?= $f['id'] ?>" onclick="return confirm('Ștergi furnizorul?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="width: 100%;">
            <h3><?= $furnizor_edit ? '✏️ Editare furnizor' : '➕ Adaugă furnizor nou' ?></h3>

            <form method="post">
                <input type="hidden" name="editare" value="<?= $furnizor_edit ? '1' : '0' ?>">
                <input type="hidden" name="id" value="<?= $furnizor_edit['id'] ?? '' ?>">

                <label>Nume firmă:</label>
                <input type="text" name="nume" required value="<?= $furnizor_edit['nume'] ?? '' ?>">

                <label>Persoană de contact:</label>
                <input type="text" name="persoana_contact" required value="<?= $furnizor_edit['persoana_contact'] ?? '' ?>">

                <label>Telefon:</label>
                <input type="text" name="telefon" required value="<?= $furnizor_edit['telefon'] ?? '' ?>">

                <label>Email:</label>
                <input type="email" name="email" required value="<?= $furnizor_edit['email'] ?? '' ?>">

                <br><br>
                <button type="submit"><?= $furnizor_edit ? '💾 Salvează modificările' : '➕ Adaugă furnizor' ?></button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
