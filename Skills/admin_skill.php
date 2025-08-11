<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}

include '../Connection/db_connect.php';

$error = "";
$success = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Skills WHERE Skill_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        // Redirect with success message to avoid resubmission on refresh
        header("Location: admin_skill.php?msg=Skill+deleted+successfully");
        exit;
    } else {
        $error = "Error deleting skill: " . $conn->error;
    }
}

// Show success message from redirect if exists
if (isset($_GET['msg'])) {
    $success = htmlspecialchars($_GET['msg']);
}

// Handle Add Skill Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    $skill_name = trim($_POST['skill_name']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);
    $mode = $_POST['mode'];
    $user_id = $_POST['user_id'];

    if ($skill_name && $mode && $user_id) {
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

// Fetch all skills with user names
$sql = "SELECT s.Skill_id, s.Skill_name, s.Skill_description, s.Availability_time, s.Mode, u.F_name, u.L_name 
        FROM Skills s JOIN Users u ON s.User_id = u.User_id
        ORDER BY s.Skill_id ASC";
$result = $conn->query($sql);

// Fetch all users for add/edit dropdowns
$users_result = $conn->query("SELECT User_id, F_name, L_name FROM Users ORDER BY F_name");
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[$row['User_id']] = $row['F_name'] . ' ' . $row['L_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Skill Management | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4" style="max-width: 900px;">
    <h2 class="mb-4 text-primary">Skill Management (Admin)</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Add Skill Form -->
    <button class="btn btn-success mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#addSkillForm" aria-expanded="false" aria-controls="addSkillForm">
        + Add New Skill
    </button>
    <div class="collapse" id="addSkillForm">
        <form method="POST" class="mb-4 border p-3 rounded bg-light">
            <input type="hidden" name="add_skill" value="1" />
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
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Availability Time</label>
                <input type="text" name="availability" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Mode *</label>
                <select name="mode" class="form-select" required>
                    <option value="">Select Mode</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="both">Both</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Assign to User *</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Skill</button>
        </form>
    </div>

    <!-- Skill Table -->
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Skill Name</th>
                <th>Description</th>
                <th>Availability</th>
                <th>Mode</th>
                <th>Owner</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['Skill_id'] ?></td>
                <td><?= htmlspecialchars($row['Skill_name']) ?></td>
                <td><?= htmlspecialchars($row['Skill_description']) ?></td>
                <td><?= htmlspecialchars($row['Availability_time']) ?></td>
                <td><?= htmlspecialchars(ucfirst($row['Mode'])) ?></td>
                <td><?= htmlspecialchars($row['F_name'] . ' ' . $row['L_name']) ?></td>
                <td>
    <div class="d-flex gap-2">
        <a href="edit_skill_admin.php?id=<?= $row['Skill_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        <a href="admin_skill.php?delete=<?= $row['Skill_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this skill?');">Delete</a>
    </div>
</td>

            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">No skills found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="../Login/admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
