<?php
session_start();
include "db_connect.php"; 

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$userId = $_SESSION["user_id"];
$role = $_SESSION["role"];

// ✅ Set correct table based on role
$table = ($role === "user") ? "users" : (($role === "driver") ? "drivers" : "admins");

// ✅ Ensure users and drivers can edit their profile (admins cannot)
if ($role === "admin") {
    echo "<script>alert('❌ Admins cannot edit their profile here.'); window.location.href='profile.php';</script>";
    exit();
}

// ✅ Validate Phone Number Input
$phoneNumber = isset($_POST["phone_number"]) ? trim($_POST["phone_number"]) : "";

// ✅ Ensure `uploads/` folder exists
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Profile Picture Upload Handling
$profilePicPath = "";
if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] === 0) {
    $allowed = ["jpg", "jpeg", "png", "gif"];
    $fileExt = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed) || $_FILES["profile_pic"]["size"] > 2 * 1024 * 1024) {
        echo "<script>alert('❌ Invalid file type or size too large. Only JPG, PNG, and GIF allowed (max 2MB).'); window.location.href='profile.php';</script>";
        exit();
    }

    $profilePicPath = $uploadDir . uniqid() . "." . $fileExt;
    if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $profilePicPath)) {
        echo "<script>alert('❌ File upload failed. Please try again.'); window.location.href='profile.php';</script>";
        exit();
    }
}

// ✅ Fetch Current Profile Pic (to avoid overwriting if not updating)
$stmt = $conn->prepare("SELECT profile_pic FROM $table WHERE {$role}_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentData = $result->fetch_assoc();
$stmt->close();

// If profile pic is not updated, keep the old one
if (empty($profilePicPath) && !empty($currentData["profile_pic"])) {
    $profilePicPath = $currentData["profile_pic"];
}

// ✅ Prevent Changes to Name & Email
$stmt = $conn->prepare("SELECT full_name, email FROM $table WHERE {$role}_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$originalData = $result->fetch_assoc();
$stmt->close();

if (!$originalData) {
    echo "<script>alert('❌ User not found.'); window.location.href='profile.php';</script>";
    exit();
}

// ✅ Update Profile Information (Only Phone Number & Profile Picture)
$stmt = $conn->prepare("UPDATE $table SET phone_number = ?, profile_pic = ? WHERE {$role}_id = ?");
$stmt->bind_param("ssi", $phoneNumber, $profilePicPath, $userId);

if ($stmt->execute()) {
    echo "<script>alert('✅ Profile updated successfully!'); window.location.href='profile.php';</script>";
} else {
    echo "<script>alert('❌ Error updating profile. Please try again.'); window.location.href='profile.php';</script>";
}

$stmt->close();
$conn->close();
?>
