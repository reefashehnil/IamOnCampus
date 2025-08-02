<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $skill_name = trim($_POST['skill_name']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);
    $mode = $_POST['mode'];
    $user_id = $_SESSION['user_id']; // assign skill to current user

    if ($skill_name && $mode) {
        $stmt = $conn->prepare("INSERT INTO Skills (Skill_name, Skill_description, Availability_time, Mode, User_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $skill_name, $description, $availability, $mode, $user_id);
        if ($stmt->execute()) {
            $success = "Skill added successfully.";
        } else {
            $error = "Error adding skill: " . $conn->error;
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Skill | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2>Add New Skill</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
    <label for="skill_name" class="form-label">Skill Name *</label>
    <select id="skill_name" name="skill_name" class="form-select" required>
        <?php
        $skills = [
            "Public Speaking",
            "Creative Writing",
            "Mathematical Problem Solving",
            "Critical Thinking",
            "Research & Report Writing",
            "Programming (e.g., Python, C++)",
            "Graphic Design (e.g., Photoshop, Canva)",
            "Video Editing (e.g., Premiere Pro, CapCut)",
            "Web Development (HTML, CSS, JS)",
            "MS Excel / Google Sheets (Data Handling)",
            "English Speaking Practice",
            "Translation (e.g., Bengali ↔ English)",
            "Academic Presentation Preparation",
            "Resume/CV Writing & Review",
            "Debate Coaching",
            "Photography Basics",
            "Sketching & Drawing",
            "Playing a Musical Instrument (e.g., Guitar, Piano)",
            "DIY Crafts",
            "Content Creation (YouTube/Instagram Reels)"
        ];

        foreach ($skills as $skill_option) {
    $selected = ($skill_option === $skill['Skill_name']) ? 'selected' : '';
    echo "<option value=\"" . htmlspecialchars($skill_option) . "\" $selected>$skill_option</option>";
}

        ?>
    </select>
</div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Availability Time</label>
            <input type="text" name="availability" class="form-control">
        </div>
        <div class="mb-3">
            <label>Mode *</label>
            <select name="mode" class="form-select" required>
                <option value="">Select Mode</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="both">Both</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Add Skill</button>
    </form>

    <a href="../Login/dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
