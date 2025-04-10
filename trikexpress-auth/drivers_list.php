<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.html");
    exit();
}

// Fetch all drivers
$stmt = $conn->prepare("SELECT driver_id, full_name, email, phone_number, status, psa_doc, valid_id FROM drivers");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers List | TrikExpress</title>

    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <!-- ✅ Burger Menu -->
    <div class="burger-menu" onclick="toggleSidebar()">☰</div>

    <!-- ✅ Sidebar -->
    <div class="sidebar">
        <div class="branding"><i class="fas fa-motorcycle"></i> TrikExpress</div>
        <div class="sidebar-links">
            <a href="admin_dashboard.php" class="square-btn"><i class="fas fa-home"></i> Dashboard</a>
            <a href="drivers_list.php" class="square-btn active"><i class="fas fa-user-tie"></i> Drivers</a>
            <a href="users_list.php" class="square-btn"><i class="fas fa-users"></i> Users</a>
            <a href="signup-driver.html" class="square-btn"><i class="fas fa-id-card"></i> Register Driver</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- ✅ Main Content -->
    <div class="content">
        <h2 class="text-center">Registered Drivers</h2>
        <div class="table-container">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>PSA Document</th>
                        <th>Valid ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr id="driver-<?php echo $row['driver_id']; ?>">
                            <td><?php echo $row["full_name"]; ?></td>
                            <td><?php echo $row["email"]; ?></td>
                            <td><?php echo $row["phone_number"]; ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($row["status"] === 'approved') ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($row["status"]); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row["psa_doc"])) { ?>
                                    <a href="uploads/<?php echo $row["psa_doc"]; ?>" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-file-alt"></i> View PSA
                                    </a>
                                <?php } else { echo "Not Provided"; } ?>
                            </td>
                            <td>
                                <?php if (!empty($row["valid_id"])) { ?>
                                    <a href="uploads/<?php echo $row["valid_id"]; ?>" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-id-card"></i> View ID
                                    </a>
                                <?php } else { echo "Not Provided"; } ?>
                            </td>
                            <td>
                                <?php if ($row["status"] === 'pending') { ?>
                                    <button class="btn btn-success btn-sm" onclick="approveDriver(<?php echo $row['driver_id']; ?>)">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="denyDriver(<?php echo $row['driver_id']; ?>)">Deny</button>
                                <?php } else { ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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

        function approveDriver(driverId) {
            if (!confirm("Are you sure you want to approve this driver?")) return;

            fetch("approve_driver.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "driver_id=" + driverId
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    location.reload();
                }
            })
            .catch(error => console.error("Error approving driver:", error));
        }

        function denyDriver(driverId) {
            if (!confirm("Are you sure you want to deny this driver?")) return;

            fetch("deny_driver.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "driver_id=" + driverId
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    location.reload();
                }
            })
            .catch(error => console.error("Error denying driver:", error));
        }
    </script>

</body>
</html>
