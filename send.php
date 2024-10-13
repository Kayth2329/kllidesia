<?php
// Database connection
$servername = "127.0.0.1"; // Your database server
$dbUsername = "root"; // Your database username
$dbPassword = ""; // Your database password
$dbname = "idesia"; // Your database name

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_email = $_POST['recipient_email'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $monthly_income = $_POST['income'];
    $date_of_visit = $_POST['date'];
    $time_of_visit = $_POST['time'];

    // Prepare the SQL query
    $stmt = $conn->prepare("INSERT INTO scheduled_trippings (recipient_email, name, age, gender, monthly_income, date_of_visit, time_of_visit) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $recipient_email, $name, $age, $gender, $monthly_income, $date_of_visit, $time_of_visit);

    // Execute the query
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
