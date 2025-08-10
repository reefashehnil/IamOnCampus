<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id'])) exit("No thread ID.");
$id = intval($_GET['id']);

// Fetch thread
$stmt = $conn->prepare("
    SELECT dt.*, u.F_name, u.L_name, u.User_id AS ThreadOwner 
    FROM discussion_threads dt
    JOIN users u ON dt.User_id = u.User_id
    WHERE Thread_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$thread = $stmt->get_result()->fetch_assoc();
if (!$thread) exit("Thread not found.");

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $reply = trim($_POST['reply_content'] ?? '');
    if ($reply !== '') {
        $user_id = $_SESSION['user_id'];

        // Insert reply
        $stmt = $conn->prepare("
            INSERT INTO thread_replies (Treply_content, Treply_date, Thread_id, User_id) 
            VALUES (?, NOW(), ?, ?)
        ");
        $stmt->bind_param("sii", $reply, $id, $user_id);
        $stmt->execute();

        // Notify thread owner
        $msg = "Your discussion thread received a new reply.";
        $stmt = $conn->prepare("INSERT INTO notifications (Message, User_id) VALUES (?, ?)");
        $stmt->bind_param("si", $msg, $thread['ThreadOwner']);
        $stmt->execute();

        header("Location: view_thread.php?id=" . $id);
        exit;
    } else {
        echo "<div class='alert alert-warning'>Reply cannot be empty.</div>";
    }
}

// Fetch replies with user names
$stmt = $conn->prepare("
    SELECT tr.Treply_content, tr.Treply_date, u.F_name, u.L_name
    FROM thread_replies tr
    JOIN users u ON tr.User_id = u.User_id
    WHERE tr.Thread_id = ?
    ORDER BY tr.Treply_date
");
$stmt->bind_param("i", $id);
$stmt->execute();
$replies = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($thread['Dt_title']) ?></title>
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
        <h4 class="fw-bold"><?= htmlspecialchars($thread['Dt_title']) ?></h4>
        <h6 class="text-muted">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($thread['F_name']." ".$thread['L_name']) ?> |
            <i class="bi bi-tag"></i> <?= htmlspecialchars($thread['Dt_tag']) ?>
        </h6>
        <p class="mt-3"><?= nl2br(htmlspecialchars($thread['Dt_content'])) ?></p>
    </div>
</div>

<h5 class="fw-bold">Replies</h5>
<?php while ($r = $replies->fetch_assoc()): ?>
    <div class="card mb-2 shadow-sm">
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($r['Treply_content'])) ?></p>
            <small class="text-muted">
                <i class="bi bi-clock"></i> <?= $r['Treply_date'] ?> |
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($r['F_name']." ".$r['L_name']) ?>
            </small>
        </div>
    </div>
<?php endwhile; ?>

<form method="post" class="mt-4">
    <div class="mb-3">
        <label for="reply_content" class="form-label fw-bold">Add a Reply</label>
        <textarea name="reply_content" id="reply_content" class="form-control" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-send"></i> Reply
    </button>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
