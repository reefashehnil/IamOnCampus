<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}
include '../Connection/db_connect.php';

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title   = $_POST['title'];
    $content = $_POST['content'];
    $tag     = $_POST['tag'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        INSERT INTO discussion_threads (Dt_title, Dt_content, Dt_tag, User_id, Timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssss", $title, $content, $tag, $user_id);
    if ($stmt->execute()) {
        header("Location: moderate_content.php");
        exit;
    } else {
        $msg = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Discussion Thread</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="text-success mb-4">Add Discussion Thread</h2>
    <a href="moderate_content.php" class="btn btn-secondary mb-3">Back</a>

    <?php if ($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Tag</label>
            <input type="text" name="tag" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Thread</button>
    </form>
</div>

</body>
</html>
