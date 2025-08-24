<?php
session_start();
require '../Connection/db_connect.php';

$dashboardLink = ($_SESSION['role'] === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";

$events = $conn->query("SELECT * FROM events ORDER BY Event_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css?v=1.0" rel="stylesheet" />
    <style>
        body {
            background-color: #1a0d2b !important; /* Dark violet-black background */
            color: #e6e6fa !important; /* Light lavender text for readability */
        }
        .container {
            background-color: #2a1b3d !important; /* Slightly lighter dark shade */
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px !important;
        }
        h2 {
            color: #d8bfd8 !important; /* Thistle color for heading */
        }
        .bi {
            color: #e6e6fa !important; /* Light lavender for icons */
        }
        .card {
            background-color: #3c2f5c !important; /* Dark purple card background */
            border-color: #5a4b7c !important; /* Medium purple border */
            color: #e6e6fa !important; /* Light lavender text */
        }
        .card-title {
            color: #d8bfd8 !important; /* Thistle for card title */
        }
        .card-text {
            color: #e6e6fa !important; /* Light lavender for card text */
        }
        .card.shadow {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important; /* Darker shadow */
        }
        .btn-success {
            background-color: #6a5acd !important; /* Slate blue button */
            border-color: #6a5acd !important;
        }
        .btn-success:hover {
            background-color: #483d8b !important; /* Darker slate blue on hover */
            border-color: #483d8b !important;
        }
        /* No custom styles for btn-secondary to keep default Bootstrap styling */
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>
    <h2 class="mb-4"><i class="bi bi-calendar-event"></i> Upcoming Events</h2>
    <div class="row">
        <?php while ($row = $events->fetch_assoc()): ?>
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['Event_title']) ?></h5>
                    <p class="card-text"><strong>Date:</strong> <?= $row['Event_date'] ?></p>
                    <p class="card-text"><strong>Location:</strong> <?= htmlspecialchars($row['Event_location']) ?></p>
                    <p><?= htmlspecialchars($row['Event_description']) ?></p>
                    <?php if ($_SESSION['role'] === 'Student'): ?>
                        <a href="register_event.php?event_id=<?= $row['Event_id'] ?>" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>