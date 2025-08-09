<?php
session_start();
require '../Connection/db_connect.php';

if ($_SESSION['role'] !== 'Student') {
    die("Only students can view registered events.");
}

$dashboardLink = "../Login/dashboard.php";
$user_id = $_SESSION['user_id'];

// Fetch events the user has registered for
$sql = "
SELECT e.Event_id, e.Event_title, e.Event_date, e.Event_location, e.Event_description, c.Club_name
FROM event_registration r
JOIN event_participants p ON r.Participant_id = p.Participant_id
JOIN events e ON r.Event_id = e.Event_id
JOIN club c ON e.Club_id = c.Club_id
WHERE p.User_id = ?
ORDER BY e.Event_date ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Registered Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>

    <div class="card shadow p-4">
        <h2 class="text-primary"><i class="bi bi-list-check"></i> My Registered Events</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped table-hover mt-3">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Club</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Event_title']) ?></td>
                            <td><?= htmlspecialchars($row['Club_name']) ?></td>
                            <td><?= date("M d, Y H:i", strtotime($row['Event_date'])) ?></td>
                            <td><?= htmlspecialchars($row['Event_location']) ?></td>
                            <td><?= htmlspecialchars($row['Event_description']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> You have not registered for any events yet.
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
