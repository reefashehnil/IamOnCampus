<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) exit;

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$message_text = trim($_POST['message']);

if ($message_text != '') {
    $stmt = $conn->prepare("INSERT INTO messages (Sender_id, Receiver_id, Message_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);
    $stmt->execute();

    // Get sender's name
    $name_stmt = $conn->prepare("SELECT F_name, L_name FROM users WHERE User_id = ?");
    $name_stmt->bind_param("i", $sender_id);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result()->fetch_assoc();
    $sender_name = $name_result['F_name'] . ' ' . $name_result['L_name'];

    // Add notification
    $notif_msg = "New message from $sender_name";
    $notify = $conn->prepare("INSERT INTO notifications (Seen_status, Message, User_id) VALUES (0, ?, ?)");
    $notify->bind_param("si", $notif_msg, $receiver_id);
    $notify->execute();
}
?>
