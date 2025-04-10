<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_pic"])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    // Check if it's an image
    $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];

    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
            echo "<script>alert('Profile picture uploaded successfully!'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Error uploading file.');</script>";
        }
    } else {
        echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, GIF allowed.');</script>";
    }
}
?>
