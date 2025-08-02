<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

// Fetch all posts with user info and display picture
$stmt = $conn->prepare("
    SELECT p.Content, p.Image_Path, p.Timestamp, u.F_name, u.L_name, u.DP 
    FROM userposts p
    JOIN Users u ON p.User_id = u.User_id
    ORDER BY p.Timestamp DESC
");
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All User Posts | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dp {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">All User Posts</h3>
        <?php

$role = $_SESSION['role'] ?? '';
if ($role === 'Admin') {
    $dashboardPath = '../Login/admin_dashboard.php';
} elseif ($role === 'Student' || $role === 'User') {
    $dashboardPath = '../Login/dashboard.php';
} else {
    $dashboardPath = '../Login/login.php'; // fallback if not logged in
}
?>
<a href="<?= $dashboardPath ?>" class="btn btn-sm btn-secondary">Back to Dashboard</a>

    </div>

    <?php if ($posts->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php while ($row = $posts->fetch_assoc()): ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <?php
                                $dp = !empty($row['DP']) && file_exists("../uploads/" . $row['DP'])
                                    ? "../uploads/" . $row['DP']
                                    : "../uploads/default.png";
                                ?>
                                <img src="<?= htmlspecialchars($dp) ?>" class="dp" alt="DP">
                                <strong><?= htmlspecialchars($row['F_name'] . ' ' . $row['L_name']) ?></strong>
                            </div>

                            <p class="card-text"><?= nl2br(htmlspecialchars($row['Content'])) ?></p>

                            <?php if (!empty($row['Image_Path']) && file_exists($row['Image_Path'])): ?>
                                <img src="<?= htmlspecialchars($row['Image_Path']) ?>" class="img-fluid mt-2 mb-2" style="max-height: 250px; object-fit: cover;">
                            <?php endif; ?>

                            <p class="text-muted small mb-0">
                                Posted on <?= date("d M Y, h:i A", strtotime($row['Timestamp'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No posts available yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
