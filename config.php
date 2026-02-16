<?php
// AT LEAST REPLACE THE PASSWORD!
$servername = "localhost";
$username = "journal";
$password = "SECURE PASSWORD HERE";
$dbname = "dreamJournal";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");
?>
