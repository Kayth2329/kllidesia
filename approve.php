<?php
require 'config.php'; // Include your database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirement_id = $_POST['requirement_id'];
    $username = $_POST['username'];

    // Update the file's approval status
    $stmt = $conn->prepare("UPDATE user_files SET approved = 1 WHERE requirement_id = ? AND username = ?");
    $stmt->bind_param("is", $requirement_id, $username);

    if ($stmt->execute()) {
        header("Location: adminDashboard.php?success=1"); // Redirect with success
    } else {
        echo "Error updating file status.";
    }
}
?>
