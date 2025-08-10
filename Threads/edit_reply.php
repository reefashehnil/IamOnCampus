<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) exit("Unauthorized");
$id = intval($_GET['id']);

// Fetch reply
$stmt = $conn->prepare("SELECT * FROM replies WHERE Acd_reply_id = ? AND User_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$reply = $stmt->get_result()->fetch_assoc();
if (!$reply) exit("Reply not found.");

// Update on POST
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
<form method="post">
    <textarea name="reply_content" rows="3" class="form-control"><?= htmlspecialchars($reply['Reply_content']) ?></textarea>
    <button class="btn btn-success mt-2">Update</button>
</form>
