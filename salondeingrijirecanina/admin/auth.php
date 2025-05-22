<?php
function verifica_admin_sau_angajat() {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['admin', 'angajat'])) {
        header("Location: ../login.php");
        exit();
    }
}

function verifica_client() {
    if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'client') {
        header("Location: login.php");
        exit();
    }
}
