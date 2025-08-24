<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$profile_id = $_GET['user_id'] ?? $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT F_name, M_name, L_name, Role, DeptName, DP FROM Users WHERE User_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

$dp_path = $user['DP'] && file_exists("../" . $user['DP']) ? "../" . $user['DP'] : "../DP_Uploads/default.png";

$post_stmt = $conn->prepare("SELECT content, created_at FROM UserPosts WHERE user_id = ? ORDER BY created_at DESC");
$post_stmt->bind_param("i", $profile_id);
$post_stmt->execute();
$posts = $post_stmt->get_result();

$dp_path = "../DP_Uploads/default.png";
if (!empty($user['DP']) && file_exists("../DP_Uploads/" . $user['DP'])) {
    $dp_path = "../DP_Uploads/" . $user['DP'];
}

$logged_in_role = $_SESSION['role'] ?? 'User';
$dashboard_link = ($logged_in_role === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=1.0" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css?v=1.0" rel="stylesheet">
    <style>
        body {
            background-color: #1a0d2b !important;
            color: #e6e6fa !important;
        }
        .container {
            background-color: #2a1b3d !important;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px !important;
        }
        h3, h5 {
            color: #d8bfd8 !important;
        }
        h4 {
            color: #e6e6fa !important;
        }
        .card {
            background-color: #3c2f5c !important;
            border-color: #5a4b7c !important;
            color: #e6e6fa !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
        }
        .card-header {
            background-color: #4b3c7a !important;
            color: #e6e6fa !important;
            border-color: #5a4b7c !important;
        }
        .text-muted {
            color: #b0a8d8 !important;
        }
        .post-content {
            background-color: #3c2f5c !important;
            border-color: #5a4b7c !important;
            color: #e6e6fa !important;
        }
        .post-content p.mb-1 {
            color: #e6e6fa !important;
        }
        .post-content small.text-muted {
            color: #b0a8d8 !important;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Profile of <?= htmlspecialchars($user['F_name'] . ' ' . $user['L_name']) ?></h3>
        <a href="<?= $dashboard_link ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center">
            <img src="<?= htmlspecialchars($dp_path) ?>" alt="Profile Picture" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
            <h4><?= htmlspecialchars($user['F_name'] . ' ' . $user['L_name']) ?></h4>
            <p class="text-muted"><?= htmlspecialchars($user['Role']) ?> - <?= htmlspecialchars($user['DeptName']) ?></p>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">User Posts</h5>
        </div>
        <div class="card-body">
            <?php if ($posts && $posts->num_rows > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="mb-3">
                        <div class="border p-3 rounded post-content">
                            <p class="mb-1"><?= htmlspecialchars($post['content']) ?></p>
                            <small class="text-muted">Posted on <?= htmlspecialchars($post['created_at']) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No posts yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>