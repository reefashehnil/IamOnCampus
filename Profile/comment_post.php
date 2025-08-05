<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(403);
    exit("Unauthorized access");
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add_comment') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($post_id === 0 || $comment === '') {
        exit("Missing post ID or comment.");
    }

    // Insert comment
    $stmt = $conn->prepare("INSERT INTO post_comments (Post_id, User_id, Comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    $stmt->execute();
    $stmt->close();

    // Notify post owner with post preview
    $ownerStmt = $conn->prepare("SELECT User_id, Content FROM userposts WHERE Post_id = ?");
    $ownerStmt->bind_param("i", $post_id);
    $ownerStmt->execute();
    $ownerResult = $ownerStmt->get_result();

    if ($ownerRow = $ownerResult->fetch_assoc()) {
        $owner_id = $ownerRow['User_id'];
        $post_content = $ownerRow['Content'];

        if ($owner_id != $user_id) {
            // Get commenter info
            $userStmt = $conn->prepare("SELECT F_name FROM Users WHERE User_id = ?");
            $userStmt->bind_param("i", $user_id);
            $userStmt->execute();
            $userResult = $userStmt->get_result();

            if ($userRow = $userResult->fetch_assoc()) {
                $commenterName = $userRow['F_name'];

                // Trim post content for preview
                $shortPost = mb_substr($post_content, 0, 50) . (strlen($post_content) > 50 ? '...' : '');

                $message = "$commenterName commented on your post: \"$shortPost\"";

                $notifyStmt = $conn->prepare("INSERT INTO notifications (Seen_status, Message, User_id) VALUES (0, ?, ?)");
                $notifyStmt->bind_param("si", $message, $owner_id);
                $notifyStmt->execute();
                $notifyStmt->close();
            }
            $userStmt->close();
        }
    }
    $ownerStmt->close();

    header("Location: user_posts.php");
    exit;
}

elseif ($action === 'edit_comment') {
    $comment_id = intval($_POST['comment_id'] ?? 0);
    $new_comment = trim($_POST['comment'] ?? '');

    if ($comment_id === 0 || $new_comment === '') {
        exit("Missing comment ID or new comment text.");
    }

    // Check ownership before updating
    $checkStmt = $conn->prepare("SELECT User_id FROM post_comments WHERE Comment_id = ?");
    $checkStmt->bind_param("i", $comment_id);
    $checkStmt->execute();
    $checkStmt->bind_result($owner_id);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($owner_id !== $user_id) {
        exit("Unauthorized to edit this comment.");
    }

    // Update comment
    $updateStmt = $conn->prepare("UPDATE post_comments SET Comment_text = ? WHERE Comment_id = ?");
    $updateStmt->bind_param("si", $new_comment, $comment_id);
    $updateStmt->execute();
    $updateStmt->close();

    header("Location: user_posts.php");
    exit;
}

elseif ($action === 'delete_comment') {
    $comment_id = intval($_POST['comment_id'] ?? 0);

    if ($comment_id === 0) {
        exit("Missing comment ID.");
    }

    // Check ownership before deleting
    $checkStmt = $conn->prepare("SELECT User_id FROM post_comments WHERE Comment_id = ?");
    $checkStmt->bind_param("i", $comment_id);
    $checkStmt->execute();
    $checkStmt->bind_result($owner_id);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($owner_id !== $user_id) {
        exit("Unauthorized to delete this comment.");
    }

    // Delete comment
    $deleteStmt = $conn->prepare("DELETE FROM post_comments WHERE Comment_id = ?");
    $deleteStmt->bind_param("i", $comment_id);
    $deleteStmt->execute();
    $deleteStmt->close();

    header("Location: user_posts.php");
    exit;
}

else {
    exit("Invalid action.");
}
?>
