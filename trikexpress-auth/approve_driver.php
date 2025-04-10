<?php
session_start();
include "db_connect.php";

// ✅ Ensure the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

// ✅ Check if driver_id is provided
if (!isset($_POST["driver_id"])) {
    echo json_encode(["status" => "error", "message" => "Missing driver ID."]);
    exit();
}

$driverId = intval($_POST["driver_id"]);

// ✅ Approve the driver by updating their status
$stmt = $conn->prepare("UPDATE drivers SET status = 'approved' WHERE driver_id = ?");
$stmt->bind_param("i", $driverId);

if ($stmt->execute()) {
    // ✅ Insert a notification for the driver
    $message = "✅ Your driver application has been approved! You can now start accepting rides.";
    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, 'driver', ?)");
    $notifStmt->bind_param("is", $driverId, $message);
    $notifStmt->execute();
    $notifStmt->close();

    echo json_encode(["status" => "success", "message" => "Driver approved successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error. Try again."]);
}

$stmt->close();
$conn->close();
?>
