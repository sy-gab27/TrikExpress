<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admins") {
    header("Location: index.html");
    exit();
}

if (!isset($_GET["email"])) {
    echo "<script>alert('Invalid request.'); window.location.href='admin_dashboard.php';</script>";
    exit();
}

$email = $_GET["email"];

// Fetch driver details
$stmt = $conn->prepare("SELECT full_name, id_number, gender, marital_status, psa_doc, valid_id, status FROM drivers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

if (!$driver) {
    echo "<script>alert('Driver not found.'); window.location.href='admin_dashboard.php';</script>";
    exit();
}

// Handle Approve & Reject
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["approve"])) {
        $updateStmt = $conn->prepare("UPDATE drivers SET status = 'approved' WHERE email = ?");
    } elseif (isset($_POST["reject"])) {
        $updateStmt = $conn->prepare("UPDATE drivers SET status = 'rejected' WHERE email = ?");
    }

    $updateStmt->bind_param("s", $email);
    if ($updateStmt->execute()) {
        $message = (isset($_POST["approve"])) ? "Driver approved successfully!" : "Driver rejected.";
        echo "<script>alert('$message'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating driver status.'); window.location.href='driver_verification.php?email=$email';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Verification | TrikExpress</title>
    <link rel="stylesheet" href="admin.css">
    <script defer src="script.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="burger-menu" onclick="toggleSidebar()">☰</div>
        <div class="sidebar-links">
            <h2>TrikExpress</h2>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="drivers_list.php" class="active">Drivers</a>
            <a href="users_list.php">Users</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Driver Verification</h2>
        <table>
            <tr><th>Full Name:</th><td><?php echo $driver["full_name"]; ?></td></tr>
            <tr><th>ID Number:</th><td><?php echo $driver["id_number"]; ?></td></tr>
            <tr><th>Gender:</th><td><?php echo $driver["gender"]; ?></td></tr>
            <tr><th>Marital Status:</th><td><?php echo $driver["marital_status"]; ?></td></tr>
            <tr>
                <th>PSA Document:</th>
                <td><a href="uploads/<?php echo $driver["psa_doc"]; ?>" target="_blank">View PSA</a></td>
            </tr>
            <tr>
                <th>Valid ID:</th>
                <td><a href="uploads/<?php echo $driver["valid_id"]; ?>" target="_blank">View ID</a></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td><?php echo ucfirst($driver["status"]); ?></td>
            </tr>
        </table>

        <form method="post">
            <button type="submit" name="approve" class="btn approve-btn">Approve Driver</button>
            <button type="submit" name="reject" class="btn reject-btn">Reject Driver</button>
        </form>
    </div>

</body>
</html>
