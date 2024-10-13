<?php
session_start(); // Start a session

// Connect to the database
$servername = "127.0.0.1"; // Your database server
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "idesia"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the email key exists in the POST request
if (isset($_POST['email'])) {
    $email = $_POST['email']; // Get email from POST request

    // Prepare the SQL statement to check for the user
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(json_encode(array("registered" => false, "error" => "SQL prepare failed: " . $conn->error)));
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Check for execution errors
    if ($stmt->error) {
        die(json_encode(array("registered" => false, "error" => "Execute failed: " . $stmt->error)));
    }

    // Get the result
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        // User is registered
        echo json_encode(array("registered" => true));
    } else {
        // User is not registered
        echo json_encode(array("registered" => false));
    }

    // Close the statement
    $stmt->close();
} else {
    // Handle the case where the email was not provided
    echo json_encode(array("registered" => false, "error" => "Email not provided."));
}

// Close the connection
$conn->close();
?>
