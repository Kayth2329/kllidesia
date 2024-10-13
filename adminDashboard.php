<?php
session_start();
require 'config.php'; // Include your database configuration

// Check if user is logged in as admin
if (!isset($_SESSION['username'])) {
    header("Location: adminLogin.php"); // Redirect to admin login if not logged in
    exit();
}

// Fetch number of users
$userStmt = $conn->prepare("SELECT COUNT(*) as user_count FROM users");
$userStmt->execute();
$userResult = $userStmt->get_result();
$userCount = $userResult->fetch_assoc()['user_count'];

// Fetch users who sent all required requirements
$completeRequirementsStmt = $conn->prepare("SELECT username FROM user_files GROUP BY username HAVING COUNT(DISTINCT requirement_id) = (SELECT COUNT(*) FROM reservation_requirements)");
$completeRequirementsStmt->execute();
$completeRequirementsResult = $completeRequirementsStmt->get_result();
$completeUsersCount = $completeRequirementsResult->num_rows; // Count of users who sent all requirements

// Fetch all uploaded files
$stmt = $conn->prepare("SELECT uf.requirement_id, uf.username, uf.file_name, rr.requirement_name, uf.approved FROM user_files uf JOIN reservation_requirements rr ON uf.requirement_id = rr.requirement_id");

if ($stmt->execute()) {
    $result = $stmt->get_result();
} else {
    echo "Error executing query: " . $stmt->error;
    exit();
}

// Fetch scheduled trippings
$scheduleStmt = $conn->prepare("SELECT * FROM scheduled_trippings");
$scheduleStmt->execute();
$scheduleResult = $scheduleStmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $fileId = $_POST['file_id'];
    $stmt = $conn->prepare("UPDATE user_files SET approved = 'yes' WHERE requirement_id = ?");
    $stmt->bind_param("i", $fileId);
    
    if ($stmt->execute()) {
        header("Location: adminDashboard.php"); // Redirect back to the dashboard
        exit();
    } else {
        // Handle error
        echo "Error updating file approval: " . $stmt->error;
    }
}

// Prepare events for FullCalendar
$events = [];
while ($schedule = $scheduleResult->fetch_assoc()) {
    $events[] = [
        'title' => $schedule['name'],
        'start' => $schedule['date_of_visit'], // Use the date_of_visit as the event date
        'time' => $schedule['time_of_visit']
    ];
}

// Fetch current settings before the HTML form
$settingsStmt = $conn->prepare("SELECT * FROM settings WHERE id=1");
$settingsStmt->execute();
$settings = $settingsStmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js'></script>
    <style>
        /* Your existing styles remain unchanged */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Permanent Header */
        .header {
            background: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .header img {
            width: 50px;
            vertical-align: middle;
        }

        .logout-container {
            float: right;
            margin-right: 20px;
        }

        .logout-button {
            background: #e63946;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        /* Side Navigation */
        .side-nav {
            height: 100%;
            width: 250px;
            position: fixed;
            z-index: 1;
            top: 141px; /* Below header */
            left: 0;
            background-color: #111;
            padding-top: 20px;
        }

        .side-nav a {
            padding: 15px 10px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            transition: 0.3s;
        }

        .side-nav a:hover {
            background-color: #575757;
        }

        .main-content {
            margin-left: 270px;
            margin-top: 120px;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f0f0f0;
        }

        .approve-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .approve-button:hover {
            background: #45a049;
        }

        .approved {
            background-color: #c5e1a5;
        }

        .not-approved {
            background-color: #ef9a9a;
        }

        /* Hide sections */
        .section {
            display: none;
        }

        /* Dashboard card styling */
        .dashboard-card {
            display: flex;
            align-items: center;
            background-color: #333; /* Darker background color */
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            margin-top: 20px;
            max-width: 300px; /* Adjust width to your preference */
        }

        .dashboard-card .icon img {
            width: 50px; /* Adjust the size of the icon */
            margin-right: 15px; /* Space between the icon and text */
        }

        .dashboard-card .info {
            display: flex;
            flex-direction: column;
        }

        .dashboard-card .info p {
            font-size: 16px;
            margin: 0;
            color: #ccc; /* Slightly lighter text for the label */
        }

        .dashboard-card .info h3 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
        }

        .section {
    background-color: #f9f9f9; /* Light background for the section */
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
}

h2 {
    color: #333; /* Darker text color for headings */
    margin-bottom: 20px;
}

.settings-form {
    display: flex;
    flex-direction: column; /* Arrange form items vertically */
}

.form-group {
    margin-bottom: 15px; /* Space between form fields */
}

label {
    font-weight: bold; /* Make labels bold */
    margin-bottom: 5px;
    color: #555; /* Darker color for labels */
}

input[type="text"],
input[type="email"] {
    padding: 10px; /* Padding for input fields */
    border: 1px solid #ccc; /* Light border */
    border-radius: 4px; /* Rounded corners */
    font-size: 16px; /* Slightly larger font */
    width: 100%; /* Full width */
    box-sizing: border-box; /* Ensure padding doesn't affect total width */
}

input[type="submit"] {
    padding: 10px 15px; /* Padding for the button */
    border: none; /* Remove border */
    border-radius: 4px; /* Rounded corners for button */
    background-color: #007bff; /* Primary button color */
    color: white; /* White text color */
    font-size: 16px; /* Font size */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s; /* Smooth background transition */
}

input[type="submit"]:hover {
    background-color: #0056b3; /* Darker shade on hover */
}


        /* Calendar styling */
        #calendar {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
    <script>
        $(document).ready(function() {
            // Initialize FullCalendar
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: <?php echo json_encode($events); ?>, // Pass PHP events to JavaScript
                editable: true,
                droppable: true // Allow dragging and dropping
            });
        });

        // Function to show the selected section
        function showSection(sectionId) {
            // Hide all sections
            var sections = document.getElementsByClassName("section");
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = "none";
            }
            // Show the selected section
            document.getElementById(sectionId).style.display = "block";
        }

        // Show dashboard by default
        window.onload = function() {
            showSection('dashboard');
        }
    </script>
