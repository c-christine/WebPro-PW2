<?php
// DB connection settings
$host = "localhost";
$user = "cchong3";
$pass = "cchong3";
$dbname = "cchong3";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>