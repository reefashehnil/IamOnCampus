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
$skill_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
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

// Normalize Availability_time to match dropdown format (e.g., "5:00 PM - 6:00 PM")
$db_time = $skill['Availability_time'] ?? '';
if ($db_time) {
    try {
        // Handle formats like "Weekdays 5-7 PM" or "5:00 PM - 6:00 PM"
        $db_time = preg_replace('/^Weekdays\s+/i', '', $db_time); // Remove "Weekdays"
        $db_time = str_replace(' - ', '-', $db_time); // Normalize separator
        $time_parts = explode('-', $db_time);
        if (count($time_parts) === 2) {
            $start_time = strtotime(trim($time_parts[0]));
            $end_time = strtotime(trim($time_parts[1]));
            if ($start_time !== false && $end_time !== false) {
                $db_time = date("g:00 A", $start_time) . " - " . date("g:00 A", $end_time);
            } else {
                $db_time = '';
            }
        } else {
            $db_time = '';
        }
    } catch (Exception $e) {
        $db_time = '';
        error_log("Error normalizing Availability_time: " . $e->getMessage());
    }
}
$skill['Availability_time'] = $db_time;

// Fetch users for dropdown
$users_result = $conn->query("SELECT User_id, F_name, L_name FROM Users ORDER BY F_name");
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[$row['User_id']] = $row['F_name'] . ' ' . $row['L_name'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = "Please fill in all required fields or select a valid availability time.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Skill (Admin) | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family: Arial;
            color: #e0e0e0;
            padding: 2rem;
        }
        .container {
            max-width: 600px;
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
        .form-control, .form-select {
            background: #2a2a4a;
            border: 1px solid #8b5cf6;
            color: #e0e0e0;
            padding-right: 30px;
        }
        .form-control::placeholder {
            color: #b0a8ff;
        }
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23d8b4fe'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2rem;
        }
        .form-select option {
            background: #2a2a4a;
            color: #e0e0e0;
        }
        .form-label {
            color: #d8b4fe;
        }
        .btn-primary {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-primary:hover {
            background-color: #a78bfa;
            border-color: #a78bfa;
        }
        .btn-warning {
            background: #ffca28;
            border-color: #ffca28;
            color: #000000ff;
        }
        .btn-warning:hover {
            background: #ffca28;
            border-color: #ffca28;
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
            color: #d8b4fe;
            font-size: 0.8rem;
            pointer-events: none;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Edit Skill (Admin)</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="skill_name" class="form-label">Skill Name *</label>
            <div class="select-wrapper">
                <select id="skill_name" name="skill_name" class="form-select" required>
                    <option value="" disabled <?= empty($_POST['skill_name']) && empty($skill['Skill_name']) ? 'selected' : '' ?>>-- Select Skill --</option>
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
                    $selected_skill = $_POST['skill_name'] ?? $skill['Skill_name'] ?? '';
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
            <textarea name="description" id="description" class="form-control" placeholder="Enter skill description"><?= htmlspecialchars($_POST['description'] ?? $skill['Skill_description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="availability" class="form-label">Availability Time *</label>
            <div class="select-wrapper">
                <select name="availability" id="availability" class="form-select" required>
                    <option value="" disabled <?= empty($_POST['availability']) && empty($skill['Availability_time']) ? 'selected' : '' ?>>-- Select Time Slot --</option>
                    <?php
                    $selected_time = $_POST['availability'] ?? $skill['Availability_time'] ?? '';
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
                    <option value="" disabled <?= empty($_POST['mode']) && empty($skill['Mode']) ? 'selected' : '' ?>>-- Select Mode --</option>
                    <?php
                    $modes = ['online' => 'Online', 'offline' => 'Offline', 'both' => 'Both'];
                    $selected_mode = $_POST['mode'] ?? $skill['Mode'] ?? '';
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
                    <option value="" disabled <?= empty($_POST['user_id']) && empty($skill['User_id']) ? 'selected' : '' ?>>-- Select User --</option>
                    <?php
                    $selected_user = $_POST['user_id'] ?? $skill['User_id'] ?? '';
                    foreach ($users as $id => $name) {
                        $selected = ($id == $selected_user) ? 'selected' : '';
                        echo "<option value='$id' $selected>" . htmlspecialchars($name) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Skill</button>
    </form>

    <a href="admin_skill.php" class="btn btn-warning mt-3 w-100">Back to Skill Management</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const availabilitySelect = document.getElementById('availability');
        const selectedTime = <?= json_encode($skill['Availability_time'] ?? '') ?>;
        if (selectedTime) {
            availabilitySelect.value = selectedTime;
        }
    });
</script>
</body>
</html>