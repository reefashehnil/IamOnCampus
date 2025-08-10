<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) exit("Unauthorized");
$id = intval($_GET['id']);
$thread_id = intval($_GET['thread'] ?? 0);

$stmt = $conn->prepare("DELETE FROM thread_replies WHERE Reply_id = ? AND User_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();

header("Location: view_thread.php?id=" . $thread_id);
exit;
