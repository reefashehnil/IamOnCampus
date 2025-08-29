<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id === 0) {
        $_SESSION['error'] = "Invalid user ID.";
        header("Location: manage_users.php");
        exit;
    }

    // Prevent admin from deleting themselves
    if ($user_id === intval($_SESSION['user_id'])) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header("Location: manage_users.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM Users WHERE User_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user. Please try again.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: manage_users.php");
exit;