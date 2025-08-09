<?php
session_start();
require '../Connection/db_connect.php';

$dashboardLink = ($_SESSION['role'] === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";

$events = $conn->query("SELECT * FROM events ORDER BY Event_date ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
    <h2 class="mb-4 text-primary"><i class="bi bi-calendar-event"></i> Upcoming Events</h2>
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
