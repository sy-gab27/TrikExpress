<?php
session_start();
include 'db_connect.php'; // Make sure this file is correctly set up for database connections

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "users") {
    header("Location: index.html");
    exit();
}

$userId = $_SESSION["user_id"];

// Fetch user details
$stmt = $conn->prepare("SELECT full_name, email, phone_number, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone_number"]);

    // Handle profile picture upload
    if (!empty($_FILES["profile_pic"]["name"])) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["profile_pic"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath);

        // Update with new profile picture
        $updateStmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone_number=?, profile_pic=? WHERE user_id=?");
        $updateStmt->bind_param("ssssi", $fullName, $email, $phone, $targetFilePath, $userId);
    } else {
        // Update without changing profile picture
        $updateStmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone_number=? WHERE user_id=?");
        $updateStmt->bind_param("sssi", $fullName, $email, $phone, $userId);
    }

    if ($updateStmt->execute()) {
        $_SESSION["full_name"] = $fullName;
        $_SESSION["email"] = $email;
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrikExpress | Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="burger-menu" onclick="toggleSidebar()">☰</div>
        <div class="sidebar-links">
            <h2>TrikExpress</h2>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="content">
        <h2>User Profile</h2>
        <div class="profile-container">
            <form method="post" enctype="multipart/form-data">
                <div class="profile-pic-container">
                    <img id="profileImage" src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'default-profile.png'; ?>" alt="Profile Picture">
                    <label for="profile_pic" class="edit-icon">📷</label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(event)">
                </div>

                <div class="input-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user["full_name"]); ?>" required>
                </div>
                <div class="input-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user["email"]); ?>" required>
                </div>
                <div class="input-group">
                    <label>Phone Number:</label>
                    <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user["phone_number"]); ?>" required>
                </div>

                <button type="submit" class="btn save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const img = document.getElementById("profileImage");
                img.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function toggleSidebar() {
            let sidebar = document.querySelector(".sidebar");
            let content = document.querySelector(".content");

            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-250px";
                content.style.marginLeft = "0";
            } else {
                sidebar.style.left = "0px";
                content.style.marginLeft = "250px";
            }
        }
    </script>

</body>
</html>
