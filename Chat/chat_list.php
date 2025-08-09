<?php
session_start();
include '../Connection/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../Login/login.php"); exit; }
$my_id = $_SESSION['user_id'];

// Fetch user role
$role_stmt = $conn->prepare("SELECT Role FROM users WHERE User_id = ?");
$role_stmt->bind_param("i", $my_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();

$user_role = '';
if ($role_row = $role_result->fetch_assoc()) {
    $user_role = $role_row['Role'];
} else {
    // Role not found - fallback or error
    $user_role = 'user'; // default role fallback
}

// Debug: Uncomment below line to verify role is fetched correctly
// echo "User role: " . htmlspecialchars($user_role); exit;

if ($user_role === 'Admin') {
    $dashboard_link = '../Login/admin_dashboard.php';
} else {
    $dashboard_link = '../Login/dashboard.php';
}

$stmt = $conn->prepare("
    SELECT u.User_id, u.F_name, u.L_name, u.DP,
           (SELECT Message_text FROM messages 
            WHERE (Sender_id = u.User_id AND Receiver_id = ?) 
               OR (Sender_id = ? AND Receiver_id = u.User_id)
            ORDER BY Timestamp DESC LIMIT 1) AS last_message,
           (SELECT COUNT(*) FROM messages 
            WHERE Sender_id = u.User_id AND Receiver_id = ? AND Seen_status = 0) AS unread_count
    FROM users u
    WHERE u.User_id != ?
    ORDER BY unread_count DESC, u.F_name
");
$stmt->bind_param("iiii", $my_id, $my_id, $my_id, $my_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: Arial; }
        .title-bar {
            max-width: 600px;
            margin: 20px auto 10px auto;
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            text-align: left;
        }
        .chat-list { max-width: 600px; margin: 0 auto 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .chat-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #ddd; text-decoration: none; color: inherit; transition: background 0.2s; }
        .chat-item:hover { background: #f5f5f5; }
        .chat-item img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
        .chat-info { flex: 1; }
        .chat-info .name { font-weight: bold; }
        .chat-info .last-msg { font-size: 0.9rem; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .badge { font-size: 0.75rem; }
        .back-btn { display: block; max-width: 600px; margin: 0 auto 20px auto; }
    </style>
</head>
<body>

<div class="title-bar">Messages</div>

<a href="<?= htmlspecialchars($dashboard_link) ?>" class="btn btn-secondary back-btn"> Back to Dashboard</a>

<div class="chat-list">
<?php while($row = $result->fetch_assoc()): ?>
    <a href="chat.php?id=<?= $row['User_id'] ?>" class="chat-item">
        <img src="../DP_uploads/<?= htmlspecialchars($row['DP'] ?: 'default.jpg') ?>" alt="">
        <div class="chat-info">
            <div class="name"><?= htmlspecialchars($row['F_name'] . ' ' . $row['L_name']) ?></div>
            <div class="last-msg">
                <?= htmlspecialchars($row['last_message'] ?: 'No messages yet') ?>
            </div>
        </div>
        <?php if ($row['unread_count'] > 0): ?>
            <span class="badge bg-danger"><?= $row['unread_count'] ?></span>
        <?php endif; ?>
    </a>
<?php endwhile; ?>
</div>

</body>
</html>
