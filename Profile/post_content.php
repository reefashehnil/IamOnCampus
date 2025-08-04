<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

$targetDir = "../Post_uploads/";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = trim($_POST['content']);
    $created_at = date("Y-m-d H:i:s");
    $imagePath = null;
    $videoPath = null;

    // Handle image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $imageName = "post_" . uniqid() . "_" . basename($_FILES["image"]["name"]);
        $targetDir = "../DP_uploads/";
        $targetFilePath = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imagePath = $targetFilePath;
        }
    }

    // Handle video upload
    if (isset($_FILES["video"]) && $_FILES["video"]["error"] == 0) {
        $videoName = "video_" . uniqid() . "_" . basename($_FILES["video"]["name"]);
        $videoTargetPath = $targetDir . $videoName;

        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        if (in_array($_FILES["video"]["type"], $allowedTypes)) {
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $videoTargetPath)) {
                $videoPath = $videoTargetPath;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO userposts (User_id, Content, Image_Path, Video_Path, Created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $content, $imagePath, $videoPath, $created_at);
    if ($stmt->execute()) {
        $success = "Your post was successfully submitted!";
    } else {
        $error = "There was an error posting your content. Please try again.";
    }
    $stmt->close();
    
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
        <div class="mb-3">
        <label for="video" class="form-label">Attach Video (optional):</label>
        <input type="file" name="video" class="form-control" accept="video/mp4,video/webm,video/ogg">
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
