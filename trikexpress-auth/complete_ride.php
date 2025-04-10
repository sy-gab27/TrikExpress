<?php
session_start();
include "db_connect.php"; // Ensure database connection

header("Content-Type: application/json");

// ✅ Ensure the driver is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "driver") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$driverId = $_SESSION["user_id"];

// ✅ Validate ride ID
if (!isset($_POST["ride_id"]) || !is_numeric($_POST["ride_id"])) {
    echo json_encode(["status" => "error", "message" => "Invalid Ride ID."]);
    exit();
}
$rideId = intval($_POST["ride_id"]);

// ✅ Move ride to `ride_history`
$sql = "INSERT INTO ride_history (ride_id, user_id, driver_id, pickup, destination, status, fare, created_at) 
        SELECT id, user_id, driver_id, pickup, destination, 'completed', fare, NOW() FROM bookings WHERE id = ? AND driver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $rideId, $driverId);

if ($stmt->execute()) {
    // ✅ Delete ride from `bookings`
    $deleteStmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $deleteStmt->bind_param("i", $rideId);
    $deleteStmt->execute();
    $deleteStmt->close();

    echo json_encode(["status" => "success", "message" => "Ride completed!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
