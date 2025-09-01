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
    $skill_name = trim($_POST['skill_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $availability = trim($_POST['availability'] ?? '');
    $mode = trim($_POST['mode'] ?? '');
    $user_id = (int)($_POST['user_id'] ?? 0);

    // Generate valid time slots for validation
    $valid_slots = [];
    for ($i = 0; $i < 24; $i++) {
        $start = date("g:00 A", strtotime("$i:00"));
        $end = date("g:00 A", strtotime(($i + 1) . ":00"));
        $valid_slots[] = "$start - $end";
    }

    if ($skill_name && $mode && $user_id && $availability && in_array($availability, $valid_slots)) {
        $stmt = $conn->prepare("INSERT INTO Skills (Skill_name, Skill_description, Availability_time, Mode, User_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $skill_name, $description, $availability, $mode, $user_id);
        if ($stmt->execute()) {
            $success = "Skill added successfully.";
        } else {
            $error = "Error adding skill: " . $conn->error;
        }
    } else {
        $error = "Please fill in all required fields or select a valid availability time.";
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
    <meta charset="UTF-8">
    <title>Admin Skill Management | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }
        .container {
            max-width: 900px;
            margin-top: 40px;
            padding: 30px;
            background: #2a2a4a;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
        }
        h2 {
            color: #d8b4fe;
            text-align: center;
        }
        .btn-success {
            background-color: #4a704a;
            border-color: #4a704a;
        }
        .btn-success:hover {
            background-color: #5a805a;
            border-color: #5a805a;
        }
        .btn-primary {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-primary:hover {
            background-color: #a78bfa;
            border-color: #a78bfa;
        }
        .btn-secondary {
            background-color: #4a4a6a;
            border-color: #4a4a6a;
        }
        .btn-secondary:hover {
            background-color: #5a5a7a;
            border-color: #5a5a7a;
        }
        .btn-warning {
            background-color: #e6e614;
            border-color: #e6e614;
            color: #1a1a2e;
        }
        .btn-warning:hover {
            background-color: #ffb300;
            border-color: #ffb300;
        }
        .btn-no-hover {
            background-color: #ffca28;
            border-color: #ffca28;
            color: #1a1a2e;
        }
        .btn-no-hover:hover {
            background-color: #ffca28;
            border-color: #ffca28;
            color: #1a1a2e;
        }
        .btn-danger {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }
        .btn-danger:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
        }
        .form-select, .form-control {
            background-color: #2a2a4a;
            color: #e0e0e0;
            border: 1px solid #8b5cf6;
        }
        .form-select:focus, .form-control:focus {
            background-color: #2a2a4a;
            color: #e0e0e0;
            border-color: #a78bfa;
            box-shadow: 0 0 5px rgba(167, 139, 250, 0.5);
        }
        .form-select::placeholder, .form-control::placeholder {
            color: #b0a8ff;
        }
        .form-label {
            color: #d8b4fe;
        }
        .alert-success {
            background-color: #4a704a;
            color: #d4edda;
            border-color: #4a704a;
        }
        .alert-danger {
            background-color: #703a4a;
            color: #f8d7da;
            border-color: #703a4a;
        }
        .table {
            background-color: #3a3a5a;
            color: #e0e0e0;
            border: 1px solid #8b5cf6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #4a4a6a;
        }
        .table-hover tbody tr:hover {
            background-color: #5a5a7a;
        }
        th {
            color: #d8b4fe;
            background-color: #2a2a4a;
            border-color: #8b5cf6;
        }
        td {
            color: #e0e0e0;
            border-color: #8b5cf6;
        }
        .table-light {
            background-color: #2a2a4a !important;
            color: #d8b4fe;
        }
        .border {
            border: 1px solid #8b5cf6 !important;
        }
        .select-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .select-wrapper::after {
            content: '\25BC';
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: #ccc;
            font-size: 0.8rem;
            pointer-events: none;
        }
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23fff'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2rem;
        }
        .form-select option {
            background: #2a2a4a;
            color: #e0e0e0;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Skill Management (Admin)</h2>

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
        <form method="POST" class="mb-4 border p-3 rounded">
            <input type="hidden" name="add_skill" value="1">
            <div class="mb-3">
                <label for="skill_name" class="form-label">Skill Name *</label>
                <div class="select-wrapper">
                    <select id="skill_name" name="skill_name" class="form-select" required>
                        <option value="" disabled <?= empty($_POST['skill_name']) ? 'selected' : '' ?>>-- Select Skill --</option>
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
                        $selected_skill = $_POST['skill_name'] ?? '';
                        foreach ($skills as $skill_option) {
                            $selected = ($skill_option === $selected_skill) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($skill_option) . "\" $selected>" . htmlspecialchars($skill_option) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" placeholder="Enter skill description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="availability" class="form-label">Availability Time *</label>
                <div class="select-wrapper">
                    <select name="availability" id="availability" class="form-select" required>
                        <option value="" disabled <?= empty($_POST['availability']) ? 'selected' : '' ?>>-- Select Time Slot --</option>
                        <?php
                        $selected_time = $_POST['availability'] ?? '';
                        // Debugging: Log the selected time
                        error_log("Selected time for availability dropdown: " . $selected_time);
                        for ($i = 0; $i < 24; $i++) {
                            $start = date("g:00 A", strtotime("$i:00"));
                            $end = date("g:00 A", strtotime(($i + 1) . ":00"));
                            $slot = "$start - $end";
                            $selected = ($slot === $selected_time) ? 'selected' : '';
                            echo "<option value='$slot' $selected>$slot</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="mode" class="form-label">Mode *</label>
                <div class="select-wrapper">
                    <select name="mode" id="mode" class="form-select" required>
                        <option value="" disabled <?= empty($_POST['mode']) ? 'selected' : '' ?>>-- Select Mode --</option>
                        <?php
                        $modes = ['online' => 'Online', 'offline' => 'Offline', 'both' => 'Both'];
                        $selected_mode = $_POST['mode'] ?? '';
                        foreach ($modes as $value => $label) {
                            $selected = ($value === $selected_mode) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">Assign to User *</label>
                <div class="select-wrapper">
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="" disabled <?= empty($_POST['user_id']) ? 'selected' : '' ?>>-- Select User --</option>
                        <?php
                        $selected_user = $_POST['user_id'] ?? '';
                        foreach ($users as $id => $name) {
                            $selected = ($id == $selected_user) ? 'selected' : '';
                            echo "<option value='$id' $selected>" . htmlspecialchars($name) . "</option>";
                        }
                        ?>
                    </select>
                </div>
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

    <a href="../Login/admin_dashboard.php" class="btn btn-no-hover mt-3">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>