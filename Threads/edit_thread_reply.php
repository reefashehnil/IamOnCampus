<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) exit("Unauthorized");
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM thread_replies WHERE Reply_id = ? AND User_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$reply = $stmt->get_result()->fetch_assoc();
if (!$reply) exit("Reply not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['reply_content']);
    if ($content !== '') {
        $stmt = $conn->prepare("UPDATE thread_replies SET Treply_content = ? WHERE Reply_id = ?");
        $stmt->bind_param("si", $content, $id);
        $stmt->execute();
        header("Location: view_thread.php?id=" . $reply['Thread_id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Thread Reply | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=1.0" rel="stylesheet">
    <style>
        body {
            background-color: #1a0d2b !important; /* Dark violet-black background */
            color: #e6e6fa !important; /* Light lavender text for readability */
        }
        .edit-reply-card {
            max-width: 600px;
            margin: 40px auto;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important; /* Darker shadow for contrast */
            border-radius: 12px;
            background: #2a1b3d !important; /* Slightly lighter dark shade */
        }
        .edit-reply-card .form-label {
            color: #e6e6fa !important; /* Light lavender for labels */
        }
        .edit-reply-card textarea.form-control {
            background-color: #3c2f5c !important; /* Dark purple textarea background */
            color: #e6e6fa !important; /* Light lavender text */
            border-color: #5a4b7c !important; /* Medium purple border */
        }
        .edit-reply-card textarea.form-control:focus {
            border-color: #9370db !important; /* Medium purple border on focus */
            box-shadow: 0 0 8px rgba(123, 104, 238, 0.3) !important; /* Lighter purple shadow */
        }
        .edit-reply-card h4 {
            color: #d8bfd8 !important; /* Thistle color for heading */
        }
        .btn-success {
            background-color: #6a5acd !important; /* Slate blue button */
            border-color: #6a5acd !important;
        }
        .btn-success:hover {
            background-color: #483d8b !important; /* Darker slate blue on hover */
            border-color: #483d8b !important;
        }
        /* No custom styles for btn-outline-secondary to keep default Bootstrap styling */
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
                rows="5" 
                class="form-control" 
                required
                minlength="5"
            ><?= htmlspecialchars($reply['Treply_content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success me-2">Update</button>
        <a href="view_thread.php?id=<?= $reply['Thread_id'] ?>" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
</body>
</html>