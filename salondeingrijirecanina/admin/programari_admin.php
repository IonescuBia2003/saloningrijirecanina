<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../autentificare.php");
    exit();
}

$month = isset($_GET['luna']) ? (int)$_GET['luna'] : date('n');
$year = isset($_GET['an']) ? (int)$_GET['an'] : date('Y');

$days_ro = ['D', 'L', 'Ma', 'Mi', 'J', 'V', 'S'];
$luni_ro = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDay = date('w', strtotime("$year-$month-01"));

$stmt = $conn->prepare("
    SELECT p.id, p.data, p.ora, c.nume AS caine, s.denumire AS serviciu
    FROM programari p
    JOIN caini c ON p.id_caine = c.id
    JOIN programari_servicii ps ON p.id = ps.id_programare
    JOIN servicii s ON ps.id_serviciu = s.id
    WHERE MONTH(p.data) = ? AND YEAR(p.data) = ?
    ORDER BY p.data, p.ora
");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// Grupăm programările după ID
$programari = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    if (!isset($programari[$id])) {
        $programari[$id] = [
            'data' => $row['data'],
            'ora' => $row['ora'],
            'caine' => $row['caine'],
            'servicii' => []
        ];
    }
    $programari[$id]['servicii'][] = $row['serviciu'];
}

// Grupăm programările după zi
$programari_pe_zi = [];
foreach ($programari as $p) {
    $data = $p['data'];
    if (!isset($programari_pe_zi[$data])) {
        $programari_pe_zi[$data] = [];
    }
    $programari_pe_zi[$data][] = $p;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Calendar programări</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body { font-family: sans-serif; background: #f7f9fb; padding: 30px; }
        .calendar { width: 100%; max-width: 1000px; margin: auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; width: 14.2%; vertical-align: top; height: 120px; padding: 5px; }
        th { background: #2c3e50; color: white; }
        .header { text-align: center; margin-bottom: 20px; }
        .day-number { font-weight: bold; }
        .card { background: #e0f7fa; border-radius: 5px; margin: 3px 0; padding: 2px 5px; font-size: 12px; }
        .nav { text-align: center; margin: 20px; }
        .nav form { display: inline-block; padding: 15px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        select, button { padding: 5px; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="header">
    <h2>📅 Calendar programări - <?= $luni_ro[$month - 1] . ' ' . $year ?></h2>
</div>

<div class="nav">
    <form method="get">
        <label for="luna">Luna:</label>
        <select name="luna" id="luna">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($m == $month) ? 'selected' : '' ?>><?= $luni_ro[$m - 1] ?></option>
            <?php endfor; ?>
        </select>
        <label for="an">An:</label>
        <select name="an" id="an">
            <?php for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                <option value="<?= $y ?>" <?= ($y == $year) ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit">🔍 Afișează</button>
    </form>
</div>

<table class="calendar">
    <tr>
        <?php foreach ($days_ro as $day): ?>
            <th><?= $day ?></th>
        <?php endforeach; ?>
    </tr>
    <tr>
        <?php
        $dayCount = 0;
        for ($i = 0; $i < $firstDay; $i++) {
            echo "<td></td>";
            $dayCount++;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            echo "<td><div class='day-number'>$day</div>";

            foreach ($programari_pe_zi[$date] ?? [] as $p) {
                $servicii = implode(', ', $p['servicii']);
                echo "<div class='card'>{$p['ora']} - {$p['caine']}<br><small>$servicii</small></div>";
            }

            echo "</td>";
            $dayCount++;
            if ($dayCount % 7 == 0) echo "</tr><tr>";
        }

        while ($dayCount % 7 != 0) {
            echo "<td></td>";
            $dayCount++;
        }
        ?>
    </tr>
</table>

</body>
</html>
