<?php
session_start();
require 'db_connect.php'; // Ensure database connection

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$userId = $_SESSION["user_id"];
$role = $_SESSION["role"];

if ($role === "user") {
    // ✅ Fetch the most recent ride (pending or accepted)
    $query = "SELECT id, driver_id, pickup, destination, status, fare 
              FROM bookings 
              WHERE user_id = ? AND status IN ('pending', 'accepted') 
              ORDER BY created_at DESC LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ride = $result->fetch_assoc();
    $stmt->close();

    if ($ride) {
        // ✅ Successfully fetched ride
        echo json_encode([
            "status" => "success",
            "ride_id" => $ride["id"],
            "driver_id" => $ride["driver_id"],
            "pickup" => $ride["pickup"],
            "destination" => $ride["destination"],
            "ride_status" => $ride["status"],
            "fare" => floatval($ride["fare"])
        ]);
        exit();
    } else {
        // ✅ No active rides
        echo json_encode(["status" => "no_rides"]); 
        exit();
    }
}

if ($role === "driver") {
    // ✅ Fetch available rides for drivers
    $query = "SELECT id, user_id, pickup, pickup_lat, pickup_lng, destination, fare 
              FROM bookings 
              WHERE status = 'pending' 
              ORDER BY created_at ASC";

    $result = $conn->query($query);
    $rides = [];

    while ($row = $result->fetch_assoc()) {
        $rides[] = $row;
    }

    if (count($rides) > 0) {
        // ✅ Return list of available rides
        echo json_encode(["status" => "success", "rides" => $rides]);
    } else {
        // ✅ No available rides for driver
        echo json_encode(["status" => "no_rides"]);
    }
    exit();
}

// ✅ If the request is invalid or role is not recognized
echo json_encode(["status" => "error", "message" => "Invalid request or user role."]);
$conn->close();
?>
