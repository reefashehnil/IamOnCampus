<?php
session_start();
require '../Connection/db_connect.php';

if ($_SESSION['role'] !== 'Admin') {
    die("Unauthorized access");
}

$dashboardLink = ($_SESSION['role'] === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";

$events = $conn->query("SELECT * FROM events ORDER BY Event_date ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>
    <div class="card shadow p-4">
        <h2 class="mb-4 text-primary"><i class="bi bi-calendar-event"></i> Manage Events</h2>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Club ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $events->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Event_title']) ?></td>
                    <td><?= $row['Event_date'] ?></td>
                    <td><?= htmlspecialchars($row['Event_location']) ?></td>
                    <td><?= htmlspecialchars($row['Event_description']) ?></td>
                    <td><?= $row['Club_id'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
