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
    $course  = $_POST['course'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        INSERT INTO academic_posts (Acd_title, Acd_content, Course, User_id, Date_posted)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssss", $title, $content, $course, $user_id);
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
    <title>Add Academic Post | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c003e 0%, #4b0082 100%);
            color: #e6e6fa;
            padding: 2rem;
        }
        .container {
            background: #3c0a5e;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
            max-width: 600px;
            margin: 2rem auto;
        }
        h2 {
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
       
        .alert-danger {
            background-color: #703a4a;
            color: #f8d7da;
            border-color: #703a4a;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">Add Academic Post</h2>
    <a href="moderate_content.php" class="btn btn-warning mb-3">Back</a>

    <?php if ($msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

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
            <label class="form-label">Course</label>
            <input type="text" name="course" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Post</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>