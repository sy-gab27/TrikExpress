<?php
session_start();
include "db_connect.php"; 

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    header("Location: index.html");
    exit();
}

$userId = $_SESSION["user_id"];
$role = $_SESSION["role"];

// ✅ Set correct table based on role
$table = ($role === "user") ? "users" : (($role === "driver") ? "drivers" : "admins");

// ✅ Fetch user details
$stmt = $conn->prepare("SELECT full_name, email, phone_number, profile_pic FROM $table WHERE {$role}_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ If user not found, redirect
if (!$user) {
    echo "<script>alert('User not found.'); window.location.href = 'index.html';</script>";
    exit();
}

// ✅ Set profile picture (default if not set)
$profilePic = !empty($user["profile_pic"]) ? $user["profile_pic"] : "images/default-profile.png";

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | TrikExpress</title>

    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg bg-dark navbar-dark fixed-top">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="burger-menu" onclick="toggleSidebar()">☰</div>
        <a class="navbar-brand mx-auto"><i class="fas fa-motorcycle"></i> TrikExpress</a>
    </div>
</nav>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <div class="branding">
        <i class="fas fa-motorcycle"></i> TrikExpress
    </div>
    <div class="sidebar-links">
        <a href="<?php echo $role; ?>_dashboard.php" class="square-btn"><i class="fas fa-home"></i> Dashboard</a>
        <a href="profile.php" class="square-btn active"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- ✅ Profile Content -->
<div class="content d-flex flex-column align-items-center">
    <h2 class="mb-4 text-white">Profile Details</h2>

    <div class="profile-container">
        <form action="update_profile.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="role" value="<?php echo $role; ?>">

            <!-- ✅ Profile Picture Upload -->
            <div class="profile-pic-container">
                <label for="profile_pic">
                    <img id="profileImage" src="<?php echo $profilePic; ?>" alt="Profile Picture">
                    <div class="edit-icon"><i class="fas fa-camera"></i></div>
                </label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(event)">
            </div>
            <?php if ($role !== "admin") { ?>
                <button type="submit" class="btn btn-primary w-100 mt-3">Upload Picture</button>
            <?php } ?>

            <!-- ✅ User Details -->
            <div class="form-group mt-3">
                <label>Full Name:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled>
            </div>
            <div class="form-group mt-3">
                <label>Email:</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group mt-3">
                <label>Phone Number:</label>
                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" <?php echo ($role === "admin") ? "disabled" : ""; ?>>
            </div>

            <!-- ✅ Save Button -->
            <?php if ($role !== "admin") { ?>
                <button type="submit" class="btn btn-success w-100 mt-3">Save Changes</button>
            <?php } ?>
        </form>
    </div>
</div>

<!-- ✅ JavaScript for Sidebar Toggle -->
<script>
 function toggleSidebar() {
            const sidebar = document.querySelector(".sidebar");
            sidebar.classList.toggle("active");
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profileImage');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
</script>

</body>
</html>
