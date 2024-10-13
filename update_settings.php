<?php
// update_settings.php

// Database connection
$conn = new mysqli('127.0.0.1', 'root', '', 'idesia');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind
$stmt = $conn->prepare("UPDATE settings SET model1=?, model1_price=?, model2=?, model2_price=?, model3=?, model3_price=?, facebook=?, twitter=?, instagram=?, phone=?, email=? WHERE id=?");

$stmt->bind_param("ssdssdsdssss", $model1, $model1_price, $model2, $model2_price, $model3, $model3_price, $facebook, $twitter, $instagram, $phone, $email, $id);

// Set parameters and execute with checks for undefined keys
$model1 = isset($_POST['model1']) ? $_POST['model1'] : '';
$model1_price = isset($_POST['model1_price']) ? floatval($_POST['model1_price']) : 0.00;
$model2 = isset($_POST['model2']) ? $_POST['model2'] : '';
$model2_price = isset($_POST['model2_price']) ? floatval($_POST['model2_price']) : 0.00;
$model3 = isset($_POST['model3']) ? $_POST['model3'] : '';
$model3_price = isset($_POST['model3_price']) ? floatval($_POST['model3_price']) : 0.00;
$facebook = isset($_POST['facebook']) ? $_POST['facebook'] : '';
$twitter = isset($_POST['twitter']) ? $_POST['twitter'] : '';
$instagram = isset($_POST['instagram']) ? $_POST['instagram'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$id = 1; // Update this with the correct ID as necessary

if ($stmt->execute()) {
    echo "Settings updated successfully.";
} else {
    echo "Error updating settings: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
