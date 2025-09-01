<?php
session_start();
include '../Connection/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$skill_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$skill_id) {
    header("Location: my_skills.php?error=Invalid+skill+ID");
    exit;
}

// Verify ownership
$stmt = $conn->prepare("SELECT Skill_id FROM Skills WHERE Skill_id = ? AND User_id = ?");
$stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_skills.php?error=Skill+not+found+or+not+owned+by+you");
    exit;
}

// Delete skill
$stmt = $conn->prepare("DELETE FROM Skills WHERE Skill_id = ? AND User_id = ?");
$stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    header("Location: my_skills.php?msg=Skill+deleted+successfully");
    exit;
} else {
    header("Location: my_skills.php?error=Error+deleting+skill");
    exit;
}
?>