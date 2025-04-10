<?php
session_start();
include "db_connect.php"; 

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "driver") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$rideId = intval($_POST["ride_id"]);

// ✅ Update ride status to "canceled"
$sql = "UPDATE bookings SET status = 'canceled' WHERE id = ? AND status = 'accepted'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rideId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Ride canceled."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to cancel ride."]);
}

$stmt->close();
$conn->close();
?>
