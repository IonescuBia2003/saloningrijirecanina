<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "saloncanin";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}
?>
