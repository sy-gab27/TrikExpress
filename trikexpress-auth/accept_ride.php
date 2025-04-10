<?php
session_start();
include "db_connect.php"; 

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "driver") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$driverId = $_SESSION["user_id"];
$rideId = intval($_POST["ride_id"]);

// ✅ Debugging log to track the driver ID and incoming ride ID
error_log("Driver ID: " . $driverId);
error_log("Ride ID being accepted: " . $rideId);

// ✅ Check if the driver actually has an active ride
$activeRideCheck = $conn->prepare("
    SELECT id, status FROM bookings WHERE driver_id = ? AND status IN ('accepted', 'in_progress') LIMIT 1
");
$activeRideCheck->bind_param("i", $driverId);
$activeRideCheck->execute();
$activeRideCheck->store_result();
$activeRideCheck->bind_result($existingRideId, $existingRideStatus);

if ($activeRideCheck->fetch()) {
    // If the driver already has an active ride, let's allow them to cancel or end the current ride
    $activeRideCheck->close();

    // Optionally: You can choose to automatically update the status of the existing ride to 'completed' or 'cancelled'
    $updateCurrentRideStmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE driver_id = ? AND status = 'accepted' LIMIT 1");
    $updateCurrentRideStmt->bind_param("i", $driverId);
    $updateCurrentRideStmt->execute();
    $updateCurrentRideStmt->close();

    // Now proceed with accepting the new ride
    // ✅ Accept the new ride
    $updateStmt = $conn->prepare("UPDATE bookings SET status = 'accepted', driver_id = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $driverId, $rideId);

    if ($updateStmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Ride accepted!",
            "ride_id" => $rideId,
            "pickup" => $ride["pickup"],
            "destination" => $ride["destination"],
            "fare" => floatval($ride["fare"])
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }

    $updateStmt->close();
} else {
    $activeRideCheck->close();

    // ✅ Ensure the ride is still pending
    $checkStmt = $conn->prepare("SELECT pickup, destination, fare FROM bookings WHERE id = ? AND status = 'pending' LIMIT 1");
    $checkStmt->bind_param("i", $rideId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $ride = $result->fetch_assoc();
    $checkStmt->close();

    if (!$ride) {
        echo json_encode(["status" => "error", "message" => "Ride is no longer available."]);
        exit();
    }

    // ✅ Accept the ride (no active ride found)
    $updateStmt = $conn->prepare("UPDATE bookings SET status = 'accepted', driver_id = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $driverId, $rideId);

    if ($updateStmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Ride accepted!",
            "ride_id" => $rideId,
            "pickup" => $ride["pickup"],
            "destination" => $ride["destination"],
            "fare" => floatval($ride["fare"])
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }

    $updateStmt->close();
}

$conn->close();
?>
