<?php
include "db_connect.php"; 

header("Content-Type: application/json");

$rideId = intval($_GET["ride_id"]);

$sql = "SELECT status FROM bookings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rideId);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

echo json_encode(["status" => $status]);
$conn->close();
?>
