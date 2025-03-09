<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $roles = [
        "users" => "user_id",
        "drivers" => "driver_id",
        "admins" => "admin_id"
    ];

    foreach ($roles as $table => $id_column) {
        $stmt = $conn->prepare("SELECT $id_column, full_name, password, email " . ($table === "drivers" ? ", status" : "") . " FROM $table WHERE email = ?");
        
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            if ($table === "drivers") {
                $stmt->bind_result($id, $full_name, $hashed_password, $user_email, $status);
            } else {
                $stmt->bind_result($id, $full_name, $hashed_password, $user_email);
                $status = "approved"; // Default for non-driver roles
            }

            $stmt->fetch();

            // 🔹 Debugging: Check Retrieved Data
            echo "Entered Email: " . $email . "<br>";
            echo "Entered Password: " . $password . "<br>";
            echo "Hashed Password from Database: " . $hashed_password . "<br>";

            // Check if driver is pending approval
            if ($table === "drivers" && $status === "pending") {
                echo "<script>alert('Your account is pending approval. Please wait.'); window.location.href='index.html';</script>";
                exit();
            }

            // Password verification
            if (password_verify($password, $hashed_password)) {
                echo "✅ Password Match!"; // Debugging line
                $_SESSION["user_id"] = $id;
                $_SESSION["full_name"] = $full_name;
                $_SESSION["email"] = $user_email;
                $_SESSION["role"] = $table;

                // Redirect based on role
                header("Location: " . ($table === "admins" ? "admin_dashboard.php" : ($table === "drivers" ? "driver_dashboard.php" : "user_dashboard.php")));
                exit();
            } else {
                echo "❌ Password Does Not Match!"; // Debugging line
            }
        }
        $stmt->close();
    }

    // If no match found, show error
    echo "<script>alert('Invalid email or password.'); window.location.href='index.html';</script>";
    exit();
}

$conn->close();
?>
