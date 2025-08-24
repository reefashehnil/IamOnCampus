<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit("Unauthorized");
if (!isset($_GET['Thread_id'])) exit("Invalid Request");

$id = intval($_GET['Thread_id']);
$stmt = $conn->prepare("SELECT * FROM discussion_threads WHERE Thread_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$thread = $stmt->get_result()->fetch_assoc();
if (!$thread) exit("Thread not found");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tag = trim($_POST['tag']);
    $stmt = $conn->prepare("UPDATE discussion_threads SET Dt_title=?, Dt_content=?, Dt_tag=? WHERE Thread_id=?");
    $stmt->bind_param("sssi", $title, $content, $tag, $id);
    $stmt->execute();
    header("Location: moderate_content.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Discussion Thread | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c003e 0%, #4b0082 100%);
            color: #e6e6fa;
            padding: 2rem;
        }
        .edit-card {
            background: #3c0a5e;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
            max-width: 700px;
            margin: 2rem auto;
        }
        h3 {
            color: #d8bfd8;
        }
        .form-control {
            background-color: #3c0a5e !important;
            color: #e6e6fa !important;
            border: 1px solid #8a2be2 !important;
        }
        .form-control:focus {
            background-color: #3c0a5e !important;
            color: #e6e6fa !important;
            border-color: #9932cc !important;
            box-shadow: 0 0 5px rgba(153, 50, 204, 0.5) !important;
        }
        .form-control::placeholder {
            color: #b19cd9 !important;
        }
        .form-label {
            color: #d8bfd8;
        }
        .btn-success {
            background-color: #8a2be2;
            border-color: #8a2be2;
            color: #fff;
        }
        .btn-success:hover {
            background-color: #9932cc;
            border-color: #9932cc;
            color: #fff;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="edit-card">
        <h3 class="mb-4">Edit Discussion Thread</h3>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($thread['Dt_title']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($thread['Dt_content']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Tag</label>
                <input type="text" name="tag" value="<?= htmlspecialchars($thread['Dt_tag']) ?>" class="form-control">
            </div>
            <div class="d-flex justify-content-between">
                <a href="moderate_content.php" class="btn btn-secondary">Back to Moderate Content</a>
                <button type="submit" class="btn btn-success">Update Thread</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>