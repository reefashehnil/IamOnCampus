<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) exit("Unauthorized");
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM replies WHERE Acd_reply_id = ? AND User_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$reply = $stmt->get_result()->fetch_assoc();
if (!$reply) exit("Reply not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['reply_content']);
    if ($content !== '') {
        $stmt = $conn->prepare("UPDATE replies SET Reply_content = ? WHERE Acd_reply_id = ?");
        $stmt->bind_param("si", $content, $id);
        $stmt->execute();
        header("Location: view_academic_post.php?id=" . $reply['Post_id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Reply | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    body {
        background: linear-gradient(135deg, #2c003e, #4b0082);
        color: #ffffff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .edit-reply-card {
        max-width: 800px; /* Increased from 600px for wider form */
        margin: 40px auto;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }
    .edit-reply-card h4 {
        color: #e0b0ff;
    }
    .edit-reply-card .form-label {
        color: #ffffff;
    }
    .edit-reply-card textarea.form-control {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        border: 1px solid #6a0dad !important;
        width: 100% !important;
        height: 350px !important; 
        font-size: 1.3rem !important; 
        padding: 20px !important; 
        resize: vertical !important; 
        line-height: 1.5 !important; 
    }
    .edit-reply-card textarea.form-control:focus {
        border-color: #c71585 !important;
        box-shadow: 0 0 8px rgba(199, 21, 133, 0.3) !important;
    }
    .btn-success {
        background-color: #c71585;
        border-color: #c71585;
    }
    .btn-success:hover {
        background-color: #db7093;
        border-color: #db7093;
    }
    .btn-outline-secondary {
        color: #ffffff;
        border-color: #4b0082;
        background-color: #4b0082;
    }
    .btn-outline-secondary:hover {
        background-color: #6a0dad;
        border-color: #6a0dad;
    }
</style>
</head>
<body>
<div class="edit-reply-card">
    <h4 class="mb-4">Edit Your Reply</h4>
    <form method="post">
        <div class="mb-3">
            <label for="reply_content" class="form-label">Reply Content</label>
            <textarea
                id="reply_content"
                name="reply_content"
                class="form-control"
                required
                minlength="5"
            ><?= htmlspecialchars($reply['Reply_content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success me-2">Update</button>
        <a href="view_academic_post.php?id=<?= $reply['Post_id'] ?>" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
</body>
</html>