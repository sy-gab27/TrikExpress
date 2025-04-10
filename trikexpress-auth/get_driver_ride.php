<?php
session_start();
include "db_connect.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "driver") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$driverId = $_SESSION["user_id"];

// ✅ Fetch the driver's active ride
$query = $conn->prepare("
    SELECT id, pickup, destination, fare FROM bookings 
    WHERE driver_id = ? AND status IN ('accepted', 'in_progress') 
    LIMIT 1
");
$query->bind_param("i", $driverId);
$query->execute();
$result = $query->get_result();
$ride = $result->fetch_assoc();

if ($ride) {
    echo json_encode([
        "status" => "success",
        "ride_id" => $ride["id"],
        "pickup" => $ride["pickup"],
        "destination" => $ride["destination"],
        "fare" => floatval($ride["fare"])
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "No active ride."]);
}

$query->close();
$conn->close();
?>
