<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$skill_id = $_GET['id'] ?? null;
if (!$skill_id) {
    die("Error: No skill ID provided.");
}

// DEBUG: Show skill ID and user ID
echo "<p><strong>Debug Info:</strong></p>";
echo "<p>Skill ID to delete: " . htmlspecialchars($skill_id) . "</p>";
echo "<p>Logged-in User ID: " . htmlspecialchars($_SESSION['user_id']) . "</p>";

// Verify ownership before deletion
$stmt = $conn->prepare("SELECT Skill_id FROM Skills WHERE Skill_id = ? AND User_id = ?");
$stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<p style='color:red;'>Error: Skill not found or not owned by you.</p>");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("DELETE FROM Skills WHERE Skill_id = ? AND User_id = ?");
    $stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        header("Location: view_skill.php?msg=Skill+deleted+successfully");
        exit;
    } else {
        die("Error deleting skill: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Delete Skill | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2>Delete Skill</h2>

    <p>Are you sure you want to delete this skill?</p>
    <form method="POST">
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
        <a href="view_skill.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
