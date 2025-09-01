<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$skill_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$skill_id) {
    header("Location: view_skill.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM Skills WHERE Skill_id = ? AND User_id = ?");
$stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_skill.php");
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

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $skill_name = trim($_POST['skill_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $availability = trim($_POST['availability'] ?? '');
    $mode = trim($_POST['mode'] ?? '');

    // Generate valid time slots for validation
    $valid_slots = [];
    for ($i = 0; $i < 24; $i++) {
        $start = date("g:00 A", strtotime("$i:00"));
        $end = date("g:00 A", strtotime(($i + 1) . ":00"));
        $valid_slots[] = "$start - $end";
    }

    if ($skill_name && $mode && $availability && in_array($availability, $valid_slots)) {
        $stmt = $conn->prepare("UPDATE Skills SET Skill_name = ?, Skill_description = ?, Availability_time = ?, Mode = ? WHERE Skill_id = ? AND User_id = ?");
        $stmt->bind_param("ssssii", $skill_name, $description, $availability, $mode, $skill_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success = "Skill updated successfully.";
            // Update local skill variable for form display
            $skill['Skill_name'] = $skill_name;
            $skill['Skill_description'] = $description;
            $skill['Availability_time'] = $availability;
            $skill['Mode'] = $mode;
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
    <title>Edit Skill | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #2a1a3a);
            font-family: Arial;
            color: #fff;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
            background: #2c1e3f;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        h2 {
            color: #fff;
            text-align: center;
        }
        .form-control, .form-select {
            background: #3a2a5a;
            border: 1px solid #4a3066;
            color: #fff;
            padding-right: 30px;
        }
        .form-control::placeholder {
            color: #ccc;
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
            background: #3a2a5a;
            color: #fff;
        }
        .form-label {
            color: #fff;
        }
        .btn-primary {
            background: #4a3066;
            border: none;
            color: #fff;
        }
        .btn-primary:hover {
            background: #5a4080;
            color: #000000ff;
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
            background: #4a3066;
            color: #fff;
            border: 1px solid #5a4080;
        }
        .alert-danger {
            background: #ff6666;
            color: #fff;
            border: 1px solid #4a3066;
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
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Edit Skill</h2>

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

        <button type="submit" class="btn btn-primary w-100">Update Skill</button>
    </form>

    <a href="my_skills.php" class="btn btn-warning mt-3 w-100">Back to Skills</a>
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