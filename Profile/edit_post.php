<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['id'] ?? null;

if (!$post_id || !is_numeric($post_id)) {
    die("Invalid post ID.");
}

// Fetch the post and check ownership
$stmt = $conn->prepare("SELECT Content, Image_Path FROM userposts WHERE Post_id = ? AND User_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post not found or you do not have permission to edit it.");
}

$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = trim($_POST['content']);
    $image_path = $post['Image_Path'];

    // Handle image upload if a new file was provided
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../DP_uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        // Delete old image if exists
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }

        $filename = uniqid("post_") . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;

        if ($_FILES["image"]["size"] <= 2 * 1024 * 1024 && getimagesize($_FILES["image"]["tmp_name"])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Failed to upload new image.";
            }
        } else {
            $error = "Invalid or too large image (max 2MB).";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE userposts SET Content = ?, Image_Path = ? WHERE Post_id = ? AND User_id = ?");
        $stmt->bind_param("ssii", $content, $image_path, $post_id, $user_id);

        if ($stmt->execute()) {
            $success = "Post updated successfully.";
            // Refresh post data
            $post['Content'] = $content;
            $post['Image_Path'] = $image_path;
        } else {
            $error = "Failed to update post.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Post | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-3">Edit Post</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <textarea name="content" class="form-control" required><?= htmlspecialchars($post['Content']) ?></textarea>
        </div>

        <?php if (!empty($post['Image_Path']) && file_exists($post['Image_Path'])): ?>
            <div class="mb-3">
                <label>Current Image:</label><br>
                <img src="<?= htmlspecialchars($post['Image_Path']) ?>" alt="Post Image" style="max-width: 100%; max-height: 250px; object-fit: contain;">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="image" class="form-label">Change Image (optional, JPG, max 2MB):</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Update Post</button>
        <a href="my_posts.php" class="btn btn-secondary">Back to My Posts</a>
    </form>
</div>
</body>
</html>
