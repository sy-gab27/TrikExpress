<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "drivers") {
    die(json_encode(["status" => "error", "message" => "Unauthorized access."]));
}

$driverId = $_SESSION["user_id"];
$rideId = $_POST["ride_id"];

$sql = "UPDATE bookings SET status = 'accepted', driver_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $driverId, $rideId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Ride accepted!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
