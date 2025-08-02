<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['id'] ?? null;

if (!$post_id || !is_numeric($post_id)) {
    die("Invalid post ID.");
}

// Verify post ownership and get image path
$stmt = $conn->prepare("SELECT Image_Path FROM userposts WHERE Post_id = ? AND User_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post not found or you do not have permission to delete it.");
}

// Delete image file if exists
if (!empty($post['Image_Path']) && file_exists($post['Image_Path'])) {
    unlink($post['Image_Path']);
}

// Delete post
$stmt = $conn->prepare("DELETE FROM userposts WHERE Post_id = ? AND User_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();

header("Location: my_posts.php");
exit;
?>
