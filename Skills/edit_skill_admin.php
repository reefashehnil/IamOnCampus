<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}

include '../Connection/db_connect.php';

$error = "";
$success = "";

// Get skill ID from query string
$skill_id = $_GET['id'] ?? null;
if (!$skill_id) {
    header("Location: admin_skill.php");
    exit;
}

// Fetch skill data
$stmt = $conn->prepare("SELECT * FROM Skills WHERE Skill_id = ?");
$stmt->bind_param("i", $skill_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_skill.php");
    exit;
}
$skill = $result->fetch_assoc();

// Fetch users for dropdown
$users_result = $conn->query("SELECT User_id, F_name, L_name FROM Users ORDER BY F_name");
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[$row['User_id']] = $row['F_name'] . ' ' . $row['L_name'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill_name = trim($_POST['skill_name']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);
    $mode = $_POST['mode'];
    $user_id = $_POST['user_id'];

    if ($skill_name && $mode && $user_id) {
        $stmt = $conn->prepare("UPDATE Skills SET Skill_name = ?, Skill_description = ?, Availability_time = ?, Mode = ?, User_id = ? WHERE Skill_id = ?");
        $stmt->bind_param("ssssii", $skill_name, $description, $availability, $mode, $user_id, $skill_id);

        if ($stmt->execute()) {
            $success = "Skill updated successfully.";
            // Update local skill variable for form display
            $skill['Skill_name'] = $skill_name;
            $skill['Skill_description'] = $description;
            $skill['Availability_time'] = $availability;
            $skill['Mode'] = $mode;
            $skill['User_id'] = $user_id;
        } else {
            $error = "Error updating skill: " . $conn->error;
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
<title>Edit Skill (Admin) | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-4 text-primary">Edit Skill (Admin)</h2>

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
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($skill['Skill_description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Availability Time</label>
            <input type="text" name="availability" class="form-control"
                   value="<?= htmlspecialchars($skill['Availability_time']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Mode *</label>
            <select name="mode" class="form-select" required>
                <option value="">Select Mode</option>
                <option value="online" <?= $skill['Mode'] === 'online' ? 'selected' : '' ?>>Online</option>
                <option value="offline" <?= $skill['Mode'] === 'offline' ? 'selected' : '' ?>>Offline</option>
                <option value="both" <?= $skill['Mode'] === 'both' ? 'selected' : '' ?>>Both</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Assign to User *</label>
            <select name="user_id" class="form-select" required>
                <option value="">Select User</option>
                <?php foreach ($users as $id => $name): ?>
                    <option value="<?= $id ?>" <?= ($skill['User_id'] == $id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Skill</button>
    </form>

    <a href="admin_skill.php" class="btn btn-secondary mt-3">Back to Skill Management</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
