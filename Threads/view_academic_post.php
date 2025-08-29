<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id'])) exit("No post ID.");
$id = intval($_GET['id']);

// Fetch post
$stmt = $conn->prepare("SELECT ap.*, u.F_name, u.L_name, u.User_id AS PostOwner
FROM academic_posts ap
JOIN users u ON ap.User_id = u.User_id
WHERE Post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) exit("Post not found.");

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
$reply = trim($_POST['reply_content'] ?? '');
if ($reply !== '') {
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO replies (Reply_content, Reply_date, Post_id, User_id) VALUES (?, NOW(), ?, ?)");
$stmt->bind_param("sii", $reply, $id, $user_id);
$stmt->execute();

// Notify post owner
$msg = "Your academic post received a new reply.";
$stmt = $conn->prepare("INSERT INTO notifications (Message, User_id) VALUES (?, ?)");
$stmt->bind_param("si", $msg, $post['PostOwner']);
$stmt->execute();

header("Location: view_academic_post.php?id=" . $id);
exit;
} else {
echo "<div class='alert alert-warning'>Reply cannot be empty.</div>";
}
}

// Fetch replies with user info
$stmt = $conn->prepare("SELECT r.*, u.F_name, u.L_name
FROM replies r
JOIN users u ON r.User_id = u.User_id
WHERE r.Post_id = ?
ORDER BY r.Reply_date");
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
body {
background: linear-gradient(135deg, #1a1a1a, #2a1a3a); /* Black to dark violet gradient */
font-family: Arial;
color: #fff; /* White text for contrast */
}
.container {
margin: 0 auto; /* Original margin */
padding: 15px; /* Original padding */
background: #2c1e3f; /* Dark violet shade */
border-radius: 8px;
box-shadow: 0 0 10px rgba(0,0,0,0.5); /* Darker shadow for contrast */
}
.card {
background: #3a2a5a; /* Slightly lighter violet for cards */
border: 1px solid #4a3066; /* Violet border */
color: #fff; /* White text for card content */
}
.card-body {
background: #3a2a5a; /* Match card background */
}
.fw-bold {
color: #fff; /* White for bold text (e.g., post title, "Replies") */
}
.text-muted {
color: #ccc !important; /* Light gray for muted text (e.g., author, course, date) */
}
.btn-success {
background: #4a3066; /* Violet for submit button */
border: none;
color: #fff; /* White text */
}
.btn-success:hover {
background: #5a4080; /* Lighter violet on hover */
color: #fff;
}
.btn-light {
background: #4a3066; /* Violet for dropdown button */
border: none;
color: #fff; /* White text */
}
.btn-light:hover {
background: #5a4080; /* Lighter violet on hover */
color: #fff;
}
.dropdown-menu {
background: #3a2a5a; /* Dark violet for dropdown */
border: 1px solid #4a3066; /* Violet border */
}
.dropdown-item {
color: #fff; /* White text for dropdown items */
}
.dropdown-item:hover {
background: #4a3066; /* Lighter violet on hover */
color: #fff;
}
.text-danger {
color: #ff6666 !important; /* Light red for delete link */
}
.form-control {
background: #3a2a5a; /* Dark violet for textarea */
border: 1px solid #4a3066; /* Violet border */
color: #fff; /* White text */
}
.form-control::placeholder {
color: #ccc; /* Light gray placeholder text */
}
.form-label {
color: #fff; /* White for form label */
}
.alert-warning {
background: #ff6666; /* Light red for warning */
color: #fff; /* White text */
border: 1px solid #4a3066; /* Violet border */
}
</style>
</head>
<body class="container mt-4">

<a href="../Login/dashboard.php" class="btn btn-warning mb-3">Back to Dashboard</a>

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
<div class="card-body d-flex justify-content-between align-items-start">
<div>
<p><?= nl2br(htmlspecialchars($r['Reply_content'])) ?></p>
<small class="text-muted">
<i class="bi bi-clock"></i> <?= $r['Reply_date'] ?> |
<i class="bi bi-person-circle"></i> <?= htmlspecialchars($r['F_name']." ".$r['L_name']) ?>
</small>
</div>
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $r['User_id']): ?>
<div class="dropdown ms-2">
<button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
<ul class="dropdown-menu">
<li><a class="dropdown-item" href="edit_reply.php?id=<?= $r['Acd_reply_id'] ?>">Edit</a></li>
<li><a class="dropdown-item text-danger" href="delete_reply.php?id=<?= $r['Acd_reply_id'] ?>&post=<?= $id ?>" onclick="return confirm('Delete this reply?')">Delete</a></li>
</ul>
</div>
<?php endif; ?>
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