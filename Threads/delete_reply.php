<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) exit("Unauthorized");
$id = intval($_GET['id']);
$post_id = intval($_GET['post'] ?? 0);

$stmt = $conn->prepare("DELETE FROM replies WHERE Acd_reply_id = ? AND User_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();

header("Location: view_academic_post.php?id=" . $post_id);
exit;
