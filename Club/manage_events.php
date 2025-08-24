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
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }
        .container {
            margin-top: 40px;
            padding: 30px;
            background: #2a2a4a;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
        }
        .card {
            background-color: #3a3a5a;
            border: 1px solid #8b5cf6;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        h2 {
            color: #d8b4fe;
            text-align: center;
        }
        .btn-secondary {
            background-color: #4a4a6a;
            border-color: #4a4a6a;
        }
        .btn-secondary:hover {
            background-color: #5a5a7a;
            border-color: #5a5a7a;
        }
        .table {
            background-color: #3a3a5a;
            color: #e0e0e0;
            border: 1px solid #8b5cf6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #4a4a6a;
        }
        .table-hover tbody tr:hover {
            background-color: #5a5a7a;
        }
        th {
            color: #d8b4fe;
            background-color: #2a2a4a;
            border-color: #8b5cf6;
        }
        td {
            color: #e0e0e0;
            border-color: #8b5cf6;
        }
        .bi {
            color: #d8b4fe;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
             Back to Dashboard
    </a>
    <div class="card shadow p-4">
        <h2 class="mb-4"><i class="bi bi-calendar-event"></i> Manage Events</h2>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>