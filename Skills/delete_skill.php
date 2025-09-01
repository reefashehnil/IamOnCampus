<?php
ob_start();
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

// Verify ownership before deletion
$stmt = $conn->prepare("SELECT Skill_id FROM Skills WHERE Skill_id = ? AND User_id = ?");
$stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<p style='color:red;'>Error: Skill not found or not owned by you.</p>");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Start a transaction to ensure data consistency
    $conn->begin_transaction();
    try {
        // Delete related records in skill_requests
        $stmt = $conn->prepare("DELETE FROM skill_requests WHERE Skill_id = ?");
        $stmt->bind_param("i", $skill_id);
        $stmt->execute();

        // Delete the skill from Skills table
        $stmt = $conn->prepare("DELETE FROM Skills WHERE Skill_id = ? AND User_id = ?");
        $stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();
        header("Location: my_skills.php?msg=Skill+deleted+successfully");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        die("Error deleting skill: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Delete Skill | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    body {
        background: linear-gradient(135deg, #2c003e, #4b0082);
        color: #ffffff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .container {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 20px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    h2 {
        color: #e0b0ff;
    }
    .btn-danger {
        background-color: #c71585;
        border-color: #c71585;
    }
    .btn-danger:hover {
        background-color: #db7093;
        border-color: #db7093;
    }
    .btn-secondary {
        background-color: #4b0082;
        border-color: #4b0082;
    }
    .btn-secondary:hover {
        background-color: #6a0dad;
        border-color: #6a0dad;
    }
</style>
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2>Delete Skill</h2>
    <p>Are you sure you want to delete this skill? This will also remove related requests.</p>
    <form method="POST" onsubmit="document.getElementById('deleteBtn').disabled = true;">
        <button type="submit" id="deleteBtn" class="btn btn-danger">Yes, Delete</button>
        <a href="my_skills.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
<?php ob_end_flush(); ?>