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
        h3 {
            color: #d8b4fe;
            text-align: center;
        }
        .card {
            background-color: #3a3a5a;
            border: 1px solid #8b5cf6;
            border-radius: 10px;
        }
        .card-body {
            color: #e0e0e0;
        }
        .card-text {
            color: #e0e0e0;
        }
        .text-muted {
            color: #a3a3c2 !important;
        }
        .btn-primary {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-primary:hover {
            background-color: #a78bfa;
            border-color: #a78bfa;
        }
        .btn-warning {
            background-color: #d97706;
            border-color: #d97706;
        }
        .btn-warning:hover {
            background-color: #f59e0b;
            border-color: #f59e0b;
        }
        .btn-danger {
            background-color: #9b2c2c;
            border-color: #9b2c2c;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
            border-color: #b91c1c;
        }
        .btn-secondary {
            background-color: #4a4a6a;
            border-color: #4a4a6a;
        }
        .btn-secondary:hover {
            background-color: #5a5a7a;
            border-color: #5a5a7a;
        }
        video, .card-img-top {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
    </style>
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
        $dashboardPath = '../Login/login.php';
    }
    ?>
    <a href="<?= $dashboardPath ?>" class="btn btn-secondary mt-4 mb-4">Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>