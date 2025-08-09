<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$other_id = intval($_GET['other_id'] ?? 0);

// Mark as seen
$conn->query("UPDATE messages SET Seen_status = 1 WHERE Receiver_id = $user_id AND Sender_id = $other_id");

// Fetch messages
$stmt = $conn->prepare("
    SELECT m.*, u.F_name AS SenderFirst, u.L_name AS SenderLast
    FROM messages m
    JOIN users u ON m.Sender_id = u.User_id
    WHERE (Sender_id=? AND Receiver_id=?) OR (Sender_id=? AND Receiver_id=?)
    ORDER BY Timestamp ASC
");
$stmt->bind_param("iiii", $user_id, $other_id, $other_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $row['SenderName'] = $row['SenderFirst'] . ' ' . $row['SenderLast'];
    $messages[] = $row;
}

// Seen info
$last_msg = $conn->query("SELECT Seen_status FROM messages WHERE Sender_id = $user_id AND Receiver_id = $other_id ORDER BY Timestamp DESC LIMIT 1")->fetch_assoc();
$seen_info = ($last_msg && $last_msg['Seen_status'] == 1) ? "Seen" : "";

echo json_encode(['messages' => $messages, 'seen_info' => $seen_info]);
?>
