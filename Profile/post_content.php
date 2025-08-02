<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    $image_path = "";

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_path = $target_dir . basename($_FILES["image"]["name"]);

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
            $error = "Error uploading image.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO userposts (User_id, Content, Image_Path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $content, $image_path);
        if ($stmt->execute()) {
            $success = "Post shared successfully!";
        } else {
            $error = "Error posting: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Something | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-3">Share a Post</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <textarea name="content" class="form-control" placeholder="What's on your mind?" required></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Attach Image (optional):</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button class="btn btn-primary"style="margin-left: 8px;">Post</button>
        <a href="my_posts.php" class="btn btn-danger"style="margin-left: 8px;">View My Posts</a>
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
<a href="<?= $dashboardPath ?>" class="btn btn-secondary" style="margin-left: 8px;">Back to Dashboard</a>

    </form>
</div>
</body>
</html>
