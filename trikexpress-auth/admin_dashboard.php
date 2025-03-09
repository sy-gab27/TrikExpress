<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admins") {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TrikExpress</title>

    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <!-- ✅ Small Transparent Burger Button -->
    <div class="burger-menu" onclick="toggleSidebar()">☰</div>

    <!-- ✅ Sidebar -->
    <div class="sidebar">
        <div class="branding"><i class="fas fa-motorcycle"></i> TrikExpress</div>
        <div class="sidebar-links">
            <a href="admin_dashboard.php" class="square-btn"><i class="fas fa-home"></i> Dashboard</a>
            <a href="drivers_list.php" class="square-btn"><i class="fas fa-user-tie"></i> Drivers</a>
            <a href="users_list.php" class="square-btn"><i class="fas fa-users"></i> Users</a>
            <a href="signup-driver.html" class="square-btn"><i class="fas fa-id-card"></i> Register Driver</a>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- ✅ Main Content -->
    <div class="content">
        <h2 class="text-center">Welcome, Admin!</h2>
        <p class="text-center">Manage your system efficiently.</p>
<!-- ✅ Admin Options (Square Buttons) -->
<div class="admin-options">
    <a href="drivers_list.php" class="option-btn"><i class="fas fa-user-tie"></i> Manage Drivers</a>
    <a href="users_list.php" class="option-btn"><i class="fas fa-users"></i> Manage Users</a>
    <a href="signup-driver.html" class="option-btn"><i class="fas fa-id-card"></i> Register Driver</a>
</div>

    </div>

    <!-- ✅ Bootstrap & JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector(".sidebar").classList.toggle("active");
            document.querySelector(".content").classList.toggle("shift");
        }
    </script>

</body>
</html>
