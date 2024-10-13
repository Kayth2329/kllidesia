<?php
session_start();
require 'config.php'; // Include your database configuration

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch all requirements and their submission status for the logged-in user
$stmt = $conn->prepare("
    SELECT rr.requirement_id, rr.requirement_name, 
           COALESCE(uf.status, 'not submitted') AS status 
    FROM reservation_requirements rr 
    LEFT JOIN user_files uf ON rr.requirement_id = uf.requirement_id AND uf.username = ?
");

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$submittedRequirements = [];
$notSubmittedRequirements = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'submitted') {
        $submittedRequirements[] = htmlspecialchars($row['requirement_name']);
    } else {
        $notSubmittedRequirements[] = $row;
    }
}

$stmt->close();

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_upload'])) {
    $requirementId = $_POST['requirement_id'];
    $file = $_FILES['file_upload'];
    $targetDir = "uploads/";

    // Ensure uploads directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file["name"]);
    $targetFile = $targetDir . basename($fileName);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (file_exists($targetFile)) {
        $uploadOk = 0;
        echo "<div class='alert alert-warning'>The file <strong>" . htmlspecialchars(basename($fileName)) . "</strong> already exists. Please rename your file and try again.</div>";
    }

    if ($file["size"] > 5000000) {
        $uploadOk = 0;
        echo "<div class='alert alert-warning'>The uploaded file is too large. Please ensure your file is under 5 MB and try again.</div>";
    }

    if (!in_array($fileType, ['pdf', 'jpg', 'png'])) {
        $uploadOk = 0;
        echo "<div class='alert alert-warning'>Invalid file format. Please upload a file in PDF, JPG, or PNG format.</div>";
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO user_files (username, requirement_id, file_name, status, approved, created_at) VALUES (?, ?, ?, 'submitted', 'no', NOW())");
            $stmt->bind_param("sis", $username, $requirementId, $fileName);
            $stmt->execute();
            echo "<div class='alert alert-success'>The file <strong>" . htmlspecialchars(basename($fileName)) . "</strong> has been successfully uploaded for review.</div>";
        } else {
            echo "<div class='alert alert-danger'>An error occurred while uploading the file. Please try again later.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }

        .header img {
            width: 50px; /* Adjust logo size */
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
            transition: background-color 0.3s;
        }

        .logout-button:hover {
            background: #d62839; /* Darker red on hover */
        }

        .side-nav {
            width: 220px;
            background-color: #333;
            height: calc(100vh - 60px); /* Adjust height to account for header */
            padding-top: 30px;
            position: fixed;
            top: 60px; /* Position it below the header */
            box-shadow: 3px 0 5px rgba(0, 0, 0, 0.1);
        }

        .side-nav a {
            display: block;
            padding: 15px 20px;
            color: white;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .side-nav a:hover {
            background-color: #575757;
        }

        .main-content {
            margin-left: 240px; /* Space for side nav */
            padding: 80px 20px 20px; /* Increased padding at the top */
            width: calc(100% - 240px); /* Adjusted for side nav width */
            overflow-y: auto; /* Enable vertical scrolling */
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin-bottom: 30px;
        }

        h2, h3 {
            font-weight: 300;
            text-align: center;
            color: #333;
        }

        .requirements {
            margin-top: 20px;
            background-color: #f0f8ff; /* Light background for requirements */
            padding: 15px;
            border-radius: 8px;
        }

        .requirement {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .submitted {
            background-color: #c5e1a5; /* Light green */
        }

        .not-submitted {
            background-color: #ef9a9a; /* Light red */
        }

        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #dff0d8; /* Light green background */
            color: #3c763d; /* Dark green text */
        }

        .alert-warning {
            background-color: #fcf8e3; /* Light yellow background */
            color: #8a6d3b; /* Dark yellow text */
        }

        .alert-danger {
            background-color: #f2dede; /* Light red background */
            color: #a94442; /* Dark red text */
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="path/to/your/logo.png" alt="Logo">
        <h1>User Dashboard</h1>
        <div class="logout-container">
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
    </div>

    <div class="side-nav">
        <a href="javascript:void(0);" onclick="showSection('dashboard')" class="active">Dashboard</a>
        <a href="javascript:void(0);" onclick="showSection('download')">Download Files</a>
        <a href="javascript:void(0);" onclick="showSection('transactions')">Transactions</a>
    </div>

    <div class="main-content">
        <!-- Dashboard Section -->
        <div class="container hidden" id="dashboard">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>

            <div class="requirements">
                <h3>Submitted Requirements</h3>
                <?php if (count($submittedRequirements) > 0): ?>
                    <?php foreach ($submittedRequirements as $req): ?>
                        <div class="requirement submitted">
                            <?php echo htmlspecialchars($req); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="requirement submitted">No requirements submitted yet.</div>
                <?php endif; ?>
            </div>

            <div class="requirements">
                <h3>Requirements to Submit</h3>
                <?php if (count($notSubmittedRequirements) > 0): ?>
                    <?php foreach ($notSubmittedRequirements as $req): ?>
                        <div class="requirement not-submitted">
                            <form action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="requirement_id" value="<?php echo $req['requirement_id']; ?>">
                                <span><?php echo htmlspecialchars($req['requirement_name']); ?></span>
                                <input type="file" name="file_upload" required>
                                <button type="submit">Upload</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="requirement not-submitted">All requirements have been submitted.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Download Section -->
        <div class="container hidden" id="download">
            <h2>Download Files</h2>
            <p>Coming soon...</p>
        </div>

        <!-- Transactions Section -->
        <div class="container hidden" id="transactions">
            <h2>Transaction History</h2>
            <p>Coming soon...</p>
        </div>
    </div>

    <script>
        // Show the default section
        showSection('dashboard');

        function showSection(sectionId) {
            const sections = document.querySelectorAll('.container');
            sections.forEach(section => {
                section.classList.add('hidden');
            });

            const selectedSection = document.getElementById(sectionId);
            selectedSection.classList.remove('hidden');
        }
    </script>
</body>
</html>
