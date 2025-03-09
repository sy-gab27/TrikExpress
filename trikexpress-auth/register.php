<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $phone = trim($_POST["phone_number"]);
    $role = trim($_POST["role"]);

    // Encrypt Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Default status
    $status = ($role == "driver") ? "pending" : "approved";

    // Check if email is already registered
    $checkStmt = $conn->prepare("
        SELECT email FROM users WHERE email = ? 
        UNION 
        SELECT email FROM drivers WHERE email = ? 
        UNION 
        SELECT email FROM admins WHERE email = ?
    ");
    $checkStmt->bind_param("sss", $email, $email, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>alert('Email is already registered. Use a different email.'); window.location.href='signup.html';</script>";
        exit();
    }
    $checkStmt->close();

    // Insert into the respective table
    if ($role == "driver") {
        $stmt = $conn->prepare("INSERT INTO drivers (full_name, email, password, phone_number, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $hashed_password, $phone, $status);
    } elseif ($role == "admin") {
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password, phone_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $hashed_password, $phone);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone_number, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $hashed_password, $phone, $status);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='index.html';</script>";
        exit();
    } else {
        echo "<script>alert('Registration failed. Try again.'); window.location.href='signup.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
