<?php
// Database Connection using PDO
$host = "localhost";
$port = "3306"; // Specify the port
$db_name = "mswdo_dbv2"; // Change to your actual database name
$username = "root"; // Change if needed
$password = ""; // Change if needed

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
