<?php
session_start();
include "db_connect.php"; // Ensure this file correctly connects to MySQL

// ✅ Enable error reporting (REMOVE in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Ensure the driver is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "drivers") {
    die(json_encode(["status" => "error", "message" => "Unauthorized access."]));
}

// ✅ Fetch pending rides with coordinates
$sql = "SELECT id, pickup, pickup_lat, pickup_lng, destination, fare FROM bookings WHERE status = 'pending'";
$result = $conn->query($sql);

// ✅ Check for database errors
if (!$result) {
    die(json_encode(["status" => "error", "message" => "Database error: " . $conn->error]));
}

$rides = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Ensure latitude and longitude are properly formatted
    $row['pickup_lat'] = floatval($row['pickup_lat']); // Convert to float
    $row['pickup_lng'] = floatval($row['pickup_lng']); // Convert to float
    $rides[] = $row;
}

// ✅ Return JSON response
header("Content-Type: application/json");
echo json_encode($rides);

$conn->close();
?>
