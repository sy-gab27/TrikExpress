<?php
session_start();
include "db_connect.php"; // Ensure this file correctly connects to MySQL

// ✅ Enable error reporting (REMOVE in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Ensure user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "users") {
    die(json_encode(["status" => "error", "message" => "Unauthorized access."]));
}

// ✅ Check if all required fields are received
if (!isset($_POST["pickup"], $_POST["pickup_lat"], $_POST["pickup_lng"], $_POST["destination"])) {
    die(json_encode(["status" => "error", "message" => "Missing required fields."]));
}

$userId = $_SESSION["user_id"];
$pickup = mysqli_real_escape_string($conn, $_POST["pickup"]);
$pickupLat = floatval($_POST["pickup_lat"]);
$pickupLng = floatval($_POST["pickup_lng"]);
$destination = mysqli_real_escape_string($conn, $_POST["destination"]);

// ✅ Ensure coordinates are valid
if ($pickupLat === 0.000000 || $pickupLng === 0.000000) {
    die(json_encode(["status" => "error", "message" => "Invalid coordinates received."]));
}

// ✅ Insert ride request into database
$sql = "INSERT INTO bookings (user_id, pickup, pickup_lat, pickup_lng, destination, status) 
        VALUES ('$userId', '$pickup', '$pickupLat', '$pickupLng', '$destination', 'pending')";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Ride request sent!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$conn->close();
?>
