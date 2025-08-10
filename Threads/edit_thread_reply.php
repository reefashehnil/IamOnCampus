<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) exit("Unauthorized");
$id = intval($_GET['id']);

// Fetch reply
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
<form method="post">
    <textarea name="reply_content" rows="3" class="form-control"><?= htmlspecialchars($reply['Treply_content']) ?></textarea>
    <button class="btn btn-success mt-2">Update</button>
</form>
