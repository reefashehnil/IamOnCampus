<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT Post_id, Content, Image_Path, Video_Path, Timestamp FROM userposts WHERE User_id = ? ORDER BY Timestamp DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Posts | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">My Posts</h3>

    <?php if ($posts->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <?php if (!empty($post['Image_Path'])): ?>
                            <img src="<?= htmlspecialchars($post['Image_Path']) ?>" class="card-img-top" style="max-height: 250px; object-fit: cover;">
                        <?php endif; ?>
                        <?php if (!empty($post['Video_Path']) && file_exists($post['Video_Path'])): ?>
                            <video controls class="w-100" style="max-height: 250px;">
                                <source src="<?= htmlspecialchars($post['Video_Path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                        <div class="card-body">
                            <p class="card-text"><?= nl2br(htmlspecialchars($post['Content'])) ?></p>
                            <p class="text-muted small"><?= date("d M Y, h:i A", strtotime($post['Timestamp'])) ?></p>

                            <!-- Edit and Delete buttons -->
                            <a href="edit_post.php?id=<?= $post['Post_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete_post.php?id=<?= $post['Post_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">You haven't posted anything yet.</p>
    <?php endif; ?>

    <a href="post_content.php" class="btn btn-primary mt-4 mb-4">Create New Post</a>
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
<a href="<?= $dashboardPath ?>" class="btn btn-secondary  mt-4 mb-4">Back to Dashboard</a>

</div>
</body>
</html>
