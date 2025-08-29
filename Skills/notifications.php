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
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #2a1a3a); /* Black to dark violet gradient */
            font-family: Arial;
            color: #fff; /* White text for contrast */
        }
        .container {
            margin: 0 auto; /* Original margin */
            padding: 15px; /* Original padding */
            background: #2c1e3f; /* Dark violet shade */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5); /* Darker shadow for contrast */
        }
        h3 {
            color: #fff; /* White text for "Your Notifications" */
        }
        .alert-secondary {
            background: #3a2a5a; /* Slightly lighter violet for seen notifications */
            color: #fff; /* White text for notifications */
            border: 1px solid #4a3066; /* Violet border */
        }
        .alert-info {
            background: #4a3066; /* Violet for unseen notifications */
            color: #fff; /* White text for notifications */
            border: 1px solid #5a4080; /* Lighter violet border */
        }
        .alert-warning {
            background: #ff6666; /* Light red for no notifications */
            color: #fff; /* White text for notifications */
            border: 1px solid #4a3066; /* Violet border */
        }
        .text-muted {
            color: #fff !important; /* White for "Seen"/"New" text */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <a href="../Login/dashboard.php" class="btn btn-warning mb-3">Back to Dashboard</a>
    <h3 class="text-center mb-4">Your Notifications</h3>

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