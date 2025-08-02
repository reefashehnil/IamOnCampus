<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$request_id = $_GET['id'] ?? null;

if (!$request_id) {
    header("Location: view_skill_requests.php"); // or wherever you list requests
    exit;
}

// Delete only if this request belongs to the logged-in user
$stmt = $conn->prepare("DELETE FROM Skill_Requests WHERE Request_id = ? AND User_id = ?");
$stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
if ($stmt->execute()) {
    // Redirect back with success message
    header("Location: view_skill_requests.php?msg=Request+cancelled+successfully");
    exit;
} else {
    echo "Error cancelling request: " . $conn->error;
}
