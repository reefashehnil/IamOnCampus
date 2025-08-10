<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id'])) exit("No post ID.");
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT ap.*, u.F_name, u.L_name FROM academic_posts ap JOIN users u ON ap.User_id = u.User_id WHERE Post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) exit("Post not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $reply = trim($_POST['reply_content']);
    if ($reply !== '') {
        $stmt = $conn->prepare("INSERT INTO replies (Reply_content, Reply_date, Post_id) VALUES (?, NOW(), ?)");
        $stmt->bind_param("si", $reply, $id);
        $stmt->execute();
        $msg = "Your academic post received a new reply.";
        $stmt = $conn->prepare("INSERT INTO notifications (Message, User_id) VALUES (?, ?)");
        $stmt->bind_param("si", $msg, $post['User_id']);
        $stmt->execute();
        header("Location: view_academic_post.php?id=".$id);
        exit;
    }
}

$stmt = $conn->prepare("SELECT * FROM replies WHERE Post_id = ? ORDER BY Reply_date");
$stmt->bind_param("i", $id);
$stmt->execute();
$replies = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($post['Acd_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f5f9fc; }
    </style>
</head>
<body class="container mt-4">

<a href="../Login/dashboard.php" class="btn btn-secondary mb-3">
    Back to Dashboard
</a>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h4 class="fw-bold"><?= htmlspecialchars($post['Acd_title']) ?></h4>
        <h6 class="text-muted"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($post['F_name']." ".$post['L_name']) ?> | <i class="bi bi-book"></i> <?= htmlspecialchars($post['Course']) ?></h6>
        <p class="mt-3"><?= nl2br(htmlspecialchars($post['Acd_content'])) ?></p>
    </div>
</div>

<h5 class="fw-bold">Replies</h5>
<?php while ($r = $replies->fetch_assoc()): ?>
    <div class="card mb-2 shadow-sm">
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($r['Reply_content'])) ?></p>
            <small class="text-muted"><i class="bi bi-clock"></i> <?= $r['Reply_date'] ?></small>
        </div>
    </div>
<?php endwhile; ?>

<form method="post" class="mt-4">
    <div class="mb-3">
        <label for="reply_content" class="form-label fw-bold">Add a Reply</label>
        <textarea name="reply_content" id="reply_content" class="form-control" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Reply</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
