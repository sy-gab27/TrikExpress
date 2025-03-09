<?php
session_start();

// Determine the correct login page based on the role
$redirectPage = "index.html"; // Default fallback

if (isset($_SESSION["role"])) {
    switch ($_SESSION["role"]) {
        case "admins":
            $redirectPage = "login-admin.html"; // Redirect to Admin Login
            break;
        case "drivers":
            $redirectPage = "login-driver.html"; // Redirect to Driver Login
            break;
        case "users":
            $redirectPage = "login-user.html"; // Redirect to User Login
            break;
    }
}

// Destroy session and redirect
session_unset();
session_destroy();
header("Location: $redirectPage");
exit();
?>
