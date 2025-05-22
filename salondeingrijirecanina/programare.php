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

$groomeri = [];
$res = $conn->query("SELECT id, nume FROM angajati WHERE functie = 'Groomer'");
while ($row = $res->fetch_assoc()) {
    $groomeri[] = $row;
}

$servicii = [];
$res = $conn->query("SELECT id, denumire FROM servicii");
while ($row = $res->fetch_assoc()) {
    $servicii[] = $row;
}
$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume_caine = $_POST['nume_caine'];
    $rasa = $_POST['rasa'];
    $varsta = $_POST['varsta'];
    $gen = $_POST['gen'];
    $tip = $_POST['tip'];
    $observatii = $_POST['observatii'];
    $id_angajat = $_POST['id_angajat'];
    $id_servicii = $_POST['id_serviciu'] ?? [];
    $data = $_POST['data'];
    $ora = $_POST['ora'];

    $data_ora_programare = strtotime($data . ' ' . $ora);

    if ($data_ora_programare < time()) {
        $error = "Nu poți face o programare în trecut.";
    } elseif (!preg_match('/^[a-zA-ZăîâșțĂÎÂȘȚ\s]+$/u', $nume_caine)) {
        $error = "Numele câinelui poate conține doar litere și spații.";
    } elseif (empty($id_servicii)) {
        $error = "Trebuie să selectați cel puțin un serviciu.";
    } else {
        $birth_date = date('Y-m-d', strtotime("-$varsta years"));

        $stmt = $conn->prepare("INSERT INTO caini (nume, rasa, data_nasterii, gen, tip, id_stapan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $nume_caine, $rasa, $birth_date, $gen, $tip, $id_stapan);
        $stmt->execute();
        $id_caine = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO programari (data, ora, id_caine, id_angajat, status, observatii) VALUES (?, ?, ?, ?, 'Programat', ?)");
        $stmt->bind_param("ssiis", $data, $ora, $id_caine, $id_angajat, $observatii);
        $stmt->execute();
        $id_programare = $stmt->insert_id;
        $stmt->close();

       
        $stmt = $conn->prepare("INSERT INTO programari_servicii (id_programare, id_serviciu) VALUES (?, ?)");
        foreach ($id_servicii as $id_serviciu) {
            $stmt->bind_param("ii", $id_programare, $id_serviciu);
            $stmt->execute();
        }
        $stmt->close();

        header("Location: programare.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Programare - Pet Salon SRL</title>
  <link rel="stylesheet" href="programare.css">
  <style>
    .checkbox-list label {
      display: inline-block;
      margin-right: 20px;
      margin-bottom: 10px;
    }
    .error {
      color: red;
      margin-bottom: 15px;
    }
    .success {
      color: green;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Formular de Programare</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="success">✅ Programarea a fost înregistrată cu succes!</div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <fieldset>
      <legend>Datele Câinelui</legend>

      <label>Nume Câine:</label>
      <input type="text" name="nume_caine" required value="<?= $_POST['nume_caine'] ?? '' ?>">

      <label>Rasă:</label>
      <input type="text" name="rasa" required value="<?= $_POST['rasa'] ?? '' ?>">

      <label>Vârstă (în ani):</label>
      <input type="number" name="varsta" min="0" max="25" required value="<?= $_POST['varsta'] ?? '' ?>">

      <label>Gen:</label>
      <select name="gen" required>
        <option value="">-- Alege genul --</option>
        <option value="Mascul" <?= (($_POST['gen'] ?? '') == 'Mascul') ? 'selected' : '' ?>>Mascul</option>
        <option value="Femelă" <?= (($_POST['gen'] ?? '') == 'Femelă') ? 'selected' : '' ?>>Femelă</option>
      </select>

      <label>Tip de câine:</label>
      <select name="tip" required>
        <option value="Bland" <?= (($_POST['tip'] ?? '') == 'Bland') ? 'selected' : '' ?>>Bland</option>
        <option value="Agresiv" <?= (($_POST['tip'] ?? '') == 'Agresiv') ? 'selected' : '' ?>>Agresiv</option>
        <option value="Jucaus" <?= (($_POST['tip'] ?? '') == 'Jucaus') ? 'selected' : '' ?>>Jucăuș</option>
        <option value="Timid" <?= (($_POST['tip'] ?? '') == 'Timid') ? 'selected' : '' ?>>Timid</option>
      </select>
    </fieldset>

    <fieldset>
      <legend>Servicii dorite</legend>
      <div class="checkbox-list">
        <?php foreach ($servicii as $s): ?>
          <label>
            <input type="checkbox" name="id_serviciu[]" value="<?= $s['id'] ?>"
              <?= (isset($_POST['id_serviciu']) && in_array($s['id'], $_POST['id_serviciu'])) ? 'checked' : '' ?>>
            <?= htmlspecialchars($s['denumire']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset>
      <legend>Groomer preferat</legend>
      <label>Selectează angajat:</label>
      <select name="id_angajat" required>
        <option value="">-- Alege Groomer --</option>
        <?php foreach ($groomeri as $g): ?>
          <option value="<?= $g['id'] ?>" <?= (($_POST['id_angajat'] ?? '') == $g['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($g['nume']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </fieldset>

    <fieldset>
      <legend>Data și Ora</legend>
      <label>Data:</label>
      <input type="date" name="data" required value="<?= $_POST['data'] ?? '' ?>">

      <label>Ora:</label>
      <input type="time" name="ora" required value="<?= $_POST['ora'] ?? '' ?>">
    </fieldset>

    <fieldset>
      <legend>Observații speciale</legend>
      <textarea name="observatii" rows="3" placeholder="Alergii, preferințe, sensibilități..."><?= $_POST['observatii'] ?? '' ?></textarea>
    </fieldset>

    <button type="submit">Trimite Programarea</button>
  </form>
</div>
</body>
</html>
