<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE Receiver_id=? AND Seen_status=0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode($result);
?>
