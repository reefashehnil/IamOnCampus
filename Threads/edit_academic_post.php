<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit("Unauthorized");
if (!isset($_GET['Post_id'])) exit("Invalid Request");

$id = intval($_GET['Post_id']);
$stmt = $conn->prepare("SELECT * FROM academic_posts WHERE Post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) exit("Post not found");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $course = trim($_POST['course']);
    $stmt = $conn->prepare("UPDATE academic_posts SET Acd_title=?, Acd_content=?, Course=? WHERE Post_id=?");
    $stmt->bind_param("sssi", $title, $content, $course, $id);
    $stmt->execute();
    header("Location: moderate_content.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Academic Post</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f8f9fa; }
    .edit-card {
        max-width: 700px;
        margin: 40px auto;
        padding: 25px;
        background: white;
        border-radius: 10px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
    }
</style>
</head>
<body>

<div class="container">
    <div class="edit-card">
        <h3 class="mb-4 text-primary">Edit Academic Post</h3>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($post['Acd_title']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($post['Acd_content']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Course</label>
                <input type="text" name="course" value="<?= htmlspecialchars($post['Course']) ?>" class="form-control">
            </div>
            <div class="d-flex justify-content-between">
                <a href="moderate_content.php" class="btn btn-secondary">Back to Moderate Content</a>
                <button type="submit" class="btn btn-success">Update Post</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
