<?php
// Database connection
$servername = "127.0.0.1"; // Your database server
$dbUsername = "root"; // Your database username
$dbPassword = ""; // Your database password
$dbname = "idesia"; // Your database name

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    $sql = "INSERT INTO scheduled_trippings (recipient_email, name, age, gender, monthly_income, date_of_visit, time_of_visit)
            VALUES ('$recipient_email', '$name', '$age', '$gender', '$monthly_income', '$date_of_visit', '$time_of_visit')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Tripping | IDESIA Subdivision</title>
</head>

<style>
    body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background-color: #f0f0f0;
}

.header 
{
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /*navigation section*/
        .top-nav {
            display: flex;
            justify-content: flex-end; 
            align-items: center;
            gap: 15px; 
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #D2B48C;
            padding: 10px;
            z-index: 1000;
        }
        .schedule {
            background-color: #F0E6D2; 
            padding: 10px 20px; 
            border-radius: 20px;
            margin: 20px;
        }
        .schedule a {
            text-decoration: none;
            color: black;
        }
        .title {
            display: flex; 
            align-items: center; 
            margin-right: auto; 
        }
        .title-img {
            width: 50px; 
            height: auto; 
        }
        .nav {
            display: flex;
            gap: 15px;
        }

        @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
        }

        nav a {
        color: #333;
        text-decoration: none;
        margin: 0 15px;
        padding: 5px;
        display: inline-block;
        transition: transform 0.3s;
        }

        nav a:hover {
        animation: bounce 1s;
        }


main {
    max-width: 600px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

form {
    display: grid;
    gap: 10px;
}

label {
    font-weight: bold;
}

input[type="text"],
input[type="number"],
select,
input[type="date"],
input[type="time"],
input[type="email"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background-color: #333;
    color: #fff;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
}

button:hover {
    background-color: #555;
}

</style>
<body>
<header>
        <div class="header">
            <div class="top-nav">
                <div class="title">
                    <h1 class="title-text">IDESIA</h1>
                    <img class="title-img" src="C:\Users\mark justin\Downloads\c80c4daa-1aea-4126-91c0-5f0002d7fcf4.jpg" alt="Title Image">
                </div>
                <nav class="nav">
                    <a href="login.php">Login</a>
                </nav>
                <div class="schedule">
                    <a href="http://localhost:3000/Schedule.php">Schedule A Tripping
                    </a>
                </div>
            </div>
        </div>
    </header>
    <br><br><br><br><br><br>
    <main>
        <form action="send.php" method="post">
            <label for="recipient_email">Email:</label>
            <input type="email" id="recipient_email" name="recipient_email" required>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>

            <label for="income">Monthly Income:</label>
            <input type="number" id="income" name="income" required>

            <label for="date">Date of Visit:</label>
            <input type="date" id="date" name="date" required>

            <label for="time">Time of Visit:</label>
            <input type="time" id="time" name="time" required>

            <button type="submit" name="send">Schedule Tripping</button>
        </form>
    </main>

</body>
</html>
