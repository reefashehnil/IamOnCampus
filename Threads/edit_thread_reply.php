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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  .edit-reply-card {
    max-width: 600px;
    margin: 40px auto;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-radius: 12px;
    background: #fff;
  }
  .edit-reply-card textarea:focus {
    border-color: #28a745;
    box-shadow: 0 0 8px rgba(40,167,69,0.3);
  }
</style>

<div class="edit-reply-card">
  <h4 class="mb-4 text-success">Edit Your Reply</h4>
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
