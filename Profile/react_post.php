<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);
$reaction = $_POST['reaction_type'] ?? '';

if (!$post_id || !$reaction) {
    exit("Invalid post ID or reaction.");
}

$stmt = $conn->prepare("INSERT INTO post_reactions (Post_id, User_id, Reaction_type) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE Reaction_type = ?");
$stmt->bind_param("iiss", $post_id, $user_id, $reaction, $reaction);
$stmt->execute();
$stmt->close();

// Send notification to post owner
$ownerStmt = $conn->prepare("SELECT User_id, Content FROM userposts WHERE Post_id = ?");
$ownerStmt->bind_param("i", $post_id);
$ownerStmt->execute();
$ownerResult = $ownerStmt->get_result();

if ($ownerRow = $ownerResult->fetch_assoc()) {
    $post_owner = $ownerRow['User_id'];
    $post_content = $ownerRow['Content'];

    if ($post_owner != $user_id) {
        // Get reactor's info
        $userStmt = $conn->prepare("SELECT F_name FROM Users WHERE User_id = ?");
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();

        if ($userRow = $userResult->fetch_assoc()) {
            $reactorName = $userRow['F_name'];

            // Trim post content for notification
            $shortPost = mb_substr($post_content, 0, 50) . (strlen($post_content) > 50 ? '...' : '');

            $message = "$reactorName reacted '$reaction' to your post: \"$shortPost\"";

            $notifyStmt = $conn->prepare("INSERT INTO notifications (Seen_status, Message, User_id) VALUES (0, ?, ?)");
            $notifyStmt->bind_param("si", $message, $post_owner);
            $notifyStmt->execute();
            $notifyStmt->close();
        }
        $userStmt->close();
    }
}

$ownerStmt->close();

header("Location: user_posts.php");
exit;
?>