</head>
<body>

    <!-- Permanent Header -->
    <div class="header">
        <img src="path/to/your/logo.png" alt="Logo">
        <h1>Admin Dashboard</h1>
        <div class="logout-container">
            <a href="admin_logout.php" class="logout-button">Logout</a>
        </div>
    </div>

    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" onclick="showSection('dashboard')">Dashboard</a>
        <a href="#" onclick="showSection('userFiles')">User Files</a>
        <a href="#" onclick="showSection('scheduledTrippings')">Scheduled Trippings</a>
        <a href="#" onclick="showSection('settings')">Settings</a>
    </div>

    <div class="main-content">
        <div id="dashboard" class="section">
            <h2>Total Users: <?php echo $userCount; ?></h2>
            <h2>Users with All Requirements: <?php echo $completeUsersCount; ?></h2>
            <div class="dashboard-card">
                <div class="icon"><img src="path/to/your/icon.png" alt="User Icon"></div>
                <div class="info">
                    <p>Total Users</p>
                    <h3><?php echo $userCount; ?></h3>
                </div>
            </div>
        </div>

        <div id="userFiles" class="section">
            <h2>User Files for Approval</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Requirement</th>
                        <th>File Name</th>
                        <th>Approval Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($file = $result->fetch_assoc()): ?>
                        <tr class="<?php echo $file['approved'] === 'yes' ? 'approved' : 'not-approved'; ?>">
                            <td><?php echo htmlspecialchars($file['username']); ?></td>
                            <td><?php echo htmlspecialchars($file['requirement_name']); ?></td>
                            <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                            <td><?php echo $file['approved'] === 'yes' ? 'Approved' : 'Pending'; ?></td>
                            <td>
                                <?php if ($file['approved'] !== 'yes'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="file_id" value="<?php echo $file['requirement_id']; ?>">
                                        <button type="submit" name="approve" class="approve-button">Approve</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="scheduledTrippings" class="section">
            <h2>Scheduled Trippings</h2>
            <div id="calendar"></div>
        </div>

        <div id="settings" class="section">
    <h2>Settings</h2>
    <form action="update_settings.php" method="post">
    <input type="text" name="model1" placeholder="Model 1">
    <input type="text" name="model1_price" placeholder="Model 1 Price">
    <input type="text" name="model2" placeholder="Model 2">
    <input type="text" name="model2_price" placeholder="Model 2 Price">
    <input type="text" name="model3" placeholder="Model 3">
    <input type="text" name="model3_price" placeholder="Model 3 Price">
    <input type="text" name="facebook" placeholder="Facebook">
    <input type="text" name="twitter" placeholder="Twitter">
    <input type="text" name="instagram" placeholder="Instagram">
    <input type="text" name="phone" placeholder="Phone">
    <input type="email" name="email" placeholder="Email">
    <button type="submit">Update Settings</button>
</form>
</div>

    </div>
</body>
</html>