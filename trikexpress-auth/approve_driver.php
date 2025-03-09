<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admins") {
    header("Location: login.php");
    exit();
}

if (isset($_GET["id"])) {
    $driver_id = $_GET["id"];
    $stmt = $conn->prepare("UPDATE drivers SET status='approved' WHERE driver_id=?");
    $stmt->bind_param("i", $driver_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Driver approved!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error approving driver.'); window.location.href='admin_dashboard.php';</script>";
    }
}
?>
