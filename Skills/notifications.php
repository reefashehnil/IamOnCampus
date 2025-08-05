<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for logged-in user, newest first
$stmt = $conn->prepare("SELECT Notify_id, Message, Seen_status FROM Notifications WHERE User_id = ? ORDER BY Notify_id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

// Mark all unseen notifications as seen
$update_stmt = $conn->prepare("UPDATE Notifications SET Seen_status = 1 WHERE User_id = ? AND Seen_status = 0");
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Notifications | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <a href="../Login/dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <h3 class="text-center mb-4 text-primary">Your Notifications</h3>

    <?php if ($notifications->num_rows > 0): ?>
        <?php while ($notif = $notifications->fetch_assoc()): ?>
            <div class="alert <?= $notif['Seen_status'] ? 'alert-secondary' : 'alert-info' ?>">
                <?= htmlspecialchars($notif['Message']) ?>
                <small class="text-muted float-end"><?= $notif['Seen_status'] ? 'Seen' : 'New' ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">No notifications yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
