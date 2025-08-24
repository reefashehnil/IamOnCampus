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

$targetDir = "../Post_Uploads/";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = trim($_POST['content']);
    $created_at = date("Y-m-d H:i:s");
    $imagePath = null;
    $videoPath = null;

    // Handle image upload
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $imageName = "post_" . uniqid() . "_" . basename($_FILES["image"]["name"]);
        $targetDir = "../DP_Uploads/";
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
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }
        .container {
            max-width: 700px;
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
        .form-label {
            font-weight: bold;
            color: #d8b4fe;
        }
        .form-control {
            background-color: #3a3a5a;
            color: #e0e0e0;
            border: 1px solid #8b5cf6;
        }
        .form-control::placeholder {
            color: #b0a8ff;
        }
        .form-control:focus {
            background-color: #3a3a5a;
            color: #e0e0e0;
            border-color: #a78bfa;
            box-shadow: 0 0 5px rgba(167, 139, 250, 0.5);
        }
        .btn-primary {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-primary:hover {
            background-color: #a78bfa;
            border-color: #a78bfa;
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
        .alert-success {
            background-color: #4a704a;
            color: #d4edda;
            border-color: #4a704a;
        }
        .alert-danger {
            background-color: #703a4a;
            color: #f8d7da;
            border-color: #703a4a;
        }
    </style>
</head>
<body>
<div class="container mt-5">
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
        <button class="btn btn-primary" style="margin-left: 8px;">Post</button>
        <a href="my_posts.php" class="btn btn-danger" style="margin-left: 8px;">View My Posts</a>
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
        <a href="<?= $dashboardPath ?>" class="btn btn-secondary" style="margin-left: 8px;">Back to Dashboard</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>