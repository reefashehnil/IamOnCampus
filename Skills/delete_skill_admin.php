<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}

include '../Connection/db_connect.php';

$skill_id = $_GET['id'] ?? null;

if (!$skill_id) {
    header("Location: admin_skill.php");
    exit;
}

// Delete skill
$stmt = $conn->prepare("DELETE FROM Skills WHERE Skill_id = ?");
$stmt->bind_param("i", $skill_id);

if ($stmt->execute()) {
    // Optionally, you can add a success message via session or redirect with GET param
    header("Location: admin_skill.php?msg=Skill+deleted+successfully");
    exit;
} else {
    // On error, show message or redirect with error
    echo "Error deleting skill: " . $conn->error;
}
?>
