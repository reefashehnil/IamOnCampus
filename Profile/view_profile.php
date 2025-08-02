<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
header("Location: ../Login/login.php");
exit;
}

$profile_id = $_GET['user_id'] ?? $_SESSION['user_id'];

// Fetch user details (including DP)
$stmt = $conn->prepare("SELECT F_name, M_name, L_name, Role, DeptName, DP FROM Users WHERE User_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
echo "User not found.";
exit;
}
// Determine DP path or fallback
$dp_path = $user['DP'] && file_exists("../" . $user['DP']) ? "../" . $user['DP'] : "../uploads/default.png";

// Fetch posts
$post_stmt = $conn->prepare("SELECT content, created_at FROM UserPosts WHERE user_id = ? ORDER BY created_at DESC");
$post_stmt->bind_param("i", $profile_id);
$post_stmt->execute();
$posts = $post_stmt->get_result();

// Decide profile picture
$dp_path = "../uploads/default.png";
if (!empty($user['DP']) && file_exists("../uploads/" . $user['DP'])) {
$dp_path = "../uploads/" . $user['DP'];
}

// Get role for redirection
$logged_in_role = $_SESSION['role'] ?? 'User';
$dashboard_link = ($logged_in_role === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-3">
<h3>Profile of <?= htmlspecialchars($user['F_name'] . ' ' . $user['L_name']) ?></h3>
<a href="<?= $dashboard_link ?>" class="btn btn-secondary">Back to Dashboard</a>

</div>

<div class="card mb-4 shadow-sm">
<div class="card-body text-center">
<img src="<?= htmlspecialchars($dp_path) ?>" alt="Profile Picture" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">

<h4><?= htmlspecialchars($user['F_name'] . ' ' . $user['L_name']) ?></h4>
<p class="text-muted"><?= htmlspecialchars($user['Role']) ?> - <?= htmlspecialchars($user['DeptName']) ?></p>
</div>
</div>

<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
<h5 class="mb-0">User Posts</h5>
</div>
<div class="card-body">
<?php if ($posts && $posts->num_rows > 0): ?>
<?php while ($post = $posts->fetch_assoc()): ?>
<div class="mb-3">
<div class="border p-3 rounded bg-white">
<p class="mb-1"><?= htmlspecialchars($post['content']) ?></p>
<small class="text-muted">Posted on <?= htmlspecialchars($post['created_at']) ?></small>
</div>
</div>
<?php endwhile; ?>
<?php else: ?>
<p class="text-muted">No posts yet.</p>
<?php endif; ?>
</div>
</div>

</div>
</body>
</html>