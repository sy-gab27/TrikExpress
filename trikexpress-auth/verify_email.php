<?php
include 'db_connect.php';

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Check if token exists in users or drivers table
    $stmt = $conn->prepare("
        SELECT 'users' AS role FROM users WHERE verification_token = ? AND is_verified = 0
        UNION 
        SELECT 'drivers' AS role FROM drivers WHERE verification_token = ? AND is_verified = 0
    ");
    $stmt->bind_param("ss", $token, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($role);
        $stmt->fetch();
        $stmt->close();

        // Update the account as verified
        $updateStmt = $conn->prepare("UPDATE $role SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $updateStmt->bind_param("s", $token);
        if ($updateStmt->execute()) {
            echo "<script>alert('Your email has been verified! You can now login.'); window.location.href='index.html';</script>";
        } else {
            echo "<script>alert('Verification failed. Try again.'); window.location.href='index.html';</script>";
        }
        $updateStmt->close();
    } else {
        echo "<script>alert('Invalid or expired token.'); window.location.href='index.html';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='index.html';</script>";
}

$conn->close();
?>
