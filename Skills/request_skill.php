<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$skill_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($skill_id <= 0) {
    die("Skill ID not provided in URL.");
}

// Fetch skill details
$stmt = $conn->prepare("SELECT Skill_id, Skill_name, User_id FROM Skills WHERE Skill_id = ?");
$stmt->bind_param("i", $skill_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Skill not found in database.");
}

$skill = $result->fetch_assoc();
$error = $success = "";

// Prevent requesting your own skill
if ($skill['User_id'] == $_SESSION['user_id']) {
    $error = "You cannot request your own skill.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requester_id = $_SESSION['user_id'];
    $owner_id = $skill['User_id'];

    // Check for duplicate request
    $check_stmt = $conn->prepare("SELECT Request_id FROM Skill_Requests WHERE Skill_id = ? AND User_id = ?");
    $check_stmt->bind_param("ii", $skill_id, $requester_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "You have already requested this skill.";
    } else {
        // Insert request
        $insert_stmt = $conn->prepare("INSERT INTO Skill_Requests (Skill_id, User_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $skill_id, $requester_id);
        if ($insert_stmt->execute()) {
            // Insert notification
            $message = "New request for your skill '" . $skill['Skill_name'] . "' by user ID " . $requester_id;
            $notif_stmt = $conn->prepare("INSERT INTO Notifications (Message, User_id) VALUES (?, ?)");
            $notif_stmt->bind_param("si", $message, $owner_id);
            $notif_stmt->execute();

            $success = "Request sent successfully.";
        } else {
            $error = "Error sending request: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Skill | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2>Request Skill: <?= htmlspecialchars($skill['Skill_name']) ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!$success && !$error): ?>
        <form method="POST">
            <p>Are you sure you want to request this skill?</p>
            <button type="submit" class="btn btn-primary">Send Request</button>
            <a href="available_skills.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php else: ?>
        <a href="available_skills.php" class="btn btn-secondary mt-3">Back to Available Skills</a>
    <?php endif; ?>
</div>
</body>
</html>