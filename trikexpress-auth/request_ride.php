<?php
session_start();
include "db_connect.php";

header("Content-Type: application/json");

// ✅ Ensure user is logged in and is a passenger
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$userId = $_SESSION["user_id"];

// ✅ Handle Ride Cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "cancel") {
    $cancelQuery = "UPDATE bookings SET status = 'canceled' WHERE user_id = ? AND status = 'pending'";
    $cancelStmt = $conn->prepare($cancelQuery);
    $cancelStmt->bind_param("i", $userId);
    $cancelStmt->execute();

    if ($cancelStmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "🚫 Ride canceled successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "❌ No pending ride to cancel."]);
    }

    $cancelStmt->close();
    $conn->close();
    exit();
}

// ✅ Prevent Duplicate Ride Requests
$checkQuery = "SELECT id FROM bookings WHERE user_id = ? AND status IN ('pending', 'accepted')";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "❌ You already have a pending ride request."]);
    $checkStmt->close();
    exit();
}
$checkStmt->close();

// ✅ Validate Required Inputs
$requiredFields = ["pickup", "destination", "fare", "pickup_lat", "pickup_lng", "dropoff_lat", "dropoff_lng"];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        echo json_encode(["status" => "error", "message" => "❌ Missing or invalid ride details: $field"]);
        exit();
    }
}

// ✅ Sanitize & Convert Inputs
$pickup = htmlspecialchars(trim($_POST["pickup"]));
$destination = htmlspecialchars(trim($_POST["destination"]));
$fare = floatval($_POST["fare"]);
$pickupLat = floatval($_POST["pickup_lat"]);
$pickupLng = floatval($_POST["pickup_lng"]);
$dropoffLat = floatval($_POST["dropoff_lat"]);
$dropoffLng = floatval($_POST["dropoff_lng"]);

// ✅ Ensure Valid Coordinates
if (!($pickupLat >= -90 && $pickupLat <= 90) || !($pickupLng >= -180 && $pickupLng <= 180) || 
    !($dropoffLat >= -90 && $dropoffLat <= 90) || !($dropoffLng >= -180 && $dropoffLng <= 180)) {
    echo json_encode(["status" => "error", "message" => "❌ Invalid location coordinates."]);
    exit();
}

// ✅ Insert New Ride Request
$insertQuery = "INSERT INTO bookings (user_id, pickup, pickup_lat, pickup_lng, destination, dropoff_lat, dropoff_lng, fare, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("issssssd", $userId, $pickup, $pickupLat, $pickupLng, $destination, $dropoffLat, $dropoffLng, $fare);

if ($insertStmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "🚖 Ride request placed successfully!",
        "ride_id" => $insertStmt->insert_id
    ]);
} else {
    error_log("❌ Ride request failed: " . $insertStmt->error); // Log error
    echo json_encode(["status" => "error", "message" => "❌ Ride request failed. Please try again later."]);
}

$insertStmt->close();
$conn->close();
?>
