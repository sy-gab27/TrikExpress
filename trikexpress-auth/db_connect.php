<?php
$host = "localhost"; // Change to your actual database host if needed
$user = "root";      // Change to your actual database username
$pass = "";          // Change to your actual database password
$db = "trikexpress"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
