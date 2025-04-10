<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]); // Role should always be specified from the login form

    // Define role-based table mappings
    $roles = [
        "user" => ["table" => "users", "id_column" => "user_id", "redirect" => "user_dashboard.php"],
        "driver" => ["table" => "drivers", "id_column" => "driver_id", "redirect" => "driver_dashboard.php"],
        "admin" => ["table" => "admins", "id_column" => "admin_id", "redirect" => "admin_dashboard.php"]
    ];

    // ✅ Check if the selected role is valid
    if (!isset($roles[$role])) {
        echo "<script>alert('Invalid login attempt!'); window.location.href='login.php';</script>";
        exit();
    }

    $table = $roles[$role]["table"];
    $id_column = $roles[$role]["id_column"];
    $redirect_page = $roles[$role]["redirect"];

    // ✅ Prepare and execute the query
    $stmt = $conn->prepare("SELECT $id_column, full_name, password, email " . ($role === "driver" ? ", status" : "") . " FROM $table WHERE email = ?");
    
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // ✅ Verify if the email exists
    if ($stmt->num_rows > 0) {
        if ($role === "driver") {
            $stmt->bind_result($id, $full_name, $hashed_password, $user_email, $status);
        } else {
            $stmt->bind_result($id, $full_name, $hashed_password, $user_email);
            $status = "approved"; // Default for non-drivers
        }

        $stmt->fetch();

        // 🚫 Block pending drivers from logging in
        if ($role === "driver" && strtolower($status) === "pending") {
            echo "<script>alert('Your account is pending approval. Please wait.'); window.location.href='login.php';</script>";
            exit();
        }

        // 🔑 Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["full_name"] = $full_name;
            $_SESSION["email"] = $user_email;
            $_SESSION["role"] = $role; // Set session role to avoid confusion

            // ✅ Redirect to the correct dashboard
            header("Location: $redirect_page");
            exit();
        }
    }

    // ❌ Login failed
    echo "<script>
        alert('Invalid email or password. Please try again.');
        window.history.back(); 
      </script>";
exit();

}

$conn->close();
?>
